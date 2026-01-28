<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contractor;
use App\Models\Decision;
use App\Models\Document;
use App\Models\Incident;
use App\Models\Membership;
use App\Models\Photo;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use App\Models\Validation;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Customer']);

        $account = Account::factory()->create([
            'name' => 'Admin Account',
        ]);

        //create admin user
        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Admin');
        Membership::factory()->create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'role' => 'Admin',
        ]);
        $user->forceFill(['current_account_id' => $account->id])->save();

        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@hubchantier.test',
            'password' => Hash::make('password'),
        ]);

        $account = Account::factory()->create([
            'name' => 'Hub Chantier Demo',
        ]);

        Membership::factory()->create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'role' => 'Customer',
        ]);

        $user->forceFill(['current_account_id' => $account->id])->save();
        $user->assignRole('Customer');

        $projects = Project::factory()
            ->count(4)
            ->create(['account_id' => $account->id]);

        Contractor::factory()
            ->count(6)
            ->create(['account_id' => $account->id]);

        foreach ($projects as $project) {
            Document::factory()->count(3)->create([
                'account_id' => $account->id,
                'project_id' => $project->id,
            ]);

            Validation::factory()->count(2)->create([
                'account_id' => $account->id,
                'project_id' => $project->id,
            ]);

            Incident::factory()->count(1)->create([
                'account_id' => $account->id,
                'project_id' => $project->id,
            ]);

            Decision::factory()->count(2)->create([
                'account_id' => $account->id,
                'project_id' => $project->id,
            ]);

            ProjectTask::factory()->count(4)->create([
                'account_id' => $account->id,
                'project_id' => $project->id,
            ]);

            Photo::factory()->count(2)->create([
                'account_id' => $account->id,
                'project_id' => $project->id,
            ]);
        }
    }
}
