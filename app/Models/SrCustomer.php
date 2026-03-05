<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrCustomer extends Model
{
    use HasFactory;

    protected $table = 'sr_customers';

    protected $fillable = [
        'name', 'code', 'active',
    ];
}


