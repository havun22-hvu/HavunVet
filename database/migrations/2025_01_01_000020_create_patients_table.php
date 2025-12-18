<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('havunadmin_customer_id')->nullable();
            $table->string('owner_name');
            $table->string('owner_email')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('owner_address')->nullable();
            $table->string('owner_city')->nullable();
            $table->string('owner_postal_code')->nullable();
            $table->string('name'); // Naam dier
            $table->string('species'); // Soort (hond, kat, etc.)
            $table->string('breed')->nullable(); // Ras
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'unknown'])->default('unknown');
            $table->boolean('neutered')->default(false);
            $table->string('chip_number')->nullable();
            $table->decimal('weight', 6, 2)->nullable(); // kg
            $table->string('color')->nullable();
            $table->json('allergies')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->date('deceased_at')->nullable();
            $table->timestamps();

            $table->index('havunadmin_customer_id');
            $table->index('chip_number');
            $table->index('species');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
