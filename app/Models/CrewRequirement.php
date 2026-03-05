<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrewRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'position',
        'requirement',
        'required'
    ];

    protected $casts = [
        'required' => 'boolean'
    ];

    /**
     * Получить все требования для конкретной должности
     */
    public static function getRequirementsForPosition($position)
    {
        return self::where('position', $position)
                   ->where('required', true)
                   ->pluck('requirement')
                   ->toArray();
    }

    /**
     * Проверить, требуется ли конкретное требование для должности
     */
    public static function isRequired($position, $requirement)
    {
        return self::where('position', $position)
                   ->where('requirement', $requirement)
                   ->where('required', true)
                   ->exists();
    }

    /**
     * Получить все должности с их требованиями
     */
    public static function getAllRequirements()
    {
        return self::where('required', true)
                   ->get()
                   ->groupBy('position')
                   ->map(function ($requirements) {
                       return $requirements->pluck('requirement')->toArray();
                   });
    }
}
