<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key', 
        'value',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return $setting->value ?? $default;
    }

    public static function set(string $key, mixed $value, ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ],
        );
    }

    public static function getJamBuka(): string
    {
        return static::get('jam_buka', '06:00');
    }

    public static function getJamTutup(): string
    {
        return static::get('jam_tutup', '21:00');
    }

    public static function getJamOperasional(string $jamBuka, string $jamTutup): void
    {
        static::set('jam_buka', $jamBuka, 'Jam buka operasional lapangan');
        static::set('jam_tutup', $jamTutup, 'Jam tutup operasional lapangan');
    }
}
