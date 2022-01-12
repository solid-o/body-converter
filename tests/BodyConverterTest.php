<?php

declare(strict_types=1);

namespace Solido\BodyConverter\Tests;

use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Solido\BodyConverter\BodyConverter;
use Solido\BodyConverter\Decoder\DecoderInterface;
use Solido\BodyConverter\Decoder\DecoderProvider;
use Solido\BodyConverter\Decoder\DecoderProviderInterface;
use Solido\BodyConverter\Decoder\FormDecoder;
use Solido\BodyConverter\Decoder\JsonDecoder;
use Solido\Common\AdapterFactoryInterface;
use Solido\Common\RequestAdapter\SymfonyHttpFoundationRequestAdapter;
use Symfony\Component\HttpFoundation\Request;

class BodyConverterTest extends TestCase
{
    use ProphecyTrait;

    private BodyConverter $converter;

    protected function setUp(): void
    {
        $provider = new DecoderProvider(['form' => new FormDecoder(), 'json' => new JsonDecoder()]);
        $this->converter = new BodyConverter($provider);
    }

    public function testShouldSetDefaultDecoders(): void
    {
        $this->converter = new BodyConverter();

        $request = new Request([], [], [], [], [], [], '{ "options": { "option": false } }');
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('Content-Type', 'application/json');

        self::assertEquals(['options' => ['option' => '0']], $this->converter->decode($request));

        $request = new Request([], [], [], [], [], [], 'options[option]=0');
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

        self::assertEquals(['options' => ['option' => '0']], $this->converter->decode($request));
    }

    public function testShouldDecodeContentCorrectly(): void
    {
        $request = new Request([], [], [], [], [], [], '{ "options": { "option": false } }');
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('Content-Type', 'application/json');

        self::assertEquals(['options' => ['option' => '0']], $this->converter->decode($request));
    }

    public function testShouldDecodeContentCorrectlyFromPsrServerRequest(): void
    {
        $request = new ServerRequest(
            'POST',
            'http://localhost/',
            ['Content-Type' => 'application/json'],
            '{ "options": { "option": false } }'
        );

        self::assertEquals(['options' => ['option' => '0']], $this->converter->decode($request));
    }

    public function testShouldReturnACopyOfOriginalParameterBagIfEmptyContentTypeHasPassed(): void
    {
        $request = new Request([], ['test'], [], [], [], [], 'options[option]=0');
        $request->headers->set('Content-Type', '');
        $request->setMethod(Request::METHOD_POST);

        $ret = $this->converter->decode($request);

        self::assertEquals(['test'], $ret);
    }

    /**
     * @dataProvider provideGetMethods
     */
    public function testShouldReturnACopyOfOriginalParameterBagIfGetOrHeadMethodHasPassed(string $method): void
    {
        $request = (new ServerRequest('GET', 'http://localhost/'))
            ->withParsedBody(['test'])
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Stream::create('options[option]=0'));

        $ret = $this->converter->decode($request);

        self::assertEquals(['test'], $ret);
    }

    public function provideGetMethods(): iterable
    {
        yield ['GET'];
        yield ['HEAD'];
    }

    public function testShouldReturnACopyOfParsedBodyIfFormDataHasBeenPassed(): void
    {
        $request = (new ServerRequest('POST', 'http://localhost/'))
            ->withBody(Stream::create('options[option]=0'));

        $ret = $this->converter->decode($request);

        self::assertEquals(['options' => ['option' => '0']], $ret);
    }

    public function testShouldUseInjectedObjects(): void
    {
        $provider = $this->prophesize(DecoderProviderInterface::class);
        $adapter = $this->prophesize(AdapterFactoryInterface::class);

        $request = new Request([], [], [], [], [], [], '{}');
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('Content-Type', 'application/json');

        $adapter->createRequestAdapter($request)
            ->shouldBeCalledOnce()
            ->willReturn(new SymfonyHttpFoundationRequestAdapter($request));

        $decoder = $this->prophesize(DecoderInterface::class);
        $decoder->decode('{}')->willReturn(['options']);

        $provider->supports('json')->willReturn(true);
        $provider->get('json')
            ->shouldBeCalledOnce()
            ->willReturn($decoder);

        $this->converter = new BodyConverter($provider->reveal(), $adapter->reveal());
        self::assertEquals(['options'], $this->converter->decode($request));
    }
}
