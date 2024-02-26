<?php
use Symfony\Contracts\EventDispatcher\Event;

class UserCreatedEvent extends Event {
    private $user;

    public function __construct($user) {
        $this->user = $user;
    }

    public function getUser() {
        return $this->user;
    }
}