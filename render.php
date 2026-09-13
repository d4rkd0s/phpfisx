<?php
require_once('boot.php');

use phpfisx\areas\field as field;

// ── Scene-based request (from editor) ────────────────────────────────────────
// Scene parsing lives in field::fromScene() so this GD/HTML playback path and
// live.php's SSE streaming path can never drift on how a scene is interpreted.
if (!empty($_GET['scene'])) {
    $scene = json_decode($_GET['scene'], true) ?? [];
    $field = field::fromScene($scene);
    $field->visualize();
    exit;
}

// ── Legacy GET-param request (backward compat) ────────────────────────────────
$points      = max(1,   min(500,  (int)(   $_GET['points']      ?? 40)));
$steps       = max(1,   min(200,  (int)(   $_GET['steps']       ?? 60)));
$gravity     = max(0.0, min(20.0, (float)($_GET['gravity']     ?? 1.0)));
$friction    = max(0.0, min(1.0,  (float)($_GET['friction']    ?? 0.98)));
$restitution = max(0.0, min(1.0,  (float)($_GET['restitution'] ?? 0.7)));
$shapes      = !empty($_GET['shapes']);

$field = new field([0, 500, 0, 500], $gravity, 4, $friction, 5.0, $restitution);
$field->desiredPointCount($points);
$field->setSteps($steps);

if ($shapes) {
    $field->spawnBox(140, 60, 70, 50, 3.0);
    $field->spawnBox(360, 40, 50, 80, 2.0);
    $field->spawnCircle(250, 80, 35, 10, 1.5);
}

$field->visualize();
