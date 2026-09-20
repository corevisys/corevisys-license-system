<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class License extends Model
{
    use HasFactory, HasUuids;

    public $raw_key; // Non-persistent property

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'license_key_hash',
        'secret_salt',
        'status',
        'reset_count',
        'activation_limit',
        'type',
        'auto_renew',
        'next_billing_at',
        'gateway_subscription_id',
        'bound_domain',
        'bound_ip',
        'bound_fingerprint',
        'fingerprint_missing_grace',
        'activated_at',
        'expires_at',
        'grace_expires_at',
        'last_check_at',
        'enforcement_mode',
        'team_id',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'grace_expires_at' => 'datetime',
        'last_check_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'auto_renew' => 'boolean',
        'fingerprint_missing_grace' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function activations()
    {
        return $this->hasMany(LicenseActivation::class);
    }

    public function resets()
    {
        return $this->hasMany(LicenseReset::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Determine whether the license is "running" (client is actively checking in).
     *
     * A license is considered running when its last heartbeat (`last_check_at`)
     * falls within the allowed window:
     *   pulse_interval_days (how often the client is expected to call /pulse)
     *   + pulse_grace_days  (tolerance for late check-ins)
     *
     * Both values come from config/license.php, which reads OFFLINE_VALIDITY_DAYS,
     * LICENSE_PULSE_INTERVAL_DAYS, and LICENSE_PULSE_GRACE_DAYS from .env.
     *
     * A license that has NEVER sent a pulse (last_check_at is null) is NOT running.
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        if (!$this->last_check_at) {
            return false;
        }

        $windowDays = (int) config('license.pulse_interval_days', 30)
                    + (int) config('license.pulse_grace_days', 7);

        return $this->last_check_at->gte(now()->subDays($windowDays));
    }
}
