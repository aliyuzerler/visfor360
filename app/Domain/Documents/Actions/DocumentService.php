<?php

namespace App\Domain\Documents\Actions;

use App\Domain\Audit\AuditLogger;
use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Models\Document;
use App\Domain\Documents\Models\DocumentType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Evrak yükleme/sürümleme servisi (Prompt 05).
 * Güvenlik: private disk, rastgele storage key, MIME/uzantı/boyut allowlist,
 * orijinal ad yalnızca metadata. Dosyalar asla public root'a yazılmaz.
 */
final class DocumentService
{
    /** İzinli MIME türleri (müşteri kararıyla genişletilebilir) */
    private const ALLOWED_MIMES = [
        'application/pdf' => 'pdf',
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    ];

    /** Maksimum 10 MB (müşteri kararıyla değişebilir) */
    private const MAX_SIZE_BYTES = 10 * 1024 * 1024;

    /**
     * @return Document yeni (aktif) sürüm
     */
    public function upload(
        Customer $customer,
        DocumentType $type,
        UploadedFile $file,
        ?User $actor,
        ?\DateTimeInterface $validUntil = null,
    ): Document {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw ValidationException::withMessages(['file' => __('Dosya yükleme başarısız.')]);
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw ValidationException::withMessages(['file' => __('Dosya 10 MB sınırını aşıyor.')]);
        }

        $mime = $file->getMimeType() ?? '';
        if (! isset(self::ALLOWED_MIMES[$mime])) {
            throw ValidationException::withMessages(['file' => __('İzinli dosya türleri: PDF, PNG, JPG, DOC, DOCX, XLS, XLSX.')]);
        }

        // Orijinal adı güvenli hale getir (metadata olarak saklanır)
        $originalName = $this->sanitizeOriginalName($file->getClientOriginalName() ?: 'dosya');

        $storageKey = 'documents/'.$customer->id.'/'.Str::uuid()->toString().'.'.self::ALLOWED_MIMES[$mime];
        $file->storeAs('', $storageKey, ['disk' => 'local', 'visibility' => 'private']);

        // Aynı tipin aktif sürümünü supersede et (sürümler korunur)
        $previous = Document::query()
            ->where('customer_id', $customer->id)
            ->where('document_type_id', $type->id)
            ->whereNull('superseded_at')
            ->first();

        $document = Document::create([
            'customer_id' => $customer->id,
            'document_type_id' => $type->id,
            'uploaded_by' => $actor?->id,
            'original_name' => $originalName,
            'storage_key' => $storageKey,
            'mime_type' => $mime,
            'size_bytes' => $file->getSize(),
            'valid_until' => $validUntil,
        ]);

        if ($previous !== null) {
            $previous->update(['superseded_at' => now(), 'superseded_by' => $document->id]);
            app(AuditLogger::class)->log('superseded', $previous, null, ['superseded_by' => $document->id]);
        }

        app(AuditLogger::class)->log('created', $document);

        return $document;
    }

    /**
     * Private diskten güvenli indirme akışı (controller policy sonrası çağırır).
     *
     * @return array{content: string, mime: string, name: string}
     */
    public function download(Document $document): array
    {
        if (! Storage::disk('local')->exists($document->storage_key)) {
            abort(404);
        }

        return [
            'content' => Storage::disk('local')->get($document->storage_key),
            'mime' => $document->mime_type,
            'name' => $document->original_name,
        ];
    }

    /**
     * Eksik zorunlu evrak listesi: aktif ve zorunlu tip olup aktif belgesi olmayanlar.
     *
     * @return list<array<string, mixed>>
     */
    public function statusFor(Customer $customer): array
    {
        $types = DocumentType::query()->where('is_active', true)->orderBy('label')->get();
        $activeDocs = Document::query()
            ->where('customer_id', $customer->id)
            ->whereNull('superseded_at')
            ->get()
            ->groupBy('document_type_id');

        $rows = [];

        foreach ($types as $type) {
            $document = $activeDocs->get($type->id)?->first();

            $status = match (true) {
                $document === null && $type->required => 'missing',
                $document?->isExpired() => 'expired',
                $document?->isExpiringSoon($type->validity_warn_days) => 'expiring_soon',
                $document !== null => 'valid',
                default => 'optional_missing',
            };

            $rows[] = ['type' => $type, 'status' => $status, 'document' => $document];
        }

        return $rows;
    }

    private function sanitizeOriginalName(string $name): string
    {
        $name = str_replace(['/', '\\', '..'], '_', $name);

        return Str::limit(mb_substr($name, 0, 200), 200, '');
    }
}
