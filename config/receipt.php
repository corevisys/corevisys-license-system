<?php

return [
    'storage_disk' => env('RECEIPT_DISK', env('RECEIPTS_STORAGE_DISK', 'local')),
    'retention_days' => (int) env('RECEIPT_RETENTION_DAYS', 90),
    'max_upload_size_kb' => (int) env('RECEIPT_MAX_UPLOAD_SIZE_KB', 2048),
    'allowed_mimes' => array_filter(array_map('trim', explode(',', env('RECEIPT_ALLOWED_MIME_TYPES', 'pdf,jpg,png,jpeg')))),
    'scan_endpoint' => env('RECEIPT_SCAN_ENDPOINT'),
    'scan_api_key' => env('RECEIPT_SCAN_API_KEY'),
];
