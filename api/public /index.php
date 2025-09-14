<?php

declare(strict_types=1);

use App\Db;
use App\Queue;
use App\Logger;

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($path === '/health' && $method === 'GET') {
    try {
        Db::pdo()->query('SELECT 1');
        $db = true;
    } catch (Throwable) {
        $db = false;
    }
    echo json_encode(['ok' => true, 'db' => $db]);
    exit;
}

if ($path === '/v1/events' && $method === 'POST') {
    $raw = file_get_contents('php://input') ?: '';
    if (!function_exists('json_validate') || !json_validate($raw)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_json']);
        exit;
    }
    $body = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);

    $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
    $type = (string)($body['type'] ?? 'generic');

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO events (id, type, payload) VALUES (:id, :type, :payload::jsonb)');
    $stmt->execute([':id' => $id, ':type' => $type, ':payload' => json_encode($body)]);

    (new Queue())->enqueue(['id' => $id]);

    echo json_encode(['id' => $id, 'status' => 'queued']);
    exit;
}

if (preg_match('#^/v1/events/([0-9a-f-]{36})$#i', $path, $m) && $method === 'GET') {
    $stmt = Db::pdo()->prepare('SELECT * FROM events WHERE id = :id');
    $stmt->execute([':id' => $m[1]]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'not_found']);
        exit;
    }
    echo json_encode($row);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'not_found']);
