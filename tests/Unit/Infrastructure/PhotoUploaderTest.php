<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Infrastructure\Upload\PhotoUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoUploaderTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/photo_uploader_test_' . bin2hex(random_bytes(4));
        mkdir($this->tmpDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    public function testUploadValidJpeg(): void
    {
        $uploader = new PhotoUploader($this->tmpDir);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.jpg';
        // Minimal valid JFIF JPEG recognized by getimagesize()
        $jpeg = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00"
            . "\xFF\xDB\x00\x43\x00\x08\x06\x06\x07\x06\x05\x08\x07\x07\x07\x09\x09"
            . "\x08\x0A\x0C\x14\x0D\x0C\x0B\x0B\x0C\x19\x12\x13\x0F\x14\x1D\x1A\x1F"
            . "\x1E\x1D\x1A\x1C\x1C\x20\x24\x2E\x27\x20\x22\x2C\x23\x1C\x1C\x28\x37"
            . "\x29\x2C\x30\x31\x34\x34\x34\x1F\x27\x39\x3D\x38\x32\x3C\x2E\x33\x34\x32"
            . "\xFF\xC0\x00\x0B\x08\x00\x01\x00\x01\x01\x01\x11\x00"
            . "\xFF\xC4\x00\x1F\x00\x00\x01\x05\x01\x01\x01\x01\x01\x01\x00\x00\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B"
            . "\xFF\xC4\x00\x1F\x01\x00\x03\x01\x01\x01\x01\x01\x01\x01\x01\x01\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B"
            . "\xFF\xDA\x00\x08\x01\x01\x00\x00\x3F\x00\x7B\x40"
            . "\xFF\xD9";
        file_put_contents($tmpFile, $jpeg);

        $file = new UploadedFile($tmpFile, 'photo.jpg', 'image/jpeg', null, true);

        $result = $uploader->upload([$file], 'test-estimation-id');

        $this->assertCount(1, $result);
        $this->assertStringStartsWith('test-estimation-id/', $result[0]);
    }

    public function testRejectsOversizedFile(): void
    {
        $uploader = new PhotoUploader($this->tmpDir);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        // Simulate oversized file via a mock
        $file = $this->createMock(UploadedFile::class);
        $file->method('isValid')->willReturn(true);
        $file->method('getSize')->willReturn(10 * 1024 * 1024); // 10 Mo
        $file->method('getMimeType')->willReturn('image/jpeg');

        $result = $uploader->upload([$file], 'test-id');

        $this->assertCount(0, $result);
    }

    public function testRejectsNonImageMime(): void
    {
        $uploader = new PhotoUploader($this->tmpDir);

        $file = $this->createMock(UploadedFile::class);
        $file->method('isValid')->willReturn(true);
        $file->method('getSize')->willReturn(1024);
        $file->method('getMimeType')->willReturn('application/pdf');

        $result = $uploader->upload([$file], 'test-id');

        $this->assertCount(0, $result);
    }

    public function testGetPublicPath(): void
    {
        $uploader = new PhotoUploader($this->tmpDir);

        $this->assertEquals(
            '/uploads/estimations/abc/photo.jpg',
            $uploader->getPublicPath('abc/photo.jpg')
        );
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
