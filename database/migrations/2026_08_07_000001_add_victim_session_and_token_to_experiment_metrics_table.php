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
            $table->string('victim_session_id')->nullable()->after('victim_authentication_type');
            $table->text('victim_token')->nullable()->after('victim_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiment_metrics', function (Blueprint $table) {
            $table->dropColumn(['victim_session_id', 'victim_token']);
        });
    }
};
