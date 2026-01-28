<?php

namespace App\Http\Controllers\Validations;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\UseCases\Validations\ListValidations;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private readonly ListValidations $listValidations)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $filters = $request->only(['project', 'status', 'type', 'search']);

        return Inertia::render('validations/Index', [
            'filters' => $filters,
            'validations' => $this->listValidations->handle($account, $filters),
            'projects' => Project::query()->where('account_id', $account->id)->get(['id', 'name']),
        ]);
    }
}

