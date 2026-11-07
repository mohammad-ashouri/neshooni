<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * انتقال نقش‌ها و دسترسی‌ها به ساختار پکیج spatie/laravel-permission.
 *
 * جدول roles حفظ می‌شود (برچسب، توضیح و is_system همچنان در آن است) و فقط:
 * - ستون key به name تغییر نام می‌دهد و guard_name اضافه می‌شود؛
 * - آرایه JSON دسترسی‌ها به جدول‌های permissions و role_has_permissions منتقل می‌شود؛
 * - users.role_id به جدول model_has_roles منتقل می‌شود.
 *
 * نقش admin هیچ ردیف دسترسی ندارد؛ همه دسترسی‌ها را از Gate::before می‌گیرد (به‌جای '*' قبلی).
 */
return new class extends Migration
{
    private const GUARD = 'web';

    public function up(): void
    {
        $tables = config('permission.table_names');
        $columns = config('permission.column_names');

        Schema::create($tables['permissions'], function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::table($tables['roles'], function (Blueprint $table) {
            $table->dropUnique(['key']);
        });
        Schema::table($tables['roles'], function (Blueprint $table) {
            $table->renameColumn('key', 'name');
        });
        Schema::table($tables['roles'], function (Blueprint $table) {
            $table->string('guard_name')->default(self::GUARD)->after('name');
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tables['model_has_permissions'], function (Blueprint $table) use ($tables, $columns) {
            $table->foreignId('permission_id')->constrained($tables['permissions'])->cascadeOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger($columns['model_morph_key']);
            $table->index([$columns['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->primary(['permission_id', $columns['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::create($tables['model_has_roles'], function (Blueprint $table) use ($tables, $columns) {
            $table->foreignId('role_id')->constrained($tables['roles'])->cascadeOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger($columns['model_morph_key']);
            $table->index([$columns['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->primary(['role_id', $columns['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        Schema::create($tables['role_has_permissions'], function (Blueprint $table) use ($tables) {
            $table->foreignId('permission_id')->constrained($tables['permissions'])->cascadeOnDelete();
            $table->foreignId('role_id')->constrained($tables['roles'])->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        $this->moveData($tables, $columns['model_morph_key']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
        Schema::table($tables['roles'], function (Blueprint $table) {
            $table->dropColumn('permissions');
        });

        $this->forgetCache();
    }

    private function moveData(array $tables, string $morphKey): void
    {
        $now = now();
        $roles = DB::table($tables['roles'])->get(['id', 'permissions']);

        // همه کلیدهای فهرست، به‌علاوه هر کلیدی که در نقش‌ها مانده (به‌جز '*')
        $names = collect(config('permissions', []))->collapse()->keys()
            ->merge($roles->flatMap(fn ($role) => json_decode($role->permissions ?? '[]', true) ?: []))
            ->reject(fn ($name) => $name === '*')
            ->unique()->values();

        DB::table($tables['permissions'])->insert(
            $names->map(fn ($name) => ['name' => $name, 'guard_name' => self::GUARD, 'created_at' => $now, 'updated_at' => $now])->all(),
        );
        $permissionIds = DB::table($tables['permissions'])->pluck('id', 'name');

        foreach ($roles as $role) {
            $rows = collect(json_decode($role->permissions ?? '[]', true) ?: [])
                ->reject(fn ($name) => $name === '*')
                ->map(fn ($name) => ['permission_id' => $permissionIds[$name], 'role_id' => $role->id])
                ->all();
            DB::table($tables['role_has_permissions'])->insert($rows);
        }

        DB::table('users')->whereNotNull('role_id')->orderBy('id')->each(function ($user) use ($tables, $morphKey) {
            DB::table($tables['model_has_roles'])->insert([
                'role_id' => $user->role_id,
                'model_type' => 'App\\Models\\User',
                $morphKey => $user->id,
            ]);
        });
    }

    public function down(): void
    {
        $tables = config('permission.table_names');
        $morphKey = config('permission.column_names.model_morph_key');

        Schema::table($tables['roles'], function (Blueprint $table) {
            $table->json('permissions')->nullable();
        });
        Schema::table('users', function (Blueprint $table) use ($tables) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained($tables['roles'])->nullOnDelete();
        });

        foreach (DB::table($tables['roles'])->get(['id', 'name']) as $role) {
            $permissions = $role->name === 'admin' ? ['*'] : DB::table($tables['role_has_permissions'])
                ->join($tables['permissions'], 'permission_id', '=', $tables['permissions'].'.id')
                ->where('role_id', $role->id)->pluck('name')->all();
            DB::table($tables['roles'])->where('id', $role->id)->update(['permissions' => json_encode($permissions)]);
        }

        DB::table($tables['model_has_roles'])->where('model_type', 'App\\Models\\User')->orderBy('role_id')
            ->each(fn ($row) => DB::table('users')->where('id', $row->{$morphKey})->update(['role_id' => $row->role_id]));

        Schema::dropIfExists($tables['role_has_permissions']);
        Schema::dropIfExists($tables['model_has_roles']);
        Schema::dropIfExists($tables['model_has_permissions']);
        Schema::dropIfExists($tables['permissions']);

        Schema::table($tables['roles'], function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
        });
        Schema::table($tables['roles'], function (Blueprint $table) {
            $table->dropColumn('guard_name');
        });
        Schema::table($tables['roles'], function (Blueprint $table) {
            $table->renameColumn('name', 'key');
        });
        Schema::table($tables['roles'], function (Blueprint $table) {
            $table->unique('key');
        });

        $this->forgetCache();
    }

    private function forgetCache(): void
    {
        $store = config('permission.cache.store');
        app('cache')->store($store !== 'default' ? $store : null)->forget(config('permission.cache.key'));
    }
};
