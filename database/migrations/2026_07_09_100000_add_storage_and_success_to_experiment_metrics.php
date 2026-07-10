<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiment_metrics', function (Blueprint $table) {
            $table->unsignedInteger('storage_bytes')->default(0)->after('query_count');
            $table->boolean('success')->default(true)->after('storage_bytes');
        });
    }

    public function down(): void
    {
        Schema::table('experiment_metrics', function (Blueprint $table) {
            $table->dropColumn(['storage_bytes', 'success']);
        });
    }
};
