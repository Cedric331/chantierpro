<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('feature_key');
            $table->timestamp('used_at');
            $table->timestamps();

            $table->index(['account_id', 'feature_key']);
            $table->index(['account_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_usages');
    }
};



