<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseActivation extends Model
{
    protected $fillable = [
        'license_id',
        'request_ip',
        'request_domain',
        'status',
        'failure_reason',
    ];

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
