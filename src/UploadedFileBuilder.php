<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

abstract class UploadedFileBuilder
{
    /**
     * @var callable(string|null,StreamInterface|null,int|null,int,string|null,string|null):UploadedFile
     */
    private $constructor;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var StreamInterface|null
     */
    private $stream;

    /**
     * @var int|null
     */
    private $size;

    /**
     * @var int|null
     */
    private $errorStatus;

    /**
     * @var string|null
     */
    private $clientFileName;

    /**
     * @var string|null
     */
    private $clientMediaType;

    /**
     * @param callable(string|null,StreamInterface|null,int|null,int,string|null,string|null):UploadedFile $constructor
     */
    public function __construct(callable $constructor)
    {
        $this->constructor = $constructor;
    }

    /**
     * @param resource $resource
     * @return static
     */
    public function withResource($resource)
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (!\is_resource($resource)) {
            throw new InvalidArgumentException('Invalid resource provided');
        }
        if ($this->path !== null || $this->stream !== null) {
            throw new InvalidArgumentException('Path or stream already specified.');
        }
        $this->stream = Stream::fromResource($resource);
        return $this;
    }

    /**
     * @param StreamInterface $stream
     * @return static
     */
    public function withStream(StreamInterface $stream)
    {
        if ($this->path !== null || $this->stream !== null) {
            throw new InvalidArgumentException('Path or stream already specified.');
        }
        $this->stream = $stream;
        return $this;
    }

    /**
     * @param string $path
     * @return static
     */
    public function withPath(string $path)
    {
        if ($this->path !== null || $this->stream !== null) {
            throw new InvalidArgumentException('Path or stream already specified.');
        }
        $this->path = $path;
        return $this;
    }

    /**
     * @param int $size
     * @return static
     */
    public function withSize(int $size)
    {
        if ($this->size !== null) {
            throw new InvalidArgumentException('Size already specified.');
        }
        if ($size < 0) {
            throw new InvalidArgumentException('File size must be a non-negative number');
        }
        $this->size = $size;
        return $this;
    }

    /**
     * @param int $status
     * @return static
     */
    public function withErrorStatus(int $status)
    {
        if ($this->errorStatus !== null) {
            throw new InvalidArgumentException('Error status already specified.');
        }
        /** @psalm-suppress MixedArgument */
        if (!\in_array($status, UploadedFile::ERROR_STATUSES)) {
            throw new InvalidArgumentException('Unknown error status "' . $status . '" set.');
        }
        $this->errorStatus = $status;
        return $this;
    }

    /**
     * @param string|null $fileName
     * @return static
     */
    public function withClientFileName(?string $fileName)
    {
        if ($fileName !== null) {
            if ($this->clientFileName !== null) {
                throw new InvalidArgumentException('Client file name already specified.');
            }
            if (strpos($fileName, '/') !== false || strpos($fileName, '\0') !== false) {
                throw new InvalidArgumentException('Invalid client file name provided.');
            }
            $this->clientFileName = $fileName;
        }
        return $this;
    }

    /**
     * @param string|null $mediaType
     * @return static
     */
    public function withClientMediaType(?string $mediaType)
    {
        if ($mediaType !== null) {
            if ($this->clientMediaType !== null) {
                throw new InvalidArgumentException('Client media type name already specified.');
            }
            // https://stackoverflow.com/a/48046041/1646086
            if (!\preg_match('#^\w+/[-.\w]+(?:\+[-.\w]+)?$#', $mediaType)) {
                throw new InvalidArgumentException('Invalid media type specified.');
            }
            $this->clientMediaType = $mediaType;
        }
        return $this;
    }

    public function build(): UploadedFile
    {
        if ($this->path === null && $this->stream === null) {
            throw new InvalidArgumentException('Path or stream required.');
        }
        if ($this->size === null) {
            throw new InvalidArgumentException('Size not specified.');
        }
        if ($this->errorStatus === null) {
            throw new InvalidArgumentException('Error status not specified.');
        }
        return ($this->constructor)($this->path, $this->stream, $this->size, $this->errorStatus, $this->clientFileName, $this->clientMediaType);
    }
}
