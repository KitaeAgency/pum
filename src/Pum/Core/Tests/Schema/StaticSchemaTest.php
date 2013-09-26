<?php

namespace Pum\Core\Tests\Schema;

use Pum\Core\Schema\StaticSchema;

class StaticSchemaTest extends AbstractSchemaTest
{
    /**
     * {@inheritdoc}
     */
    public function createSchema($hash)
    {
        return new StaticSchema();
    }
}
