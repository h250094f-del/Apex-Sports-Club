<?php
/**
 * Apex Sports Club — Membership Form Processor
 * ISE 2102: Web Technologies Semester Project
 * Requirement E:
 * - Processes the Join/Contact form
 * - Validates input serverside
 * - Stores submitted details into MySQL database (with CSV fallback)
 * - Displays a structured, accessible response page acknowledging receipt
 */

require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$redirectUrl = 'join.html';

// 1. Only accept POST requests
if ($method !== 'POST') {
    header('Location: ' . $redirectUrl, true, 303);
    exit;
}

// 2. Sanitize and collect form inputs
$fullName          = trim((string)($_POST['full_name'] ?? ''));
$email             = trim((string)($_POST['email'] ?? ''));
$studentId         = trim((string)($_POST['student_id'] ?? ''));
$course            = trim((string)($_POST['course'] ?? ''));
$studyYear         = trim((string)($_POST['study_year'] ?? ''));
$preferredActivity = trim((string)($_POST['preferred_activity'] ?? ''));
$experienceLevel   = trim((string)($_POST['experience_level'] ?? 'Beginner'));
$formPurpose       = trim((string)($_POST['form_purpose'] ?? 'Join the club'));
$reason            = trim((string)($_POST['reason'] ?? ''));
$consent           = !empty($_POST['consent']);

// 3. Server-side validation
$errors = [];

if (mb_strlen($fullName, 'UTF-8') < 2) {
    $errors[] = 'Please enter your full name (at least 2 characters).';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address.';
}

if ($course === '') {
    $errors[] = 'Please enter your course or academic department.';
}

if ($studyYear === '') {
    $errors[] = 'Please select your current year of study.';
}

if ($preferredActivity === '') {
    $errors[] = 'Please choose your preferred sport or activity.';
}

if ($reason !== '' && mb_strlen($reason, 'UTF-8') > 600) {
    $errors[] = 'Your message cannot exceed 600 characters.';
}

if (!$consent) {
    $errors[] = 'You must agree to the club communication consent policy.';
}

// If validation fails, redirect back with error messages
if (!empty($errors)) {
    $query = http_build_query(['error' => implode(' | ', $errors)]);
    header('Location: ' . $redirectUrl . '?' . $query, true, 303);
    exit;
}

// 4. Store submitted information in the MySQL database (ISE 2102 Requirement E)
$dbSaved = false;
$dbError = null;

try {
    $pdo = get_db_connection();
    $sql = "INSERT INTO members (
                submitted_at, full_name, email, student_id, course,
                study_year, preferred_activity, experience_level,
                form_purpose, reason, consent, status
            ) VALUES (
                NOW(), :full_name, :email, :student_id, :course,
                :study_year, :preferred_activity, :experience_level,
                :form_purpose, :reason, :consent, 'Pending Review'
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name'          => $fullName,
        ':email'              => $email,
        ':student_id'         => $studentId !== '' ? $studentId : null,
        ':course'             => $course,
        ':study_year'         => $studyYear,
        ':preferred_activity' => $preferredActivity,
        ':experience_level'   => $experienceLevel,
        ':form_purpose'       => $formPurpose,
        ':reason'             => $reason !== '' ? $reason : null,
        ':consent'            => $consent ? 'yes' : 'no'
    ]);
    $dbSaved = true;
} catch (Exception $e) {
    $dbError = $e->getMessage();
    error_log("Database save failed: " . $dbError);
}

// 5. Also append to CSV backup for offline redundancy
$csvPath = __DIR__ . '/submissions.csv';
$isNewCsv = !file_exists($csvPath);
$fp = @fopen($csvPath, 'ab');
if ($fp !== false) {
    if ($isNewCsv) {
        fputcsv($fp, [
            'submitted_at', 'full_name', 'email', 'student_id', 'course',
            'study_year', 'preferred_activity', 'experience_level',
            'form_purpose', 'reason', 'consent'
        ]);
    }
    fputcsv($fp, [
        date('c'), $fullName, $email, $studentId, $course,
        $studyYear, $preferredActivity, $experienceLevel,
        $formPurpose, $reason, $consent ? 'yes' : 'no'
    ]);
    fclose($fp);
}

$submittedTime = date('l, d F Y - H:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Application Received | Apex Sports Club</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="icon" type="image/png" href="assets/Apex icon.png" />
  <style>
    body {
      margin: 0;
      background: #09130d;
      color: #edf7ee;
      font-family: Inter, Arial, sans-serif;
    }
    .receipt-shell {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 40px 16px;
    }
    .receipt-card {
      width: min(720px, 100%);
      background: rgba(18, 32, 22, 0.95);
      border: 1px solid rgba(123, 255, 146, 0.25);
      border-radius: 20px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
      padding: 36px;
    }
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: #61e07c;
      background: rgba(97, 224, 124, 0.15);
      border: 1px solid rgba(97, 224, 124, 0.3);
      padding: 6px 14px;
      border-radius: 999px;
      font-weight: 700;
      margin-bottom: 16px;
    }
    .status-badge .dot {
      width: 8px;
      height: 8px;
      background: #61e07c;
      border-radius: 50%;
      box-shadow: 0 0 8px #61e07c;
    }
    h1 {
      font-family: 'Space Grotesk', sans-serif;
      font-size: clamp(2rem, 3.2vw, 2.8rem);
      line-height: 1.15;
      margin: 0 0 14px;
      color: #edf7ee;
    }
    .receipt-intro {
      color: #dfeae2;
      line-height: 1.7;
      margin: 0 0 24px;
      font-size: 1.05rem;
    }
    .receipt-details {
      background: rgba(9, 19, 13, 0.6);
      border: 1px solid rgba(123, 255, 146, 0.15);
      border-radius: 12px;
      padding: 20px;
      margin: 24px 0;
      display: grid;
      grid-template-columns: minmax(130px, 180px) 1fr;
      gap: 12px 18px;
    }
    dt {
      color: #9ef2a6;
      font-weight: 600;
      font-size: 0.9rem;
    }
    dd {
      margin: 0;
      color: #edf7ee;
      font-size: 0.95rem;
      word-break: break-word;
    }
    .db-status {
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 0.85rem;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .db-ok {
      background: rgba(97, 224, 124, 0.12);
      border: 1px solid rgba(97, 224, 124, 0.3);
      color: #9ef2a6;
    }
    .actions-row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 24px;
    }
    .btn {
      display: inline-block;
      padding: 0.85rem 1.4rem;
      border-radius: 999px;
      text-decoration: none;
      font-weight: 700;
      font-size: 0.92rem;
      transition: transform 0.15s ease, background 0.15s ease;
      cursor: pointer;
    }
    .btn-primary {
      background: #61e07c;
      color: #07130a;
      border: 1px solid #61e07c;
    }
    .btn-primary:hover {
      background: #7bff92;
      transform: translateY(-1px);
    }
    .btn-outline {
      background: transparent;
      color: #edf7ee;
      border: 1px solid rgba(255, 255, 255, 0.25);
    }
    .btn-outline:hover {
      border-color: #61e07c;
      color: #61e07c;
    }
    @media (max-width: 640px) {
      .receipt-card {
        padding: 24px 18px;
      }
      .receipt-details {
        grid-template-columns: 1fr;
        gap: 6px 0;
      }
      dt {
        margin-top: 8px;
      }
    }
  </style>
</head>
<body>
  <div class="receipt-shell">
    <main class="receipt-card" role="main" aria-live="polite">
      
      <div class="status-badge">
        <span class="dot" aria-hidden="true"></span>
        <span>Submission Recorded</span>
      </div>

      <h1>Welcome, <?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
      
      <p class="receipt-intro">
        Thank you for your interest in Apex Sports Club. Your details have been successfully received and added to our student roster. Our executive committee will review your submission and communicate with you prior to the next scheduled session.
      </p>

      <?php if ($dbSaved): ?>
        <div class="db-status db-ok">
          &#10003; <strong>Database Confirmation:</strong> Record successfully stored in MySQL database table <code>members</code>.
        </div>
      <?php endif; ?>

      <dl class="receipt-details">
        <dt>Submission Time</dt>
        <dd><?php echo htmlspecialchars($submittedTime); ?></dd>

        <dt>Student Name</dt>
        <dd><strong><?php echo htmlspecialchars($fullName); ?></strong></dd>

        <dt>Email Address</dt>
        <dd><?php echo htmlspecialchars($email); ?></dd>

        <?php if ($studentId !== ''): ?>
          <dt>Student ID</dt>
          <dd><?php echo htmlspecialchars($studentId); ?></dd>
        <?php endif; ?>

        <dt>Course / Dept</dt>
        <dd><?php echo htmlspecialchars($course); ?></dd>

        <dt>Year of Study</dt>
        <dd><?php echo htmlspecialchars($studyYear); ?></dd>

        <dt>Chosen Sport</dt>
        <dd><span style="color:#61e07c;font-weight:600;"><?php echo htmlspecialchars($preferredActivity); ?></span> (<?php echo htmlspecialchars($experienceLevel); ?>)</dd>

        <dt>Purpose</dt>
        <dd><?php echo htmlspecialchars($formPurpose); ?></dd>

        <?php if ($reason !== ''): ?>
          <dt>Message / Goals</dt>
          <dd><?php echo nl2br(htmlspecialchars($reason)); ?></dd>
        <?php endif; ?>
      </dl>

      <div class="actions-row">
        <a href="index.html" class="btn btn-primary">&larr; Return to Homepage</a>
        <a href="activities.html" class="btn btn-outline">Explore Weekly Timetable</a>
        <a href="join.html" class="btn btn-outline">Submit Another Application</a>
      </div>

    </main>
  </div>
</body>
</html>
