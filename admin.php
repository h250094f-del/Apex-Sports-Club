<?php
/**
 * Apex Sports Club — Administrator Records Dashboard
 * ISE 2102: Web Technologies Semester Project
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
      background: #09130d;
      color: #edf7ee;
      font-family: Inter, Arial, sans-serif;
      padding: 30px 16px;
    }
    .admin-container {
      max-width: 1280px;
      margin: 0 auto;
    }
    .admin-topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(123, 255, 146, 0.2);
      margin-bottom: 26px;
    }
    .brand-title h1 {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 1.8rem;
      margin: 0 0 4px;
      color: #edf7ee;
    }
    .brand-title p {
      margin: 0;
      font-size: 0.88rem;
      color: #a4baa9;
    }
    .admin-session {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .user-tag {
      background: rgba(97, 224, 124, 0.12);
      border: 1px solid rgba(97, 224, 124, 0.3);
      color: #9ef2a6;
      font-size: 0.84rem;
      padding: 6px 14px;
      border-radius: 999px;
      font-weight: 600;
    }
    .btn-action {
      display: inline-block;
      padding: 7px 16px;
      border-radius: 999px;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 600;
      transition: all 0.15s ease;
      cursor: pointer;
    }
    .btn-public {
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.25);
      color: #edf7ee;
    }
    .btn-public:hover {
      border-color: #61e07c;
      color: #61e07c;
    }
    .btn-logout {
      background: rgba(255, 77, 77, 0.15);
      border: 1px solid rgba(255, 77, 77, 0.35);
      color: #ff9999;
    }
    .btn-logout:hover {
      background: rgba(255, 77, 77, 0.3);
    }
    /* Stats Grid */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .kpi-card {
      background: rgba(18, 32, 22, 0.95);
      border: 1px solid rgba(123, 255, 146, 0.2);
      border-radius: 14px;
      padding: 20px 22px;
    }
    .kpi-num {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 2.2rem;
      font-weight: 800;
      color: #61e07c;
      line-height: 1;
      margin-bottom: 6px;
    }
    .kpi-label {
      font-size: 0.82rem;
      color: #a4baa9;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      font-weight: 600;
    }
    /* Filters */
    .filter-bar {
      background: rgba(18, 32, 22, 0.8);
      border: 1px solid rgba(123, 255, 146, 0.18);
      border-radius: 12px;
      padding: 16px 20px;
      margin-bottom: 24px;
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
      justify-content: space-between;
    }
    .filter-form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      flex: 1;
    }
    .input-sm {
      padding: 8px 12px;
      border-radius: 6px;
      border: 1px solid rgba(123, 255, 146, 0.25);
      background: rgba(9, 19, 13, 0.9);
      color: #edf7ee;
      font-size: 0.88rem;
    }
    .btn-filter {
      padding: 8px 16px;
      background: #61e07c;
      color: #07130a;
      border: none;
      border-radius: 6px;
      font-weight: 700;
      cursor: pointer;
      font-size: 0.88rem;
    }
    .btn-reset {
      color: #a4baa9;
      text-decoration: none;
      font-size: 0.84rem;
      margin-left: 6px;
    }
    /* Table */
    .table-card {
      background: rgba(18, 32, 22, 0.95);
      border: 1px solid rgba(123, 255, 146, 0.2);
      border-radius: 14px;
      overflow-x: auto;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.45);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      min-width: 900px;
    }
    th, td {
      padding: 13px 15px;
      border-bottom: 1px solid rgba(123, 255, 146, 0.1);
      font-size: 0.88rem;
      vertical-align: middle;
    }
    th {
      background: rgba(25, 45, 30, 0.9);
      color: #9ef2a6;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.74rem;
      letter-spacing: 0.05em;
    }
    tr:hover td {
      background: rgba(97, 224, 124, 0.04);
    }
    .badge-sport {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 999px;
      font-size: 0.74rem;
      font-weight: 600;
      background: rgba(97, 224, 124, 0.15);
      color: #61e07c;
      border: 1px solid rgba(97, 224, 124, 0.3);
    }
    .badge-status {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 999px;
      font-size: 0.72rem;
      font-weight: 600;
      background: rgba(255, 193, 7, 0.15);
      color: #ffc107;
      border: 1px solid rgba(255, 193, 7, 0.3);
    }
    .badge-approved {
      background: rgba(97, 224, 124, 0.2);
      color: #61e07c;
      border-color: rgba(97, 224, 124, 0.4);
    }
    .alert-banner {
      background: rgba(97, 224, 124, 0.15);
      border: 1px solid rgba(97, 224, 124, 0.4);
      color: #9ef2a6;
      padding: 12px 18px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 0.9rem;
    }
    .status-select {
      padding: 4px 6px;
      background: #09130d;
      color: #edf7ee;
      border: 1px solid rgba(123, 255, 146, 0.3);
      border-radius: 4px;
      font-size: 0.78rem;
    }
    .btn-delete {
      background: transparent;
      border: 1px solid rgba(255, 77, 77, 0.4);
      color: #ff9999;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.74rem;
      cursor: pointer;
    }
    .btn-delete:hover {
      background: rgba(255, 77, 77, 0.25);
    }
  </style>
</head>
<body>
  <div class="admin-container">

    <!-- Topbar -->
    <header class="admin-topbar">
      <div class="brand-title">
        <h1>Apex Sports Club — Admin Portal</h1>
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

    <!-- KPI Aggregates -->
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
      <div class="kpi-card">
        <div class="kpi-num" style="font-size:1.6rem;padding-top:6px;">MySQL / PDO</div>
        <div class="kpi-label">Backend Database Active</div>
      </div>
    </section>

    <!-- Search & Filter Controls -->
    <section class="filter-bar">
      <form method="GET" action="admin.php" class="filter-form">
        <input
          type="text"
          name="search"
          placeholder="Search name, email, student ID..."
          value="<?php echo htmlspecialchars($search); ?>"
          class="input-sm"
          style="min-width: 260px;"
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
          <a href="admin.php" class="btn-reset">Reset</a>
        <?php endif; ?>
      </form>
      <div>
        <span style="font-size:0.85rem;color:#a4baa9;">Showing: <strong><?php echo count($members); ?></strong> records</span>
      </div>
    </section>

    <!-- Members Table (ISE 2102 Requirement E: Store & Retrieve) -->
    <main class="table-card">
      <?php if (empty($members)): ?>
        <p style="padding: 36px; text-align: center; color: #a4baa9;">
          No student applications match the query. Form submissions from <code>join.html</code> will automatically populate this database table.
        </p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Submitted</th>
              <th>Student Name</th>
              <th>Email</th>
              <th>Student ID</th>
              <th>Course / Dept</th>
              <th>Year</th>
              <th>Sport</th>
              <th>Level</th>
              <th>Status</th>
              <th>Message</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($members as $m): ?>
              <tr>
                <td><?php echo (int)$m['id']; ?></td>
                <td><small><?php echo htmlspecialchars(substr((string)$m['submitted_at'], 0, 16)); ?></small></td>
                <td><strong><?php echo htmlspecialchars($m['full_name']); ?></strong></td>
                <td><a href="mailto:<?php echo htmlspecialchars($m['email']); ?>" style="color:#9ef2a6;"><?php echo htmlspecialchars($m['email']); ?></a></td>
                <td><code><?php echo htmlspecialchars($m['student_id'] ?: '—'); ?></code></td>
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
                <td><small style="color:#a4baa9;"><?php echo htmlspecialchars($m['reason'] ?: 'None'); ?></small></td>
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
