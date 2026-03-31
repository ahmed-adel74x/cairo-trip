<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('place_id')
                  ->constrained('places')
                  ->onDelete('cascade');
            $table->foreignId('booking_id')
                  ->constrained('bookings')
                  ->onDelete('cascade');
            $table->date('trip_date');
            $table->integer('person_count');
            $table->string('price_ar');
            $table->string('price_en');
            $table->decimal('price_number', 10, 2);
            $table->enum('status', ['completed', 'upcoming'])
                  ->default('upcoming');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};