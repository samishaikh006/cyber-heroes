<?php
declare(strict_types=1);

// Application settings. Keep secrets in environment variables.
define('APP_NAME', 'Cyber Security Comic Book');
define('APP_SHORT_NAME', 'Cyber Heroes');
define('BASE_PATH', dirname(__DIR__));

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
