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
     * @var bool
     */
    private $URIForwarding = false;

    /**
     * @var int
     */
    private $statusCode = Response::HTTP_MOVED_PERMANENTLY;

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
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @param array $rule
     *
     * @return RedirectRule
     */
    public function fromConfigRule($rule)
    {
        $redirectRule = clone $this;
        $redirectRule->setPattern($rule['pattern']);
        $redirectRule->setRedirect($rule['redirect']);

        if (isset($rule['forwarding'])) {
            $redirectRule->setURIForwarding($rule['forwarding']);
        }

        if (isset($rule['status'])) {
            $redirectRule->setStatusCode($rule['status']);
        }

        return $redirectRule;
    }
}
