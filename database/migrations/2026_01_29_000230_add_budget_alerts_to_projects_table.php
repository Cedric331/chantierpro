<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('budget_alert_enabled')->default(true)->after('budget');
            $table->unsignedInteger('budget_alert_threshold')->default(10)->after('budget_alert_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['budget_alert_enabled', 'budget_alert_threshold']);
        });
    }
};

