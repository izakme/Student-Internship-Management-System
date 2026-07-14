<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/company.php";
require_once "../../backend/helpers/csrf.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$companyObj = new Company();

// Delete company
if (isset($_POST['delete']) && isset($_POST['company_id'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid form submission.";
    } else {
    $company_id = filter_input(INPUT_POST, 'company_id', FILTER_VALIDATE_INT);
    if ($company_id === false || $company_id === null) {
        $_SESSION['error'] = "Invalid company ID.";
    } elseif ($companyObj->deleteCompany($company_id)) {
        $_SESSION['message'] = "Company deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete company.";
    }
    }
    header("Location: companies.php");
    exit();
}

$companies = $companyObj->getCompanies();

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2>Registered Companies</h2>
    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Company Name</th>
            <th>Location</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $companies->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['company_id']) ?></td>
                <td><?= htmlspecialchars($row['company_name']) ?></td>
                <td><?= htmlspecialchars($row['location'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Delete this company?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="company_id" value="<?= $row['company_id'] ?>">
                        <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../layouts/footer.php"; ?>
