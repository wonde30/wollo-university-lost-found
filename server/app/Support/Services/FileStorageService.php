<?php

namespace App\Support\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileStorageService
{
    public function storePublic(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    public function storePrivate(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'local');
    }

    public function delete(string $path, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }
        return false;
    }
}
