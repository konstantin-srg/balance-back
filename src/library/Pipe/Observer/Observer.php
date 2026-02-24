<?php
namespace Pipe\Observer;

class Observer
{
    protected $events = [];

    /**
     * @param $triggers
     * @param callable $listener
     * @return $this
     */
    public function add($triggers, callable $callback)
    {
        $this->events[] = [
            'triggers'  => $triggers,
            'callback'  => $callback,
        ];

        return $this;
    }

    /**
     * @param $trigger
     * @param null|object $caller
     * @param array $params
     * @return array|Results
     */
    public function call($trigger, $caller, $params = [])
    {
        $triggers = (array) $trigger;

        $results = new Results();

        foreach ($this->events as $event) {
            foreach ($triggers as $trigger) {
                if($trigger == $event['triggers'] || in_array($trigger, (array) $event['triggers'])) {
                    $results[] = call_user_func($event['callback'], new Event($caller, $params));
                    break;
                }
            }
        }

        return $results;
    }
}