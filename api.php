<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

initDb();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'students':
            echo json_encode(getStudents());
            break;
        case 'student':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) { httpError(400, 'Chybí parametr id'); }
            echo json_encode(getStudentDetail((int)$id));
            break;
        case 'categories':
            global $CATEGORIES;
            echo json_encode($CATEGORIES);
            break;
        case 'add_merit':
            requireAdmin(true);
            echo json_encode(addMerit());
            break;
        case 'add_student':
            requireAdmin(true);
            echo json_encode(addStudent());
            break;
        case 'update_student':
            requireAdmin(true);
            echo json_encode(updateStudent());
            break;
        case 'delete_merit':
            requireAdmin(true);
            echo json_encode(deleteMerit());
            break;
        case 'delete_student':
            requireAdmin(true);
            echo json_encode(deleteStudent());
            break;
        case 'admin_students':
            requireAdmin();
            echo json_encode(getAdminStudents());
            break;
        default:
            httpError(400, 'Neznámá akce');
    }
} catch (Throwable $e) {
    error_log('[silicon-ribbons] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    httpError(500, IS_LOCAL_ENV ? $e->getMessage() : 'Interní chyba serveru.');
}

// ─── Veřejné GET endpointy ────────────────────────────────────────────────────

function getStudents(): array {
    $db = getDb();

    $name     = trim($_GET['name'] ?? '');
    $category = trim($_GET['category'] ?? '');
    $minLevel = filter_input(INPUT_GET, 'min_level', FILTER_VALIDATE_INT) ?: 0;
    $sort     = in_array($_GET['sort'] ?? '', ['score', 'name']) ? $_GET['sort'] : 'score';
    $year     = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT) ?: 0;
    $limit    = min((int)($_GET['limit'] ?? 12), 100);
    $offset   = max((int)($_GET['offset'] ?? 0), 0);

    $where  = [];
    $params = [];

    if ($name !== '') {
        $where[]  = "(s.lastname LIKE :name OR s.firstname LIKE :name)";
        $params[':name'] = '%' . $name . '%';
    }
    if ($year > 0) {
        $where[]  = 's.admission_year = :year';
        $params[':year'] = $year;
    }

    $havingParts = [];
    if ($category !== '') {
        $havingParts[] = "MAX(CASE WHEN m.category = :cat THEN m.level ELSE 0 END) >= CAST(:minLvl AS INTEGER)";
        $params[':cat']    = $category;
        $params[':minLvl'] = $minLevel > 0 ? $minLevel : 1;
    } elseif ($minLevel > 0) {
        $havingParts[] = "COALESCE(MAX(m.level), 0) >= CAST(:minLvl AS INTEGER)";
        $params[':minLvl'] = $minLevel;
    }

    $whereClause  = $where      ? 'WHERE ' . implode(' AND ', $where) : '';
    $havingClause = $havingParts ? 'HAVING ' . implode(' AND ', $havingParts) : '';
    $orderClause  = $sort === 'name'
        ? 'ORDER BY s.is_public DESC, s.lastname, s.firstname'
        : 'ORDER BY merit_score DESC, s.lastname';

    $sql = "
        SELECT
            s.id,
            s.lastname,
            s.firstname,
            s.admission_year,
            s.is_public,
            COALESCE(SUM(m.level), 0)  AS merit_score,
            COALESCE(COUNT(m.id), 0)   AS merit_count,
            GROUP_CONCAT(m.category || ':' || m.level, ',') AS ribbons_raw
        FROM students s
        LEFT JOIN merits m ON m.student_id = s.id
        {$whereClause}
        GROUP BY s.id
        {$havingClause}
        {$orderClause}
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $db->prepare($sql);
    $params[':limit']  = $limit + 1;  // načteme o 1 víc, abychom věděli, zda existují další
    $params[':offset'] = $offset;
    $stmt->execute($params);
    $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasMore = count($rows) > $limit;
    if ($hasMore) {
        array_pop($rows);
    }

    return [
        'students' => array_map('formatStudentRow', $rows),
        'has_more' => $hasMore,
        'offset'   => $offset,
        'limit'    => $limit,
    ];
}

function getStudentDetail(int $id): array {
    $db  = getDb();
    $s   = $db->prepare('SELECT * FROM students WHERE id = ?');
    $s->execute([$id]);
    $student = $s->fetch(PDO::FETCH_ASSOC);
    if (!$student) { httpError(404, 'Student nenalezen'); }

    $m = $db->prepare('SELECT * FROM merits WHERE student_id = ? ORDER BY granted_at DESC');
    $m->execute([$id]);
    $merits = $m->fetchAll(PDO::FETCH_ASSOC);

    // Neveřejní žáci: popis a autor ocenění (volný text od učitele) se veřejně nevrací,
    // mohly by žáka identifikovat. Přihlášený učitel vidí vše.
    if (!(bool)$student['is_public'] && !isAdminSession()) {
        foreach ($merits as &$mm) {
            $mm['granted_by']  = '';
            $mm['description'] = null;
        }
        unset($mm);
    }

    $score = array_sum(array_column($merits, 'level'));

    return [
        'id'             => (int)$student['id'],
        'display_name'   => displayName($student),
        'admission_year' => (int)$student['admission_year'],
        'is_public'      => (bool)$student['is_public'],
        'merit_score'    => $score,
        'merits'         => $merits,
    ];
}

// ─── Admin POST endpointy ─────────────────────────────────────────────────────

/** Textové pole z JSON těla: typ, povinnost, ořez a max. délka. */
function strField(array $data, string $key, int $max, bool $required = true): ?string {
    $v = $data[$key] ?? null;
    if ($v === null || $v === '') {
        if ($required) { httpError(400, "Chybí pole: {$key}"); }
        return null;
    }
    if (!is_string($v)) { httpError(400, "Neplatné pole: {$key}"); }
    $v = trim($v);
    if ($v === '') {
        if ($required) { httpError(400, "Chybí pole: {$key}"); }
        return null;
    }
    if (mb_strlen($v) > $max) { httpError(400, "Pole {$key} je příliš dlouhé (max. {$max} znaků)"); }
    return $v;
}

function intField(array $data, string $key, int $min, int $max): int {
    $v = filter_var($data[$key] ?? null, FILTER_VALIDATE_INT);
    if ($v === false || $v < $min || $v > $max) { httpError(400, "Neplatné pole: {$key}"); }
    return $v;
}

function studentExists(PDO $db, int $id): bool {
    $s = $db->prepare('SELECT 1 FROM students WHERE id = ?');
    $s->execute([$id]);
    return (bool) $s->fetchColumn();
}

function addMerit(): array {
    $data = jsonBody();
    global $CATEGORIES;
    $studentId = intField($data, 'student_id', 1, PHP_INT_MAX);
    $category  = strField($data, 'category', 10);
    if (!isset($CATEGORIES[$category])) { httpError(400, 'Neznámá kategorie'); }
    $level     = intField($data, 'level', 1, 5);
    $grantedBy = strField($data, 'granted_by', 100);
    $desc      = strField($data, 'description', 500, false);

    $db = getDb();
    if (!studentExists($db, $studentId)) { httpError(404, 'Student nenalezen'); }

    // Stejná kombinace student+kategorie+level ocenění aktualizuje (id zůstává stejné)
    $stmt = $db->prepare('
        INSERT INTO merits (student_id, category, level, granted_by, description)
        VALUES (?,?,?,?,?)
        ON CONFLICT(student_id, category, level) DO UPDATE SET
            granted_by = excluded.granted_by,
            description = excluded.description,
            granted_at = CURRENT_TIMESTAMP
    ');
    $stmt->execute([
        $studentId,
        $category,
        $level,
        htmlspecialchars($grantedBy, ENT_QUOTES),
        $desc !== null ? htmlspecialchars($desc, ENT_QUOTES) : null,
    ]);
    $idStmt = $db->prepare('SELECT id FROM merits WHERE student_id = ? AND category = ? AND level = ?');
    $idStmt->execute([$studentId, $category, $level]);
    $id = (int) $idStmt->fetchColumn();

    auditLog('add_merit', ['id' => $id, 'student_id' => $studentId, 'category' => $category, 'level' => $level]);
    return ['ok' => true, 'id' => $id];
}

function addStudent(): array {
    $data = jsonBody();
    $last  = strField($data, 'lastname', 100);
    $first = strField($data, 'firstname', 100);
    $year  = intField($data, 'admission_year', 2000, 2099);

    $db   = getDb();
    $stmt = $db->prepare('INSERT INTO students (lastname, firstname, admission_year, is_public) VALUES (?,?,?,?)');
    $stmt->execute([
        htmlspecialchars($last, ENT_QUOTES),
        htmlspecialchars($first, ENT_QUOTES),
        $year,
        !empty($data['is_public']) ? 1 : 0,
    ]);
    $id = (int) $db->lastInsertId();
    auditLog('add_student', ['id' => $id, 'admission_year' => $year]);
    return ['ok' => true, 'id' => $id];
}

function updateStudent(): array {
    $data  = jsonBody();
    $id    = intField($data, 'id', 1, PHP_INT_MAX);
    $last  = strField($data, 'lastname', 100);
    $first = strField($data, 'firstname', 100);
    $year  = intField($data, 'admission_year', 2000, 2099);

    $db = getDb();
    if (!studentExists($db, $id)) { httpError(404, 'Student nenalezen'); }
    $isPublic = !empty($data['is_public']) ? 1 : 0;
    $stmt = $db->prepare('UPDATE students SET lastname=?, firstname=?, admission_year=?, is_public=? WHERE id=?');
    $stmt->execute([
        htmlspecialchars($last, ENT_QUOTES),
        htmlspecialchars($first, ENT_QUOTES),
        $year,
        $isPublic,
        $id,
    ]);
    auditLog('update_student', ['id' => $id, 'admission_year' => $year, 'is_public' => $isPublic]);
    return ['ok' => true];
}

function deleteMerit(): array {
    $data = jsonBody();
    $id = intField($data, 'id', 1, PHP_INT_MAX);
    $db = getDb();
    $db->prepare('DELETE FROM merits WHERE id = ?')->execute([$id]);
    auditLog('delete_merit', ['id' => $id]);
    return ['ok' => true];
}

function deleteStudent(): array {
    $data = jsonBody();
    $id = intField($data, 'id', 1, PHP_INT_MAX);
    $db = getDb();
    $db->prepare('DELETE FROM students WHERE id = ?')->execute([$id]);
    auditLog('delete_student', ['id' => $id]);
    return ['ok' => true];
}

function getAdminStudents(): array {
    $db   = getDb();
    $stmt = $db->query('SELECT s.*, COALESCE(SUM(m.level),0) AS merit_score FROM students s LEFT JOIN merits m ON m.student_id=s.id GROUP BY s.id ORDER BY s.lastname, s.firstname');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$row) {
        $row['anon_code'] = anonymousId((int)$row['id'], (int)$row['admission_year']);
    }
    return $rows;
}

// ─── Pomocné funkce ───────────────────────────────────────────────────────────

function formatStudentRow(array $row): array {
    $student = [
        'id'             => (int)$row['id'],
        'admission_year' => (int)$row['admission_year'],
        'is_public'      => (bool)$row['is_public'],
        'merit_score'    => (int)$row['merit_score'],
        'merit_count'    => (int)$row['merit_count'],
        'display_name'   => displayName($row),
        'ribbons'        => [],
    ];

    if (!empty($row['ribbons_raw'])) {
        foreach (explode(',', $row['ribbons_raw']) as $piece) {
            [$cat, $lvl] = explode(':', $piece);
            if (!isset($student['ribbons'][$cat]) || (int)$lvl > $student['ribbons'][$cat]) {
                $student['ribbons'][$cat] = (int)$lvl;
            }
        }
    }

    return $student;
}

function displayName(array $row): string {
    if ((int)$row['is_public'] === 1) {
        return $row['lastname'] . ' ' . $row['firstname'];
    }
    return anonymousId((int)$row['id'], (int)$row['admission_year']);
}

function jsonBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) { httpError(400, 'Neplatné JSON tělo'); }
    return $data;
}

/** Zápisový požadavek musí přijít z naší stránky (ochrana proti CSRF; doplněk k SameSite=Lax). */
function requireSameOrigin(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin !== '') {
        $ok = $origin === APP_ORIGIN;
        if (!$ok && IS_LOCAL_ENV) {
            $ok = parse_url($origin, PHP_URL_HOST) === parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
        }
    } else {
        // Bez Origin povolit jen prohlížečem potvrzený požadavek ze stejného originu
        $ok = ($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'same-origin';
    }
    if (!$ok) {
        httpError(403, 'Neplatný původ požadavku');
    }
}

function requireAdmin(bool $write = false): void {
    if (ssoSessionUser() === null) {
        httpError(401, 'Nepřihlášen');
    }
    if (currentTeacher() === null) {
        httpError(403, 'Přístup odepřen');
    }
    if ($write) {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            httpError(405, 'Metoda není povolena');
        }
        if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
            httpError(415, 'Očekáván application/json');
        }
        requireSameOrigin();
    }
}

function httpError(int $code, string $msg): never {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}
