<?php

namespace Autologic\Bundle\RedirectBundle\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;

abstract class AutologicTestCase extends TestCase
{
    /**
     * m::close() is required so Mockery can clean up it's container and run verifications
     * on our expectations i.e. shouldReceive()->once().
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}
