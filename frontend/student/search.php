<?php
session_start();

require_once "../../backend/classes/Internship.php";
require_once "../../backend/classes/Application.php";
require_once "../../backend/config/database.php";
require_once "../../backend/helpers/csrf.php";

/* =========================
   DATABASE CONNECTION
========================= */
$database = new Database();
$conn = $database->connect();

/* =========================
   AUTH CHECK
========================= */
if (empty($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

/* =========================
   GET STUDENT ID
========================= */
$stmt = $conn->prepare("
    SELECT student_id
    FROM students
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION['user_id']]);
$student_id = $stmt->fetchColumn();

if (!$student_id) {
    die("
        <div style='text-align:center;margin-top:50px;color:red;'>
            Student profile not found. Please complete your profile.
        </div>
    ");
}

/* =========================
   OBJECTS
========================= */
$internship = new Internship();
$application = new Application();

$message = "";

/* =========================
   APPLY ACTION
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {

    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "<div class='badge badge-danger'>Invalid form submission.</div>";
    } else {

    $internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
    $cover_letter = trim($_POST['cover_letter'] ?? '');

    if ($internship_id) {

        $applied = $application->apply($student_id, $internship_id, $cover_letter);

        if ($applied) {
            $message = "<div class='badge badge-success'>Application submitted successfully.</div>";
        } else {
            $message = "<div class='badge badge-danger'>Already applied or invalid request.</div>";
        }

    } else {
        $message = "<div class='badge badge-danger'>Invalid internship selected.</div>";
    }
    }
}

/* =========================
   SEARCH LOGIC
========================= */
$keyword = trim($_GET['search'] ?? "");

if (!empty($keyword)) {
    $result = $internship->searchActiveInternships($keyword);
} else {
    $result = $internship->activeInternships();
}
?>

<?php include "../layouts/header.php"; ?>
<?php include "../layouts/sidebar.php"; ?>

<div class="card">

<h2>Search Internship</h2>

<?= $message ?>

<form method="GET" class="mt-20">

<input
type="text"
name="search"
placeholder="Search by title, company or description..."
value="<?= htmlspecialchars($keyword) ?>">

<button type="submit" class="btn">Search</button>

</form>

</div>

<div class="card">

<div class="table-wrap">
<table class="internships-table">
<colgroup>
    <col class="col-company">
    <col class="col-title">
    <col class="col-desc">
    <col class="col-req">
    <col class="col-deadline">
    <col class="col-action">
</colgroup>
<thead>
<tr>
<th>Company</th>
<th>Title</th>
<th>Description</th>
<th>Requirements</th>
<th>Deadline</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if ($result && $result->rowCount() > 0): ?>

<?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>

<tr>

<td data-label="Company"><?= htmlspecialchars($row['company_name']) ?></td>
<td data-label="Title"><?= htmlspecialchars($row['title']) ?></td>
<td data-label="Description"><?= htmlspecialchars($row['description']) ?></td>
<td data-label="Requirements"><?= htmlspecialchars($row['requirements']) ?></td>
<td data-label="Deadline"><?= htmlspecialchars(date("M j, Y", strtotime($row['deadline']))) ?></td>

<td data-label="Action">
<form method="POST" onsubmit="return confirm('Submit application?');">
    <?= csrfField() ?>
    <input type="hidden" name="internship_id" value="<?= (int)$row['internship_id'] ?>">
    <input type="hidden" name="apply" value="1">
    <textarea name="cover_letter" placeholder="Cover letter (optional)..." rows="2" style="width:100%;font-size:11px;padding:4px;margin-bottom:4px;"></textarea>
    <button type="submit" class="btn btn-sm" style="font-size:11px;">Apply</button>
</form>
</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="6" class="center">No internships found.</td>
</tr>

<?php endif; ?>

</tbody>

</table>
</div>

</div>

<?php include "../layouts/footer.php"; ?>
