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
        Schema::create('associate_client_certificate', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('order_id'); // Foreign Key to orders table
            $table->unsignedBigInteger('associate_id'); // Foreign Key to orders table
            $table->string('associate_name');
            $table->string('product_name');
            $table->text('product_description');
            $table->string('certificate_reg_no')->unique();
            $table->string('issue_no');
            $table->date('initial_approval');
            $table->date('next_surveillance');
            $table->date('date_of_issue');
            $table->date('valid_until');
            $table->string('certificate_file')->nullable(); // File path
            $table->enum('status', ['active', 'suspend', 'withdraw'])->default('active'); // Status column
            $table->timestamps();
             // Foreign key constraint
             $table->foreign('order_id')->references('id')->on('associate_client_orders')->onDelete('cascade');
             $table->foreign('associate_id')->references('id')->on('associative_login')->onDelete('cascade');
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('associate_client_certificate');
    }
};
