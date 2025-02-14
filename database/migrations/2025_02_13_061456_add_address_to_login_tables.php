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
      

        Schema::table('admins', function (Blueprint $table) {
            $table->string('address')->after('role')->nullable();
        });

        // Add address column to associative_login table
        Schema::table('associative_login', function (Blueprint $table) {
            $table->string('address')->after('role')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('address');
        });

        // Remove address column from associative_login table
        Schema::table('associative_login', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
