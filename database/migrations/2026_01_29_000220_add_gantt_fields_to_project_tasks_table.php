<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('assigned_to');
            $table->date('end_date')->nullable()->after('start_date');
            $table->unsignedInteger('duration_days')->nullable()->after('end_date');
            $table->unsignedInteger('progress')->default(0)->after('duration_days');
            $table->foreignId('phase_id')->nullable()->after('project_id')->constrained('project_phases')->nullOnDelete();

            $table->index(['project_id', 'phase_id']);
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign(['phase_id']);
            $table->dropIndex(['project_id', 'phase_id']);
            $table->dropColumn(['start_date', 'end_date', 'duration_days', 'progress', 'phase_id']);
        });
    }
};

