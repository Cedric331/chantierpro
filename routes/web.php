<?php

use App\Http\Controllers\Billing\IndexController as BillingIndexController;
use App\Http\Controllers\Budgets\DestroyController as BudgetsDestroyController;
use App\Http\Controllers\Budgets\IndexController as BudgetsIndexController;
use App\Http\Controllers\Budgets\StoreController as BudgetsStoreController;
use App\Http\Controllers\Budgets\UpdateController as BudgetsUpdateController;
use App\Http\Controllers\Dashboard\ShowController as DashboardShowController;
use App\Http\Controllers\Decisions\DestroyController as DecisionsDestroyController;
use App\Http\Controllers\Decisions\IndexController as DecisionsIndexController;
use App\Http\Controllers\Decisions\StoreController as DecisionsStoreController;
use App\Http\Controllers\Decisions\UpdateController as DecisionsUpdateController;
use App\Http\Controllers\Documents\DestroyController as DocumentsDestroyController;
use App\Http\Controllers\Documents\IndexController as DocumentsIndexController;
use App\Http\Controllers\Documents\StoreController as DocumentsStoreController;
use App\Http\Controllers\Documents\UpdateController as DocumentsUpdateController;
use App\Http\Controllers\Incidents\DestroyController as IncidentsDestroyController;
use App\Http\Controllers\Incidents\IndexController as IncidentsIndexController;
use App\Http\Controllers\Incidents\StoreController as IncidentsStoreController;
use App\Http\Controllers\Incidents\UpdateController as IncidentsUpdateController;
use App\Http\Controllers\Planning\IndexController as PlanningIndexController;
use App\Http\Controllers\Photos\DestroyController as PhotosDestroyController;
use App\Http\Controllers\Photos\IndexController as PhotosIndexController;
use App\Http\Controllers\Photos\StoreController as PhotosStoreController;
use App\Http\Controllers\Photos\UpdateController as PhotosUpdateController;
use App\Http\Controllers\Notifications\IndexController as NotificationsIndexController;
use App\Http\Controllers\Notifications\MarkAllReadController as NotificationsMarkAllReadController;
use App\Http\Controllers\Notifications\MarkReadController as NotificationsMarkReadController;
use App\Http\Controllers\Portfolio\IndexController as PortfolioIndexController;
use App\Http\Controllers\ProjectTasks\DestroyController as ProjectTasksDestroyController;
use App\Http\Controllers\ProjectTasks\IndexController as ProjectTasksIndexController;
use App\Http\Controllers\ProjectTasks\StoreController as ProjectTasksStoreController;
use App\Http\Controllers\ProjectTasks\UpdateController as ProjectTasksUpdateController;
use App\Http\Controllers\Reports\ExportController as ReportsExportController;
use App\Http\Controllers\Reports\IndexController as ReportsIndexController;
use App\Http\Controllers\Reports\PrintController as ReportsPrintController;
use App\Http\Controllers\ProjectContractors\DestroyController as ProjectContractorsDestroyController;
use App\Http\Controllers\ProjectContractors\StoreController as ProjectContractorsStoreController;
use App\Http\Controllers\Projects\DestroyController as ProjectsDestroyController;
use App\Http\Controllers\Projects\IndexController as ProjectsIndexController;
use App\Http\Controllers\Projects\ShowController as ProjectsShowController;
use App\Http\Controllers\Projects\StoreController as ProjectsStoreController;
use App\Http\Controllers\Projects\UpdateController as ProjectsUpdateController;
use App\Http\Controllers\Contractors\DestroyController as ContractorsDestroyController;
use App\Http\Controllers\Contractors\IndexController as ContractorsIndexController;
use App\Http\Controllers\Contractors\StoreController as ContractorsStoreController;
use App\Http\Controllers\Contractors\UpdateController as ContractorsUpdateController;
use App\Http\Controllers\Validations\DestroyController as ValidationsDestroyController;
use App\Http\Controllers\Validations\IndexController as ValidationsIndexController;
use App\Http\Controllers\Validations\StoreController as ValidationsStoreController;
use App\Http\Controllers\Validations\UpdateController as ValidationsUpdateController;
use App\Http\Controllers\Milestones\DestroyController as MilestonesDestroyController;
use App\Http\Controllers\Milestones\StoreController as MilestonesStoreController;
use App\Http\Controllers\Milestones\UpdateController as MilestonesUpdateController;
use App\Http\Controllers\ProjectPhases\DestroyController as ProjectPhasesDestroyController;
use App\Http\Controllers\ProjectPhases\StoreController as ProjectPhasesStoreController;
use App\Http\Controllers\ProjectPhases\UpdateController as ProjectPhasesUpdateController;
use App\Http\Controllers\ProjectTaskDependencies\DestroyController as ProjectTaskDependenciesDestroyController;
use App\Http\Controllers\ProjectTaskDependencies\StoreController as ProjectTaskDependenciesStoreController;
use App\Http\Controllers\ProjectMessages\StoreController as ProjectMessagesStoreController;
use App\Http\Controllers\Comments\StoreController as CommentsStoreController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified', 'account'])->group(function () {
    Route::get('billing', BillingIndexController::class)->name('billing.index');
});

Route::middleware(['auth', 'verified', 'account', 'subscription'])->group(function () {
    Route::get('dashboard', DashboardShowController::class)->name('dashboard');

    Route::get('portfolio', PortfolioIndexController::class)->name('portfolio.index');
    Route::get('reports', ReportsIndexController::class)->name('reports.index');
    Route::get('reports/print', ReportsPrintController::class)->name('reports.print');
    Route::get('reports/export', ReportsExportController::class)->name('reports.export');

    Route::get('budgets', BudgetsIndexController::class)->name('budgets.index');
    Route::post('budgets', BudgetsStoreController::class)->name('budgets.store');
    Route::put('budgets/{budgetItem}', BudgetsUpdateController::class)->name('budgets.update');
    Route::delete('budgets/{budgetItem}', BudgetsDestroyController::class)->name('budgets.destroy');

    Route::post('milestones', MilestonesStoreController::class)->name('milestones.store');
    Route::put('milestones/{milestone}', MilestonesUpdateController::class)->name('milestones.update');
    Route::delete('milestones/{milestone}', MilestonesDestroyController::class)->name('milestones.destroy');

    Route::post('phases', ProjectPhasesStoreController::class)->name('phases.store');
    Route::put('phases/{phase}', ProjectPhasesUpdateController::class)->name('phases.update');
    Route::delete('phases/{phase}', ProjectPhasesDestroyController::class)->name('phases.destroy');

    Route::post('task-dependencies', ProjectTaskDependenciesStoreController::class)->name('task-dependencies.store');
    Route::delete('task-dependencies/{dependency}', ProjectTaskDependenciesDestroyController::class)->name('task-dependencies.destroy');

    Route::post('project-messages', ProjectMessagesStoreController::class)->name('project-messages.store');
    Route::post('comments', CommentsStoreController::class)->name('comments.store');

    Route::get('projects', ProjectsIndexController::class)->name('projects.index');
    Route::get('projects/{project}', ProjectsShowController::class)->name('projects.show');
    Route::post('projects', ProjectsStoreController::class)->name('projects.store');
    Route::put('projects/{project}', ProjectsUpdateController::class)->name('projects.update');
    Route::delete('projects/{project}', ProjectsDestroyController::class)->name('projects.destroy');
    Route::post('projects/{project}/contractors', ProjectContractorsStoreController::class)
        ->name('projects.contractors.store');
    Route::delete('projects/{project}/contractors/{contractor}', ProjectContractorsDestroyController::class)
        ->name('projects.contractors.destroy');

    Route::get('contractors', ContractorsIndexController::class)->name('contractors.index');
    Route::post('contractors', ContractorsStoreController::class)->name('contractors.store');
    Route::put('contractors/{contractor}', ContractorsUpdateController::class)->name('contractors.update');
    Route::delete('contractors/{contractor}', ContractorsDestroyController::class)->name('contractors.destroy');

    Route::get('documents', DocumentsIndexController::class)->name('documents.index');
    Route::post('documents', DocumentsStoreController::class)->name('documents.store');
    Route::put('documents/{document}', DocumentsUpdateController::class)->name('documents.update');
    Route::delete('documents/{document}', DocumentsDestroyController::class)->name('documents.destroy');

    Route::get('validations', ValidationsIndexController::class)->name('validations.index');
    Route::post('validations', ValidationsStoreController::class)->name('validations.store');
    Route::put('validations/{validation}', ValidationsUpdateController::class)->name('validations.update');
    Route::delete('validations/{validation}', ValidationsDestroyController::class)->name('validations.destroy');

    Route::get('incidents', IncidentsIndexController::class)->name('incidents.index');
    Route::post('incidents', IncidentsStoreController::class)->name('incidents.store');
    Route::put('incidents/{incident}', IncidentsUpdateController::class)->name('incidents.update');
    Route::delete('incidents/{incident}', IncidentsDestroyController::class)->name('incidents.destroy');

    Route::get('tasks', ProjectTasksIndexController::class)->name('tasks.index');
    Route::post('tasks', ProjectTasksStoreController::class)->name('tasks.store');
    Route::put('tasks/{task}', ProjectTasksUpdateController::class)->name('tasks.update');
    Route::delete('tasks/{task}', ProjectTasksDestroyController::class)->name('tasks.destroy');

    Route::get('planning', PlanningIndexController::class)->name('planning.index');

    Route::get('notifications', NotificationsIndexController::class)->name('notifications.index');
    Route::patch('notifications', NotificationsMarkAllReadController::class)->name('notifications.read-all');
    Route::patch('notifications/{notification}', NotificationsMarkReadController::class)->name('notifications.read');

    Route::get('decisions', DecisionsIndexController::class)->name('decisions.index');
    Route::post('decisions', DecisionsStoreController::class)->name('decisions.store');
    Route::put('decisions/{decision}', DecisionsUpdateController::class)->name('decisions.update');
    Route::delete('decisions/{decision}', DecisionsDestroyController::class)->name('decisions.destroy');

    Route::get('photos', PhotosIndexController::class)->name('photos.index');
    Route::post('photos', PhotosStoreController::class)->name('photos.store');
    Route::put('photos/{photo}', PhotosUpdateController::class)->name('photos.update');
    Route::delete('photos/{photo}', PhotosDestroyController::class)->name('photos.destroy');
});

require __DIR__.'/settings.php';
