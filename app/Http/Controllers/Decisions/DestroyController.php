<?php

namespace App\Http\Controllers\Decisions;

use App\Http\Controllers\Controller;
use App\Models\Decision;
use App\UseCases\Decisions\DeleteDecision;
use Illuminate\Http\RedirectResponse;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteDecision $deleteDecision)
    {
    }

    public function __invoke(Decision $decision): RedirectResponse
    {
        $this->deleteDecision->handle($decision);

        return redirect()->to('/decisions');
    }
}

