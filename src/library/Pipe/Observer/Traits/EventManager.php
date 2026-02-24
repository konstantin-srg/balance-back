<?php
namespace Pipe\Observer\Traits;

use Pipe\Observer\Observer as PEventManager;

trait EventManager {
    /** @var PEventManager */
    protected $eventManager;

    /** @return PEventManager */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->eventManager = new PEventManager();
        }

        return $this->eventManager;
    }
}