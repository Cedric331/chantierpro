<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\UseCases\Photos\DeletePhoto;
use Illuminate\Http\RedirectResponse;

class DestroyController extends Controller
{
    public function __construct(private readonly DeletePhoto $deletePhoto)
    {
    }

    public function __invoke(Photo $photo): RedirectResponse
    {
        $this->deletePhoto->handle($photo);

        return redirect()->to('/photos');
    }
}

