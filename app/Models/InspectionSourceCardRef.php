<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionSourceCardRef extends Model
{
    protected $table = 'inspection_source_card_refs';

    protected $fillable = ['code', 'name'];

}
