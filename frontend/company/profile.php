<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/company.php";
require_once "../../backend/helpers/csrf.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'company') {
    header("Location: ../authentication/login.php");
    exit();
}

$company_user_id = $_SESSION['user_id'];
$db = (new Database())->connect();
$companyObj = new Company();

// Get company info
$company = $companyObj->getCompanyByUserId($company_user_id);

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid form submission.";
    } else {
    $company_name = $_POST['company_name'] ?? '';
    $location = $_POST['location'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    if ($company_name && $location) {
        if ($companyObj->updateCompany($company['company_id'], $company_name, $location, $phone)) {
            $_SESSION['message'] = "Profile updated successfully.";
            $company = $companyObj->getCompany($company['company_id']);
        } else {
            $_SESSION['error'] = "Error updating profile.";
        }
    }
    }
}

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<style>
    .form-group {
        margin: 15px 0;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>

<div class="card">
    <h2>Company Profile</h2>
    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
            
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name" value="<?= htmlspecialchars($company['company_name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($company['location'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($company['phone'] ?? '') ?>">
                </div>
                
                <button type="submit" class="btn">Update Profile</button>
            </form>
    </div>

<?php include "../layouts/footer.php"; ?>
