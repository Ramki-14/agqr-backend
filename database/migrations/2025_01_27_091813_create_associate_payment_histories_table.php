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
        Schema::create('associate_payment_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('associate_id');
            $table->string('associate_name');
            $table->string('associate_company')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->string('client_name');
            $table->string('product_name');
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();
    
            $table->foreign('associate_id')->references('id')->on('associative_login')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('associate_client')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('associate_payment_histories');
    }
};
