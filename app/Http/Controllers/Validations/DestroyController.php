<?php

namespace App\Http\Controllers\Validations;

use App\Http\Controllers\Controller;
use App\Models\Validation;
use App\UseCases\Validations\DeleteValidation;
use Illuminate\Http\RedirectResponse;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteValidation $deleteValidation)
    {
    }

    public function __invoke(Validation $validation): RedirectResponse
    {
        $this->deleteValidation->handle($validation);

        return redirect()->to('/validations');
    }
}

