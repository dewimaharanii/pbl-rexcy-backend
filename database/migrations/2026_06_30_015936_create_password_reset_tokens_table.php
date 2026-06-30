<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email', 100)->primary();
                $table->string('token', 100);
                $table->timestamp('created_at')->nullable();
            });
        } else {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                if (!Schema::hasColumn('password_reset_tokens', 'token')) {
                    $table->string('token', 100);
                }
                if (!Schema::hasColumn('password_reset_tokens', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
