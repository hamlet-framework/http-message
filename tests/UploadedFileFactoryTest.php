<?php

namespace Hamlet\Http\Message;

use PHPUnit\Framework\TestCase;

class UploadedFileFactoryTest extends TestCase
{
    public function test_create_uploaded_file()
    {
        $factory = new UploadedFileFactory;
        $stream = Stream::fromString('test');
        $file = $factory->createUploadedFile($stream, 4, UPLOAD_ERR_OK, 'test.txt', 'text/plain');

        $this->assertSame($stream, $file->getStream());
        $this->assertSame(4, $file->getSize());
        $this->assertSame(UPLOAD_ERR_OK, $file->getError());
        $this->assertSame('test.txt', $file->getClientFilename());
        $this->assertSame('text/plain', $file->getClientMediaType());
    }
}
