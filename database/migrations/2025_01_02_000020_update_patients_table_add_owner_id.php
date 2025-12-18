<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Add owner_id foreign key
            $table->foreignId('owner_id')->nullable()->after('id')->constrained()->onDelete('cascade');

            // Remove old embedded owner fields
            $table->dropIndex(['havunadmin_customer_id']);
            $table->dropColumn([
                'havunadmin_customer_id',
                'owner_name',
                'owner_email',
                'owner_phone',
                'owner_address',
                'owner_city',
                'owner_postal_code',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');

            $table->unsignedBigInteger('havunadmin_customer_id')->nullable()->after('id');
            $table->string('owner_name')->after('havunadmin_customer_id');
            $table->string('owner_email')->nullable()->after('owner_name');
            $table->string('owner_phone')->nullable()->after('owner_email');
            $table->string('owner_address')->nullable()->after('owner_phone');
            $table->string('owner_city')->nullable()->after('owner_address');
            $table->string('owner_postal_code')->nullable()->after('owner_city');

            $table->index('havunadmin_customer_id');
        });
    }
};
