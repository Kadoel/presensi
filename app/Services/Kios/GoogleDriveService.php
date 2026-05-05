<?php

namespace App\Services\Kios;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use RuntimeException;

class GoogleDriveService
{
    protected Drive $drive;
    protected string $folderId;

    public function __construct()
    {
        $credentialPath = ROOTPATH . env('GOOGLE_DRIVE_CREDENTIAL_PATH');
        $this->folderId = (string) env('GOOGLE_DRIVE_SELFIE_FOLDER_ID');

        if (! is_file($credentialPath)) {
            throw new RuntimeException('Google credential tidak ditemukan');
        }

        if ($this->folderId === '') {
            throw new RuntimeException('Google Drive folder ID belum diset');
        }

        $client = new Client();
        $client->setAuthConfig($credentialPath);
        $client->addScope(Drive::DRIVE_FILE);

        $this->drive = new Drive($client);
    }

    public function uploadFile(
        string $localPath,
        string $fileName,
        string $mimeType = 'image/jpeg',
        ?string $folderId = null
    ): object {
        if (! is_file($localPath)) {
            throw new RuntimeException('File lokal tidak ditemukan');
        }

        $fileMetadata = new DriveFile();
        $fileMetadata->setName($fileName);
        $fileMetadata->setParents([$folderId ?: $this->folderId]);

        $file = $this->drive->files->create($fileMetadata, [
            'data'              => file_get_contents($localPath),
            'mimeType'          => $mimeType,
            'uploadType'        => 'multipart',
            'fields'            => 'id, name, webViewLink, webContentLink',
            'supportsAllDrives' => true,
        ]);

        return (object) [
            'id'               => $file->getId(),
            'name'             => $file->getName(),
            'web_view_link'    => $file->getWebViewLink(),
            'web_content_link' => $file->getWebContentLink(),
        ];
    }

    protected function findFolder(string $name, string $parentId): ?string
    {
        $query = sprintf(
            "name = '%s' and mimeType = 'application/vnd.google-apps.folder' and '%s' in parents and trashed = false",
            addslashes($name),
            addslashes($parentId)
        );

        $result = $this->drive->files->listFiles([
            'q'                     => $query,
            'fields'                => 'files(id, name)',
            'supportsAllDrives'     => true,
            'includeItemsFromAllDrives' => true,
        ]);

        $files = $result->getFiles();

        return ! empty($files) ? $files[0]->getId() : null;
    }

    protected function createFolder(string $name, string $parentId): string
    {
        $folderMetadata = new DriveFile();
        $folderMetadata->setName($name);
        $folderMetadata->setMimeType('application/vnd.google-apps.folder');
        $folderMetadata->setParents([$parentId]);

        $folder = $this->drive->files->create($folderMetadata, [
            'fields'            => 'id',
            'supportsAllDrives' => true,
        ]);

        return $folder->getId();
    }

    protected function getOrCreateFolder(string $name, string $parentId): string
    {
        $folderId = $this->findFolder($name, $parentId);

        if ($folderId !== null) {
            return $folderId;
        }

        return $this->createFolder($name, $parentId);
    }

    public function getOrCreateNestedFolder(array $paths): string
    {
        $parentId = $this->folderId;

        foreach ($paths as $folderName) {
            $folderName = trim((string) $folderName);

            if ($folderName === '') {
                continue;
            }

            $parentId = $this->getOrCreateFolder($folderName, $parentId);
        }

        return $parentId;
    }
}
