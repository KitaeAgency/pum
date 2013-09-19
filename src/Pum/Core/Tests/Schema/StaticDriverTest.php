<?php

namespace Pum\Core\Tests\Driver;

use Pum\Core\Driver\StaticDriver;

class StaticDriverTest extends AbstractDriverTest
{
    /**
     * {@inheritdoc}
     */
    public function createDriver($hash)
    {
        return new StaticDriver();
    }
}
