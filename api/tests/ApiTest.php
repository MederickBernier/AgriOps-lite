<?php

declare(strict_types=1);

it('rejects invalid json', function () {
    $ch = curl_init('http://nginx:80/v1/events');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => 'not json',
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    expect($code)->toBe(400);
});
