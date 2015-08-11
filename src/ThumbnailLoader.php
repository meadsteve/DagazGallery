<?php

namespace MeadSteve\DagazGallery;

use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemInterface;

class ThumbnailLoader
{
    private $thumbnailStorage;
    private $originalStorage;
    private $imageManager;

    public function __construct(
        FilesystemInterface $thumbnailStorage,
        FilesystemInterface $originalStorage,
        ImageManager $imageManager= null
    ) {
        $this->thumbnailStorage = $thumbnailStorage;
        $this->originalStorage = $originalStorage;
        $this->imageManager = isset($imageManager) ? $imageManager : new ImageManager();
    }

    public function readStream($path)
    {
        if (!$this->thumbnailStorage->has($path)) {
            return $this->streamAndStoreNewThumbnail($path);
        }
        return $this->thumbnailStorage->readStream($path);
    }

    private function streamAndStoreNewThumbnail($path)
    {
        if (!$this->originalStorage->has($path)) {
            throw new \RuntimeException("File not found: " . $path);
        }
        $thumbnail = $this->makeThumbnail($path);
        $this->thumbnailStorage->write($path, (string)$thumbnail);
        $thumbnailStream = $thumbnail->stream();
        return $thumbnailStream;
    }

    private function makeThumbnail($path)
    {
        $originalRaw = $this->originalStorage->read($path);
        $original = $this->imageManager->make($originalRaw);
        $thumbnail = $original->resize(200, 200);
        return $thumbnail;
    }
}
