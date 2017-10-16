<?php

namespace Autologic\Bundle\RedirectBundle\ValueObject;

use Symfony\Component\HttpFoundation\Response;

class RedirectRule
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $redirect;

    /**
     * @var boolean
     */
    private $URIForwarding = false;

    /**
     * @var integer
     */
    private $method = Response::HTTP_MOVED_PERMANENTLY;

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * @param string $redirect
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * @return bool
     */
    public function isURIForwarding()
    {
        return $this->URIForwarding;
    }

    /**
     * @param bool $URIForwarding
     */
    public function setURIForwarding($URIForwarding)
    {
        $this->URIForwarding = $URIForwarding;
    }

    /**
     * @return int
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param int $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @param string $pattern
     * @param array $rule
     * @return RedirectRule
     */
    public function fromConfigRule($pattern, $rule)
    {
        $redirectRule = clone $this;
        $redirectRule->setPattern($pattern);
        $redirectRule->setRedirect($rule['redirect']);

        if (isset($rule['forwarding'])) {
            $redirectRule->setURIForwarding($rule['forwarding']);
        }

        if (isset($rule['method'])) {
            $redirectRule->setMethod($rule['method']);
        }

        return $redirectRule;
    }
}
