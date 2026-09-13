<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notify_via_email',
        'notify_via_sms',
        'notify_via_push',
        'phone_number',
        'fcm_token',
    ];

    protected $casts = [
        'notify_via_email' => 'boolean',
        'notify_via_sms' => 'boolean',
        'notify_via_push' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
