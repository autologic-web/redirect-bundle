<?php

namespace Autologic\Bundle\RedirectBundle\Service;

use Autologic\Bundle\RedirectBundle\Exception\RedirectionRuleNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectService
{
    /**
     * @var array
     */
    private $rules;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->rules = $container->getParameter('autologic_redirect.rules');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws RedirectionRuleNotFoundException
     */
    public function redirect(Request $request)
    {
        $path = $request->getPathInfo();
        if (array_key_exists($path, $this->rules)) {
            return new RedirectResponse($this->rules[$path]['redirect'], Response::HTTP_MOVED_PERMANENTLY);
        } else {
            throw new RedirectionRuleNotFoundException('Redirection rule not found for ' . $path);
        }
    }
}
