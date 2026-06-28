<?php

return [
    'env' => getenv('APP_ENV') ?: 'local',
    'sandbox' => filter_var(getenv('APP_SANDBOX') ?: 'true', FILTER_VALIDATE_BOOLEAN),
];
