<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoyaltySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected $casts = [
        'value' => 'decimal:4',
    ];

    public static function getDecimalValue(string $key, float $default = 0): float
    {
        $setting = self::query()->where('key', $key)->value('value');

        if ($setting === null) {
            return $default;
        }

        return (float) $setting;
    }
}
