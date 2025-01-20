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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('product_name');
            $table->string('product_description');
            $table->string('certificate_reg_no');
            $table->string('issue_no');
            $table->date('initial_approval');
            $table->date('next_surveillance');
            $table->date('date_of_issue');
            $table->date('valid_until');
            $table->string('certificate_file')->nullable(); // Assuming you're saving the file path
            $table->timestamps();

            $table->foreign('order_id')
            ->references('id') // The referenced column in the parent table
            ->on('orders') // The name of the parent table (e.g., 'client_profiles')
            ->onDelete('cascade'); // Optionally specify the delete behavior
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
