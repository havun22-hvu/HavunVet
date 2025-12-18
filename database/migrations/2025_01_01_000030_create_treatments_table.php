<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_location_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('complaint')->nullable(); // Klacht/reden bezoek
            $table->text('anamnesis')->nullable(); // Voorgeschiedenis
            $table->text('examination')->nullable(); // Onderzoek bevindingen
            $table->text('diagnosis')->nullable();
            $table->text('treatment_description')->nullable();
            $table->boolean('follow_up_needed')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->string('veterinarian')->nullable();
            $table->unsignedBigInteger('havunadmin_invoice_id')->nullable();
            $table->enum('status', ['draft', 'completed', 'invoiced'])->default('draft');
            $table->timestamps();

            $table->index('date');
            $table->index('havunadmin_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
