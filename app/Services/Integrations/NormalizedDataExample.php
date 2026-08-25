<?php

namespace App\Services\Integrations;

class NormalizedDataExample
{
    /**
     * Normalizes an external raw manpower record response into a consistent internal OneClick structure.
     * Demonstrates how "completelyDifferentFieldNames" or raw types are adapted before reaching components.
     *
     * @param array $rawExternalPayload Raw response payload from the external source
     * @return array Normalized internal data format
     */
    public static function normalizeManpowerData(array $rawExternalPayload): array
    {
        $firstName = $rawExternalPayload['emp_first_name'] ?? '';
        $lastName = $rawExternalPayload['emp_last_name'] ?? '';

        return [
            'nrp' => $rawExternalPayload['raw_nrp'] ?? $rawExternalPayload['nrp'] ?? 'Unknown',
            'full_name' => trim("{$firstName} {$lastName}"),
            'role' => $rawExternalPayload['assigned_role_name'] ?? 'view',
            'status' => ($rawExternalPayload['active_state_val'] ?? 1) === 1 ? 'active' : 'inactive',
            'meta' => [
                'synchronized_at' => now()->toIso8601String(),
                'provider' => 'external_hr_api',
                'raw_reference_id' => $rawExternalPayload['completelyDifferentFieldNames']['id'] ?? null,
            ],
        ];
    }
}
