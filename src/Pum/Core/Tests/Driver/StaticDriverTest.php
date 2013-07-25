<?php

namespace Pum\Core\Tests\Driver;

use Pum\Core\Driver\StaticDriver;

class StaticDriverTest extends AbstractDriverTest
{
    /**
     * {@inheritdoc}
     */
    public function getDriver()
    {
        return new StaticDriver($this->getEntityManager());
    }
}
