<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/company.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$companyObj = new Company();

// Delete company
if (isset($_GET['delete'])) {
    $company_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    if ($company_id === false || $company_id === null) {
        $_SESSION['error'] = "Invalid company ID.";
    } elseif ($companyObj->deleteCompany($company_id)) {
        $_SESSION['message'] = "Company deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete company.";
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
                    <a href="companies.php?delete=<?php echo $row['company_id']; ?>" 
                       onclick="return confirm('Delete this company?');" 
                       class="btn btn-danger" style="padding:5px 10px;font-size:12px;">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../layouts/footer.php"; ?>
