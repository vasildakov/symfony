<?php

namespace App\EventListener;

use App\Event\CustomEvent;

class MyListener
{
    public function __invoke(CustomEvent $event): void
    {
        //dd($event);
    }
}
