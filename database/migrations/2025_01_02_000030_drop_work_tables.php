<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove foreign keys from treatments and appointments first
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropForeign(['work_location_id']);
            $table->dropColumn('work_location_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['work_location_id']);
            $table->dropColumn('work_location_id');
        });

        // Drop work_sessions first (has foreign key to work_locations)
        Schema::dropIfExists('work_sessions');
        Schema::dropIfExists('work_locations');
    }

    public function down(): void
    {
        // Recreate work_locations first (needed for foreign key)
        Schema::create('work_locations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['clinic', 'home_visit', 'own_practice'])->default('clinic');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('house_number')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->text('contract_notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Recreate work_sessions
        Schema::create('work_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_location_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_minutes')->default(0);
            $table->integer('travel_distance_km')->nullable();
            $table->decimal('travel_cost_per_km', 4, 2)->default(0.23);
            $table->decimal('travel_cost_total', 8, 2)->nullable();
            $table->decimal('parking_costs', 8, 2)->nullable();
            $table->decimal('other_costs', 8, 2)->nullable();
            $table->string('other_costs_description')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('havunadmin_invoice_id')->nullable();
            $table->enum('status', ['draft', 'submitted', 'invoiced'])->default('draft');
            $table->timestamps();
        });

        // Restore foreign keys in treatments and appointments
        Schema::table('treatments', function (Blueprint $table) {
            $table->foreignId('work_location_id')->nullable()->after('patient_id')->constrained()->nullOnDelete();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('work_location_id')->nullable()->after('patient_id')->constrained()->nullOnDelete();
        });
    }
};
