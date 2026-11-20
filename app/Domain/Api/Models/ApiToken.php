<?php

namespace App\Domain\Api\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * API token (ADR-0015): Bearer token; yalnız sha256 hash saklanır.
 * Scopes allowlist; expiry + revocation.
 *
 * @property Carbon|null $expires_at
 * @property Carbon|null $revoked_at
 */
class ApiToken extends Model
{
    public const SCOPE_ALLOWLIST = ['customers.read', 'customers.write', 'projects.read', 'webhooks.manage'];

    protected $fillable = ['name', 'token_hash', 'scopes', 'expires_at', 'revoked_at', 'created_by'];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValid(): bool
    {
        return $this->revoked_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? [], true);
    }

    public static function hash(string $plaintext): string
    {
        return hash('sha256', $plaintext);
    }
}
