<?php

namespace App\Casts;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class SentenceCaseCast implements CastsAttributes
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

        $cleanValue = Str::squish(strip_tags($value));

        // 2. ADIM: Kusursuz Türkçe Küçültme
        // Önce her şeyi küçültüyoruz ki "IŞIK" -> "ışık" olsun.
        // Standart mb_strtolower bazen I'yı i yapar, o yüzden önce manuel değiştiriyoruz.
        $lower = mb_strtolower(str_replace(['I', 'İ'], ['ı', 'i'], $cleanValue), 'UTF-8');

        // 3. ADIM: Cümle Başlarını Yakala ve Büyüt (Regex)
        // Açıklama:
        // (^|[.!?]\s+)  -> Cümle başı VEYA (Nokta/Ünlem/Soru + Boşluk)
        // ([a-zıüöçşğ]) -> Ardından gelen ilk harf
        return preg_replace_callback(
            '/(^|[.!?]\s+)([a-zıüöçşğ])/u',
            function ($matches) {
                $separator = $matches[1]; // Nokta ve boşluk kısmı
                $char = $matches[2];      // Büyütülecek harf

                // Harfi Türkçe kuralına göre büyüt (i -> İ, ı -> I)
                $upperChar = match ($char) {
                    'i' => 'İ',
                    'ı' => 'I',
                    default => mb_strtoupper($char, 'UTF-8')
                };

                return $separator . $upperChar;
            },
            $lower
        );
    }
}
