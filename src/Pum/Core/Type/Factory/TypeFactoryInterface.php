<?php

namespace Pum\Core\Type\Factory;

/**
 * Service to generate definition types.
 *
 * @see Pum\Core\Definition\TypeInterface
 */
interface TypeFactoryInterface
{
    /**
     * Returns a given type from a name.
     *
     * @param string $name
     *
     * @return Pum\Core\Type\TypeInterface
     */
    public function getType($name);

    /**
     * Tests if factory has a given type.
     *
     * @param string $name
     *
     * @¶eturn boolean
     */
    public function hasType($name);
}
