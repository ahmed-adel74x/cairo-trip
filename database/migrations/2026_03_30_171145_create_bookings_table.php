<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('place_id')
                  ->constrained('places')
                  ->onDelete('cascade');
            $table->date('booking_date');
            $table->integer('person_count');
            $table->string('total_price_ar');
            $table->string('total_price_en');
            $table->decimal('total_price_number', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])
                  ->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};