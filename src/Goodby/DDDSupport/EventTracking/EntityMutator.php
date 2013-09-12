<?php

namespace Goodby\DDDSupport\EventTracking;

use Goodby\Assertion\Assert;
use Goodby\DDDSupport\EventTracking\Exception\EntityMutationException;
use Goodby\EventSourcing\Event;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class EntityMutator
{
    /**
     * @param object $entity
     * @param Event $domainEvent
     * @param string $mutatorMethodFormat
     * @throws EntityMutationException
     * @throws EntityMutationException
     */
    public static function mutateWhen($entity, Event $domainEvent, $mutatorMethodFormat = 'when{EventClassName}')
    {
        Assert::argumentIsObject($entity, 'Entity must be an object');

        $eventClassName = (new ReflectionClass($domainEvent))->getShortName();
        $mutatorMethodName = str_replace('{EventClassName}', $eventClassName, $mutatorMethodFormat);

        if (method_exists($entity, $mutatorMethodName) === false) {
            throw EntityMutationException::mutatorMethodNotExist(get_class($entity), $mutatorMethodName, $domainEvent);
        }

        try {
            $mutatorMethod = new ReflectionMethod($entity, $mutatorMethodName);
            $mutatorMethod->setAccessible(true);
            $mutatorMethod->invoke($entity, $domainEvent);
        } catch (ReflectionException $because) {
            throw EntityMutationException::because($because);
        }
    }
}
