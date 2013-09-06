<?php

namespace Goodby\DDDSupport\EventTracking;

use Goodby\Assertion\Assert;
use Goodby\EventSourcing\Event;
use ReflectionClass;

trait EventSourcedRootEntity
{
    /**
     * @var Event[]
     */
    private $mutatingEvents = [];

    /**
     * @var int
     */
    private $unmutatedVersion = 0;

    /**
     * @param Event[] $domainEvents
     * @param int $eventStreamVersion
     * @return $this
     */
    public static function constructWithEventStream(array $domainEvents, $eventStreamVersion)
    {
        Assert::argumentAtLeast($eventStreamVersion, 1, 'Event version number must be at least 1');

        $self = (new ReflectionClass(get_called_class()))->newInstanceWithoutConstructor();

        foreach ($domainEvents as $domainEvent) {
            $self->mutateWhen($domainEvent);
        }

        $self->setUnmutatedVersion($eventStreamVersion);

        return $self;
    }

    /**
     * @return int
     */
    public function mutatedVersion()
    {
        return $this->unmutatedVersion + 1;
    }

    /**
     * @return Event[]
     */
    public function mutatingEvents()
    {
        return $this->mutatingEvents;
    }

    /**
     * @return int
     */
    public function unmutatedVersion()
    {
        return $this->unmutatedVersion;
    }

    /**
     * @param Event $domainEvent
     */
    private function apply(Event $domainEvent)
    {
        $this->mutatingEvents[] = $domainEvent;
        $this->mutateWhen($domainEvent);
    }

    /**
     * @param Event $domainEvent
     */
    private function mutateWhen(Event $domainEvent)
    {
        EntityMutator::mutateWhen($this, $domainEvent);
    }

    /**
     * @param int $streamVersion
     */
    private function setUnmutatedVersion($streamVersion)
    {
        $this->unmutatedVersion = $streamVersion;
    }
}
