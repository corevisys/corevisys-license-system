<?php

return [
    'offline_validity_days' => (int) env('OFFLINE_VALIDITY_DAYS', 7),
    'pulse_interval_days' => (int) env('PULSE_INTERVAL_DAYS', env('LICENSE_PULSE_INTERVAL_DAYS', 30)),
    'pulse_grace_days' => (int) env('PULSE_GRACE_DAYS', env('LICENSE_PULSE_GRACE_DAYS', 7)),

    /*
    |--------------------------------------------------------------------------
    | Activation history pagination
    |--------------------------------------------------------------------------
    |
    | The /api/v1/license/history endpoint is always paginated. Clients may
    | omit `per_page`, but a small default is enforced so large activation
    | histories are never fully deserialized by accident.
    |
    */
    'history_default_per_page' => (int) env('LICENSE_HISTORY_DEFAULT_PER_PAGE', 15),
    'history_max_per_page' => (int) env('LICENSE_HISTORY_MAX_PER_PAGE', 100),
];
