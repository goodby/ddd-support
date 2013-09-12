<?php

namespace Goodby\DDDSupport\EventTracking\Exception;

use Goodby\EventSourcing\Event;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class EntityMutationException extends \RuntimeException
{
    /**
     * @param string $aggregateClassName
     * @param string $mutatorMethodName
     * @param Event $domainEvent
     * @return EntityMutationException
     */
    public static function mutatorMethodNotExist($aggregateClassName, $mutatorMethodName, Event $domainEvent)
    {
        $domainEventClass = new ReflectionClass($domainEvent);
        $eventClassName = $domainEventClass->getShortName();

        $domainEventMethods = array_filter(
            array_diff(
                array_map(
                    function (ReflectionMethod $method) {
                        return $method->getName();
                    },
                    $domainEventClass->getMethods()
                ),
                array_map(
                    function (ReflectionMethod $method) {
                        return $method->getName();
                    },
                    (new ReflectionClass('Goodby\EventSourcing\Event'))->getMethods()
                )
            ),
            function ($method) {
                return (strpos($method, '__') !== 0); // remove magic methods
            }
        );

        $code = [];

        foreach ($domainEventMethods as $domainEventMethod) {
            $code[] = sprintf('$this->set%s($event->%s());', ucfirst($domainEventMethod), $domainEventMethod);
        }

        $code = implode("\n        ", $code);

        return new self(
            "Mutator method not found: $aggregateClassName::$mutatorMethodName($eventClassName \$event)."
            . "You may have to add following method in $aggregateClassName class:\n"
            . "```\n"
            . "    /**\n"
            . "     * @param $eventClassName \$event\n"
            . "     */\n"
            . "    private function $mutatorMethodName($eventClassName \$event)\n"
            . "    {\n"
            . "        $code\n"
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
