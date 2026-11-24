<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LucaCermenati\CommonTraits\Tests\Fixtures\Document;
use LucaCermenati\CommonTraits\Tests\TestCase;

class HasAttachmentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_upload_stores_file_and_persists_attributes(): void
    {
        $document = Document::create();

        $document->uploadFile(UploadedFile::fake()->create('cv.pdf', 10), 'docs', 'path', 'name');

        $fresh = $document->fresh();
        $this->assertStringStartsWith('docs/', $fresh->path);
        $this->assertSame('cv.pdf', $fresh->name);
        Storage::disk('public')->assertExists($fresh->path);
    }

    public function test_upload_replaces_previous_file(): void
    {
        $document = Document::create();
        $document->uploadFile(UploadedFile::fake()->create('old.pdf', 10), 'docs', 'path', 'name');
        $oldPath = $document->path;

        $document->uploadFile(UploadedFile::fake()->create('new.pdf', 10), 'docs', 'path', 'name');

        $this->assertNotSame($oldPath, $document->path);
        $this->assertSame('new.pdf', $document->fresh()->name);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($document->path);
    }

    public function test_remove_deletes_file_and_clears_attributes(): void
    {
        $document = Document::create();
        $document->uploadFile(UploadedFile::fake()->create('cv.pdf', 10), 'docs', 'path', 'name');
        $path = $document->path;

        $document->removeFile('path', 'name');

        $fresh = $document->fresh();
        $this->assertNull($fresh->path);
        $this->assertNull($fresh->name);
        Storage::disk('public')->assertMissing($path);
    }
}
