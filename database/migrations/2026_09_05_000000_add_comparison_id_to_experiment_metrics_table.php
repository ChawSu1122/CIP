<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiment_metrics', function (Blueprint $table) {
            $table->string('comparison_id')->nullable()->after('action');
            $table->index('comparison_id');
        });
    }

    public function down(): void
    {
        Schema::table('experiment_metrics', function (Blueprint $table) {
            $table->dropIndex(['comparison_id']);
            $table->dropColumn('comparison_id');
        });
    }
};