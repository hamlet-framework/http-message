<?php declare(strict_types=1);

namespace Hamlet\Http\Message\Traits;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

trait MessageValidatorTrait
{
    /** @var array<int,string> */
    protected static $REASON_PHRASES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated to 306 => '(Unused)'
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * @param mixed $version
     * @return string
     */
    protected function validateProtocolVersion($version): string
    {
        if (!\is_string($version)) {
            throw new InvalidArgumentException('Protocol version must be a string');
        }
        if (!\preg_match('/^(0\.[1-9]\d*|[1-9]\d*(\.\d+)?)$/', $version)) {
            throw new InvalidArgumentException('Invalid protocol version "' . $version . '"');
        }
        return $version;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     * @return string[]
     */
    protected function validateAndNormalizeHeader($name, $value): array
    {
        if (!\is_string($name)) {
            throw new InvalidArgumentException('Header name must be a string');
        }
        if (!\preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name)) {
            throw new InvalidArgumentException('Invalid header name: "' . $name . '"');
        }

        if (\is_array($value) && empty($value)) {
            throw new InvalidArgumentException('Header values must be a string or an array of strings, empty array given.');
        }

        $values = \is_array($value) ? $value : [$value];
        $normalizedValues = [];

        if (\strtolower($name) == 'host') {
            if (count($values) !== 1) {
                throw new InvalidArgumentException('Host header must have a single value');
            }
            $values = [\strtolower(\array_shift($values))];
        }

        foreach ($values as $value) {
            if (!\is_string($value) && !\is_int($value)) {
                throw new InvalidArgumentException('Header values must be strings');
            }
            if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", (string) $value)) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }
            if (preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', (string) $value)) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }
            $normalizedValues[] = \trim((string) $value, " \t");
        }

        return $normalizedValues;
    }

    protected function validateRequestTarget($target): string
    {
        if (!\is_string($target)) {
            throw new InvalidArgumentException('Request target must be a string');
        }
        if (\preg_match('#\s#', $target)) {
            throw new InvalidArgumentException('Request target cannot contain whitespace');
        }
        return $target;
    }

    protected function validateMethod($method): string
    {
        if (!\is_string($method)) {
            throw new InvalidArgumentException('Method must be a string');
        }
        if (!\preg_match('/^[a-zA-Z]+$/', $method)) {
            throw new InvalidArgumentException('Method name must consist of ASCII characters');
        }
        return $method;
    }

    protected function validateUploadedFiles($uploadedFiles): array
    {
        if (!\is_array($uploadedFiles)) {
            throw new InvalidArgumentException('Uploaded files must be an array');
        }
        foreach ($uploadedFiles as $item) {
            if (\is_array($item)) {
                $this->validateUploadedFiles($item);
            }
            if (!($item instanceof UploadedFileInterface)) {
                throw new InvalidArgumentException('Uploaded files must implement UploadedFileInterface');
            }
        }
        return $uploadedFiles;
    }

    protected function validateBody($body): StreamInterface
    {
        if (!($body instanceof StreamInterface)) {
            throw new InvalidArgumentException('Body must be of type StreamInterface');
        }
        return $body;
    }

    protected function validateAndNormalizeStatusCodeAndReasonPhrase($code, $phrase): array
    {
        if (!\is_int($code)) {
            throw new InvalidArgumentException('Status code must be an integer');
        }
        if ($code < 100 || 599 < $code) {
            throw new InvalidArgumentException('Invalid status code, must be in [100, 599] range');
        }
        if (!\is_string($phrase)) {
            throw new InvalidArgumentException('Reason phrase must be a string');
        }
        if ($phrase === '') {
            $phrase = self::$REASON_PHRASES[$code] ?? '';
        }
        return [$code, $phrase];
    }

    /**
     * @param mixed $body
     * @return array|object|null
     */
    protected function validateParsedBody($body)
    {
        if (!\is_array($body) && !\is_object($body) && !\is_null($body)) {
            throw new InvalidArgumentException('Parsed body needs be an array, an object or null');
        }
        return $body;
    }

    protected function validateQueryParams($queryParams): array
    {
        if (!\is_array($queryParams)) {
            throw new InvalidArgumentException('Query params must be an array');
        }
        $validatedParams = [];
        foreach ($queryParams as $key => $value) {
            if (!\is_string($key) && !\is_int($key)) {
                throw new InvalidArgumentException('Keys in query params must be strings or integers');
            }
            if (\is_string($value)) {
                $validatedParams[$key] = $value;
            } elseif (\is_array($value)) {
                $validatedParams[$key] = $this->validateQueryParams($value);
            } else {
                throw new InvalidArgumentException('Query param values must be strings or other query params');
            }
        }
        return $validatedParams;
    }

    public function validateCookieParams($cookieParams): array
    {
        if (!\is_array($cookieParams)) {
            throw new InvalidArgumentException('Cookie params must be an array');
        }
        foreach ($cookieParams as $key => $value) {
            if (!\is_string($key) || !\is_string($value)) {
                throw new InvalidArgumentException('Cookie params must be an array<string,string>');
            }
        }
        return $cookieParams;
    }

    public function validateAttributes(array $attributes): array
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($attributes as $name => &$_) {
            if (!\is_string($name)) {
                throw new InvalidArgumentException('Attribute names must be strings');
            }
        }
        return $attributes;
    }
}
