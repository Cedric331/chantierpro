<?php

namespace App\Http\Controllers\Decisions;

use App\Http\Controllers\Controller;
use App\Models\Decision;
use App\UseCases\Decisions\UpdateDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateDecision $updateDecision)
    {
    }

    public function __invoke(Request $request, Decision $decision): RedirectResponse
    {
        $this->updateDecision->handle($decision, $request->all());

        return redirect()->to('/decisions');
    }
}

