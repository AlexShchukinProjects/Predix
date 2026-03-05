<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\StubRelModel;
use Illuminate\Database\Eloquent\Model;

class RelBufSetting extends Model
{
    use StubRelModel;

    protected $table = 'rel_stub';

    protected $fillable = [
        'start_number_prefix',
    ];

    protected $attributes = [
        'start_number_prefix' => '2388',
    ];
}


