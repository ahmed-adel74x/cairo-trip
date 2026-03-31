<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar');
            $table->text('description_en');
            $table->string('image_url');
            $table->boolean('is_free')->default(false);
            $table->string('price_ar');
            $table->string('price_en');
            $table->decimal('price_number', 8, 2)->default(0);
            $table->string('working_hours_ar');
            $table->string('working_hours_en');
            $table->string('location_ar');
            $table->string('location_en');
            $table->float('rating_avg')->default(0);
            $table->integer('total_bookings')->default(0);
            $table->json('activities_ar');
            $table->json('activities_en');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};