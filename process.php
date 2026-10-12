<?php
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$baseUrl = 'join.html';

if ($method !== 'POST') {
    header('Location: ' . $baseUrl, true, 303);
    exit;
}

$fullName = trim((string)($_POST['full_name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$course = trim((string)($_POST['course'] ?? ''));
$studyYear = trim((string)($_POST['study_year'] ?? ''));
$preferredActivity = trim((string)($_POST['preferred_activity'] ?? ''));
$experienceLevel = trim((string)($_POST['experience_level'] ?? ''));
$formPurpose = trim((string)($_POST['form_purpose'] ?? ''));
$reason = trim((string)($_POST['reason'] ?? ''));
$consent = !empty($_POST['consent']);

$errors = [];

if (mb_strlen($fullName, 'UTF-8') < 2) {
    $errors[] = 'Please enter your full name.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if ($course === '') {
    $errors[] = 'Please provide your course or department.';
}

if ($studyYear === '') {
    $errors[] = 'Please select your year of study.';
}

if ($preferredActivity === '') {
    $errors[] = 'Please select your preferred sport.';
}

if ($reason !== '' && mb_strlen($reason, 'UTF-8') > 600) {
    $errors[] = 'Your message must be 600 characters or fewer.';
}

if (!$consent) {
    $errors[] = 'You must accept the consent agreement before submitting.';
}

if ($errors) {
    $qs = http_build_query(['error' => implode(' | ', $errors)]);
    header('Location: ' . $baseUrl . '?' . $qs, true, 303);
    exit;
}

$csvPath = __DIR__ . '/submissions.csv';
if (!file_exists($csvPath)) {
    $header = [
        'submitted_at',
        'full_name',
        'email',
        'course',
        'study_year',
        'preferred_activity',
        'experience_level',
        'form_purpose',
        'reason',
        'consent'
    ];
    $fp = fopen($csvPath, 'wb');
    fputcsv($fp, $header);
    fclose($fp);
}

$row = [
    date('c'),
    $fullName,
    $email,
    $course,
    $studyYear,
    $preferredActivity,
    $experienceLevel,
    $formPurpose,
    $reason,
    $consent ? 'yes' : 'no'
];

$fp = fopen($csvPath, 'ab');
if ($fp !== false) {
    fputcsv($fp, $row);
    fclose($fp);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Submission Received | Apex Sports Club</title>
  <link rel="stylesheet" href="style.css" />
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
      padding: 32px 16px;
    }
    .receipt-card {
      width: min(720px, 100%);
      background: rgba(18, 32, 22, 0.88);
      border: 1px solid rgba(123, 255, 146, 0.2);
      border-radius: 20px;
      box-shadow: 0 18px 45px rgba(0, 0, 0, 0.3);
      padding: 32px;
    }
    .eyebrow {
      display: inline-block;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: #9ef2a6;
      margin-bottom: 12px;
      font-weight: 700;
    }
    h1 {
      font-size: clamp(2rem, 3vw, 3rem);
      line-height: 1.1;
      margin: 0 0 14px;
    }
    .receipt-card p {
      color: #dfeae2;
      line-height: 1.7;
      margin: 0 0 18px;
    }
    dl {
      margin: 24px 0;
      display: grid;
      grid-template-columns: minmax(120px, 200px) 1fr;
      gap: 10px 18px;
    }
    dt {
      color: #9ef2a6;
      font-weight: 700;
    }
    dd {
      margin: 0;
      color: #edf7ee;
    }
    .btn {
      display: inline-block;
      padding: 0.9rem 1.4rem;
      border-radius: 999px;
      text-decoration: none;
      font-weight: 700;
      transition: transform 0.2s ease;
      border: 1px solid #61e07c;
      background: #61e07c;
      color: #07130a;
    }
    .btn:hover {
      transform: translateY(-1px);
    }
    @media (max-width: 640px) {
      .receipt-card {
        padding: 24px 18px;
      }
      dl {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="receipt-shell">
    <main class="receipt-card" aria-live="polite">
      <span class="eyebrow">Application received</span>
      <h1>Thank you, <?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>.</h1>
      <p>Your membership request has been recorded successfully. The Apex Sports Club committee will review it and contact you through the details you provided.</p>

      <dl>
        <dt>Email</dt>
        <dd><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></dd>

        <dt>Course</dt>
        <dd><?php echo htmlspecialchars($course, ENT_QUOTES, 'UTF-8'); ?></dd>

        <dt>Year</dt>
        <dd><?php echo htmlspecialchars($studyYear, ENT_QUOTES, 'UTF-8'); ?></dd>

        <dt>Activity</dt>
        <dd><?php echo htmlspecialchars($preferredActivity, ENT_QUOTES, 'UTF-8'); ?></dd>

        <dt>Purpose</dt>
        <dd><?php echo htmlspecialchars($formPurpose ?: 'General membership', ENT_QUOTES, 'UTF-8'); ?></dd>
      </dl>

      <a href="join.html" class="btn">Submit another application</a>
    </main>
  </div>
</body>
</html>
