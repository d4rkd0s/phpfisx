<?php
require_once('boot.php');

use phpfisx\areas\field as field;

// ── Live mode — Server-Sent Events streaming playback ────────────────────────
//
// Steps the simulation server-side and pushes each frame to the browser as it
// happens, instead of render.php's approach (run all steps, encode every frame
// as a PNG, ship one big HTML blob). Chosen over a WebSocket server because
// SSE works over plain HTTP with the PHP built-in dev server and any standard
// Apache/nginx setup, and needs no extra Composer dependency (no Ratchet, no
// Swoole) — see CLAUDE.md for the fuller rationale.
//
// Frame data is plain point/edge/joint coordinates (no GD, no PNG encoding),
// serialized by field::serializeFrame() / field::serializeStaticScene() — both
// pure functions with no transport concerns, so they're unit-testable without
// an actual HTTP connection. This file only owns headers, the step loop, and
// flushing.

$scene = json_decode($_GET['scene'] ?? '', true) ?? [];
$field = field::fromScene($scene);

set_time_limit(0);
ignore_user_abort(false);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // disable nginx response buffering, if fronted by nginx

// Disable PHP's own output buffering so each write reaches the client immediately.
while (ob_get_level() > 0) {
    ob_end_flush();
}

function sse_send(string $event, array $data): void {
    echo "event: {$event}\n";
    echo 'data: ' . json_encode($data) . "\n\n";
    @flush();
}

sse_send('init', $field->serializeStaticScene());

$steps = $field->getSteps();
for ($step = 1; $step <= $steps; $step++) {
    if (connection_aborted()) {
        break;
    }

    $field->setStep($step);
    $field->calculate();
    sse_send('step', $field->serializeFrame());

    // Pace playback so the browser sees a smooth animation rather than every
    // frame arriving at once — 80ms matches render.php's client-side player
    // interval so both playback modes feel the same speed.
    usleep(80_000);
}

sse_send('done', ['totalSteps' => $steps]);
