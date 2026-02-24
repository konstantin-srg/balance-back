<?php
namespace Pipe\Observer;

use SplStack;

class Results extends SplStack
{
    public function contains($value)
    {
        foreach ($this as $response) {
            if ($response === $value) {
                return true;
            }
        }

        return false;
    }
}