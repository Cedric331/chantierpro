<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('actor_name')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'decided_at']);
            $table->index(['project_id', 'decided_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};

