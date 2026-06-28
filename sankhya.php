<?php

return [
    'base_url' => getenv('SANKHYA_BASE_URL') ?: 'https://api.sankhya.com.br',
    'token' => getenv('SANKHYA_TOKEN') ?: '',
    'appkey' => getenv('SANKHYA_APPKEY') ?: '',

    'codemp' => (int)(getenv('SANKHYA_CODEMP') ?: 1),
    'codtipoper' => (int)(getenv('SANKHYA_TOP') ?: 102),
    'codtipvenda' => (int)(getenv('SANKHYA_CODTIPVENDA') ?: 1),
    'codvend' => (int)(getenv('SANKHYA_CODVEND') ?: 0),
    'codnat' => (int)(getenv('SANKHYA_CODNAT') ?: 0),
    'codcencus' => (int)(getenv('SANKHYA_CODCENCUS') ?: 0),
    'codlocalorig' => (int)(getenv('SANKHYA_CODLOCALORIG') ?: 0),
];
