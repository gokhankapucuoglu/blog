<?php

namespace App\Casts;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TitleCaseCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $value) {
            return null;
        }

        $value = Str::squish($value);

        $lower = mb_strtolower(str_replace(['I', 'İ'], ['ı', 'i'], $value), 'UTF-8');

        $words = explode(' ', $lower);

        foreach ($words as &$word) {
            if (empty($word)) continue;

            $firstChar = mb_substr($word, 0, 1, 'UTF-8');
            $rest = mb_substr($word, 1, null, 'UTF-8');

            $firstChar = match ($firstChar) {
                'i' => 'İ',
                'ı' => 'I',
                default => mb_strtoupper($firstChar, 'UTF-8'),
            };

            $word = $firstChar . $rest;
        }

        return implode(' ', $words);
    }
}
