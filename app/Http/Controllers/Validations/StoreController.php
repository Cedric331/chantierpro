<?php

namespace App\Http\Controllers\Validations;

use App\Http\Controllers\Controller;
use App\UseCases\Validations\CreateValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateValidation $createValidation)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createValidation->handle($account, $request->all());

        return redirect()->to('/validations');
    }
}

