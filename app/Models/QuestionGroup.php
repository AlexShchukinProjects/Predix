<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionGroup extends Model
{
    use HasFactory;

    protected $table = 'tr_question_groups';

    protected $fillable = [
        'name',
        'description',
        'sort_order',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'question_group_id')->orderBy('sort_order');
    }
}
