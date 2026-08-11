<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('experiment_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('experiment_metrics', 'logout_time')) {
                $table->timestamp('logout_time')->nullable()->after('victim_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiment_metrics', function (Blueprint $table) {
            if (Schema::hasColumn('experiment_metrics', 'logout_time')) {
                $table->dropColumn('logout_time');
            }
        });
    }
};
