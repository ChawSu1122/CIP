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
        Schema::table('auth_security_events', function (Blueprint $table) {
            if (! Schema::hasColumn('auth_security_events', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('auth_security_events', 'type')) {
                $table->string('type')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('auth_security_events', 'status')) {
                $table->string('status')->default('pending')->after('type');
            }

            if (! Schema::hasColumn('auth_security_events', 'message')) {
                $table->text('message')->nullable()->after('status');
            }

            if (! Schema::hasColumn('auth_security_events', 'payload')) {
                $table->json('payload')->nullable()->after('message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth_security_events', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'type', 'status', 'message', 'payload']);
        });
    }
};
