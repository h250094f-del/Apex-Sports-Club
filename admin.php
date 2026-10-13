<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';
$db = get_db_connection();

// Handle member deletion if requested
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delId = (int)($_POST['id'] ?? 0);
    if ($delId > 0) {
        $delStmt = $db->prepare("DELETE FROM members WHERE id = :id");
        $delStmt->execute([':id' => $delId]);
        $message = "Record #{$delId} successfully deleted.";
    }
}

// Fetch all members
$stmt = $db->query("SELECT * FROM members ORDER BY id DESC");
$members = $stmt->fetchAll();

// Statistics
$totalMembers = count($members);
$sportsCount = [];
foreach ($members as $m) {
    $sport = $m['preferred_activity'] ?: 'Other';
    $sportsCount[$sport] = ($sportsCount[$sport] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard | Apex Sports Club</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      background: #09130d;
      color: #edf7ee;
      font-family: Inter, Arial, sans-serif;
      padding: 30px 20px;
    }
    .admin-wrap {
      max-width: 1200px;
      margin: 0 auto;
    }
    .admin-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 16px;
      border-bottom: 1px solid rgba(123, 255, 146, 0.2);
      padding-bottom: 20px;
    }
    .admin-title h1 {
      font-family: 'Space Grotesk', sans-serif;
      margin: 0 0 6px;
      font-size: 1.8rem;
    }
    .admin-title p {
      margin: 0;
      color: #a4baa9;
      font-size: 0.92rem;
    }
    .admin-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .user-pill {
      background: rgba(97, 224, 124, 0.15);
      border: 1px solid rgba(97, 224, 124, 0.3);
      padding: 6px 14px;
      border-radius: 999px;
      color: #9ef2a6;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .btn-logout {
      background: rgba(255, 77, 77, 0.15);
      border: 1px solid rgba(255, 77, 77, 0.3);
      color: #ff9999;
      padding: 7px 16px;
      border-radius: 999px;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 600;
      transition: background 0.15s ease;
    }
    .btn-logout:hover {
      background: rgba(255, 77, 77, 0.3);
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      margin-bottom: 28px;
    }
    .stat-card {
      background: rgba(18, 32, 22, 0.9);
      border: 1px solid rgba(123, 255, 146, 0.2);
      border-radius: 12px;
      padding: 20px;
    }
    .stat-number {
      font-size: 2rem;
      font-weight: 800;
      color: #61e07c;
      font-family: 'Space Grotesk', sans-serif;
    }
    .stat-label {
      color: #a4baa9;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-top: 4px;
    }
    .table-card {
      background: rgba(18, 32, 22, 0.9);
      border: 1px solid rgba(123, 255, 146, 0.2);
      border-radius: 12px;
      overflow-x: auto;
      box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }
    th, td {
      padding: 14px 16px;
      border-bottom: 1px solid rgba(123, 255, 146, 0.1);
      font-size: 0.92rem;
    }
    th {
      background: rgba(25, 45, 30, 0.9);
      color: #9ef2a6;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.78rem;
      letter-spacing: 0.05em;
    }
    tr:hover td {
      background: rgba(97, 224, 124, 0.04);
    }
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 600;
      background: rgba(97, 224, 124, 0.15);
      color: #61e07c;
      border: 1px solid rgba(97, 224, 124, 0.3);
    }
    .btn-delete {
      background: transparent;
      border: 1px solid rgba(255, 77, 77, 0.4);
      color: #ff9999;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 0.75rem;
      cursor: pointer;
      transition: background 0.15s ease;
    }
    .btn-delete:hover {
      background: rgba(255, 77, 77, 0.2);
    }
    .alert-success {
      background: rgba(97, 224, 124, 0.15);
      border: 1px solid rgba(97, 224, 124, 0.4);
      color: #9ef2a6;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="admin-wrap">
    <div class="admin-header">
      <div class="admin-title">
        <h1>Apex Sports Club — Admin Portal</h1>
        <p>Student Member Database &amp; Application Records (ISE 2102 Project)</p>
      </div>
      <div class="admin-actions">
        <a href="index.html" class="user-pill" style="text-decoration:none;">&larr; View Website</a>
        <span class="user-pill">Logged in as: <?php echo htmlspecialchars($_SESSION['admin_user']); ?> (<?php echo htmlspecialchars($_SESSION['admin_role']); ?>)</span>
        <a href="logout.php" class="btn-logout">Sign Out</a>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Overview Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number"><?php echo $totalMembers; ?></div>
        <div class="stat-label">Total Registered Applicants</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo count($sportsCount); ?></div>
        <div class="stat-label">Sports Disciplines Represented</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo htmlspecialchars(getenv('DATABASE_URL') ? 'PostgreSQL' : 'SQLite'); ?></div>
        <div class="stat-label">Connected Database Backend</div>
      </div>
    </div>

    <!-- Submissions Table -->
    <div class="table-card">
      <?php if (empty($members)): ?>
        <p style="padding: 30px; text-align: center; color: #a4baa9;">No member submissions recorded yet. Once students submit via <code>join.html</code>, their records will display here.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Date / Time</th>
              <th>Student Name</th>
              <th>Email</th>
              <th>Department / Course</th>
              <th>Year</th>
              <th>Preferred Sport</th>
              <th>Level</th>
              <th>Purpose</th>
              <th>Reason / Notes</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($members as $m): ?>
              <tr>
                <td><?php echo htmlspecialchars((string)$m['id']); ?></td>
                <td><?php echo htmlspecialchars(substr((string)$m['submitted_at'], 0, 16)); ?></td>
                <td><strong><?php echo htmlspecialchars((string)$m['full_name']); ?></strong></td>
                <td><a href="mailto:<?php echo htmlspecialchars((string)$m['email']); ?>" style="color:#9ef2a6;"><?php echo htmlspecialchars((string)$m['email']); ?></a></td>
                <td><?php echo htmlspecialchars((string)$m['course']); ?></td>
                <td><?php echo htmlspecialchars((string)$m['study_year']); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars((string)$m['preferred_activity']); ?></span></td>
                <td><?php echo htmlspecialchars((string)($m['experience_level'] ?? 'N/A')); ?></td>
                <td><?php echo htmlspecialchars((string)($m['form_purpose'] ?: 'General')); ?></td>
                <td><small style="color:#a4baa9;"><?php echo htmlspecialchars((string)($m['reason'] ?: 'None')); ?></small></td>
                <td>
                  <form method="POST" action="admin.php" onsubmit="return confirm('Are you sure you want to delete this applicant?');" style="margin:0;">
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$m['id']); ?>" />
                    <button type="submit" class="btn-delete">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
