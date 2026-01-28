<?php

namespace App\Http\Controllers\Validations;

use App\Http\Controllers\Controller;
use App\Models\Validation;
use App\UseCases\Validations\UpdateValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateValidation $updateValidation)
    {
    }

    public function __invoke(Request $request, Validation $validation): RedirectResponse
    {
        $this->updateValidation->handle($validation, $request->all());

        return redirect()->to('/validations');
    }
}

