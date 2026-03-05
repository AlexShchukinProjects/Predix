<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateTlgXlsx extends Model
{
    use HasFactory;

    protected $table = 'templatetlgxlsx';
    
    protected $fillable = [
        'Service',
        'Group'
    ];
}
