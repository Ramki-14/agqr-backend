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
        Schema::table('associate_client_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('associate_id');
            $table->foreign('associate_id')->references('id')->on('associative_login')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('associate_client_orders', function (Blueprint $table) {
            $table->dropColumn('associate_id');
        });
    }
};
