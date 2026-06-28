<?php

return [
    'app' => [
        'name' => 'Importador XML Sankhya',
        'sandbox' => filter_var(getenv('APP_SANDBOX') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    ],

    'sankhya' => [
        'base_url' => rtrim(getenv('SANKHYA_BASE_URL') ?: 'https://api.sankhya.com.br', '/'),
        'token' => getenv('SANKHYA_TOKEN') ?: '',
        'appkey' => getenv('SANKHYA_APPKEY') ?: '',
        'username' => getenv('SANKHYA_USERNAME') ?: '',
        'password' => getenv('SANKHYA_PASSWORD') ?: '',
        'codtipoper' => (int)(getenv('SANKHYA_CODTIPOPER') ?: 102),
        'codemp' => (int)(getenv('SANKHYA_CODEMP') ?: 1),
        'codnat' => (int)(getenv('SANKHYA_CODNAT') ?: 0),
        'codcencus' => (int)(getenv('SANKHYA_CODCENCUS') ?: 0),
        'codproj' => (int)(getenv('SANKHYA_CODPROJ') ?: 0),
    ],
];
