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
        Schema::table('client_profiles', function (Blueprint $table) {
            // Add the user_login_id column and set it as a foreign key
            $table->unsignedBigInteger('user_login_id')->after('client_id'); // Position it after the client_id column

            // Add foreign key constraint
            $table->foreign('user_login_id')->references('id')->on('user_login')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['user_login_id']);
            $table->dropColumn('user_login_id');
        });
    }
};
