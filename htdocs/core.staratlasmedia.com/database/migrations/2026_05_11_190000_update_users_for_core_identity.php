<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->string('status')->default('active')->after('password')->index();
            $table->json('metadata')->nullable()->after('remember_token');
            $table->softDeletes();
        });

        DB::table('users')
            ->whereNull('uuid')
            ->orderBy('id')
            ->get(['id'])
            ->each(fn (object $user) => DB::table('users')
                ->where('id', $user->id)
                ->update(['uuid' => (string) Str::uuid()]));

        Schema::table('users', function (Blueprint $table) {
            $table->unique('uuid');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY name VARCHAR(255) NULL, MODIFY email VARCHAR(255) NULL, MODIFY password VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET name = COALESCE(name, ''), email = COALESCE(email, CONCAT('user-', id, '@invalid.local')), password = COALESCE(password, '')");
            DB::statement('ALTER TABLE users MODIFY name VARCHAR(255) NOT NULL, MODIFY email VARCHAR(255) NOT NULL, MODIFY password VARCHAR(255) NOT NULL');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn(['uuid', 'status', 'metadata']);
            $table->dropSoftDeletes();
        });
    }
};
