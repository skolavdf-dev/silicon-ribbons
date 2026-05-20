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
            requireAdmin();
            echo json_encode(addMerit());
            break;
        case 'add_student':
            requireAdmin();
            echo json_encode(addStudent());
            break;
        case 'update_student':
            requireAdmin();
            echo json_encode(updateStudent());
            break;
        case 'delete_merit':
            requireAdmin();
            echo json_encode(deleteMerit());
            break;
        case 'delete_student':
            requireAdmin();
            echo json_encode(deleteStudent());
            break;
        case 'admin_students':
            requireAdmin();
            echo json_encode(getAdminStudents());
            break;
        default:
            httpError(400, 'Neznámá akce');
    }
} catch (Exception $e) {
    httpError(500, $e->getMessage());
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

function addMerit(): array {
    $data = jsonBody();
    $required = ['student_id', 'category', 'level', 'granted_by'];
    foreach ($required as $f) {
        if (empty($data[$f])) { httpError(400, "Chybí pole: {$f}"); }
    }
    global $CATEGORIES;
    $level = (int)$data['level'];
    if ($level < 1 || $level > 5) { httpError(400, 'Level musí být 1–5'); }
    if (!isset($CATEGORIES[$data['category']])) { httpError(400, 'Neznámá kategorie'); }

    $db   = getDb();
    $stmt = $db->prepare('INSERT OR REPLACE INTO merits (student_id, category, level, granted_by, description) VALUES (?,?,?,?,?)');
    $stmt->execute([
        (int)$data['student_id'],
        $data['category'],
        $level,
        htmlspecialchars($data['granted_by'], ENT_QUOTES),
        isset($data['description']) ? htmlspecialchars($data['description'], ENT_QUOTES) : null,
    ]);
    return ['ok' => true, 'id' => (int)$db->lastInsertId()];
}

function addStudent(): array {
    $data = jsonBody();
    foreach (['lastname', 'firstname', 'admission_year'] as $f) {
        if (empty($data[$f])) { httpError(400, "Chybí pole: {$f}"); }
    }
    $year = (int)$data['admission_year'];
    if ($year < 2000 || $year > 2099) { httpError(400, 'Neplatný rok nástupu'); }

    $db   = getDb();
    $stmt = $db->prepare('INSERT INTO students (lastname, firstname, admission_year, is_public) VALUES (?,?,?,?)');
    $stmt->execute([
        htmlspecialchars(trim($data['lastname']), ENT_QUOTES),
        htmlspecialchars(trim($data['firstname']), ENT_QUOTES),
        $year,
        isset($data['is_public']) && $data['is_public'] ? 1 : 0,
    ]);
    return ['ok' => true, 'id' => (int)$db->lastInsertId()];
}

function updateStudent(): array {
    $data = jsonBody();
    if (empty($data['id'])) { httpError(400, 'Chybí id'); }
    $db = getDb();
    $stmt = $db->prepare('UPDATE students SET lastname=?, firstname=?, admission_year=?, is_public=? WHERE id=?');
    $stmt->execute([
        htmlspecialchars(trim($data['lastname']), ENT_QUOTES),
        htmlspecialchars(trim($data['firstname']), ENT_QUOTES),
        (int)$data['admission_year'],
        isset($data['is_public']) && $data['is_public'] ? 1 : 0,
        (int)$data['id'],
    ]);
    return ['ok' => true];
}

function deleteMerit(): array {
    $data = jsonBody();
    if (empty($data['id'])) { httpError(400, 'Chybí id'); }
    $db = getDb();
    $db->prepare('DELETE FROM merits WHERE id = ?')->execute([(int)$data['id']]);
    return ['ok' => true];
}

function deleteStudent(): array {
    $data = jsonBody();
    if (empty($data['id'])) { httpError(400, 'Chybí id'); }
    $db = getDb();
    $db->prepare('DELETE FROM students WHERE id = ?')->execute([(int)$data['id']]);
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

function requireAdmin(): void {
    session_start();
    if (empty($_SESSION['teacher_email'])) {
        httpError(401, 'Nepřihlášen');
    }
    global $ALLOWED_TEACHERS;
    if (!in_array($_SESSION['teacher_email'], $ALLOWED_TEACHERS, true)) {
        httpError(403, 'Přístup odepřen');
    }
}

function httpError(int $code, string $msg): never {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}
