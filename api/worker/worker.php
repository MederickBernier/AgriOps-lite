<?php

declare(strict_types=1);

use App\Db;
use App\Queue;
use App\Logger;

require __DIR__ . '/../vendor/autoload.php';

$log = Logger::make('worker');
$q = new Queue();
$pdo = DB::pdo();

$log->info('Worker Started');

while (true) {
    try {
        $job = $q->blockingDequeue(5);
        if ($job === null) {
            continue;
        }

        $id = $job['id'] ?? null;
        if (!$id) {
            $log->warning('Malformed job');
            continue;
        }

        // Simulate processing work (replace with real enrichment)
        usleep(250_000);

        $stmt = $pdo->prepare('UPDATE events SET status = :s, processed_at = now() WHERE id = :id');
        $stmt->execute([':s' => 'processed', ':id' => $id]);

        $log->info('Processed event', ['id' => $id]);
    } catch (Throwable $e) {
        $log->error('worker error: ' . $e->getMessage());
        usleep(500_000);
    }
}
