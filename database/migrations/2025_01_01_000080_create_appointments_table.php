<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_location_id')->nullable()->constrained()->nullOnDelete();
            $table->datetime('scheduled_at');
            $table->integer('duration_minutes')->default(30);
            $table->enum('type', [
                'consult',
                'checkup',
                'vaccination',
                'surgery',
                'dental',
                'emergency',
                'home_visit',
                'other'
            ])->default('consult');
            $table->enum('status', [
                'scheduled',
                'confirmed',
                'arrived',
                'in_progress',
                'completed',
                'cancelled',
                'no_show'
            ])->default('scheduled');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamps();

            $table->index('scheduled_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
