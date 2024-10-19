<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use App\FileHelper;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileHelperTest extends TestCase
{
    /**
     * Files created during the test.
     */
    private array $filesCreated = [];

    public function setUp(): void
    {
        parent::setUp();
        // Reset the filesCreated array before each test
        $this->filesCreated = [];
    }

    public function tearDown(): void
    {
        // Delete all created files after each test
        foreach ($this->filesCreated as $file) {
            FileHelper::deleteFile($file);
        }
        parent::tearDown();
    }

    public function test_file_exists(): void
    {
        // Create a file that will be checked later in this test
        $file = UploadedFile::fake()->image('avatar.jpg');
        $fileName = Storage::disk('public')->put('', $file);
        array_push($this->filesCreated, $fileName);

        $this->assertTrue(FileHelper::exists($fileName));
    }

    public function test_upload_file(): void
    {
        // Create a new fake file
        $file = UploadedFile::fake()->image('avatar.jpg');

        // Save the file using your helper class
        $fileName = FileHelper::saveFile($file);

        // Append the file to the list of created files
        array_push($this->filesCreated, $fileName);

        // Assert that the file exists
        $this->assertTrue(FileHelper::exists($fileName));
    }

    public function test_delete_file(): void
    {
        // Create a file that will be deleted later in this test
        $file = UploadedFile::fake()->image('avatar.jpg');
        $fileName = FileHelper::saveFile($file);
        array_push($this->filesCreated, $fileName);

        // Delete the file
        FileHelper::deleteFile($fileName);


        // Assert that the file has been deleted
        $this->assertFalse(FileHelper::exists($fileName));
    }
}
