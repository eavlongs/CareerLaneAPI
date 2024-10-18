<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{

    public static function saveFile($file, string|null $fileToReplace = null): string
    {
        // save file to the specified directory
        $path = Storage::disk('public')->put('', $file);

        // delete the old file if it exists
        if ($fileToReplace) {
            self::deleteFile($fileToReplace);
        }

        return self::joinPaths("storage", "uploads", $path);
    }

    public static function deleteFile(string $fileName): void
    {
        if (Storage::disk('public')->exists(self::joinPaths($fileName))) {
            Storage::disk('public')->delete(self::joinPaths($fileName));
        }
    }

    public static function joinPaths(string ...$paths): string
    {
        return preg_replace('~[/\\\\]+~', DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $paths));
    }
}
