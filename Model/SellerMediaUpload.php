<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerPromotion
 * @author    Fanny DECLERCK <fadec@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\Seller\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\File\Mime;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * SellerMediaUpload Model
 *
 * @category Smile
 * @package  Smile\RetailerPromotion
 * @author   Fanny DECLERCK <fadec@smile.fr>
 */
class SellerMediaUpload
{
    /**
     * Image Uploader.
     *
     * @var ImageUploader
     */
    protected ImageUploader $imageUploader;

    /**
     * Directory List.
     *
     * @var DirectoryList
     */
    protected DirectoryList $directoryList;

    /**
     * Core file storage database
     *
     * @var Database
     */
    protected Database $coreFileStorageDatabase;

    /**
     * Media directory object (writable).
     *
     * @var WriteInterface
     */
    protected WriteInterface $mediaDirectory;

    /**
     * @var Mime
     */
    protected Mime $mime;

    /**
     * SellerMediaUpload constructor.
     *
     * @param ImageUploader $imageUploader           Image uploader
     * @param DirectoryList $directoryList           Directory List.
     * @param Database      $coreFileStorageDatabase File Storage Database.
     * @param Filesystem    $filesystem              File System.
     * @param Mime          $mime                    Mime helper.
     *
     * @throws FileSystemException
     */
    public function __construct(
        ImageUploader $imageUploader,
        DirectoryList $directoryList,
        Database $coreFileStorageDatabase,
        Filesystem $filesystem,
        Mime $mime
    ) {
        $this->imageUploader           = $imageUploader;
        $this->directoryList           = $directoryList;
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->mediaDirectory          = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mime                    = $mime;
    }

    /**
     * Remove media From Tmp Directory.
     *
     * @param SellerInterface $seller Seller.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeMediaFromTmp(SellerInterface $seller): void
    {
        $media = str_replace($seller->getRetailerId().'_', '', $seller->getMediaPath());
        $baseTmpPath = $this->imageUploader->getBaseTmpPath();
        if (!empty($media) && $this->pathExist($baseTmpPath, $media)) {
            $this->moveFileFromTmp($media, $seller);
        }
    }

    /**
     * Move media From Tmp Directory.
     *
     * @param string             $imageName Image name.
     * @param SellerInterface    $seller Seller.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function moveFileFromTmp(string $imageName, SellerInterface $seller): void
    {
        $baseTmpPath = $this->imageUploader->getBaseTmpPath();
        $basePath = $this->imageUploader->getBasePath();

        $baseImagePath = $this->imageUploader->getFilePath(
            $basePath,
            $seller->getRetailerId().'_'.$imageName
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
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }
    }

    /**
     * Remove File.
     *
     * @param SellerInterface $seller Seller.
     *
     * @return void
     * @throws FileSystemException
     */
    public function removeMedia(SellerInterface $seller): void
    {
        $media = $seller->getRetailerId().'_'.$seller->getMediaPath();
        $basePath = $this->imageUploader->getBasePath();
        if (!empty($media) && $this->pathExist($basePath, $media)) {
            $this->removeFile($media);
        }
    }

    /**
     * Check if media exist.
     *
     * @param string $basePath Base Path.
     * @param string $fileName File Name.
     *
     * @return bool
     * @throws FileSystemException
     */
    public function pathExist(string $basePath, string $fileName): bool
    {
        return file_exists($this->getPath($basePath, $fileName));
    }

    /**
     * Get media Path.
     *
     * @param string $basePath Base Path.
     * @param string $fileName File Name.
     *
     * @return string
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
     * @param string $fileName File Name.
     *
     * @return void
     *
     * @throws FileSystemException
     */
    public function removeFile(string $fileName): void
    {
        unlink($this->getPath($this->imageUploader->getBasePath(), $fileName));
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $fileName
     * @return string
     */
    public function getMimeType(string $fileName): string
    {
        $filePath = $this->getPath($this->imageUploader->getBasePath(), $fileName);
        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($filePath);

        $result = $this->mime->getMimeType($absoluteFilePath);

        return $result;
    }

    /**
     * Get file statistics data
     *
     * @param string $fileName
     * @return array
     */
    public function getStat(string $fileName): array
    {
        $filePath = $this->getPath($this->imageUploader->getBasePath(), $fileName);

        return $this->mediaDirectory->stat($filePath);
    }
}
