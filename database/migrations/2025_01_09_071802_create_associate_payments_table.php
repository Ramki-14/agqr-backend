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
        Schema::create('associate_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('associate_id');
            $table->string('associate_company')->nullable();
            $table->string('associate_name');
            $table->decimal('principal_amount', 10, 2)->nullable();
            $table->decimal('returned_amount', 10, 2)->nullable();
            $table->decimal('outstanding_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('associate_id')->references('id')->on('associative_login')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('associate_payments');
    }
};
