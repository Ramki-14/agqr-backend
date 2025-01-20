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
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id('client_id'); // primary key
            $table->string('client_name');
            $table->string('contact_person');
            $table->string('email'); // Foreign key for user_login table (must match 'email' column type)
            $table->string('contact_no');
            $table->text('address');
            $table->string('category');
            $table->string('BA_name'); // Business Associate name
            $table->string('Audit_type');
            $table->enum('status', ['active', 'inactive', 'pending', 'following']); // Status with predefined options
            $table->string('image')->nullable(); 
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key constraint (reference the email column in the user_login table)
            // $table->foreign('user_login_id')->references('id')->on('user_login')->onDelete('cascade');
       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
