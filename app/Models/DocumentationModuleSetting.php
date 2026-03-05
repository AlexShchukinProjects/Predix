<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DocumentationModuleSetting extends Model
{
    protected $table = 'doc_module_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $cacheKey = 'doc_module_setting_' . $key;
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $row = static::find($key);
            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrInsert(['key' => $key], ['value' => (string) $value]);
        Cache::forget('doc_module_setting_' . $key);
    }

    public static function isApprovalEnabled(): bool
    {
        return (string) static::get('approval_enabled', '1') === '1';
    }

    public static function isFamiliarizationEnabled(): bool
    {
        return (string) static::get('familiarization_enabled', '1') === '1';
    }
}
