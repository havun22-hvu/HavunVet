<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->datetime('scheduled_at');
            $table->string('address');
            $table->string('city');
            $table->string('postal_code');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('travel_distance_km', 6, 1)->nullable();
            $table->integer('travel_time_minutes')->nullable();
            $table->decimal('travel_cost', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'in_transit', 'arrived', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();

            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_visits');
    }
};
