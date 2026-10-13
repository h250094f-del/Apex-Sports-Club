<?php
require_once __DIR__ . '/db.php';

$db = get_db_connection();
$stmt = $db->query("SELECT * FROM members ORDER BY id DESC");
$members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Members & Applications | Apex Sports Club</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      background: #09130d;
      color: #edf7ee;
      font-family: Inter, Arial, sans-serif;
      padding: 30px 20px;
    }
    .admin-container {
      max-width: 1100px;
      margin: 0 auto;
    }
    .header-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 12px;
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
      font-size: 0.95rem;
    }
    th {
      background: rgba(25, 45, 30, 0.8);
      color: #9ef2a6;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.05em;
    }
    tr:hover td {
      background: rgba(97, 224, 124, 0.05);
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
    .btn-link {
      color: #61e07c;
      text-decoration: none;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <div class="admin-container">
    <div class="header-bar">
      <div>
        <a href="index.html" class="btn-link">&larr; Back to Apex Site</a>
        <h1 style="margin: 8px 0 0;">Registered Members &amp; Applications</h1>
      </div>
      <div>
        <span class="badge">Total Submissions: <?php echo count($members); ?></span>
      </div>
    </div>

    <div class="table-card">
      <?php if (empty($members)): ?>
        <p style="padding: 24px; text-align: center; color: #a4baa9;">No member submissions found yet.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Submitted At</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Course</th>
              <th>Year</th>
              <th>Activity</th>
              <th>Experience</th>
              <th>Purpose</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($members as $m): ?>
              <tr>
                <td><?php echo htmlspecialchars((string)$m['id']); ?></td>
                <td><?php echo htmlspecialchars(substr((string)$m['submitted_at'], 0, 19)); ?></td>
                <td><strong><?php echo htmlspecialchars((string)$m['full_name']); ?></strong></td>
                <td><?php echo htmlspecialchars((string)$m['email']); ?></td>
                <td><?php echo htmlspecialchars((string)$m['course']); ?></td>
                <td><?php echo htmlspecialchars((string)$m['study_year']); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars((string)$m['preferred_activity']); ?></span></td>
                <td><?php echo htmlspecialchars((string)($m['experience_level'] ?? 'N/A')); ?></td>
                <td><?php echo htmlspecialchars((string)($m['form_purpose'] ?? 'General')); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
