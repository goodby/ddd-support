<?php

namespace Foo\Bar;

use Goodby\Assertion\Assert;
use Goodby\DDDSupport\EventTracking\EventSourcedRootEntity;
use Goodby\EventSourcing\Event;

require __DIR__ . '/vendor/autoload.php';

class User
{
    use EventSourcedRootEntity;

    private $userId;

    private $name;

    private $password;

    /**
     * @param string $userId
     * @param string $name
     * @param string $password
     */
    public function __construct($userId, $name, $password)
    {
        Assert::argumentNotEmpty($userId, 'User ID is required');
        Assert::argumentNotEmpty($name, 'User name is required');
        Assert::argumentNotEmpty($password, 'User password is required');

        $this->apply(new UserRegistered($userId, $name, $password));
    }

    /**
     * @param UserRegistered $event
     */
    private function whenUserRegistered(UserRegistered $event)
    {
        $this->userId = $event->userId();
        $this->name = $event->name();
        $this->password = $event->password();
    }
}

class UserRegistered implements Event
{
    private $eventVersion;
    private $occurredOn;
    private $userId;
    private $name;
    private $password;

    public function __construct($userId, $name, $password)
    {
        $this->eventVersion = 1;
        $this->occurredOn = time();
        $this->userId = $userId;
        $this->name = $name;
        $this->password = $password;
    }

    /**
     * @return int
     */
    public function eventVersion()
    {
        return $this->eventVersion;
    }

    /**
     * @return \DateTime
     */
    public function occurredOn()
    {
        return $this->occurredOn;
    }

    /**
     * Must return a primitive key-value set which is serializable.
     * @return mixed[]
     */
    public function toContractualData()
    {
        return [
            'eventVersion' => $this->eventVersion,
            'occurredOn'   => $this->occurredOn,
            'userId'       => $this->userId,
            'name'         => $this->name,
            'password'     => $this->password,
        ];
    }

    /**
     * @param mixed[] $data
     * @return Event
     */
    public static function fromContractualData(array $data)
    {
        $self = new self($data['userId'], $data['name'], $data['password']);

        return $self;
    }

    public function userId()
    {
        return $this->userId;
    }

    public function name()
    {
        return $this->name;
    }

    public function password()
    {
        return $this->password;
    }
}

$user = new User('0cc175b9c0f1b6a831c399e269772661', 'alice', 'p@ssW0rd');

var_dump($user);