<?php

use Hamlet\Http\Message\Uri;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

$generators = [
    'hamlet' => function () {
        return \Hamlet\Http\Message\ServerRequest::empty();
    },
    'guzzle' => function () {
        return new \GuzzleHttp\Psr7\ServerRequest('GET', '');
    },
    'hyholm' => function() {
        return new \Nyholm\Psr7\ServerRequest('GET', '');
    },
    'zend' => function () {
        return new \Zend\Diactoros\ServerRequest();
    }
];

$uri = Uri::parse('http://example.net/x?y=2');
$runs = 100000;
foreach ($generators as $name => $generator) {
    $start = microtime(true);
    for ($i = 0; $i < $runs; $i++) {
        /** @var ServerRequestInterface $request */
        $request = $generator();
        $request->withAttribute('a', '1')
            ->withUri($uri)
            ->withHeader('hOst', 'mail.ru')
            ->withAddedHeader('Language', 'ru')
            ->withAttribute('name', time());

        $request->getAttribute('host');
        $request->withoutHeader('host');
        $request->getAttribute('host');
    }
    $end = microtime(true);
    printf("%20s: %4.8f ms\n", $name, ($end - $start) * 1000 / $runs);
}
