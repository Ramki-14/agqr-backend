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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('client_id'); // Foreign Key
            $table->string('product_name');
            $table->string('product_description');
            $table->string('invoice_number');
            $table->decimal('sgst_amount', 10, 2);
            $table->decimal('cgst_amount', 10, 2);
            $table->decimal('igst_amount', 10, 2);
            $table->decimal('rate', 10, 2);
            $table->decimal('gst_amount', 10, 2); // Total GST
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();

            $table->foreign('client_id')->references('client_id')->on('client_profiles')->onDelete('cascade');

       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
