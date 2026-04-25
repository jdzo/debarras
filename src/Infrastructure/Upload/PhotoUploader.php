<?php

declare(strict_types=1);

namespace App\Infrastructure\Upload;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class PhotoUploader
{
    private const MAX_SIZE_BYTES = 5 * 1024 * 1024; // 5 Mo
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private readonly string $uploadDir,
    ) {
    }

    /**
     * @param UploadedFile[] $files
     * @return string[] Noms de fichiers uploadés
     */
    public function upload(array $files, string $estimationId): array
    {
        $filenames = [];
        $targetDir = $this->uploadDir . '/' . $estimationId;

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            if ($file->getSize() > self::MAX_SIZE_BYTES) {
                continue;
            }

            $mime = $file->getMimeType();
            if (!in_array($mime, self::ALLOWED_MIMES, true)) {
                continue;
            }

            if (@getimagesize($file->getPathname()) === false) {
                continue;
            }

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $extension = $file->guessExtension() ?? 'jpg';
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;

            $file->move($targetDir, $filename);
            $filenames[] = $estimationId . '/' . $filename;
        }

        return $filenames;
    }

    public function getPublicPath(string $filename): string
    {
        return '/uploads/estimations/' . $filename;
    }
}
