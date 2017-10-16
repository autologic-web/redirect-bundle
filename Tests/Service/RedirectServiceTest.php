<?php

namespace Autologic\Bundle\RedirectBundle\Tests\Service;

use Autologic\Bundle\RedirectBundle\Exception\RedirectionRuleNotFoundException;
use Autologic\Bundle\RedirectBundle\Service\RedirectService;
use Autologic\Bundle\RedirectBundle\Tests\AutologicTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Mockery as m;
use Symfony\Component\HttpFoundation\Response;

class RedirectServiceTest extends AutologicTestCase
{
    const MATCHING_PATH = '/some-matching-path/something-else';
    const MATCHING_URI = 'http://domain.com' . self::MATCHING_PATH;
    const MATCHING_REDIRECT_URI = 'domain.com/the-redirect';
    const PROTOCOL = 'http://';

    public function testRedirect_MatchFound_ReturnsRedirectResponse()
    {
        $rules = [
            '/.*some-matching-path/' => [
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
        Assert::assertEquals(self::PROTOCOL . self::MATCHING_REDIRECT_URI, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_MOVED_PERMANENTLY, $redirectResponse->getStatusCode());
    }

    public function testRedirect_NoMatchFound_ThrowsException()
    {
        $this->expectException(RedirectionRuleNotFoundException::class);

        $rules = [
            '/.*non-matching-path/' => [
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
            '/.*some-matching-path/' => [
                'redirect'   => self::MATCHING_REDIRECT_URI,
                'forwarding' => true
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
        Assert::assertEquals(self::PROTOCOL . self::MATCHING_REDIRECT_URI . self::MATCHING_PATH, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_MOVED_PERMANENTLY, $redirectResponse->getStatusCode());
    }

    public function testRedirect_WithMethodSet_ReturnsRedirectWithMethod()
    {
        $rules = [
            '/.*some-matching-path/' => [
                'redirect'   => self::MATCHING_REDIRECT_URI,
                'method' => Response::HTTP_TEMPORARY_REDIRECT
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
        Assert::assertEquals(self::PROTOCOL . self::MATCHING_REDIRECT_URI, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_TEMPORARY_REDIRECT, $redirectResponse->getStatusCode());
    }

    public function testRedirect_MultipleMatches_UsesFirstMatch()
    {
        $uriExtra = '/extra';
        $rules = [
            '/.*some-matching-path\/something-else/' => [
                'redirect' => self::MATCHING_REDIRECT_URI . $uriExtra,
            ],
            '/.*some-matching-path/' => [
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
        Assert::assertEquals(self::PROTOCOL . self::MATCHING_REDIRECT_URI . $uriExtra, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_MOVED_PERMANENTLY, $redirectResponse->getStatusCode());
    }

    public function testRedirect_WithHTTPS_ReturnsHTTPSURL()
    {
        $rules = [
            '/.*some-matching-path/' => [
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
        Assert::assertEquals('https://' . self::MATCHING_REDIRECT_URI, $redirectResponse->getTargetUrl());
        Assert::assertEquals(Response::HTTP_MOVED_PERMANENTLY, $redirectResponse->getStatusCode());
    }

    /**
     * @param array $rules
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
