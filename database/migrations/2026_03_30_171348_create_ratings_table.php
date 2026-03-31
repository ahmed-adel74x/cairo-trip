<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('place_id')
                  ->constrained('places')
                  ->onDelete('cascade');
            $table->foreignId('trip_id')
                  ->constrained('trips')
                  ->onDelete('cascade');
            $table->float('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            // one rating per trip per user
            $table->unique(['user_id', 'trip_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};