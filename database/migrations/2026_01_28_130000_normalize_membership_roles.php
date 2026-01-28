<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('memberships')
            ->where('role', 'Customer')
            ->update(['role' => 'collaborator']);

        DB::table('memberships')
            ->where('role', 'Admin')
            ->update(['role' => 'owner']);
    }

    public function down(): void
    {
        DB::table('memberships')
            ->where('role', 'collaborator')
            ->update(['role' => 'Customer']);

        DB::table('memberships')
            ->where('role', 'owner')
            ->update(['role' => 'Admin']);
    }
};

