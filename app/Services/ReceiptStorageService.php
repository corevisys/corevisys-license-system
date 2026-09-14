<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReceiptStorageService
{
    public function storeUploadedReceipt(UploadedFile $file): string
    {
        $this->validateUploadedReceipt($file);

        $scanEndpoint = (string) config('receipt.scan_endpoint', '');
        if ($scanEndpoint !== '') {
            $this->scanReceipt($file, $scanEndpoint);
        }

        $disk = config('receipt.storage_disk', 'local');
        $filename = $this->buildReceiptFilename($file);
        $directory = 'receipts';

        try {
            $stored = Storage::disk($disk)->putFileAs($directory, $file, $filename);
        } catch (\Throwable $e) {
            if ($disk === 'receipts') {
                Log::warning('Receipt storage disk fallback triggered', [
                    'fallback_disk' => 'local',
                    'error' => $e->getMessage(),
                ]);

                $stored = Storage::disk('local')->putFileAs($directory, $file, $filename);
            } else {
                throw new \RuntimeException('Receipt storage failed.', 0, $e);
            }
        }

        if ($stored === false) {
            throw new \RuntimeException('Receipt storage failed.');
        }

        return $stored;
    }

    public function pruneExpiredReceipts(?int $days = null): int
    {
        $retentionDays = $days ?? (int) config('receipt.retention_days', 90);
        $cutoff = now()->subDays($retentionDays);

        $payments = Payment::whereNotNull('payment_proof_path')
            ->where('created_at', '<', $cutoff)
            ->get();

        $disk = config('receipt.storage_disk', 'receipts');
        $deleted = 0;

        foreach ($payments as $payment) {
            try {
                if ($payment->payment_proof_path) {
                    Storage::disk($disk)->delete($payment->payment_proof_path);
                }

                $payment->delete();
                $deleted++;
            } catch (\Throwable $e) {
                Log::error('Receipt retention cleanup failed', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $deleted;
    }

    protected function validateUploadedReceipt(UploadedFile $file): void
    {
        $limitKb = (int) config('receipt.max_upload_size_kb', 2048);
        $allowedMimeTypes = config('receipt.allowed_mimes', ['pdf', 'jpg', 'png', 'jpeg']);

        if ($file->getSize() > ($limitKb * 1024)) {
            throw new \RuntimeException('Receipt size exceeds the allowed limit.');
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: '');
        $mimeType = strtolower($file->getMimeType() ?: '');
        $allowedMimeMap = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
        ];

        $allowed = $allowedMimeTypes;
        if (! in_array($extension, $allowed, true) && ! in_array($mimeType, $allowedMimeMap[$extension] ?? [], true)) {
            throw new \RuntimeException('Receipt file type is not allowed.');
        }
    }

    protected function scanReceipt(UploadedFile $file, string $scanEndpoint): void
    {
        $apiKey = (string) config('receipt.scan_api_key', '');

        $response = Http::withHeaders(array_filter([
            'Authorization' => $apiKey !== '' ? 'Bearer ' . $apiKey : null,
            'Accept' => 'application/json',
        ]))->attach(
            'file',
            fopen($file->getPathname(), 'rb'),
            $file->getClientOriginalName()
        )->post($scanEndpoint);

        if ($response->failed()) {
            throw new \RuntimeException('Receipt malware scan failed.');
        }

        $payload = $response->json();
        $isClean = $payload['clean'] ?? $payload['status'] ?? true;

        if ($isClean === false || strtolower((string) $isClean) === 'malware') {
            throw new \RuntimeException('Receipt failed malware scan.');
        }
    }

    protected function buildReceiptFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'bin';

        return sprintf('%s.%s', md5(uniqid((string) now()->timestamp, true)), $extension);
    }
}
