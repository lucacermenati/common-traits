<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasImages
{
    /**
     * Store and upload file for the given path attribute, deleting the file it replaces.
     * The new path (and optionally the original file name) is written in the model.
     */
    public function uploadFile(
        UploadedFile $file, 
        string $directory,
        string $pathAttribute,
        ?string $nameAttribute = null,
        ?string $disk = 'public'
    ): void {
        if ($this->$pathAttribute && Storage::disk($disk)->exists($this->$pathAttribute)) {
            // Delete old file
            Storage::disk($disk)->delete($this->$pathAttribute);
        }
                
        // Upload new file
        $this->$pathAttribute = $file->store($directory, $disk);
        
        if ($nameAttribute) {
            $this->$nameAttribute = $file->getClientOriginalName();
        }
        
        $this->save();
    }

    public function removeFile(
        string $pathAttribute, 
        ?string $nameAttribute = null, 
        ?string $disk = 'public'
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
    