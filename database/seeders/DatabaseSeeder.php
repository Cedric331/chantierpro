<?php

namespace Database\Seeders;

use App\Enums\MembershipRole;
use App\Models\Account;
use App\Models\Contractor;
use App\Models\Decision;
use App\Models\Document;
use App\Models\Incident;
use App\Models\Membership;
use App\Models\Photo;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectBudgetItem;
use App\Models\ProjectMessage;
use App\Models\ProjectMilestone;
use App\Models\ProjectPhase;
use App\Models\ProjectTask;
use App\Models\ProjectTaskDependency;
use App\Models\User;
use App\Models\Validation;
use App\Models\Comment;
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
        $faker = fake();
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
            'role' => MembershipRole::Owner,
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
            'role' => MembershipRole::Owner,
        ]);

        $user->forceFill(['current_account_id' => $account->id])->save();
        $user->assignRole('Customer');

        $projects = Project::factory()
            ->count(4)
            ->create([
                'account_id' => $account->id,
                'budget_alert_enabled' => true,
                'budget_alert_threshold' => 10,
            ]);

        Contractor::factory()
            ->count(6)
            ->create(['account_id' => $account->id]);

        foreach ($projects as $project) {
            $phases = collect();
            foreach (range(1, 3) as $index) {
                $phases->push(ProjectPhase::create([
                    'account_id' => $account->id,
                    'project_id' => $project->id,
                    'title' => $faker->randomElement(['Préparation', 'Gros œuvre', 'Second œuvre', 'Finitions']),
                    'description' => $faker->sentence(),
                    'start_date' => now()->addDays($faker->numberBetween(0, 15)),
                    'end_date' => now()->addDays($faker->numberBetween(16, 60)),
                    'position' => $index,
                ]));
            }

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

            $tasks = ProjectTask::factory()->count(6)->create([
                'account_id' => $account->id,
                'project_id' => $project->id,
            ]);

            $tasks->each(function (ProjectTask $task) use ($phases, $faker) {
                $start = now()->addDays($faker->numberBetween(0, 20));
                $end = (clone $start)->addDays($faker->numberBetween(2, 12));
                $task->update([
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'duration_days' => $start->diffInDays($end),
                    'progress' => $faker->numberBetween(5, 95),
                    'due_date' => $end->toDateString(),
                    'phase_id' => $phases->isNotEmpty() ? $phases->random()->id : null,
                ]);
            });

            if ($tasks->count() > 1) {
                for ($i = 1; $i < min(3, $tasks->count()); $i++) {
                    ProjectTaskDependency::create([
                        'account_id' => $account->id,
                        'project_id' => $project->id,
                        'task_id' => $tasks[$i]->id,
                        'depends_on_task_id' => $tasks[$i - 1]->id,
                        'dependency_type' => 'finish_to_start',
                    ]);
                }
            }

            $budgetItems = collect();
            foreach (range(1, 5) as $index) {
                $estimated = $faker->numberBetween(2000, 15000);
                $actual = $faker->numberBetween(1500, 18000);
                $budgetItems->push(ProjectBudgetItem::create([
                    'account_id' => $account->id,
                    'project_id' => $project->id,
                    'name' => $faker->randomElement(['Gros œuvre', 'VRD', 'Électricité', 'Plomberie', 'Menuiseries']),
                    'category' => $faker->randomElement(['Structure', 'Second œuvre', 'Finitions']),
                    'estimated_cost' => $estimated,
                    'committed_cost' => $faker->numberBetween(1000, $estimated),
                    'actual_cost' => $actual,
                    'variation_amount' => $actual - $estimated,
                    'notes' => $faker->sentence(),
                ]));
            }

            $milestones = collect();
            foreach (range(1, 3) as $index) {
                $milestones->push(ProjectMilestone::create([
                    'account_id' => $account->id,
                    'project_id' => $project->id,
                    'title' => $faker->randomElement(['Dépôt PC', 'Fin gros œuvre', 'Livraison']),
                    'status' => $faker->randomElement(['pending', 'in_progress', 'done']),
                    'due_date' => now()->addDays($faker->numberBetween(10, 90)),
                    'owner_name' => $faker->name(),
                    'description' => $faker->sentence(),
                ]));
            }

            Photo::factory()->count(2)->create([
                'account_id' => $account->id,
                'project_id' => $project->id,
            ]);

            foreach ($budgetItems as $item) {
                Comment::create([
                    'account_id' => $account->id,
                    'author_id' => $user->id,
                    'commentable_type' => ProjectBudgetItem::class,
                    'commentable_id' => $item->id,
                    'body' => $faker->sentence(),
                ]);
            }

            foreach ($milestones as $milestone) {
                Comment::create([
                    'account_id' => $account->id,
                    'author_id' => $user->id,
                    'commentable_type' => ProjectMilestone::class,
                    'commentable_id' => $milestone->id,
                    'body' => $faker->sentence(),
                ]);
            }

            foreach (range(1, 2) as $index) {
                ProjectMessage::create([
                    'account_id' => $account->id,
                    'project_id' => $project->id,
                    'author_id' => $user->id,
                    'body' => $faker->sentence(),
                ]);
            }

            ProjectActivity::create([
                'account_id' => $account->id,
                'project_id' => $project->id,
                'actor_id' => $user->id,
                'type' => 'project_update',
                'payload' => [
                    'message' => $faker->sentence(),
                ],
            ]);
        }
    }
}
