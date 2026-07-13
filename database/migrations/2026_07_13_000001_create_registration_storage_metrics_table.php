<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_storage_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('auth_method', 20);
            $table->string('user_name');
            $table->unsignedInteger('user_row_bytes')->default(0);
            $table->unsignedInteger('session_row_bytes')->default(0);
            $table->unsignedInteger('token_bytes')->default(0);
            $table->unsignedInteger('total_bytes')->default(0);
            $table->decimal('total_kb', 10, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_storage_metrics');
    }
};
