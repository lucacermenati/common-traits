<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasImages
{
    /**
     * Store the uploaded file for the given path attribute, deleting the file it replaces.
     * The new path (and optionally the original file name) is written in the model.
     */
    public function uploadFile(
        UploadedFile $file,
        string $directory,
        string $pathAttribute,
        ?string $nameAttribute = null,
        string $disk = 'public'
    ): void {
        $previousPath = $this->$pathAttribute;

        // Store the new file first so a failed store leaves the previous file untouched
        $this->$pathAttribute = $file->store($directory, $disk);

        if ($previousPath && $previousPath !== $this->$pathAttribute && Storage::disk($disk)->exists($previousPath)) {
            Storage::disk($disk)->delete($previousPath);
        }

        if ($nameAttribute) {
            $this->$nameAttribute = $file->getClientOriginalName();
        }

        $this->save();
    }

    public function removeFile(
        string $pathAttribute,
        ?string $nameAttribute = null,
        string $disk = 'public'
    ): void {
        if ($this->$pathAttribute && Storage::disk($disk)->exists($this->$pathAttribute)) {
            Storage::disk($disk)->delete($this->$pathAttribute);
        }

        $this->$pathAttribute = null;

        if ($nameAttribute) {
            $this->$nameAttribute = null;
        }

        $this->save();
    }
}
