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
        Schema::create('experiment_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('auth_type');
            $table->string('action');
            $table->string('method');
            $table->string('path')->nullable();
            $table->integer('duration_ms')->default(0);
            $table->integer('memory_usage')->default(0);
            $table->integer('query_count')->default(0);
            $table->integer('victim_id')->nullable();
            $table->string('victim_name')->nullable();
            $table->string('victim_email')->nullable();
            $table->integer('attacker_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiment_metrics');
    }
};
