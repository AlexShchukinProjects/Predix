<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Получить значение по ключу (десериализация JSON при необходимости).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = self::find($key);
        if (!$row || $row->value === null) {
            return $default;
        }
        $v = $row->value;
        if (str_starts_with($v, '[') || str_starts_with($v, '{')) {
            return json_decode($v, true) ?? $default;
        }
        return $v;
    }

    /**
     * Записать значение (массивы/объекты сохраняются как JSON).
     */
    public static function set(string $key, mixed $value): void
    {
        $encoded = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $encoded]
        );
    }
}
