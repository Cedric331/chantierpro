<?php

namespace App\UseCases\Photos;

use App\Models\Photo;

class DeletePhoto
{
    public function handle(Photo $photo): void
    {
        $photo->delete();
    }
}

