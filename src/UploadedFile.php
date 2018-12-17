<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFile implements UploadedFileInterface
{
    /** @var int[] */
    public const ERROR_STATUSES = [
        \UPLOAD_ERR_OK,
        \UPLOAD_ERR_INI_SIZE,
        \UPLOAD_ERR_FORM_SIZE,
        \UPLOAD_ERR_PARTIAL,
        \UPLOAD_ERR_NO_FILE,
        \UPLOAD_ERR_NO_TMP_DIR,
        \UPLOAD_ERR_CANT_WRITE,
        \UPLOAD_ERR_EXTENSION,
    ];

    /** @var string|null */
    private $clientFilename;

    /** @var string|null */
    private $clientMediaType;

    /** @var int */
    private $error;

    /** @var string|null */
    private $file;

    /** @var bool */
    private $moved = false;

    /** @var null|int */
    private $size;

    /** @var null|StreamInterface */
    private $stream;

    private function __construct()
    {
    }

    public static function builder(): UploadedFileBuilder
    {
        $instance = new static;
        $constructor = function (
            ?string $path,
            ?StreamInterface $stream,
            ?int $size,
            int $errorStatus,
            ?string $clientFileName,
            ?string $clientMediaType
        ) use ($instance): UploadedFile {
            $instance->file = $path;
            $instance->stream = $stream;
            $instance->size = $size;
            $instance->error = $errorStatus;
            $instance->clientFilename = $clientFileName;
            $instance->clientMediaType = $clientMediaType;
            return $instance;
        };
        return new class($constructor) extends UploadedFileBuilder {
        };
    }

    /**
     * @throws RuntimeException if is moved or not ok
     */
    private function validateActive(): void
    {
        if ($this->error !== \UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }
        if ($this->moved) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }

    public function getStream(): StreamInterface
    {
        $this->validateActive();
        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        assert($this->file !== null);
        $resource = \fopen($this->file, 'r');
        if ($resource === false) {
            throw new RuntimeException('Cannot open file "' . $this->file . ' for reading');
        }
        return Stream::fromResource($resource);
    }

    public function moveTo($targetPath): void
    {
        $this->validateActive();
        if (!\is_string($targetPath) || empty($targetPath)) {
            throw new \InvalidArgumentException('Invalid path provided for move operation; must be a non-empty string');
        }
        if ($this->file !== null) {
            $this->moved = 'cli' === PHP_SAPI ? \rename($this->file, $targetPath) : \move_uploaded_file($this->file, $targetPath);
        } else {
            $stream = $this->getStream();
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
            $target = \fopen($targetPath, 'w');
            if ($target === false) {
                throw new RuntimeException('Cannot open file "' . $targetPath . ' for writing');
            }
            $this->copyToStream($stream, Stream::fromResource($target));
            $this->moved = true;
        }
        if (!$this->moved) {
            throw new RuntimeException('Uploaded file could not be moved to ' . $targetPath);
        }
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * @author Michael Dowling and contributors to guzzlehttp/psr7
     * @param StreamInterface $source Stream to read from
     * @param StreamInterface $destination Stream to write to
     * @param int $maxLen Maximum number of bytes to read. Pass -1 to read the entire stream
     * @return void
     * @throws RuntimeException on error
     */
    private function copyToStream(StreamInterface $source, StreamInterface $destination, $maxLen = -1): void
    {
        if ($maxLen === -1) {
            while (!$source->eof()) {
                if (!$destination->write($source->read(1048576))) {
                    break;
                }
            }
            return;
        }
        $bytes = 0;
        while (!$source->eof()) {
            $buf = $source->read($maxLen - $bytes);
            if (!($len = \strlen($buf))) {
                break;
            }
            $bytes += $len;
            $destination->write($buf);
            if ($bytes === $maxLen) {
                break;
            }
        }
    }
}
