<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('auth_permissions')) {
            Schema::create('auth_permissions', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug');
                $table->string('group')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('login')->nullable()->unique();
                $table->string('status', 32)->default('active');
                $table->date('blocked_until')->nullable();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->string('position')->nullable();
                $table->string('personnel_number', 64)->nullable();
                $table->string('mobile_phone', 32)->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table): void {
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->primary(['user_id', 'role_id']);
            });
        }

        if (! Schema::hasTable('auth_roles_permissions')) {
            Schema::create('auth_roles_permissions', function (Blueprint $table): void {
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('auth_permissions')->cascadeOnDelete();
                $table->primary(['role_id', 'permission_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_roles_permissions');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('users');
        Schema::dropIfExists('auth_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('departments');
    }
};
