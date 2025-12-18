<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('medication_id')->nullable()->constrained()->nullOnDelete();
            $table->string('medication_name'); // Backup als medication verwijderd wordt
            $table->string('dosage');
            $table->string('frequency'); // 2x daags, 3x daags
            $table->integer('duration_days')->nullable();
            $table->text('instructions')->nullable();
            $table->decimal('dispensed_quantity', 8, 2)->nullable();
            $table->string('dispensed_unit')->nullable();
            $table->datetime('dispensed_at')->nullable();
            $table->string('prescribed_by')->nullable();
            $table->timestamps();

            $table->index('dispensed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
