<?php

namespace App\Support;

use Normalizer;

class StudentNameBlindIndex
{
    private const PREFIX_MIN_LENGTH = 2;

    private const PREFIX_MAX_LENGTH = 24;

    public static function forValue(?string $value): ?string
    {
        $terms = static::storageTerms($value);

        if ($terms === []) {
            return null;
        }

        return '|'.implode('|', array_map(static fn (string $term): string => static::hash($term), $terms)).'|';
    }

    /**
     * Return hashed search terms that can be AND-combined in SQL.
     *
     * @return array<int, string>
     */
    public static function queryHashes(?string $value): array
    {
        return array_values(array_unique(array_map(
            static fn (string $term): string => static::hash($term),
            static::queryTerms($value)
        )));
    }

    /**
     * @return array<int, string>
     */
    public static function storageTerms(?string $value): array
    {
        $normalized = static::normalize($value);

        if ($normalized === '') {
            return [];
        }

        $terms = [$normalized];

        foreach (static::splitTokens($normalized) as $token) {
            $terms[] = $token;

            $length = min(mb_strlen($token, 'UTF-8'), self::PREFIX_MAX_LENGTH);
            for ($prefixLength = self::PREFIX_MIN_LENGTH; $prefixLength <= $length; $prefixLength++) {
                $terms[] = mb_substr($token, 0, $prefixLength, 'UTF-8');
            }
        }

        return array_values(array_unique($terms));
    }

    /**
     * @return array<int, string>
     */
    public static function queryTerms(?string $value): array
    {
        $normalized = static::normalize($value);

        if ($normalized === '') {
            return [];
        }

        $tokens = static::splitTokens($normalized);
        $terms = count($tokens) > 1 ? [$normalized] : [];

        foreach ($tokens as $token) {
            if (mb_strlen($token, 'UTF-8') >= self::PREFIX_MIN_LENGTH) {
                $terms[] = $token;
            }
        }

        return array_values(array_unique($terms));
    }

    public static function normalize(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (class_exists(Normalizer::class)) {
            $decomposed = Normalizer::normalize($value, Normalizer::FORM_D);
            if (is_string($decomposed)) {
                $value = preg_replace('/\p{Mn}+/u', '', $decomposed) ?? $decomposed;
            }
        }

        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    /**
     * @return array<int, string>
     */
    private static function splitTokens(string $value): array
    {
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $value, -1, PREG_SPLIT_NO_EMPTY);

        return $tokens === false ? [] : array_values(array_unique($tokens));
    }

    private static function hash(string $term): string
    {
        return hash_hmac('sha256', $term, static::secret());
    }

    private static function secret(): string
    {
        return (string) config('security.blind_indexes.student_names_key', config('app.key'));
    }
}
