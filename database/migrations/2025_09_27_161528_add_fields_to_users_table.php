<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->string('country');
            $table->boolean('digest_enabled')->default(false);
            $table->enum('digest_frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->timestamp('digest_last_sent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'country', 'digest_enabled', 'digest_frequency', 'digest_last_sent']);
        });
    }
};
