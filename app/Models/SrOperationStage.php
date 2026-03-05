<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrOperationStage extends Model
{
    use HasFactory;

    protected $table = 'sr_operation_stages';

    protected $fillable = [
        'name', 'code', 'active', 'parent_id', 'sort_order',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** Элемент первого уровня (без родителя). */
    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
    }
}


