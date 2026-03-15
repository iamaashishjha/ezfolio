<?php

declare(strict_types=1);

namespace App\Support;

final class EnvEditor
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = env($key, $default);

        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? (string) $value : $default;
    }

    public static function set(string $key, ?string $value): bool
    {
        return self::setMany(array($key => $value));
    }

    public static function setMany(array $pairs): bool
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return false;
        }

        $content = (string) file_get_contents($envPath);

        foreach ($pairs as $key => $value) {
            $key = (string) $key;
            $line = $key . '=' . self::formatValue($value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*/m';

            if (preg_match($pattern, $content) === 1) {
                $content = (string) preg_replace($pattern, $line, $content);
            } else {
                if ($content !== '' && substr($content, -1) !== "\n") {
                    $content .= "\n";
                }
                $content .= $line . "\n";
            }

            putenv($line);
            $_ENV[$key] = (string) ($value ?? '');
            $_SERVER[$key] = (string) ($value ?? '');
        }

        return file_put_contents($envPath, $content, LOCK_EX) !== false;
    }

    private static function formatValue(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value === '') {
            return '""';
        }

        if (preg_match('/\s|#/', $value) === 1) {
            return '"' . addcslashes($value, '"\\') . '"';
        }

        return $value;
    }
}
