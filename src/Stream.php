<?php declare(strict_types=1);

namespace Hamlet\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

class Stream implements StreamInterface, LoggerAwareInterface
{
    /** @var resource|null */
    private $stream = null;

    /** @var bool */
    private $seekable = false;

    /** @var bool */
    private $readable = false;

    /** @var bool */
    private $writable = false;

    /** @var array|mixed|null|void */
    private $uri = null;

    /** @var int|null */
    private $size = null;

    /** @var LoggerInterface|null */
    private $logger = null;

    /** @var array Hash of readable and writable stream types */
    private static $readWriteHash = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];

    protected function __construct()
    {
    }

    public static function empty(): StreamInterface
    {
        return static::fromString('');
    }

    /**
     * @param resource $resource
     * @return StreamInterface
     * @psalm-suppress MixedArrayOffset
     */
    public static function fromResource($resource): StreamInterface
    {
        if (!\is_resource($resource)) {
            throw new InvalidArgumentException('Invalid resource provided');
        }
        $instance = new static;
        $instance->stream = $resource;
        $meta = \stream_get_meta_data($instance->stream);
        $instance->seekable = (bool) $meta['seekable'];
        $instance->readable = isset(self::$readWriteHash['read'][$meta['mode']]);
        $instance->writable = isset(self::$readWriteHash['write'][$meta['mode']]);
        $instance->uri = $instance->getMetadata('uri');
        return $instance;
    }

    public static function fromString(string $data): StreamInterface
    {
        $resource = \fopen('php://temp', 'rw+');
        if ($resource === false) {
            throw new RuntimeException('Cannot open temporary stream');
        }
        \fwrite($resource, $data);
        return static::fromResource($resource);
    }

    /**
     * Closes the stream when the destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }
            return $this->getContents();
        } catch (\Exception $e) {
            $this->getLogger()->warning('Cannot get stream string representation', ['exception' => $e]);
            return '';
        }
    }

    public function close(): void
    {
        if (isset($this->stream)) {
            if (\is_resource($this->stream)) {
                \fclose($this->stream);
            }
            $this->detach();
        }
    }

    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }
        $result = $this->stream;
        $this->stream = null;
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;
        return $result;
    }

    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }
        if ($this->stream === null) {
            return null;
        }
        // Clear the stat cache if the stream has a URI
        if ($this->uri && \is_string($this->uri)) {
            \clearstatcache(true, $this->uri);
        }
        $stats = \fstat($this->stream);
        if ($stats !== false && isset($stats['size'])) {
            $this->size = (int) $stats['size'];
            return $this->size;
        }
        return null;
    }

    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('No resource');
        }
        $result = \ftell($this->stream);
        /** @psalm-suppress TypeDoesNotContainType */
        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }
        return $result;
    }

    public function eof(): bool
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('No resource');
        }
        return \feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = \SEEK_SET): void
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Unable to read stream contents');
        }
        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }
        if (\fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to stream position ' . $offset . ' with whence ' . \var_export($whence, true));
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write($string): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Unable to read stream contents');
        }
        if (!$this->writable) {
            throw new RuntimeException('Cannot write to a non-writable stream');
        }
        // We can't know the size after writing anything
        $this->size = null;
        $result = \fwrite($this->stream, $string);
        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }
        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read($length): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Unable to read stream contents');
        }
        if (!\is_int($length) || $length < 0) {
            throw new InvalidArgumentException('Length must be a non-negative number');
        }
        if (!$this->readable) {
            throw new RuntimeException('Cannot read from non-readable stream');
        }
        if ($length === 0) {
            return '';
        }
        $result = \fread($this->stream, $length);
        if ($result === false) {
            throw new RuntimeException('Cannot read from stream');
        }
        return $result;
    }

    public function getContents(): string
    {
        if (!$this->readable) {
            throw new RuntimeException('Stream non-readable');
        }
        if (!isset($this->stream)) {
            throw new RuntimeException('Unable to read stream contents');
        }
        $contents = \stream_get_contents($this->stream);
        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents');
        }
        return $contents;
    }

    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        } elseif (null === $key) {
            return \stream_get_meta_data($this->stream);
        }
        $meta = \stream_get_meta_data($this->stream);
        return isset($meta[$key]) ? $meta[$key] : null;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    protected function getLogger(): LoggerInterface
    {
        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }
        return $this->logger;
    }
}
