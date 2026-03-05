<?php

declare(strict_types=1);

namespace App\Models\Inspections;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditType extends Model
{
    protected $table = 'insp_audit_types';

    protected $fillable = [
        'name',
        'code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subtypes(): HasMany
    {
        return $this->hasMany(AuditSubtype::class, 'audit_type_id')->orderBy('sort_order')->orderBy('name');
    }
}
