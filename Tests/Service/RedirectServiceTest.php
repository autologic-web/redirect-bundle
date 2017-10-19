<?php

namespace Autologic\Bundle\RedirectBundle\Tests\Service;

use Autologic\Bundle\RedirectBundle\Exception\RedirectionRuleNotFoundException;
use Autologic\Bundle\RedirectBundle\Service\RedirectService;
use Autologic\Bundle\RedirectBundle\Tests\AutologicTestCase;
use Mockery as m;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectServiceTest extends AutologicTestCase
{
    const MATCHING_PATH = '/some-matching-path/something-else';
    const RELATIVE_PATH = '/relative-path';
    const PROTOCOL = 'http://';
    const BASE_DOMAIN = 'domain.com';
    const MATCHING_URI = self::PROTOCOL.self::BASE_DOMAIN.self::MATCHING_PATH;
    const MATCHING_REDIRECT_URI = self::BASE_DOMAIN.'/the-redirect';

    public function testRedirect_MatchFound_ReturnsRedirectResponse()
    {
        $rules = [
            [
                'pattern'  => '/.*some-matching-path/',
                'redirect' => self::MATCHING_REDIRECT_URI,
            ],
        ];
        $container = $this->createContainer($rules);

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('getUri')->once()->andReturn(self::MATCHING_URI);
        $request->shouldReceive('isSecure')->once()->andReturn(false);

        $redirectService = new RedirectService($container);
        $redirectResponse = $redirectService->redirect($request);

        Assert::assertInstanceOf(RedirectResponse::class, $redirectResponse);
        Assert::assertEquals(self::PROTOCOL.self::MATCHING_REDIRECT_URI, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_MOVED_PERMANENTLY, $redirectResponse->getStatusCode());
    }

    public function testRedirect_NoMatchFound_ThrowsException()
    {
        $this->expectException(RedirectionRuleNotFoundException::class);

        $rules = [
            [
                'pattern'  => '/.*non-matching-path/',
                'redirect' => self::MATCHING_REDIRECT_URI,
            ],
        ];
        $container = $this->createContainer($rules);

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('getUri')->once()->andReturn(self::MATCHING_URI);
        $request->shouldNotReceive('isSecure');

        $redirectService = new RedirectService($container);
        $redirectService->redirect($request);
    }

    public function testRedirect_WithForwarding_ReturnsURLWithOriginalPath()
    {
        $rules = [
            [
                'pattern'    => '/.*some-matching-path/',
                'redirect'   => self::MATCHING_REDIRECT_URI,
                'forwarding' => true,
            ],
        ];
        $container = $this->createContainer($rules);

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('getUri')->once()->andReturn(self::MATCHING_URI);
        $request->shouldReceive('getRequestUri')->once()->andReturn(self::MATCHING_PATH);
        $request->shouldReceive('isSecure')->once()->andReturn(false);

        $redirectService = new RedirectService($container);
        $redirectResponse = $redirectService->redirect($request);

        Assert::assertInstanceOf(RedirectResponse::class, $redirectResponse);
        Assert::assertEquals(self::PROTOCOL.self::MATCHING_REDIRECT_URI.self::MATCHING_PATH, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_MOVED_PERMANENTLY, $redirectResponse->getStatusCode());
    }

    public function testRedirect_WithMethodSet_ReturnsRedirectWithMethod()
    {
        $rules = [
            [
                'pattern'  => '/.*some-matching-path/',
                'redirect' => self::MATCHING_REDIRECT_URI,
                'status'   => Response::HTTP_TEMPORARY_REDIRECT,
            ],
        ];
        /** @var Container|m\mock $container */
        $container = m::mock(Container::class)->shouldIgnoreMissing();
        $container->shouldReceive('getParameter')
            ->once()
            ->with('autologic_redirect.rules')
            ->andReturn($rules);

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('getUri')->once()->andReturn(self::MATCHING_URI);
        $request->shouldReceive('isSecure')->once()->andReturn(false);

        $redirectService = new RedirectService($container);
        $redirectResponse = $redirectService->redirect($request);

        Assert::assertInstanceOf(RedirectResponse::class, $redirectResponse);
        Assert::assertEquals(self::PROTOCOL.self::MATCHING_REDIRECT_URI, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_TEMPORARY_REDIRECT, $redirectResponse->getStatusCode());
    }

    public function testRedirect_MultipleMatches_UsesFirstMatch()
    {
        $uriExtra = '/extra';
        $rules = [
            [
                'pattern'  => '/.*some-matching-path\/something-else/',
                'redirect' => self::MATCHING_REDIRECT_URI.$uriExtra,
            ],
            [
                'pattern'  => '/.*some-matching-path/',
                'redirect' => self::MATCHING_REDIRECT_URI,
            ],
        ];
        $container = $this->createContainer($rules);

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('getUri')->once()->andReturn(self::MATCHING_URI);
        $request->shouldReceive('isSecure')->once()->andReturn(false);

        $redirectService = new RedirectService($container);
        $redirectResponse = $redirectService->redirect($request);

        Assert::assertInstanceOf(RedirectResponse::class, $redirectResponse);
        Assert::assertEquals(self::PROTOCOL.self::MATCHING_REDIRECT_URI.$uriExtra, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_MOVED_PERMANENTLY, $redirectResponse->getStatusCode());
    }

    public function testRedirect_WithHTTPS_ReturnsHTTPSURL()
    {
        $rules = [
            [
                'pattern'  => '/.*some-matching-path/',
                'redirect' => self::MATCHING_REDIRECT_URI,
            ],
        ];
        $container = $this->createContainer($rules);

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('getUri')->once()->andReturn(self::MATCHING_URI);
        $request->shouldReceive('isSecure')->once()->andReturn(true);

        $redirectService = new RedirectService($container);
        $redirectResponse = $redirectService->redirect($request);

        Assert::assertInstanceOf(RedirectResponse::class, $redirectResponse);
        Assert::assertEquals('https://'.self::MATCHING_REDIRECT_URI, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_MOVED_PERMANENTLY, $redirectResponse->getStatusCode());
    }

    public function testRedirect_WithRelativePath_ReturnsSameDomainRedirect()
    {
        $rules = [
            [
                'pattern'  => '/.*some-matching-path/',
                'redirect' => self::RELATIVE_PATH,
            ],
        ];
        $container = $this->createContainer($rules);

        /** @var Request|m\mock $request */
        $request = m::mock(Request::class)->shouldIgnoreMissing();
        $request->shouldReceive('getUri')->once()->andReturn(self::MATCHING_URI);
        $request->shouldReceive('isSecure')->once()->andReturn(false);
        $request->shouldReceive('getHost')->once()->andReturn(self::BASE_DOMAIN);

        $redirectService = new RedirectService($container);
        $redirectResponse = $redirectService->redirect($request);

        Assert::assertInstanceOf(RedirectResponse::class, $redirectResponse);
        Assert::assertEquals(self::PROTOCOL.self::BASE_DOMAIN.self::RELATIVE_PATH, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_MOVED_PERMANENTLY, $redirectResponse->getStatusCode());
    }

    /**
     * @param array $rules
     *
     * @return Container|m\mock
     */
    private function createContainer($rules)
    {
        /** @var Container|m\mock $container */
        $container = m::mock(Container::class)->shouldIgnoreMissing();
        $container->shouldReceive('getParameter')
            ->once()
            ->with('autologic_redirect.rules')
            ->andReturn($rules);

        return $container;
    }
}
