<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NonconformityType extends Model
{
    use HasFactory;

    protected $table = 'insp_nonconformity_types';

    protected $fillable = [
        'code',
        'name_ru',
        'name_en',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function inspectionAnswers(): HasMany
    {
        return $this->hasMany(InspectionAnswer::class, 'nonconformity_type_id');
    }
}

