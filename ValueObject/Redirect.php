<?php

namespace Autologic\Bundle\RedirectBundle\ValueObject;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Redirect
{
    /**
     * @var Request
     */
    private $request;

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
     * @var null|string
     */
    private $protocol = null;

    /**
     * @var null|bool
     */
    private $absolute = null;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     *
     * @return Redirect
     */
    public function withRequest(Request $request)
    {
        $redirect = clone $this;
        $redirect->request = $request;

        return $redirect;
    }

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
     * @return string
     */
    public function getProtocol()
    {
        if ($this->protocol === null) {
            return ($this->getRequest()->isSecure()) ? 'https://' : 'http://';
        }

        return $this->protocol;
    }

    /**
     * @param null|string $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @return bool|null
     */
    public function isAbsolute()
    {
        return $this->absolute;
    }

    /**
     * @param bool|null $absolute
     */
    public function setAbsolute($absolute)
    {
        $this->absolute = $absolute;
    }

    /**
     * @param array $rule
     *
     * @return Redirect
     */
    public function fromConfigRule($rule)
    {
        $redirect = clone $this;
        $redirect->pattern = $rule['pattern'];
        $redirect->redirect = $rule['redirect'];
        $redirect->URIForwarding = isset($rule['forwarding']) ? $rule['forwarding'] : false;
        $redirect->statusCode = isset($rule['status']) ? $rule['status'] : Response::HTTP_MOVED_PERMANENTLY;
        $redirect->protocol = isset($rule['protocol']) ? $rule['protocol'] : null;
        $redirect->absolute = isset($rule['absolute']) ? $rule['absolute'] : null;

        return $redirect;
    }

    /**
     * @return string
     */
    public function getRedirectURI()
    {
        if ($this->isAbsolute() === true) {
            return $this->getRedirect();
        } elseif ($this->isAbsolute() === false) {
            return $this->getRequest()->getHost().$this->getRedirect();
        } else {
            return self::targetHasHost($this->getRedirect())
                ? $this->getRedirect()
                : $this->getRequest()->getHost().$this->getRedirect();
        }
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->isURIForwarding() ? $this->getRequest()->getRequestUri() : '';
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    private static function targetHasHost($url)
    {
        return preg_match('/^(([a-z0-9]([-a-z0-9]*[a-z0-9]+)?){1,63}\.)+[a-z]{2,11}/', $url) === 1;
    }
}
