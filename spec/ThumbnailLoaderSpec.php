<?php

namespace spec\MeadSteve\DagazGallery;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\StreamInterface;

class ThumbnailLoaderSpec extends ObjectBehavior
{
    function let(FilesystemInterface $thumbnailStorage, FilesystemInterface $originalStorage, ImageManager $imageManager)
    {
        // Thumbnail storage is writable
        $thumbnailStorage->write(Argument::any(), Argument::any())->willReturn(true);
        $this->beConstructedWith($thumbnailStorage, $originalStorage, $imageManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('MeadSteve\DagazGallery\ThumbnailLoader');
    }

    function it_returns_thumbnails_already_in_storage(FilesystemInterface $thumbnailStorage, $thumbnailStream)
    {
        $requestPath = "folder/my-file.jpg";
        $thumbnailStorage->has($requestPath)->willReturn(true);
        $thumbnailStorage->readStream($requestPath)->willReturn($thumbnailStream);

        $this->readStream($requestPath)->shouldReturn($thumbnailStream);
        $thumbnailStorage->has($requestPath)->shouldHaveBeenCalled();
    }

    function it_creates_thumbnails_if_not_already_made(
        FilesystemInterface $thumbnailStorage,
        FilesystemInterface $originalStorage,
        ImageManager $imageManager,
        $originalImageBinary,
        Image $originalImage,
        Image $thumbnailImage,
        StreamInterface $thumbnailStream
    ) {
        $requestPath = "folder/my-file.jpg";
        $thumbnailStorage->has($requestPath)->willReturn(false);

        $originalStorage->has($requestPath)->willReturn(true);
        $originalStorage->read($requestPath)->willReturn($originalImageBinary);

        $imageManager->make($originalImageBinary)->willReturn($originalImage);
        $originalImage->resize(200, 200)->willReturn($thumbnailImage);
        $thumbnailImage->stream()->willReturn($thumbnailStream);
        $thumbnailRaw = "fdgdfgdfgdgdfgdfg";
        $thumbnailImage->__toString()->willReturn($thumbnailRaw);

        $this->readStream($requestPath)->shouldReturn($thumbnailStream);

        $thumbnailStorage->write($requestPath, $thumbnailRaw)->shouldHaveBeenCalled();
    }

}
