<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->foreignId('project_task_id')->nullable()->after('project_id')->constrained('project_tasks')->nullOnDelete();
            $table->index(['account_id', 'project_task_id']);
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropForeign(['project_task_id']);
            $table->dropColumn('project_task_id');
        });
    }
};

