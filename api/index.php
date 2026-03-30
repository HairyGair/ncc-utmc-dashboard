<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

// ---------------------------------------------------------------------------
// CORS and content-type headers
// ---------------------------------------------------------------------------

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function json_error(string $message, int $code = 400): never
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

function json_success(array $extra = []): never
{
    echo json_encode(array_merge(['success' => true], $extra));
    exit;
}

function get_body(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === '' || $raw === false) {
        return [];
    }

    $data = json_decode($raw, true);

    if (!is_array($data)) {
        json_error('Invalid JSON body');
    }

    return $data;
}

function require_fields(array $body, array $fields): void
{
    foreach ($fields as $field) {
        if (!isset($body[$field]) || $body[$field] === '') {
            json_error("Missing required field: {$field}");
        }
    }
}

function validate_enum(string $value, array $allowed, string $field): void
{
    if (!in_array($value, $allowed, true)) {
        json_error("Invalid value for {$field}: {$value}");
    }
}

function to_mysql_datetime(string $iso): string
{
    $dt = new DateTimeImmutable($iso);
    return $dt->format('Y-m-d H:i:s');
}

// ---------------------------------------------------------------------------
// Routing
// ---------------------------------------------------------------------------

$page   = $_GET['page'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($page) {
        case 'warnings':
            handle_warnings($method);
            break;

        case 'installations':
            handle_installations($method);
            break;

        case 'visits':
            handle_visits($method);
            break;

        default:
            json_error('Unknown page', 404);
    }
} catch (PDOException $e) {
    json_error('Database error: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
    json_error('Server error: ' . $e->getMessage(), 500);
}

// ---------------------------------------------------------------------------
// Warnings page
// ---------------------------------------------------------------------------

function handle_warnings(string $method): void
{
    $pdo = get_pdo();

    if ($method === 'GET') {
        // Fetch all warning states
        $stmt = $pdo->query(
            'SELECT site_idx, field_name, status, note, tech_name, recorded_at
             FROM warning_states
             ORDER BY site_idx, field_name'
        );

        $warnings = [];

        foreach ($stmt->fetchAll() as $row) {
            $idx   = (string) $row['site_idx'];
            $field = $row['field_name'];

            $warnings[$idx][$field] = [
                'status'    => $row['status'],
                'note'      => $row['note'] ?? '',
                'techName'  => $row['tech_name'] ?? '',
                'timestamp' => $row['recorded_at'],
            ];
        }

        // Fetch all tech notes
        $stmt = $pdo->query(
            'SELECT site_idx, note, tech_name, updated_at
             FROM warning_tech_notes
             ORDER BY site_idx'
        );

        $tech_notes = [];

        foreach ($stmt->fetchAll() as $row) {
            $tech_notes[(string) $row['site_idx']] = $row['note'] ?? '';
        }

        json_success(['warnings' => $warnings, 'techNotes' => $tech_notes]);
    }

    if ($method === 'POST') {
        $body   = get_body();
        $action = $body['action'] ?? '';

        switch ($action) {
            case 'warning':
                require_fields($body, ['siteIdx', 'fieldName', 'status', 'timestamp']);
                validate_enum($body['status'], ['pending', 'resolved', 'issue'], 'status');

                $stmt = $pdo->prepare(
                    'INSERT INTO warning_states
                         (site_idx, field_name, status, note, tech_name, recorded_at)
                     VALUES
                         (:site_idx, :field_name, :status, :note, :tech_name, :recorded_at)
                     ON DUPLICATE KEY UPDATE
                         status      = VALUES(status),
                         note        = VALUES(note),
                         tech_name   = VALUES(tech_name),
                         recorded_at = VALUES(recorded_at)'
                );

                $stmt->execute([
                    ':site_idx'    => (int) $body['siteIdx'],
                    ':field_name'  => (string) $body['fieldName'],
                    ':status'      => (string) $body['status'],
                    ':note'        => isset($body['note']) ? (string) $body['note'] : null,
                    ':tech_name'   => isset($body['techName']) ? (string) $body['techName'] : null,
                    ':recorded_at' => to_mysql_datetime((string) $body['timestamp']),
                ]);

                json_success();

            case 'technote':
                require_fields($body, ['siteIdx']);

                $stmt = $pdo->prepare(
                    'INSERT INTO warning_tech_notes
                         (site_idx, note, tech_name, updated_at)
                     VALUES
                         (:site_idx, :note, :tech_name, :updated_at)
                     ON DUPLICATE KEY UPDATE
                         note       = VALUES(note),
                         tech_name  = VALUES(tech_name),
                         updated_at = VALUES(updated_at)'
                );

                $stmt->execute([
                    ':site_idx'   => (int) $body['siteIdx'],
                    ':note'       => isset($body['note']) ? (string) $body['note'] : null,
                    ':tech_name'  => isset($body['techName']) ? (string) $body['techName'] : null,
                    ':updated_at' => date('Y-m-d H:i:s'),
                ]);

                json_success();

            case 'reset':
                require_fields($body, ['siteIdx', 'fieldName']);

                $stmt = $pdo->prepare(
                    'DELETE FROM warning_states
                     WHERE site_idx = :site_idx AND field_name = :field_name'
                );

                $stmt->execute([
                    ':site_idx'   => (int) $body['siteIdx'],
                    ':field_name' => (string) $body['fieldName'],
                ]);

                json_success();

            case 'clear':
                $pdo->exec('TRUNCATE TABLE warning_states');
                $pdo->exec('TRUNCATE TABLE warning_tech_notes');
                json_success();

            default:
                json_error('Unknown action');
        }
    }

    json_error('Method not allowed', 405);
}

// ---------------------------------------------------------------------------
// Installations page
// ---------------------------------------------------------------------------

function handle_installations(string $method): void
{
    $pdo = get_pdo();

    if ($method === 'GET') {
        $stmt = $pdo->query(
            'SELECT site_id, type, note, tech_name, recorded_at
             FROM installation_outcomes
             ORDER BY site_id'
        );

        $outcomes = [];

        foreach ($stmt->fetchAll() as $row) {
            $outcomes[(string) $row['site_id']] = [
                'type'      => $row['type'],
                'note'      => $row['note'] ?? '',
                'techName'  => $row['tech_name'] ?? '',
                'timestamp' => $row['recorded_at'],
            ];
        }

        json_success(['outcomes' => $outcomes]);
    }

    if ($method === 'POST') {
        $body   = get_body();
        $action = $body['action'] ?? '';

        switch ($action) {
            case 'save':
                require_fields($body, ['siteId', 'type', 'timestamp']);
                validate_enum($body['type'], ['online', 'issue'], 'type');

                $stmt = $pdo->prepare(
                    'INSERT INTO installation_outcomes
                         (site_id, type, note, tech_name, recorded_at)
                     VALUES
                         (:site_id, :type, :note, :tech_name, :recorded_at)
                     ON DUPLICATE KEY UPDATE
                         type        = VALUES(type),
                         note        = VALUES(note),
                         tech_name   = VALUES(tech_name),
                         recorded_at = VALUES(recorded_at)'
                );

                $stmt->execute([
                    ':site_id'     => (int) $body['siteId'],
                    ':type'        => (string) $body['type'],
                    ':note'        => isset($body['note']) ? (string) $body['note'] : null,
                    ':tech_name'   => isset($body['techName']) ? (string) $body['techName'] : null,
                    ':recorded_at' => to_mysql_datetime((string) $body['timestamp']),
                ]);

                json_success();

            case 'edit':
                require_fields($body, ['siteId']);

                $stmt = $pdo->prepare(
                    'DELETE FROM installation_outcomes WHERE site_id = :site_id'
                );

                $stmt->execute([':site_id' => (int) $body['siteId']]);

                json_success();

            case 'clear':
                $pdo->exec('TRUNCATE TABLE installation_outcomes');
                json_success();

            default:
                json_error('Unknown action');
        }
    }

    json_error('Method not allowed', 405);
}

// ---------------------------------------------------------------------------
// Site visits page
// ---------------------------------------------------------------------------

function handle_visits(string $method): void
{
    $pdo = get_pdo();

    if ($method === 'GET') {
        $stmt = $pdo->query(
            'SELECT scn, type, note, tech_name, recorded_at
             FROM site_visit_outcomes
             ORDER BY scn'
        );

        $outcomes = [];

        foreach ($stmt->fetchAll() as $row) {
            $outcomes[$row['scn']] = [
                'type'      => $row['type'],
                'note'      => $row['note'] ?? '',
                'techName'  => $row['tech_name'] ?? '',
                'timestamp' => $row['recorded_at'],
            ];
        }

        json_success(['outcomes' => $outcomes]);
    }

    if ($method === 'POST') {
        $body   = get_body();
        $action = $body['action'] ?? '';

        switch ($action) {
            case 'save':
                require_fields($body, ['scn', 'type', 'timestamp']);
                validate_enum($body['type'], ['online', 'issue'], 'type');

                $stmt = $pdo->prepare(
                    'INSERT INTO site_visit_outcomes
                         (scn, type, note, tech_name, recorded_at)
                     VALUES
                         (:scn, :type, :note, :tech_name, :recorded_at)
                     ON DUPLICATE KEY UPDATE
                         type        = VALUES(type),
                         note        = VALUES(note),
                         tech_name   = VALUES(tech_name),
                         recorded_at = VALUES(recorded_at)'
                );

                $stmt->execute([
                    ':scn'         => (string) $body['scn'],
                    ':type'        => (string) $body['type'],
                    ':note'        => isset($body['note']) ? (string) $body['note'] : null,
                    ':tech_name'   => isset($body['techName']) ? (string) $body['techName'] : null,
                    ':recorded_at' => to_mysql_datetime((string) $body['timestamp']),
                ]);

                json_success();

            case 'edit':
                require_fields($body, ['scn']);

                $stmt = $pdo->prepare(
                    'DELETE FROM site_visit_outcomes WHERE scn = :scn'
                );

                $stmt->execute([':scn' => (string) $body['scn']]);

                json_success();

            case 'clear':
                $pdo->exec('TRUNCATE TABLE site_visit_outcomes');
                json_success();

            default:
                json_error('Unknown action');
        }
    }

    json_error('Method not allowed', 405);
}
