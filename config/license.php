<?php

return [
    'offline_validity_days' => (int) env('OFFLINE_VALIDITY_DAYS', 7),
    'pulse_interval_days' => (int) env('PULSE_INTERVAL_DAYS', env('LICENSE_PULSE_INTERVAL_DAYS', 30)),
    'pulse_grace_days' => (int) env('PULSE_GRACE_DAYS', env('LICENSE_PULSE_GRACE_DAYS', 7)),
];
