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
        Schema::create('associate_client_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->decimal('sgst_amount', 8, 2)->nullable();
            $table->decimal('cgst_amount', 8, 2)->nullable();
            $table->decimal('igst_amount', 8, 2)->nullable();
            $table->decimal('rate', 10, 2);
            $table->decimal('gst_amount', 8, 2)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('balance_amount', 10, 2);
            $table->string('audit_type');
            $table->string('status')->nullable();
            $table->string('associate_company')->nullable();
            $table->string('associate_name');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('client_id')->references('id')->on('associate_client')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('associate_client_orders');
    }
};
