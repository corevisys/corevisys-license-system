<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessedWebhook extends Model
{
    use HasFactory;

    protected $fillable = ['gateway', 'event_id', 'payload', 'processed_at'];

    protected $casts = [
        'processed_at' => 'datetime',
        'payload' => 'array'
    ];
}
