<?php

namespace Autologic\Bundle\RedirectBundle\Event;

use Autologic\Bundle\RedirectBundle\Exception\RedirectionRuleNotFoundException;
use Autologic\Bundle\RedirectBundle\Service\RedirectService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class RedirectListener
{
    /**
     * @var RedirectService
     */
    private $redirectService;

    /**
     * @param RedirectService $redirectService
     */
    public function __construct(RedirectService $redirectService)
    {
        $this->redirectService = $redirectService;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException()->getCode() === Response::HTTP_NOT_FOUND) {
            try {
                $event->setResponse($this->redirectService->redirect($event->getRequest()));
            } catch (RedirectionRuleNotFoundException $e) {
                // no rule found for request so don't modify response
                // possibly log or show if debug mode enabled?
            }
        }
    }
}
