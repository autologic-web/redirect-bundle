<?php

namespace Autologic\Bundle\RedirectBundle\Service;

use Autologic\Bundle\RedirectBundle\Exception\RedirectionRuleNotFoundException;
use Autologic\Bundle\RedirectBundle\ValueObject\RedirectRule;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $url = $request->getUri();
        foreach ($this->rules as $pattern => $redirect) {
            $redirectRule = (new RedirectRule())->fromConfigRule($pattern, $redirect);
            preg_match($redirectRule->getPattern(), $url, $matches);
            if (!empty($matches)) {
                return new RedirectResponse(
                    $this->generateRedirectURL($request, $redirectRule),
                    $redirectRule->getMethod()
                );
            }
        }

        throw new RedirectionRuleNotFoundException('Redirection rule not found for ' . $url);
    }

    /**
     * @param Request $request
     * @param RedirectRule $redirectRule
     * @return string
     */
    private function generateRedirectURL(Request $request, RedirectRule $redirectRule)
    {
        $protocol = ($request->isSecure()) ? 'https://' : 'http://';
        $redirectURL = $protocol . $redirectRule->getRedirect();
        if ($redirectRule->isURIForwarding()) {
            $redirectURL .= $request->getRequestUri();
        }

        return $redirectURL;
    }
}
