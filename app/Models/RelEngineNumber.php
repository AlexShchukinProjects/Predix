<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\StubRelModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelEngineNumber extends Model
{
    use HasFactory;
    use StubRelModel;

    protected $table = 'rel_stub';

    protected $guarded = [];

    public function engineType()
    {
        return $this->belongsTo(RelEngineType::class, 'engine_type_id');
    }
}


