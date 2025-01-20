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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('order_id');
            $table->string('client_name');
            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->decimal('rate', 10, 2);
            $table->decimal('sgst', 10, 2);
            $table->decimal('cgst', 10, 2);
            $table->decimal('igst', 10, 2);
            $table->decimal('payable_amount', 10, 2);
            $table->decimal('received_amount', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->string('payment_method');
            $table->date('payment_date');
            $table->timestamps();

            // Foreign keys
            $table->foreign('client_id')->references('client_id')->on('client_profiles')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
