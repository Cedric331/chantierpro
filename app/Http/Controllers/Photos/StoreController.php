<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\UseCases\Photos\CreatePhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreatePhoto $createPhoto)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $file = $request->file('image');

        if (! $file) {
            return redirect()->to('/photos');
        }

        $this->createPhoto->handle($account, $request->all(), $file);

        return redirect()->to('/photos');
    }
}

