<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'login',
        'status',
        'blocked_until',
        'department_id',
        'position',
        'personnel_number',
        'mobile_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blocked_until' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /** Есть ли у пользователя роль «Суперадминистратор» (без ограничений по страницам). */
    public function isSuperAdmin(): bool
    {
        return $this->roles()->where('name', Role::SUPER_ADMIN_NAME)->exists();
    }

    /**
     * Есть ли у пользователя доступ к странице по имени маршрута.
     * Суперадминистратор имеет доступ ко всему.
     */
    public function hasPermissionTo(string $routeName): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $slugs = $this->getAllowedRouteSlugs();
        return $slugs !== null && in_array($routeName, $slugs, true);
    }

    /**
     * Список slug (имен маршрутов), к которым пользователь имеет доступ.
     * null = без ограничений (Суперадминистратор).
     *
     * @return array<int, string>|null
     */
    public function getAllowedRouteSlugs(): ?array
    {
        if ($this->isSuperAdmin()) {
            return null;
        }
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
