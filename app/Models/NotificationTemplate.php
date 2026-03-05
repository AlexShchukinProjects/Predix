<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'module',
        'template_key',
        'name',
        'subject',
        'content',
        'active',
        'notify_days',
        'notify_frequency',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'active' => 'boolean',
        'notify_days' => 'integer',
    ];

    /**
     * Получить шаблон по модулю и ключу
     */
    public static function getByModuleAndKey(string $module, string $templateKey): ?self
    {
        return static::where('module', $module)
            ->where('template_key', $templateKey)
            ->first();
    }

    /**
     * Создать или обновить шаблон
     */
    public static function createOrUpdate(string $module, string $templateKey, array $data): self
    {
        return static::updateOrCreate(
            ['module' => $module, 'template_key' => $templateKey],
            $data
        );
    }

    /**
     * Получить все шаблоны для модуля
     */
    public static function getByModule(string $module): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('module', $module)->get();
    }
}
