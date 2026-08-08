<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiment_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('experiment_metrics', 'victim_user_agent')) {
                $table->text('victim_user_agent')->nullable()->after('victim_token');
            }

            if (! Schema::hasColumn('experiment_metrics', 'attacker_user_agent')) {
                $table->text('attacker_user_agent')->nullable()->after('victim_user_agent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('experiment_metrics', function (Blueprint $table) {
            if (Schema::hasColumn('experiment_metrics', 'victim_user_agent')) {
                $table->dropColumn('victim_user_agent');
            }

            if (Schema::hasColumn('experiment_metrics', 'attacker_user_agent')) {
                $table->dropColumn('attacker_user_agent');
            }
        });
    }
};
