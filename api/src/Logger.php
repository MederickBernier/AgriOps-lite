<?php

declare(strict_types=1);

namespace App;

use Monolog\Logger as MLogger;
use Monolog\Handler\StreamHandler;

final class Logger
{
    public static function make(string $name = 'app'): MLogger
    {
        $log = new MLogger($name);
        $log->pushHandler(new StreamHandler('php://stdout'));
        return $log;
    }
}
