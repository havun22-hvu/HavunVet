<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_location_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('havunadmin_invoice_id')->nullable();
            $table->enum('status', ['draft', 'submitted', 'invoiced'])->default('draft');
            $table->timestamps();

            $table->index('date');
            $table->index('havunadmin_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_sessions');
    }
};
