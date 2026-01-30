<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_budget_items', function (Blueprint $table) {
            $table->timestamp('alerted_at')->nullable()->after('variation_amount');
        });
    }

    public function down(): void
    {
        Schema::table('project_budget_items', function (Blueprint $table) {
            $table->dropColumn('alerted_at');
        });
    }
};

