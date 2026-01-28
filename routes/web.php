<?php

use App\Http\Controllers\Billing\IndexController as BillingIndexController;
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
use App\Http\Controllers\ProjectTasks\DestroyController as ProjectTasksDestroyController;
use App\Http\Controllers\ProjectTasks\IndexController as ProjectTasksIndexController;
use App\Http\Controllers\ProjectTasks\StoreController as ProjectTasksStoreController;
use App\Http\Controllers\ProjectTasks\UpdateController as ProjectTasksUpdateController;
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
