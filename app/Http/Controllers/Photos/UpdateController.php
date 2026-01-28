<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\UseCases\Photos\UpdatePhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdatePhoto $updatePhoto)
    {
    }

    public function __invoke(Request $request, Photo $photo): RedirectResponse
    {
        $this->updatePhoto->handle($photo, $request->all(), $request->file('image'));

        return redirect()->to('/photos');
    }
}

