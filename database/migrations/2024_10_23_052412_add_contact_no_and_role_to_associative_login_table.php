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
        Schema::table('associative_login', function (Blueprint $table) {
            $table->string('contact_no')->after('password');
            $table->string('role')->after('contact_no');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('associative_login', function (Blueprint $table) {
            $table->dropColumn(['contact_no', 'role']);
            //
        });
    }
};
