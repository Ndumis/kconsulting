<?php
/* =====================================================================
 *  TEMPORARY DEBUG BLOCK — REMOVE AFTER EVERYTHING WORKS
 * ===================================================================== */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/* =====================================================================
 *  END TEMPORARY DEBUG BLOCK
 * ===================================================================== */

// ---- Config ---------------------------------------------------------
$configPath = __DIR__ . '/../php/config.php';
if (!file_exists($configPath)) {
    die("FATAL: config.php not found at $configPath");
}
require_once $configPath;

// ---- Auth -----------------------------------------------------------
if (!defined('TACC_ADMIN_USER') || !defined('TACC_ADMIN_PASS')) {
    die("FATAL: TACC_ADMIN_USER and TACC_ADMIN_PASS must be defined in php/config.php");
}

if (!isset($_SERVER['PHP_AUTH_USER']) ||
    $_SERVER['PHP_AUTH_USER'] !== TACC_ADMIN_USER ||
    $_SERVER['PHP_AUTH_PW']   !== TACC_ADMIN_PASS) {
    header('WWW-Authenticate: Basic realm="TACC Admin"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Unauthorized');
}

// ---- DB -------------------------------------------------------------
$dbPath = __DIR__ . '/../php/db.php';
if (!file_exists($dbPath)) {
    die("FATAL: db.php not found at $dbPath");
}
require_once $dbPath;

if (!function_exists('getDbConnection')) {
    die("FATAL: getDbConnection() is not defined in db.php");
}

try {
    $conn = getDbConnection();
} catch (Throwable $e) {
    die("FATAL: getDbConnection() failed: " . $e->getMessage());
}

$isMysqli = $conn instanceof mysqli;
$isPdo    = $conn instanceof PDO;

if (!$isMysqli && !$isPdo) {
    die("FATAL: getDbConnection() returned an unsupported type: " . get_class($conn));
}

// ---- DB helpers (work with both mysqli and PDO) ---------------------
function dbQuery($conn, string $sql) {
    if ($conn instanceof mysqli) {
        $res = @$conn->query($sql);
        if ($res === false) {
            throw new RuntimeException('Query failed: ' . $conn->error . ' — SQL: ' . $sql);
        }
        return $res;
    }
    return $conn->query($sql);
}

function dbFetchAll($conn, string $sql): array {
    $res = dbQuery($conn, $sql);
    if ($conn instanceof mysqli) {
        return $res->fetch_all(MYSQLI_ASSOC);
    }
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

function dbFetchOne($conn, string $sql) {
    $res = dbQuery($conn, $sql);
    if ($conn instanceof mysqli) {
        return $res->fetch_assoc();
    }
    $row = $res->fetch(PDO::FETCH_ASSOC);
    return $row === false ? null : $row;
}

function dbScalar($conn, string $sql, $default = 0) {
    $row = dbFetchOne($conn, $sql);
    if (!$row) return $default;
    $first = reset($row);
    return $first === null ? $default : $first;
}

function tableExists($conn, string $table): bool {
    if ($conn instanceof mysqli) {
        $res = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'");
        return $res && $res->num_rows > 0;
    }
    // PDO
    try {
        $stmt = $conn->query("SHOW TABLES LIKE " . $conn->quote($table));
        return $stmt && $stmt->fetch() !== false;
    } catch (Throwable $e) {
        return false;
    }
}

// ---- Auto-create TACC tables if missing ----------------------------
function ensureTaccTables($conn): void {
    $tables = [
        "CREATE TABLE IF NOT EXISTS tacc_members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_names VARCHAR(120) NOT NULL,
            surname VARCHAR(120) NOT NULL,
            date_of_birth DATE NOT NULL,
            gender ENUM('male','female') NOT NULL,
            id_number VARCHAR(40) NOT NULL,
            marital_status ENUM('single','married','widowed','divorced') NOT NULL,
            address VARCHAR(255) NOT NULL,
            postal_code VARCHAR(10) NOT NULL,
            province VARCHAR(8) NOT NULL,
            municipality VARCHAR(120) NOT NULL,
            mobile VARCHAR(20) NOT NULL,
            alt_number VARCHAR(20) NULL,
            email VARCHAR(120) NOT NULL,
            congregation VARCHAR(120) NOT NULL,
            region VARCHAR(120) NOT NULL,
            water_baptised TINYINT(1) NOT NULL DEFAULT 0,
            water_baptism_year VARCHAR(10) NULL,
            sealed TINYINT(1) NOT NULL DEFAULT 0,
            sealing_year VARCHAR(10) NULL,
            previous_church VARCHAR(160) NULL,
            initials VARCHAR(10) NOT NULL,
            consent TINYINT(1) NOT NULL DEFAULT 0,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            INDEX idx_email (email),
            INDEX idx_submitted (submitted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS tacc_dependents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT NOT NULL,
            full_name VARCHAR(120) NOT NULL,
            relationship VARCHAR(60) NOT NULL,
            date_of_birth VARCHAR(20) NULL,
            baptised TINYINT(1) NOT NULL DEFAULT 0,
            INDEX idx_member (member_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS tacc_member_ministries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT NOT NULL,
            ministry VARCHAR(40) NOT NULL,
            UNIQUE KEY uniq_member_ministry (member_id, ministry),
            INDEX idx_member (member_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS tacc_submission_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            submission_hash CHAR(64) NOT NULL UNIQUE,
            member_id INT NULL,
            received_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($tables as $sql) {
        dbQuery($conn, $sql);
    }
}

try {
    ensureTaccTables($conn);
} catch (Throwable $e) {
    die("FATAL: Could not create TACC tables: " . $e->getMessage());
}

// ---- AJAX: detail --------------------------------------------------
if (($_GET['action'] ?? '') === 'detail' && !empty($_GET['id'])) {
    header('Content-Type: application/json');
    $id = (int)$_GET['id'];

    $member = dbFetchOne($conn, "SELECT * FROM tacc_members WHERE id = $id");
    if (!$member) {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
    }

    $dependents = dbFetchAll($conn, "SELECT full_name, relationship, date_of_birth, baptised
                                     FROM tacc_dependents WHERE member_id = $id");

    $mins = dbFetchAll($conn, "SELECT ministry FROM tacc_member_ministries WHERE member_id = $id");
    $ministries = array_column($mins, 'ministry');

    echo json_encode([
        'member'     => $member,
        'dependents' => $dependents,
        'ministries' => $ministries,
    ]);
    exit;
}

// ---- AJAX: export CSV ----------------------------------------------
if (($_GET['action'] ?? '') === 'export') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="tacc_members_' . date('Ymd_His') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Full Names','Surname','DOB','Gender','ID Number','Marital Status',
                   'Address','Postal Code','Province','Municipality','Mobile','Alt Number','Email',
                   'Congregation','Region','Water Baptised','Baptism Year','Sealed','Sealing Year',
                   'Previous Church','Initials','Consent','Submitted']);

    $rows = dbFetchAll($conn, "SELECT * FROM tacc_members ORDER BY submitted_at DESC");
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'], $r['full_names'], $r['surname'], $r['date_of_birth'], $r['gender'],
            $r['id_number'], $r['marital_status'], $r['address'], $r['postal_code'],
            $r['province'], $r['municipality'], $r['mobile'], $r['alt_number'], $r['email'],
            $r['congregation'], $r['region'],
            $r['water_baptised'] ? 'Yes' : 'No', $r['water_baptism_year'],
            $r['sealed'] ? 'Yes' : 'No', $r['sealing_year'],
            $r['previous_church'], $r['initials'], $r['consent'] ? 'Yes' : 'No',
            $r['submitted_at'],
        ]);
    }
    fclose($out);
    exit;
}

// ---- Stats ----------------------------------------------------------
$total         = (int)dbScalar($conn, "SELECT COUNT(*) c FROM tacc_members");
$thisMonth     = (int)dbScalar($conn, "SELECT COUNT(*) c FROM tacc_members
                                       WHERE MONTH(submitted_at)=MONTH(CURDATE())
                                         AND YEAR(submitted_at)=YEAR(CURDATE())");
$waterBaptised = (int)dbScalar($conn, "SELECT COUNT(*) c FROM tacc_members WHERE water_baptised=1");
$sealed        = (int)dbScalar($conn, "SELECT COUNT(*) c FROM tacc_members WHERE sealed=1");

// ---- Members list ---------------------------------------------------
$members = dbFetchAll($conn, "
    SELECT id, full_names, surname, email, mobile, congregation, region,
           water_baptised, sealed, submitted_at
    FROM tacc_members
    ORDER BY submitted_at DESC
");
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TACC Admin — Membership Registrations</title>
<style>
:root{
    --blue:#0d47a1; --blue-light:#1565c0; --blue-soft:#e3f2fd;
    --bg:#f4f6fa; --panel:#ffffff; --text:#1a1f2b; --muted:#6b7280;
    --border:#e5e7eb; --green:#16a34a; --amber:#f59e0b;
    --shadow:0 1px 2px rgba(16,24,40,.06),0 1px 3px rgba(16,24,40,.1);
    --shadow-lg:0 10px 25px rgba(16,24,40,.12),0 4px 10px rgba(16,24,40,.06);
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
    font-size:14px;line-height:1.5}
.wrap{max-width:1280px;margin:0 auto;padding:32px 24px 80px}

header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px}
.brand{display:flex;align-items:center;gap:14px}
.brand-mark{width:44px;height:44px;border-radius:10px;
    background:linear-gradient(135deg,var(--blue),var(--blue-light));
    color:#fff;display:flex;align-items:center;justify-content:center;
    font-weight:800;font-size:20px;letter-spacing:.5px}
.brand h1{margin:0;font-size:20px;color:var(--blue)}
.brand p{margin:0;color:var(--muted);font-size:12px}

.actions{display:flex;gap:10px}
.btn{display:inline-flex;align-items:center;gap:8px;
    padding:10px 16px;border-radius:8px;border:1px solid var(--border);
    background:#fff;color:var(--text);font-size:13px;font-weight:600;
    cursor:pointer;text-decoration:none;transition:.15s}
.btn:hover{background:#f9fafb}
.btn svg{width:16px;height:16px}

.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px}
.stat{background:var(--panel);border:1px solid var(--border);border-radius:12px;
    padding:20px;box-shadow:var(--shadow);position:relative;overflow:hidden}
.stat::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--blue)}
.stat.green::before{background:var(--green)}
.stat.amber::before{background:var(--amber)}
.stat-label{color:var(--muted);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px}
.stat-value{font-size:28px;font-weight:700;color:var(--text);line-height:1}
.stat-sub{color:var(--muted);font-size:12px;margin-top:6px}

.panel{background:var(--panel);border:1px solid var(--border);border-radius:12px;
    box-shadow:var(--shadow);overflow:hidden}
.panel-head{display:flex;align-items:center;justify-content:space-between;
    padding:16px 20px;border-bottom:1px solid var(--border);gap:12px;flex-wrap:wrap}
.panel-head h2{margin:0;font-size:16px}
.search{position:relative}
.search input{padding:9px 12px 9px 36px;border:1px solid var(--border);border-radius:8px;
    font-size:13px;width:280px;max-width:100%;outline:none;transition:.15s}
.search input:focus{border-color:var(--blue);box-shadow:0 0 0 3px var(--blue-soft)}
.search svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--muted)}

table{width:100%;border-collapse:collapse}
thead th{text-align:left;padding:12px 16px;font-size:11px;text-transform:uppercase;
    letter-spacing:.5px;color:var(--muted);background:#f9fafb;border-bottom:1px solid var(--border);white-space:nowrap}
tbody td{padding:14px 16px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle}
tbody tr{cursor:pointer;transition:background .12s}
tbody tr:hover{background:#f9fafb}
tbody tr:last-child td{border-bottom:none}

.name-cell{display:flex;align-items:center;gap:12px}
.avatar{width:36px;height:36px;border-radius:50%;
    background:var(--blue-soft);color:var(--blue);
    display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:13px;flex-shrink:0}
.name-main{font-weight:600;color:var(--text)}
.name-sub{font-size:12px;color:var(--muted)}

.pill{display:inline-block;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;line-height:1.5}
.pill-green{background:#dcfce7;color:#166534}
.pill-grey{background:#f3f4f6;color:#6b7280}

.empty{padding:60px 20px;text-align:center;color:var(--muted)}
.empty svg{width:48px;height:48px;color:#cbd5e1;margin-bottom:12px}

.modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.5);
    display:none;align-items:flex-start;justify-content:center;
    padding:40px 20px;overflow-y:auto;z-index:100}
.modal-backdrop.open{display:flex}
.modal{background:#fff;border-radius:14px;max-width:820px;width:100%;
    box-shadow:var(--shadow-lg);overflow:hidden;animation:pop .18s ease-out}
@keyframes pop{from{transform:scale(.97);opacity:.6}to{transform:scale(1);opacity:1}}
.modal-head{display:flex;align-items:center;justify-content:space-between;gap:16px;
    padding:20px 24px;border-bottom:1px solid var(--border);
    background:linear-gradient(135deg,var(--blue),var(--blue-light));color:#fff}
.modal-head h3{margin:0;font-size:17px}
.modal-head small{display:block;color:#dbeafe;font-size:12px;margin-top:2px}
.modal-close{background:rgba(255,255,255,.15);border:none;color:#fff;
    width:34px;height:34px;border-radius:8px;cursor:pointer;
    display:flex;align-items:center;justify-content:center;font-size:18px;transition:.15s}
.modal-close:hover{background:rgba(255,255,255,.28)}
.modal-body{padding:24px;max-height:calc(100vh - 200px);overflow-y:auto}
.section{margin-bottom:24px}
.section:last-child{margin-bottom:0}
.section h4{margin:0 0 12px;font-size:12px;text-transform:uppercase;letter-spacing:.5px;
    color:var(--blue);padding-bottom:6px;border-bottom:2px solid var(--blue-soft)}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px 24px}
.field label{display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.3px;margin-bottom:2px}
.field div{font-size:13px;color:var(--text);font-weight:500;word-break:break-word}
.mini-table{width:100%;border-collapse:collapse;font-size:13px}
.mini-table th,.mini-table td{padding:8px 10px;text-align:left;border-bottom:1px solid var(--border)}
.mini-table th{font-size:11px;text-transform:uppercase;color:var(--muted);letter-spacing:.3px}
.loading{padding:40px;text-align:center;color:var(--muted)}
</style>
</head>
<body>
<div class="wrap">

    <header>
        <div class="brand">
            <div class="brand-mark">T</div>
            <div>
                <h1>TACC Membership Registrations</h1>
                <p>Twelve Apostles' Church in Christ, Admin Dashboard</p>
            </div>
        </div>
        <div class="actions">
            <a class="btn" href="?action=export">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
        </div>
    </header>

    <div class="stats">
        <div class="stat">
            <div class="stat-label">Total Members</div>
            <div class="stat-value"><?= number_format($total) ?></div>
            <div class="stat-sub">All-time registrations</div>
        </div>
        <div class="stat">
            <div class="stat-label">This Month</div>
            <div class="stat-value"><?= number_format($thisMonth) ?></div>
            <div class="stat-sub"><?= date('F Y') ?></div>
        </div>
        <div class="stat green">
            <div class="stat-label">Water Baptised</div>
            <div class="stat-value"><?= number_format($waterBaptised) ?></div>
            <div class="stat-sub"><?= $total ? round($waterBaptised/$total*100) : 0 ?>% of members</div>
        </div>
        <div class="stat amber">
            <div class="stat-label">Sealed (Holy Spirit)</div>
            <div class="stat-value"><?= number_format($sealed) ?></div>
            <div class="stat-sub"><?= $total ? round($sealed/$total*100) : 0 ?>% of members</div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Registered Members</h2>
            <div class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input id="search" type="text" placeholder="Search name, email, mobile…">
            </div>
        </div>

        <?php if (empty($members)): ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                <h3>No registrations yet</h3>
                <p>Members will appear here once they complete the WhatsApp Flow.</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table id="membersTable">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Contact</th>
                    <th>Congregation</th>
                    <th>Status</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($members as $m):
                $initials = strtoupper(substr($m['full_names'],0,1) . substr($m['surname'],0,1));
                $bits = [];
                if (!empty($m['water_baptised'])) $bits[] = '<span class="pill pill-green">Baptised</span>';
                if (!empty($m['sealed']))         $bits[] = '<span class="pill pill-green">Sealed</span>';
                if (!$bits)                       $bits[] = '<span class="pill pill-grey">New</span>';
            ?>
                <tr data-id="<?= (int)$m['id'] ?>"
                    data-search="<?= htmlspecialchars(strtolower($m['full_names'].' '.$m['surname'].' '.$m['email'].' '.$m['mobile'].' '.$m['congregation'])) ?>">
                    <td>
                        <div class="name-cell">
                            <div class="avatar"><?= htmlspecialchars($initials) ?></div>
                            <div>
                                <div class="name-main"><?= htmlspecialchars($m['full_names'].' '.$m['surname']) ?></div>
                                <div class="name-sub">#<?= (int)$m['id'] ?> · <?= htmlspecialchars($m['region']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($m['email']) ?></div>
                        <div class="name-sub"><?= htmlspecialchars($m['mobile']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($m['congregation']) ?></td>
                    <td><?= implode(' ', $bits) ?></td>
                    <td><?= htmlspecialchars(date('d M Y', strtotime($m['submitted_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal-backdrop" id="modal">
    <div class="modal" role="dialog" aria-modal="true">
        <div class="modal-head">
            <div>
                <h3 id="mTitle">Loading…</h3>
                <small id="mSubtitle"></small>
            </div>
            <button class="modal-close" id="mClose" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body" id="mBody">
            <div class="loading">Loading member details…</div>
        </div>
    </div>
</div>

<script>
(function(){
    const search = document.getElementById('search');
    if (search) {
        search.addEventListener('input', () => {
            const q = search.value.trim().toLowerCase();
            document.querySelectorAll('#membersTable tbody tr').forEach(tr => {
                const hay = tr.dataset.search || '';
                tr.style.display = (!q || hay.includes(q)) ? '' : 'none';
            });
        });
    }

    const modal     = document.getElementById('modal');
    const mTitle    = document.getElementById('mTitle');
    const mSubtitle = document.getElementById('mSubtitle');
    const mBody     = document.getElementById('mBody');

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[c]));
    }
    function field(label, val) {
        return '<div class="field"><label>' + esc(label) + '</label><div>' + esc(val || '—') + '</div></div>';
    }

    function openModal(id) {
        modal.classList.add('open');
        mTitle.textContent = 'Loading…';
        mSubtitle.textContent = '';
        mBody.innerHTML = '<div class="loading">Loading member details…</div>';

        fetch('?action=detail&id=' + encodeURIComponent(id))
            .then(r => r.json())
            .then(payload => {
                if (payload.error) throw new Error(payload.error);
                renderMember(payload);
            })
            .catch(err => {
                mBody.innerHTML = '<div class="loading">Failed to load: ' + esc(err.message) + '</div>';
            });
    }
    function closeModal() { modal.classList.remove('open'); }

    function renderMember(payload) {
        const m = payload.member;
        mTitle.textContent = m.full_names + ' ' + m.surname;
        mSubtitle.textContent = 'Member #' + m.id + ' · Submitted ' + m.submitted_at;

        let html = '';
        html += '<div class="section"><h4>Personal</h4><div class="grid">';
        html += field('Date of Birth', m.date_of_birth);
        html += field('Gender', m.gender);
        html += field('ID / Passport', m.id_number);
        html += field('Marital Status', m.marital_status);
        html += '</div></div>';

        html += '<div class="section"><h4>Contact</h4><div class="grid">';
        html += field('Residential Address', m.address);
        html += field('Postal Code', m.postal_code);
        html += field('Province', m.province);
        html += field('Municipality / Town', m.municipality);
        html += field('Mobile', m.mobile);
        html += field('Alt. Number', m.alt_number);
        html += field('Email', m.email);
        html += '</div></div>';

        html += '<div class="section"><h4>Church</h4><div class="grid">';
        html += field('Congregation', m.congregation);
        html += field('Region / District', m.region);
        html += field('Water Baptised', m.water_baptised == 1 ? 'Yes' : 'No');
        html += field('Baptism Year', m.water_baptism_year);
        html += field('Sealed', m.sealed == 1 ? 'Yes' : 'No');
        html += field('Sealing Year', m.sealing_year);
        html += field('Previous Church', m.previous_church);
        html += '</div></div>';

        if (payload.dependents && payload.dependents.length) {
            html += '<div class="section"><h4>Dependents (' + payload.dependents.length + ')</h4>';
            html += '<table class="mini-table"><thead><tr><th>Name</th><th>Relationship</th><th>DOB</th><th>Baptised</th></tr></thead><tbody>';
            payload.dependents.forEach(d => {
                html += '<tr><td>' + esc(d.full_name) + '</td><td>' + esc(d.relationship) + '</td><td>' + esc(d.date_of_birth) + '</td><td>' + (d.baptised == 1 ? 'Yes' : 'No') + '</td></tr>';
            });
            html += '</tbody></table></div>';
        }

        if (payload.ministries && payload.ministries.length) {
            html += '<div class="section"><h4>Ministries</h4><div class="grid">';
            payload.ministries.forEach(mn => {
                html += '<div class="field"><div>' + esc(mn) + '</div></div>';
            });
            html += '</div></div>';
        }

        html += '<div class="section"><h4>Declaration</h4><div class="grid">';
        html += field('Initials', m.initials);
        html += field('Consent', m.consent == 1 ? 'Accepted' : 'Not accepted');
        html += field('IP Address', m.ip_address);
        html += '</div></div>';

        mBody.innerHTML = html;
    }

    document.addEventListener('click', e => {
        const tr = e.target.closest('#membersTable tbody tr');
        if (tr) openModal(tr.dataset.id);
        if (e.target === modal || e.target.id === 'mClose') closeModal();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModal();
    });
})();
</script>
</body>
</html>