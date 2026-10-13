<?php
/**
 * Apex Sports Club — Administrator Records Dashboard
 * Requirement E:
 * - Retrieves submitted student records from MySQL database `members` table
 * - Allows searching, filtering by sport, updating status, and deleting records
 * - Displays aggregate analytics and club applicant counts
 */

session_start();

// 1. Session protection
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.php');
    exit;
}

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }
    session_destroy();
    header('Location: admin-login.php');
    exit;
}

$notice = '';
$noticeType = 'success';

// Handle Record Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delId = (int)($_POST['id'] ?? 0);
    if ($delId > 0) {
        $delStmt = $pdo->prepare("DELETE FROM members WHERE id = :id");
        $delStmt->execute([':id' => $delId]);
        $notice = "Applicant record #{$delId} was successfully deleted.";
    }
}

// Handle Status Update (e.g. Approved / Contacted)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $upId     = (int)($_POST['id'] ?? 0);
    $newStatus = trim((string)($_POST['new_status'] ?? 'Pending Review'));
    if ($upId > 0 && in_array($newStatus, ['Pending Review', 'Approved', 'Contacted', 'Archived'], true)) {
        $upStmt = $pdo->prepare("UPDATE members SET status = :status WHERE id = :id");
        $upStmt->execute([':status' => $newStatus, ':id' => $upId]);
        $notice = "Applicant #{$upId} status changed to '{$newStatus}'.";
    }
}

// Search and Filter logic
$search = trim((string)($_GET['search'] ?? ''));
$filterSport = trim((string)($_GET['sport'] ?? ''));

$sql = "SELECT * FROM members WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (full_name LIKE :s1 OR email LIKE :s2 OR student_id LIKE :s3 OR course LIKE :s4)";
    $like = '%' . $search . '%';
    $params[':s1'] = $like;
    $params[':s2'] = $like;
    $params[':s3'] = $like;
    $params[':s4'] = $like;
}

if ($filterSport !== '') {
    $sql .= " AND preferred_activity = :sport";
    $params[':sport'] = $filterSport;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();

// Analytics / aggregates
$totalCountQuery = $pdo->query("SELECT COUNT(*) AS total FROM members");
$totalApplicants = (int)$totalCountQuery->fetch()['total'];

$sportsQuery = $pdo->query("SELECT preferred_activity, COUNT(*) as cnt FROM members GROUP BY preferred_activity ORDER BY cnt DESC");
$sportsStats = $sportsQuery->fetchAll();

$pendingQuery = $pdo->query("SELECT COUNT(*) as pending FROM members WHERE status = 'Pending Review'");
$pendingCount = (int)$pendingQuery->fetch()['pending'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard | Apex Sports Club</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="icon" type="image/png" href="assets/Apex icon.png" />
  <style>
    body {
      margin: 0;
      background: var(--bg-dark, #09130d);
      color: var(--text-main, #edf7ee);
      font-family: var(--font-body, Inter, Arial, sans-serif);
      padding: 36px 20px;
    }
    .admin-container {
      max-width: 1320px;
      margin: 0 auto;
    }
    .admin-topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
      padding-bottom: 22px;
      border-bottom: 1px solid var(--border, rgba(123, 255, 146, 0.2));
      margin-bottom: 28px;
    }
    .brand-title h1 {
      font-family: var(--font-head, 'Space Grotesk', sans-serif);
      font-size: 1.85rem;
      margin: 0;
      color: var(--text-main, #edf7ee);
      letter-spacing: -0.01em;
    }
    .admin-session {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .user-tag {
      background: rgba(56, 229, 77, 0.12);
      border: 1px solid rgba(56, 229, 77, 0.3);
      color: var(--green-soft, #9ef2a6);
      font-size: 0.85rem;
      padding: 7px 15px;
      border-radius: 999px;
      font-weight: 600;
    }
    .btn-action {
      display: inline-block;
      padding: 7px 18px;
      border-radius: 999px;
      text-decoration: none;
      font-size: 0.86rem;
      font-weight: 600;
      transition: all var(--ease-normal, 0.2s ease);
      cursor: pointer;
    }
    .btn-public {
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.25);
      color: var(--text-main, #edf7ee);
    }
    .btn-public:hover {
      border-color: var(--green-acc, #61e07c);
      color: var(--green-acc, #61e07c);
      transform: translateY(-1px);
    }
    .btn-logout {
      background: rgba(255, 77, 77, 0.15);
      border: 1px solid rgba(255, 77, 77, 0.35);
      color: #ff9999;
    }
    .btn-logout:hover {
      background: rgba(255, 77, 77, 0.3);
      transform: translateY(-1px);
    }
    .alert-banner {
      background: rgba(56, 229, 77, 0.15);
      border: 1px solid rgba(56, 229, 77, 0.4);
      color: var(--green-soft, #9ef2a6);
      padding: 12px 18px;
      border-radius: var(--radius-sm, 8px);
      margin-bottom: 24px;
      font-size: 0.92rem;
    }

    /* Stats Grid — Nicely balanced 3-column layout */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 28px;
    }
    @media (max-width: 860px) {
      .kpi-grid {
        grid-template-columns: 1fr;
      }
    }
    .kpi-card {
      background: var(--bg-card, rgba(18, 32, 22, 0.95));
      border: 1px solid var(--border, rgba(123, 255, 146, 0.2));
      border-radius: var(--radius-md, 14px);
      padding: 24px 26px;
      box-shadow: var(--shadow-sm, 0 4px 18px rgba(0, 0, 0, 0.2));
      display: flex;
      flex-direction: column;
      justify-content: center;
      transition: border-color 0.2s ease, transform 0.2s ease;
    }
    .kpi-card:hover {
      border-color: var(--green-acc, #61e07c);
      transform: translateY(-2px);
    }
    .kpi-num {
      font-family: var(--font-head, 'Space Grotesk', sans-serif);
      font-size: 2.6rem;
      font-weight: 800;
      color: var(--green-acc, #61e07c);
      line-height: 1;
      margin-bottom: 8px;
      letter-spacing: -0.02em;
    }
    .kpi-label {
      font-size: 0.85rem;
      color: var(--text-muted, #a4baa9);
      text-transform: uppercase;
      letter-spacing: 0.08em;
      font-weight: 600;
    }

    /* Filters */
    .filter-bar {
      background: var(--bg-card, rgba(18, 32, 22, 0.8));
      border: 1px solid var(--border, rgba(123, 255, 146, 0.18));
      border-radius: var(--radius-md, 12px);
      padding: 16px 22px;
      margin-bottom: 26px;
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      align-items: center;
      justify-content: space-between;
    }
    .filter-form {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
      flex: 1;
    }
    .input-sm {
      padding: 10px 14px;
      border-radius: var(--radius-sm, 8px);
      border: 1px solid var(--border, rgba(123, 255, 146, 0.25));
      background: rgba(9, 19, 13, 0.95);
      color: var(--text-main, #edf7ee);
      font-size: 0.9rem;
      font-family: var(--font-body, inherit);
    }
    .input-sm:focus {
      outline: none;
      border-color: var(--green-acc, #61e07c);
      box-shadow: 0 0 0 2px rgba(56, 229, 77, 0.2);
    }
    .btn-filter {
      padding: 10px 20px;
      background: var(--green-acc, #61e07c);
      color: #07130a;
      border: none;
      border-radius: var(--radius-sm, 8px);
      font-weight: 700;
      cursor: pointer;
      font-size: 0.9rem;
      font-family: var(--font-body, inherit);
      transition: background 0.15s ease, transform 0.15s ease;
    }
    .btn-filter:hover {
      background: var(--green-soft, #7bff92);
      transform: translateY(-1px);
    }
    .btn-reset {
      color: var(--text-muted, #a4baa9);
      text-decoration: none;
      font-size: 0.86rem;
      margin-left: 8px;
      font-weight: 500;
    }
    .btn-reset:hover {
      color: var(--green-soft, #9ef2a6);
      text-decoration: underline;
    }

    /* Table & Container Matching Activities Schedule Table */
    .schedule-table-card {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.94rem;
      text-align: left;
    }
    .schedule-table-card caption {
      padding: 18px 24px;
      font-family: var(--font-head, 'Space Grotesk', sans-serif);
      font-weight: 700;
      font-size: 1.15rem;
      text-transform: uppercase;
      color: var(--green-acc, #61e07c);
      background: rgba(13, 23, 11, 0.85);
      border-bottom: 1px solid var(--border, rgba(123, 255, 146, 0.2));
      text-align: left;
      letter-spacing: 0.04em;
    }
    .schedule-table-card th,
    .schedule-table-card td {
      padding: 15px 20px;
      border-bottom: 1px solid var(--border, rgba(123, 255, 146, 0.2));
      vertical-align: middle;
    }
    .schedule-table-card th {
      background: rgba(56, 229, 77, 0.12);
      font-family: var(--font-head, 'Space Grotesk', sans-serif);
      font-weight: 700;
      color: var(--green-soft, #9ef2a6);
      letter-spacing: 0.05em;
      text-transform: uppercase;
      font-size: 0.82rem;
    }
    .schedule-table-card tr:hover td {
      background: rgba(56, 229, 77, 0.06);
    }

    /* Table Details and Badges */
    .badge-sport {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 0.76rem;
      font-weight: 600;
      background: rgba(56, 229, 77, 0.15);
      color: var(--green-acc, #61e07c);
      border: 1px solid rgba(56, 229, 77, 0.35);
    }
    .status-select {
      padding: 6px 10px;
      background: rgba(9, 19, 13, 0.95);
      color: var(--text-main, #edf7ee);
      border: 1px solid var(--border, rgba(123, 255, 146, 0.3));
      border-radius: 6px;
      font-size: 0.82rem;
      font-family: var(--font-body, inherit);
      cursor: pointer;
    }
    .status-select:focus {
      outline: none;
      border-color: var(--green-acc, #61e07c);
    }
    .btn-delete {
      background: transparent;
      border: 1px solid rgba(255, 77, 77, 0.45);
      color: #ff9999;
      padding: 5px 10px;
      border-radius: 6px;
      font-size: 0.78rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.15s ease, border-color 0.15s ease;
    }
    .btn-delete:hover {
      background: rgba(255, 77, 77, 0.22);
      border-color: rgba(255, 77, 77, 0.8);
    }
  </style>
</head>
<body>
  <div class="admin-container">

    <!-- Topbar -->
    <header class="admin-topbar">
      <div class="brand-title">
        <h1>Admin Dashboard</h1>
      </div>
      <div class="admin-session">
        <a href="index.html" class="btn-action btn-public">&larr; Public Website</a>
        <span class="user-tag">
          <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?> (<?php echo htmlspecialchars($_SESSION['admin_role'] ?? 'Staff'); ?>)
        </span>
        <a href="admin.php?action=logout" class="btn-action btn-logout">Sign Out</a>
      </div>
    </header>

    <?php if ($notice !== ''): ?>
      <div class="alert-banner" role="alert">
        &#10003; <?php echo htmlspecialchars($notice); ?>
      </div>
    <?php endif; ?>

    <!-- KPI Aggregates — Clean 3-Card Grid -->
    <section class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-num"><?php echo $totalApplicants; ?></div>
        <div class="kpi-label">Total Registered Students</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-num"><?php echo $pendingCount; ?></div>
        <div class="kpi-label">Applications Pending Review</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-num"><?php echo count($sportsStats); ?></div>
        <div class="kpi-label">Active Sports Disciplines</div>
      </div>
    </section>

    <!-- Search & Filter Controls -->
    <section class="filter-bar">
      <form method="GET" action="admin.php" class="filter-form">
        <input
          type="text"
          name="search"
          placeholder="Search name, email, student ID, course..."
          value="<?php echo htmlspecialchars($search); ?>"
          class="input-sm"
          style="min-width: 280px;"
        />
        <select name="sport" class="input-sm">
          <option value="">-- All Sports --</option>
          <option value="Football" <?php echo $filterSport === 'Football' ? 'selected' : ''; ?>>Football</option>
          <option value="Basketball" <?php echo $filterSport === 'Basketball' ? 'selected' : ''; ?>>Basketball</option>
          <option value="Fitness" <?php echo $filterSport === 'Fitness' ? 'selected' : ''; ?>>Gym &amp; Fitness</option>
          <option value="Running" <?php echo $filterSport === 'Running' ? 'selected' : ''; ?>>Running &amp; Athletics</option>
          <option value="Volleyball" <?php echo $filterSport === 'Volleyball' ? 'selected' : ''; ?>>Volleyball</option>
          <option value="Multiple Activities" <?php echo $filterSport === 'Multiple Activities' ? 'selected' : ''; ?>>Multiple Activities</option>
          <option value="Other" <?php echo $filterSport === 'Other' ? 'selected' : ''; ?>>Other</option>
        </select>
        <button type="submit" class="btn-filter">Filter Records</button>
        <?php if ($search !== '' || $filterSport !== ''): ?>
          <a href="admin.php" class="btn-reset">Reset Filter</a>
        <?php endif; ?>
      </form>
      <div>
        <span style="font-size:0.88rem;color:var(--text-muted, #a4baa9);">Showing: <strong style="color:var(--green-soft, #9ef2a6);"><?php echo count($members); ?></strong> applicant<?php echo count($members) === 1 ? '' : 's'; ?></span>
      </div>
    </section>

    <!-- Members Table — Styled identically to activities.html schedule-table -->
    <main class="table-wrap">
      <?php if (empty($members)): ?>
        <p style="padding: 40px; text-align: center; color: var(--text-muted, #a4baa9); margin: 0;">
          No student applications match the query. Form submissions from <code>join.html</code> will automatically appear here.
        </p>
      <?php else: ?>
        <table class="schedule-table schedule-table-card">
          <caption>Student Membership Applications &amp; Registrations</caption>
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Submitted</th>
              <th scope="col">Student Name</th>
              <th scope="col">Email</th>
              <th scope="col">Student ID</th>
              <th scope="col">Course / Dept</th>
              <th scope="col">Year</th>
              <th scope="col">Sport</th>
              <th scope="col">Level</th>
              <th scope="col">Status</th>
              <th scope="col">Message / Goals</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($members as $m): ?>
              <tr>
                <td><strong><?php echo (int)$m['id']; ?></strong></td>
                <td><small style="color:var(--text-muted, #a4baa9);"><?php echo htmlspecialchars(substr((string)$m['submitted_at'], 0, 16)); ?></small></td>
                <td><strong><?php echo htmlspecialchars($m['full_name']); ?></strong></td>
                <td><a href="mailto:<?php echo htmlspecialchars($m['email']); ?>" style="color:var(--green-soft, #9ef2a6);text-decoration:none;"><?php echo htmlspecialchars($m['email']); ?></a></td>
                <td><code style="background:rgba(56,229,77,0.12);padding:2px 6px;border-radius:4px;color:var(--green-soft,#9ef2a6);"><?php echo htmlspecialchars($m['student_id'] ?: '—'); ?></code></td>
                <td><?php echo htmlspecialchars($m['course']); ?></td>
                <td><?php echo htmlspecialchars($m['study_year']); ?></td>
                <td><span class="badge-sport"><?php echo htmlspecialchars($m['preferred_activity']); ?></span></td>
                <td><small><?php echo htmlspecialchars($m['experience_level']); ?></small></td>
                <td>
                  <form method="POST" action="admin.php" style="margin:0;display:inline;">
                    <input type="hidden" name="action" value="update_status" />
                    <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>" />
                    <select name="new_status" class="status-select" onchange="this.form.submit()">
                      <option value="Pending Review" <?php echo $m['status'] === 'Pending Review' ? 'selected' : ''; ?>>Pending</option>
                      <option value="Approved" <?php echo $m['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                      <option value="Contacted" <?php echo $m['status'] === 'Contacted' ? 'selected' : ''; ?>>Contacted</option>
                      <option value="Archived" <?php echo $m['status'] === 'Archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                  </form>
                </td>
                <td><small style="color:var(--text-muted, #a4baa9);"><?php echo htmlspecialchars($m['reason'] ?: 'None'); ?></small></td>
                <td>
                  <form method="POST" action="admin.php" onsubmit="return confirm('Permanently delete applicant #<?php echo (int)$m['id']; ?>?');" style="margin:0;">
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>" />
                    <button type="submit" class="btn-delete" title="Delete record">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </main>

  </div>
</body>
</html>
