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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sgst', 8, 2)->default(0)->after('description');
            $table->decimal('cgst', 8, 2)->default(0)->after('sgst');
            $table->decimal('igst', 8, 2)->default(0)->after('cgst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sgst', 'cgst', 'igst']);
        });
    }
};
