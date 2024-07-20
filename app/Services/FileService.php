<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class FileService
{
    public function validateFileType(UploadedFile $file, string $type): bool
    {
        $allowedType = DB::table('file_types')->where('name', $type)->first();
        if (!$allowedType) {
            return false;
        }
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize() / 1024; // Convert to KB
        return in_array($extension, explode(',', $allowedType->extensions)) && $size <= $allowedType->max_size;
    }
}