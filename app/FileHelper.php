<?php

namespace App;

use Illuminate\Support\Facades\Storage;

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

    public static function deleteFile(string $fileName)
    {
        $fileName = self::removeFilePrefix($fileName);
        if (Storage::disk("public")->exists($fileName)) {
            Storage::disk("public")->delete($fileName);
        }
    }

    public static function joinPaths(string ...$paths): string
    {
        return preg_replace('~[/\\\\]+~', DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $paths));
    }

    public static function exists(string $fileName): bool
    {
        return Storage::disk("public")->exists(self::removeFilePrefix($fileName));
    }

    public static function removeFilePrefix(string $fileName)
    {
        return str_replace("storage" . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR, "", $fileName);
    }
}
