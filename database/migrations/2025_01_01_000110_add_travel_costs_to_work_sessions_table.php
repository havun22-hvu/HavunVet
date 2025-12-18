<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_sessions', function (Blueprint $table) {
            $table->decimal('travel_distance_km', 6, 1)->nullable()->after('break_minutes');
            $table->decimal('travel_cost_per_km', 5, 2)->default(0.23)->after('travel_distance_km'); // Belastingvrije km vergoeding
            $table->decimal('travel_cost_total', 8, 2)->nullable()->after('travel_cost_per_km');
            $table->decimal('parking_costs', 8, 2)->nullable()->after('travel_cost_total');
            $table->decimal('other_costs', 8, 2)->nullable()->after('parking_costs');
            $table->string('other_costs_description')->nullable()->after('other_costs');
        });
    }

    public function down(): void
    {
        Schema::table('work_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'travel_distance_km',
                'travel_cost_per_km',
                'travel_cost_total',
                'parking_costs',
                'other_costs',
                'other_costs_description',
            ]);
        });
    }
};
