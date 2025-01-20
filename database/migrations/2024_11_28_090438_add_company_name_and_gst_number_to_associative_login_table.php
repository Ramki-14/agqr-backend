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
            $table->string('company_name')->nullable()->after('id'); 
            $table->string('gst_number')->nullable()->after('contact_no'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('associative_login', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'gst_number']);
        });
    }
};
