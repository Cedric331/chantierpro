<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('depends_on_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->string('dependency_type')->default('finish_to_start');
            $table->timestamps();

            $table->unique(['task_id', 'depends_on_task_id']);
            $table->index(['account_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_dependencies');
    }
};

