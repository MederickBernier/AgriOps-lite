<?php

declare(strict_types=1);

namespace App;

use Predis\Client;

final class Queue
{
    private Client $redis;

    public function __construct(?string $url = null)
    {
        $url ??= getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379';
        $this->redis = new Client($url);
    }

    public function enqueue(array $job): void
    {
        $this->redis->rpush('queue:events', [json_encode($job, JSON_UNESCAPED_SLASHES)]);
    }

    public function blockingDequeue(int $timeout = 5): ?array
    {
        $res = $this->redis->brpop(['queue:events', $timeout]);
        if (!$res) return null;
        [, $payload] = $res;
        return json_decode($payload, true);
    }
}
