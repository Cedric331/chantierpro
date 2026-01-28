<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('client_name');
            $table->string('address');
            $table->string('city');
            $table->string('status')->default('preparation');
            $table->decimal('budget', 12, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamps();

            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

