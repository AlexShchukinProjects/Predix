<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /** Имя роли без ограничений по страницам */
    public const SUPER_ADMIN_NAME = 'Суперадминистратор';

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function permissions()
    {
        return $this->belongsToMany(AuthPermission::class, 'auth_roles_permissions', 'role_id', 'permission_id');
    }
}
