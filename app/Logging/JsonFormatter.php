<?php

namespace App\Logging;

class JsonFormatter
{
    public function __invoke($logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonLineFormatter());
        }
    }
}
