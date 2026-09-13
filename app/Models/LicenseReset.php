<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseReset extends Model
{
    public $timestamps = false; // We use created_at only (useCurrent)

    protected $fillable = [
        'license_id',
        'admin_id',
        'reason',
        'previous_domain',
        'previous_fingerprint',
        'ip_address'
    ];

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
