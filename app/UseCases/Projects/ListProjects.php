<?php

namespace App\UseCases\Projects;

use App\Models\Account;
use App\Services\ProjectService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProjects
{
    public function __construct(private readonly ProjectService $projectService)
    {
    }

    public function handle(Account $account, array $filters): LengthAwarePaginator
    {
        return $this->projectService->list($account, $filters);
    }
}

