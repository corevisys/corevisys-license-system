<?php

namespace App\Services;

use App\Models\License;
use Exception;

class LicenseStateMachine
{
    /**
     * Allowed transitions Map.
     * From => [To]
     */
    protected const TRANSITIONS = [
        'inactive' => ['active', 'suspended', 'revoked'], // Activation or Admin Ban
        'active' => ['expired', 'suspended', 'revoked'], // Expiry or Admin Ban
        'expired' => ['active', 'suspended', 'revoked'], // Renewal or Admin Ban
        'suspended' => ['active', 'expired', 'revoked'], // Admin Unban or Background Expiry
        'revoked' => ['active', 'expired'], // Explicit recovery after administrative review
    ];

    public function transition(License $license, string $toStatus)
    {
        if ($license->status === $toStatus) {
            return; // No change
        }

        if (!$this->canTransition($license->status, $toStatus)) {
            throw new Exception("Invalid license state transition from {$license->status} to {$toStatus}.");
        }

        $from = $license->status;
        $license->update(['status' => $toStatus]);

        \App\Services\AuditService::log(
            'license_state_transition',
            $license,
            ['status' => $from],
            ['status' => $toStatus]
        );
    }

    public function canTransition(string $from, string $to): bool
    {
        // Allow anything? No.
        $allowed = self::TRANSITIONS[$from] ?? [];
        return in_array($to, $allowed);
    }
}
