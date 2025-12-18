<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('vaccine_name');
            $table->string('vaccine_type')->nullable(); // Kern/niet-kern vaccinatie
            $table->string('batch_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->datetime('administered_at');
            $table->date('next_due_date')->nullable();
            $table->string('administered_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('administered_at');
            $table->index('next_due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccinations');
    }
};
