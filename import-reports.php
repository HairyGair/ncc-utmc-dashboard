<?php
/**
 * One-off import script for Stewart Rudd & David Wheeler reports (31 Mar 2026).
 * Run once, then delete this file.
 */

require_once __DIR__ . '/api/config.php';

$pdo = get_pdo();

$records = [
    // Stewart Rudd — ONLINE (6 sites)
    ['scn' => 'X10260', 'type' => 'online', 'note' => null, 'tech_name' => 'Stewart Rudd', 'recorded_at' => '2026-03-30 12:56:00'],
    ['scn' => 'X16330', 'type' => 'online', 'note' => null, 'tech_name' => 'Stewart Rudd', 'recorded_at' => '2026-03-30 15:42:00'],
    ['scn' => 'X09120', 'type' => 'online', 'note' => null, 'tech_name' => 'Stewart Rudd', 'recorded_at' => '2026-03-30 11:57:00'],
    ['scn' => 'X09140', 'type' => 'online', 'note' => null, 'tech_name' => 'Stewart Rudd', 'recorded_at' => '2026-03-30 12:17:00'],
    ['scn' => 'X13120', 'type' => 'online', 'note' => null, 'tech_name' => 'Stewart Rudd', 'recorded_at' => '2026-03-30 12:54:00'],
    ['scn' => 'X85240', 'type' => 'online', 'note' => null, 'tech_name' => 'Stewart Rudd', 'recorded_at' => '2026-03-30 16:41:00'],

    // David Wheeler — ONLINE (1 site)
    ['scn' => 'X90130', 'type' => 'online', 'note' => null, 'tech_name' => 'David Wheeler', 'recorded_at' => '2026-03-31 07:50:00'],

    // Stewart Rudd — ISSUE (1 site)
    ['scn' => 'X19110', 'type' => 'issue', 'note' => 'Requires stratos unit fitting and new Mova data set, antennas and socket bank. Router returned to depot awaiting equipment', 'tech_name' => 'Stewart Rudd', 'recorded_at' => '2026-03-30 10:36:00'],
];

$stmt = $pdo->prepare(
    'INSERT INTO site_visit_outcomes (scn, type, note, tech_name, recorded_at)
     VALUES (:scn, :type, :note, :tech_name, :recorded_at)
     ON DUPLICATE KEY UPDATE
         type        = VALUES(type),
         note        = VALUES(note),
         tech_name   = VALUES(tech_name),
         recorded_at = VALUES(recorded_at)'
);

$count = 0;

foreach ($records as $r) {
    $stmt->execute($r);
    $count++;
}

header('Content-Type: text/plain');
echo "Imported {$count} records successfully.\n\n";

// Verify
$check = $pdo->query('SELECT scn, type, note, tech_name, recorded_at FROM site_visit_outcomes ORDER BY recorded_at');
echo "Current database contents:\n";
echo str_repeat('-', 100) . "\n";
foreach ($check->fetchAll(PDO::FETCH_ASSOC) as $row) {
    printf("%-10s %-8s %-20s %s %s\n", $row['scn'], $row['type'], $row['recorded_at'], $row['tech_name'], $row['note'] ? '(' . $row['note'] . ')' : '');
}
