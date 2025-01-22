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
        Schema::table('associate_client', function (Blueprint $table) {
            if (!Schema::hasColumn('associate_client', 'associate_id')) {
                $table->unsignedBigInteger('associate_id')->after('id');
                $table->foreign('associate_id')
                      ->references('id')
                      ->on('associative_login')
                      ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('associate_client', function (Blueprint $table) {
            if (Schema::hasColumn('associate_client', 'associate_id')) {
                $table->dropForeign(['associate_id']);
                $table->dropColumn('associate_id');
            }
        });
    }
};
