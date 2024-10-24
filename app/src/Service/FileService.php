<?php

namespace App\Service;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class FileService
{
    public function __construct(
        #[Autowire('%uploaded_files_directory%')] private readonly string $uploadDir,
        private readonly EntityManagerInterface $em,
    ) {}

    public function uploadFile(UploadedFile $file): File
    {
        $fs = new Filesystem();
        if (!$fs->exists($this->uploadDir)) {
            $fs->mkdir($this->uploadDir);
        }

        $fileObject = new File();
        $fileObject->setFilename(Uuid::v7()->toRfc4122());

        $file->move($this->uploadDir, $fileObject->getFilename());
        $this->em->persist($fileObject);

        return $fileObject;
    }

    public function readJsonFile(File $file): array
    {
        $fileContent = file_get_contents($this->uploadDir . '/' . $file->getFilename());
        if (false === $fileContent) {
            throw new Exception('File content not readable');
        }

        $json = json_decode($fileContent, true);
        if (!is_array($json)) {
            throw new Exception('File content is not a valid JSON');
        }

        return $json;
    }
}
