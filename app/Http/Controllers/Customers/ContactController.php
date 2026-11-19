<?php

namespace App\Http\Controllers\Customers;

use App\Domain\Audit\AuditLogger;
use App\Domain\Customers\Models\Contact;
use App\Domain\Customers\Models\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $contact = $customer->contacts()->create($validated);

        if ($validated['is_primary'] ?? false) {
            // Tek birincil yetkili: diğerlerini pasifleştir
            $customer->contacts()->where('id', '!=', $contact->id)->update(['is_primary' => false]);
        }

        app(AuditLogger::class)->logModelChange('created', $contact);

        return back()->with('success', 'Yetkili eklendi.');
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $this->authorize('update', $contact->customer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $original = $contact->getAttributes();
        $contact->update($validated);

        if ($validated['is_primary'] ?? false) {
            $contact->customer->contacts()->where('id', '!=', $contact->id)->update(['is_primary' => false]);
        }

        app(AuditLogger::class)->logModelChange('updated', $contact, $original);

        return back()->with('success', 'Yetkili güncellendi.');
    }

    /**
     * Arşivleme (soft delete): müşteri geçmişinde yetkili bilgisi korunur.
     */
    public function archive(Contact $contact): RedirectResponse
    {
        $this->authorize('update', $contact->customer);

        $original = $contact->getAttributes();
        $contact->delete();

        app(AuditLogger::class)->logModelChange('archived', $contact, $original);

        return back()->with('success', 'Yetkili kaydı arşivlendi.');
    }
}
