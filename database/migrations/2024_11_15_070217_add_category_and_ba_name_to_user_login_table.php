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
        Schema::table('user_login', function (Blueprint $table) {
            $table->string('category')->nullable()->after('role'); // Add category field
            $table->string('ba_name')->nullable()->after('category'); // Add Business Associate name field
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_login', function (Blueprint $table) {
            $table->dropColumn(['category', 'ba_name']);
        });
    }
};
