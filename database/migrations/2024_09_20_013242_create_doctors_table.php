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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id(); // This creates an unsignedBigInteger type column
            $table->string('category')->nullable();
            $table->unsignedBigInteger('doc_id')->unique(); // Ensure this matches the type of the users.id column
            $table->unsignedInteger('patients')->nullable();
            $table->unsignedInteger('experience')->nullable();
            $table->longText('bio_data')->nullable();
            $table->string('status')->nullable();
            $table->foreign('doc_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
