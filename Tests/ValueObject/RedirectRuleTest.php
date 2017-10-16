<?php

namespace Autologic\Bundle\RedirectBundle\Tests\ValueObject;

use Autologic\Bundle\RedirectBundle\Tests\AutologicTestCase;
use Autologic\Bundle\RedirectBundle\ValueObject\RedirectRule;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class RedirectRuleTest extends AutologicTestCase
{
    const REDIRECT_PATTERN = '/*some-pattern\/';
    const REDIRECT_URL = 'domain.com';

    public function testFromConfigRule_SetsDefaults()
    {
        $rule = [
            'pattern'  => self::REDIRECT_PATTERN,
            'redirect' => self::REDIRECT_URL,
        ];

        $redirectRule = (new RedirectRule())->fromConfigRule($rule);

        Assert::assertSame(self::REDIRECT_PATTERN, $redirectRule->getPattern());
        Assert::assertSame(self::REDIRECT_URL, $redirectRule->getRedirect());
        Assert::assertSame(Response::HTTP_MOVED_PERMANENTLY, $redirectRule->getStatusCode());
        Assert::assertFalse($redirectRule->isURIForwarding());
    }

    public function testFromConfigRule_WithStatus_SetsStatus()
    {
        $rule = [
            'pattern'  => self::REDIRECT_PATTERN,
            'redirect' => self::REDIRECT_URL,
            'status'   => Response::HTTP_TEMPORARY_REDIRECT,
        ];

        $redirectRule = (new RedirectRule())->fromConfigRule($rule);

        Assert::assertSame(self::REDIRECT_PATTERN, $redirectRule->getPattern());
        Assert::assertSame(self::REDIRECT_URL, $redirectRule->getRedirect());
        Assert::assertSame(Response::HTTP_TEMPORARY_REDIRECT, $redirectRule->getStatusCode());
        Assert::assertFalse($redirectRule->isURIForwarding());
    }

    public function testFromConfigRule_WithForwarding_SetsForwarding()
    {
        $rule = [
            'pattern'    => self::REDIRECT_PATTERN,
            'redirect'   => self::REDIRECT_URL,
            'forwarding' => true,
        ];

        $redirectRule = (new RedirectRule())->fromConfigRule($rule);

        Assert::assertSame(self::REDIRECT_PATTERN, $redirectRule->getPattern());
        Assert::assertSame(self::REDIRECT_URL, $redirectRule->getRedirect());
        Assert::assertSame(Response::HTTP_MOVED_PERMANENTLY, $redirectRule->getStatusCode());
        Assert::assertTrue($redirectRule->isURIForwarding());
    }
}
