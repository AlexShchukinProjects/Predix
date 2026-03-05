<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AuthPermission extends Model
{
    use HasFactory;

    protected $table = 'auth_permissions';

    protected $fillable = [
        'name', 'slug', 'group',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'auth_roles_permissions', 'permission_id', 'role_id');
    }
}


