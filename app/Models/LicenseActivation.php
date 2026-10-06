<?php

namespace App\Models;

use App\Support\DomainNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LicenseActivation extends Model
{
    protected $fillable = [
        'license_id',
        'request_ip',
        'request_domain',
        'request_domain_normalized',
        'status',
        'failure_reason',
    ];

    /**
     * Keep the persisted normalized domain in sync with the raw request domain.
     *
     * This mirrors DomainNormalizer::normalize() so indexed lookups on
     * `request_domain_normalized` return the same results as the previous
     * in-memory normalisation, without loading rows into PHP.
     */
    protected static function booted(): void
    {
        static::saving(function (LicenseActivation $activation) {
            $activation->request_domain_normalized = DomainNormalizer::normalize(
                $activation->request_domain
            );
        });
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Scope: successful activations whose normalized domain matches the given
     * (already normalised) domain.
     */
    public function scopeForNormalizedDomain(Builder $query, ?string $normalizedDomain): Builder
    {
        return $query
            ->where('status', 'success')
            ->where('request_domain_normalized', $normalizedDomain);
    }
}
