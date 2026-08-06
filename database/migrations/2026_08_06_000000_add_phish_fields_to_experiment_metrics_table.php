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
            if (! Schema::hasColumn('experiment_metrics', 'victim_id')) {
                $table->integer('victim_id')->nullable()->after('query_count');
            }
            if (! Schema::hasColumn('experiment_metrics', 'victim_name')) {
                $table->string('victim_name')->nullable()->after('victim_id');
            }
            if (! Schema::hasColumn('experiment_metrics', 'victim_email')) {
                $table->string('victim_email')->nullable()->after('victim_name');
            }
            if (! Schema::hasColumn('experiment_metrics', 'attacker_id')) {
                $table->integer('attacker_id')->nullable()->after('victim_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiment_metrics', function (Blueprint $table) {
            $table->dropColumn(['victim_id', 'victim_name', 'victim_email', 'attacker_id']);
        });
    }
};
