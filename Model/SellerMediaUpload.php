<?php

declare(strict_types=1);

namespace Smile\Seller\Model;

use Exception;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * SellerMediaUpload Model.
 */
class SellerMediaUpload
{
    protected WriteInterface $mediaDirectory;

    public function __construct(
        protected ImageUploader $imageUploader,
        protected DirectoryList $directoryList,
        protected Database $coreFileStorageDatabase,
        Filesystem $filesystem,
        protected Mime $mime
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Remove media From Tmp Directory.
     *
     * @throws LocalizedException
     */
    public function removeMediaFromTmp(SellerInterface $seller): void
    {
        $media = str_replace($seller->getData('retailer_id') . '_', '', $seller->getMediaPath());
        $baseTmpPath = $this->imageUploader->getBaseTmpPath();
        if (!empty($media) && $this->pathExist($baseTmpPath, $media)) {
            $this->moveFileFromTmp($media, $seller);
        }
    }

    /**
     * Move media From Tmp Directory.
     *
     * @throws LocalizedException
     */
    public function moveFileFromTmp(string $imageName, SellerInterface $seller): void
    {
        $baseTmpPath = $this->imageUploader->getBaseTmpPath();
        $basePath = $this->imageUploader->getBasePath();

        $baseImagePath = $this->imageUploader->getFilePath(
            $basePath,
            $seller->getData('retailer_id') . '_' . $imageName
        );
        $baseTmpImagePath = $this->imageUploader->getFilePath($baseTmpPath, $imageName);

        try {
            $this->coreFileStorageDatabase->copyFile(
                $baseTmpImagePath,
                $baseImagePath
            );
            $this->mediaDirectory->renameFile(
                $baseTmpImagePath,
                $baseImagePath
            );
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }
    }

    /**
     * Remove File.
     *
     * @throws FileSystemException
     */
    public function removeMedia(SellerInterface $seller): void
    {
        $media = $seller->getData('retailer_id') . '_' . $seller->getMediaPath();
        $basePath = $this->imageUploader->getBasePath();
        if ($seller->getData('retailer_id') && $seller->getMediaPath() && $this->pathExist($basePath, $media)) {
            $this->removeFile($media);
        }
    }

    /**
     * Check if media exist.
     *
     * @throws FileSystemException
     */
    public function pathExist(string $basePath, string $fileName): bool
    {
        return file_exists($this->getPath($basePath, $fileName));
    }

    /**
     * Get media Path.
     *
     * @throws FileSystemException
     */
    public function getPath(string $basePath, string $fileName): string
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) .
            '/'
            . $this->imageUploader->getFilePath($basePath, $fileName);
    }

    /**
     * Remove File.
     *
     * @throws FileSystemException
     */
    public function removeFile(string $fileName): void
    {
        unlink($this->getPath($this->imageUploader->getBasePath(), $fileName));
    }

    /**
     * Retrieve MIME type of requested file.
     */
    public function getMimeType(string $fileName): string
    {
        $filePath = $this->getPath($this->imageUploader->getBasePath(), $fileName);
        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($filePath);

        $result = $this->mime->getMimeType($absoluteFilePath);

        return $result;
    }

    /**
     * Get file statistics data.
     */
    public function getStat(string $fileName): array
    {
        $filePath = $this->getPath($this->imageUploader->getBasePath(), $fileName);

        return $this->mediaDirectory->stat($filePath);
    }
}
