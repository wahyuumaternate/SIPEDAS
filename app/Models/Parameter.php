<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'tipe_data', 'deskripsi'];

    /** Ambil nilai parameter dengan cast otomatis sesuai tipe_data. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $param = static::where('key', $key)->first();

        if (! $param) {
            return $default;
        }

        return match ($param->tipe_data) {
            'number' => is_numeric($param->value) ? $param->value + 0 : $default,
            'boolean' => filter_var($param->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($param->value, true),
            default => $param->value,
        };
    }
}
