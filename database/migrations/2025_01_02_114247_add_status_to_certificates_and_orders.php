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
        Schema::table('certificates', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspend', 'withdraw'])->default('active');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspend', 'withdraw'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
