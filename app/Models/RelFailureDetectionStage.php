<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\StubRelModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelFailureDetectionStage extends Model
{
    use HasFactory;
    use StubRelModel;

    protected $table = 'rel_stub';

    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }
}


