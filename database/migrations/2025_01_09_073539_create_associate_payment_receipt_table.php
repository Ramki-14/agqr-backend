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
        Schema::create('associate_payment_receipt', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('associate_id');        
            $table->string('associate_name');                   
            $table->string('associate_company')->nullable();     
            $table->decimal('received_amount', 10, 2);          
            $table->date('received_date');                      
            $table->string('received_method');                  
            $table->timestamps();                             

            // Optionally, you can add a foreign key constraint for associate_id if needed
            $table->foreign('associate_id')->references('id')->on('associative_login')->onDelete('cascade');
        });
      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('associate_payment_receipt');
    }
};
