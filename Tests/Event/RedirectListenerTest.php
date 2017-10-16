<?php

namespace Autologic\Bundle\RedirectBundle\Tests\Event;

use Autologic\Bundle\RedirectBundle\Event\RedirectListener;
use Autologic\Bundle\RedirectBundle\Exception\RedirectionRuleNotFoundException;
use Autologic\Bundle\RedirectBundle\Service\RedirectService;
use Mockery as m;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectListenerTest extends TestCase
{
    public function testOnKernelException_WithNotFoundException_CallsRedirectService()
    {
        $request = new Request();
        $response = new RedirectResponse('http://domain.com/redirect');

        /** @var RedirectService|m\mock $redirectService */
        $redirectService = m::mock(RedirectService::class)->shouldIgnoreMissing();
        $redirectService->shouldReceive('redirect')->once()->with($request)->andReturn($response);

        /** @var GetResponseForExceptionEvent|m\mock $event */
        $event = m::mock(GetResponseForExceptionEvent::class)->shouldIgnoreMissing();
        $event->shouldReceive('getException')->once()->andReturn(new NotFoundHttpException());
        $event->shouldReceive('getRequest')->once()->andReturn($request);
        $event->shouldReceive('setResponse')->once()->with($response);

        $redirectListener = new RedirectListener($redirectService);
        $success = $redirectListener->onKernelException($event);

        Assert::assertTrue($success);
    }

    public function testOnKernelException_WithBaseException_ReturnsFalse()
    {
        /** @var RedirectService|m\mock $redirectService */
        $redirectService = m::mock(RedirectService::class)->shouldIgnoreMissing();
        $redirectService->shouldNotReceive('redirect');

        /** @var GetResponseForExceptionEvent|m\mock $event */
        $event = m::mock(GetResponseForExceptionEvent::class)->shouldIgnoreMissing();
        $event->shouldReceive('getException')->once()->andReturn(new \Exception());
        $event->shouldNotReceive('getRequest');
        $event->shouldNotReceive('setResponse');

        $redirectListener = new RedirectListener($redirectService);
        $success = $redirectListener->onKernelException($event);

        Assert::assertFalse($success);
    }

    public function testOnKernelException_WithNoRedirectMatch_ReturnsFalse()
    {
        $request = new Request();

        /** @var RedirectService|m\mock $redirectService */
        $redirectService = m::mock(RedirectService::class)->shouldIgnoreMissing();
        $redirectService->shouldReceive('redirect')->once()->with($request)->andThrow(new RedirectionRuleNotFoundException());

        /** @var GetResponseForExceptionEvent|m\mock $event */
        $event = m::mock(GetResponseForExceptionEvent::class)->shouldIgnoreMissing();
        $event->shouldReceive('getException')->once()->andReturn(new NotFoundHttpException());
        $event->shouldReceive('getRequest')->once()->andReturn($request);
        $event->shouldNotReceive('setResponse');

        $redirectListener = new RedirectListener($redirectService);
        $success = $redirectListener->onKernelException($event);

        Assert::assertFalse($success);
    }

    public function testOnKernelException_WithNoRedirectMatch_WithLogger_LogsError()
    {
        $request = new Request();

        /** @var RedirectService|m\mock $redirectService */
        $redirectService = m::mock(RedirectService::class)->shouldIgnoreMissing();
        $redirectService->shouldReceive('redirect')->once()->with($request)->andThrow(new RedirectionRuleNotFoundException());

        /** @var GetResponseForExceptionEvent|m\mock $event */
        $event = m::mock(GetResponseForExceptionEvent::class)->shouldIgnoreMissing();
        $event->shouldReceive('getException')->once()->andReturn(new NotFoundHttpException());
        $event->shouldReceive('getRequest')->once()->andReturn($request);
        $event->shouldNotReceive('setResponse');

        /** @var NullLogger|m\mock $logger */
        $logger = m::mock(NullLogger::class)->shouldIgnoreMissing();
        $logger->shouldReceive('notice')->once();

        $redirectListener = new RedirectListener($redirectService, $logger);
        $success = $redirectListener->onKernelException($event);

        Assert::assertFalse($success);
    }
}
