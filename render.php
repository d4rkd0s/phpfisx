<?php
require_once('boot.php');

use phpfisx\areas\field as field;

// ── Scene-based request (from editor) ────────────────────────────────────────
if (!empty($_GET['scene'])) {
    $scene    = json_decode($_GET['scene'], true) ?? [];
    $settings = $scene['settings'] ?? [];

    $points      = max(1,   min(500,  (int)(   $settings['points']      ?? 80)));
    $steps       = max(1,   min(200,  (int)(   $settings['steps']       ?? 50)));
    $gravity     = max(0.0, min(20.0, (float)( $settings['gravity']     ?? 1.0)));
    $friction    = max(0.0, min(1.0,  (float)( $settings['friction']    ?? 0.98)));
    $restitution = max(0.0, min(1.0,  (float)( $settings['restitution'] ?? 0.7)));

    $field = new field([0, 500, 0, 500], $gravity, 4, $friction, 5.0, $restitution);
    $field->desiredPointCount($points);
    $field->setSteps($steps);

    foreach ($scene['shapes'] ?? [] as $s) {
        switch ($s['type'] ?? '') {
            case 'box':
                $field->spawnBox(
                    (float)($s['cx']   ?? 250), (float)($s['cy']   ?? 250),
                    (float)($s['w']    ?? 60),  (float)($s['h']    ?? 40),
                    (float)($s['mass'] ?? 3.0)
                );
                break;
            case 'circle':
                $field->spawnCircle(
                    (float)($s['cx']   ?? 250), (float)($s['cy']   ?? 250),
                    (float)($s['r']    ?? 30),  (int)(  $s['n']    ?? 10),
                    (float)($s['mass'] ?? 1.5)
                );
                break;
            case 'line':
                $field->addStaticLine(
                    (float)($s['x1'] ?? 0),   (float)($s['y1'] ?? 0),
                    (float)($s['x2'] ?? 100), (float)($s['y2'] ?? 100)
                );
                break;
            case 'spawn':
                $field->setSpawnZone(
                    (float)($s['x1'] ?? 0),   (float)($s['y1'] ?? 0),
                    (float)($s['x2'] ?? 500), (float)($s['y2'] ?? 500)
                );
                break;
        }
    }

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
