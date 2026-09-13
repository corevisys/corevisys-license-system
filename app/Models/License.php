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
        'license_key',
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
}
