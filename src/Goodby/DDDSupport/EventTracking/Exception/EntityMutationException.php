<?php

namespace Goodby\DDDSupport\EventTracking\Exception;

use ReflectionException;

class EntityMutationException extends \RuntimeException
{
    /**
     * @param string  $aggregateClassName
     * @param string  $mutatorMethodName
     * @param string  $eventClassName
     * @return EntityMutationException
     */
    public static function mutatorMethodNotExist($aggregateClassName, $mutatorMethodName, $eventClassName)
    {
        return new self(
            "Mutator method not found: $aggregateClassName::$mutatorMethodName($eventClassName \$event)."
            . "You may have to add following method in $aggregateClassName class:\n"
            . "```\n"
            . "    private function $mutatorMethodName($eventClassName \$event)\n"
            . "    {\n"
            . "        // code...\n"
            . "    }\n"
            . "```\n"
        );
    }

    /**
     * @param ReflectionException $because
     * @return EntityMutationException
     */
    public static function because(ReflectionException $because)
    {
        return new self(
            sprintf('Aggregate mutation failed, because: %s', $because->getMessage()),
            null,
            $because
        );
    }
}
