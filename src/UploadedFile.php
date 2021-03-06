<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use function fopen;
use function is_string;
use function move_uploaded_file;
use function rename;
use function strlen;
use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_PARTIAL;

class UploadedFile implements UploadedFileInterface
{
    const ERROR_STATUSES = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION,
    ];

    /** @var string|null */
    private $clientFilename;

    /** @var string|null */
    private $clientMediaType;

    /** @var int */
    private $error = UPLOAD_ERR_NO_FILE;

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
        $constructor =
            /**
             * @param string|null          $path
             * @param StreamInterface|null $stream
             * @param int|null             $size
             * @param int                  $errorStatus
             * @param string|null          $clientFileName
             * @param string|null          $clientMediaType
             * @return UploadedFile
             */
            function ($path, $stream, $size, int $errorStatus, $clientFileName, $clientMediaType) use ($instance): UploadedFile {
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
     * @return void
     * @throws RuntimeException if is moved or not ok
     */
    private function validateActive()
    {
        if ($this->error !== UPLOAD_ERR_OK) {
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
        if (!file_exists($this->file)) {
            throw new RuntimeException('Cannot find file "' . $this->file . '"');
        }
        $resource = fopen($this->file, 'r');
        if ($resource === false) {
            throw new RuntimeException('Cannot open file "' . $this->file . ' for reading');
        }
        return Stream::fromResource($resource);
    }

    /**
     * @param string $targetPath
     * @return void
     */
    public function moveTo($targetPath)
    {
        $this->validateActive();
        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_string($targetPath) || empty($targetPath)) {
            throw new InvalidArgumentException('Invalid path provided for move operation; must be a non-empty string');
        }
        if ($this->file !== null) {
            $this->moved = 'cli' === PHP_SAPI ? rename($this->file, $targetPath) : move_uploaded_file($this->file, $targetPath);
        } else {
            $stream = $this->getStream();
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
            $target = fopen($targetPath, 'w');
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

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @return string|null
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * @return string|null
     */
    public function getClientMediaType()
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
    private function copyToStream(StreamInterface $source, StreamInterface $destination, int $maxLen = -1)
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
            if (!($len = strlen($buf))) {
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
