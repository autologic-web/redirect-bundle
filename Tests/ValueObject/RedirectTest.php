<?php

namespace Autologic\Bundle\RedirectBundle\Tests\ValueObject;

use Autologic\Bundle\RedirectBundle\Tests\AutologicTestCase;
use Autologic\Bundle\RedirectBundle\ValueObject\Redirect;
use Mockery as m;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectTest extends AutologicTestCase
{
    const PATTERN = '/*some-pattern\/';
    const DOMAIN = 'domain.com';
    const ROUTE = '/redirection-path';
    const DEFAULT_PROTOCOL = 'http://';
    const SECURE_PROTOCOL = 'https://';

    public function testFromConfigRule_SetsDefaults()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::DOMAIN,
        ];

        $redirectRule = (new Redirect())->fromConfigRule($rule);

        Assert::assertSame(self::PATTERN, $redirectRule->getPattern());
        Assert::assertSame(self::DOMAIN, $redirectRule->getRedirect());
        Assert::assertSame(Response::HTTP_MOVED_PERMANENTLY, $redirectRule->getStatusCode());
        Assert::assertSame(self::DEFAULT_PROTOCOL, $redirectRule->getProtocol());
        Assert::assertNull($redirectRule->isAbsolute());
        Assert::assertFalse($redirectRule->isURIForwarding());
    }

    public function testFromConfigRule_WithStatus_SetsStatus()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::DOMAIN,
            'status'   => Response::HTTP_TEMPORARY_REDIRECT,
        ];

        $redirectRule = (new Redirect())->fromConfigRule($rule);

        Assert::assertSame(self::PATTERN, $redirectRule->getPattern());
        Assert::assertSame(self::DOMAIN, $redirectRule->getRedirect());
        Assert::assertSame(Response::HTTP_TEMPORARY_REDIRECT, $redirectRule->getStatusCode());
        Assert::assertSame(self::DEFAULT_PROTOCOL, $redirectRule->getProtocol());
        Assert::assertNull($redirectRule->isAbsolute());
        Assert::assertFalse($redirectRule->isURIForwarding());
    }

    public function testFromConfigRule_WithForwarding_SetsForwarding()
    {
        $rule = [
            'pattern'    => self::PATTERN,
            'redirect'   => self::DOMAIN,
            'forwarding' => true,
        ];

        $redirectRule = (new Redirect())->fromConfigRule($rule);

        Assert::assertSame(self::PATTERN, $redirectRule->getPattern());
        Assert::assertSame(self::DOMAIN, $redirectRule->getRedirect());
        Assert::assertSame(Response::HTTP_MOVED_PERMANENTLY, $redirectRule->getStatusCode());
        Assert::assertSame(self::DEFAULT_PROTOCOL, $redirectRule->getProtocol());
        Assert::assertNull($redirectRule->isAbsolute());
        Assert::assertTrue($redirectRule->isURIForwarding());
    }

    public function testFromConfigRule_WithAbsoluteTrue_SetsAbsolute()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::DOMAIN,
            'absolute' => true,
        ];

        $redirectRule = (new Redirect())->fromConfigRule($rule);

        Assert::assertSame(self::PATTERN, $redirectRule->getPattern());
        Assert::assertSame(self::DOMAIN, $redirectRule->getRedirect());
        Assert::assertSame(Response::HTTP_MOVED_PERMANENTLY, $redirectRule->getStatusCode());
        Assert::assertSame(self::DEFAULT_PROTOCOL, $redirectRule->getProtocol());
        Assert::assertTrue($redirectRule->isAbsolute());
    }

    public function testFromConfigRule_WithAbsoluteFalse_SetsAbsolute()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::DOMAIN,
            'absolute' => false,
        ];

        $redirectRule = (new Redirect())->fromConfigRule($rule);

        Assert::assertSame(self::PATTERN, $redirectRule->getPattern());
        Assert::assertSame(self::DOMAIN, $redirectRule->getRedirect());
        Assert::assertSame(Response::HTTP_MOVED_PERMANENTLY, $redirectRule->getStatusCode());
        Assert::assertSame(self::DEFAULT_PROTOCOL, $redirectRule->getProtocol());
        Assert::assertFalse($redirectRule->isAbsolute());
    }

    public function testFromConfigRule_WithHTTP_SetsProtocol()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::DOMAIN,
            'protocol' => 'http://',
        ];

        $redirectRule = (new Redirect())->fromConfigRule($rule);

        Assert::assertSame(self::PATTERN, $redirectRule->getPattern());
        Assert::assertSame(self::DOMAIN, $redirectRule->getRedirect());
        Assert::assertSame(Response::HTTP_MOVED_PERMANENTLY, $redirectRule->getStatusCode());
        Assert::assertSame(self::DEFAULT_PROTOCOL, $redirectRule->getProtocol());
        Assert::assertNull($redirectRule->isAbsolute());
    }

    public function testFromConfigRule_WithHTTPS_SetsProtocol()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::DOMAIN,
            'protocol' => 'https://',
        ];

        $redirectRule = (new Redirect())->fromConfigRule($rule);

        Assert::assertSame(self::PATTERN, $redirectRule->getPattern());
        Assert::assertSame(self::DOMAIN, $redirectRule->getRedirect());
        Assert::assertSame(Response::HTTP_MOVED_PERMANENTLY, $redirectRule->getStatusCode());
        Assert::assertSame(self::SECURE_PROTOCOL, $redirectRule->getProtocol());
        Assert::assertNull($redirectRule->isAbsolute());
    }

    public function testGetProtocol_WithSecureRequest_ReturnsCorrectProtocol()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::DOMAIN,
        ];

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('isSecure')->once()->andReturn(true);

        $redirectRule = (new Redirect())->fromConfigRule($rule)->withRequest($request);
        Assert::assertSame(self::SECURE_PROTOCOL, $redirectRule->getProtocol());
    }

    public function testGetProtocol_WithInsecureRequest_ReturnsCorrectProtocol()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::DOMAIN,
        ];

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('isSecure')->once()->andReturn(false);

        $redirectRule = (new Redirect())->fromConfigRule($rule)->withRequest($request);
        Assert::assertSame(self::DEFAULT_PROTOCOL, $redirectRule->getProtocol());
    }

    public function testGetRedirectURI_WithAbsoluteURI_ReturnsAbsoluteURI()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::DOMAIN,
        ];

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldNotReceive('getHost');

        $redirectRule = (new Redirect())->fromConfigRule($rule)->withRequest($request);
        Assert::assertSame(self::DOMAIN, $redirectRule->getRedirectURI());
    }

    public function testGetRedirectURI_WithRelativeURI_ReturnsAbsoluteURI()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::ROUTE,
        ];

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('getHost')->once()->andReturn(self::DOMAIN);

        $redirectRule = (new Redirect())->fromConfigRule($rule)->withRequest($request);
        Assert::assertSame(self::DOMAIN.self::ROUTE, $redirectRule->getRedirectURI());
    }

    public function testGetPath_WithNoForwarding_ReturnsEmptyString()
    {
        $rule = [
            'pattern'  => self::PATTERN,
            'redirect' => self::DOMAIN,
        ];

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldNotReceive('getUri');

        $redirectRule = (new Redirect())->fromConfigRule($rule)->withRequest($request);
        Assert::assertSame('', $redirectRule->getPath());
    }

    public function testGetPath_WithForwarding_ReturnsRequestPath()
    {
        $rule = [
            'pattern'    => self::PATTERN,
            'redirect'   => self::DOMAIN,
            'forwarding' => true,
        ];

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('getRequestUri')->once()->andReturn(self::ROUTE);

        $redirectRule = (new Redirect())->fromConfigRule($rule)->withRequest($request);
        Assert::assertSame(self::ROUTE, $redirectRule->getPath());
    }
}
