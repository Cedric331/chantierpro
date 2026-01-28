<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\UseCases\Documents\ListDocuments;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private readonly ListDocuments $listDocuments)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $filters = $request->only(['project', 'category', 'status', 'search']);

        return Inertia::render('documents/Index', [
            'filters' => $filters,
            'documents' => $this->listDocuments->handle($account, $filters),
            'projects' => Project::query()->where('account_id', $account->id)->get(['id', 'name']),
        ]);
    }
}

