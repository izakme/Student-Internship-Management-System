<?php
/**
 * Database Repair Script
 * Fixes missing columns in the reports table
 * Access via: http://localhost/S-I-M-S/database/repair.php
 */

require_once "../backend/config/database.php";

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h2 style='color: #0d6efd;'>Database Repair Tool</h2>";
    
    // Check current reports table structure
    echo "<h3>Current Reports Table Structure:</h3>";
    try {
        $result = $conn->query("DESCRIBE reports");
        $columns = [];
        echo "<table border='1' cellpadding='8' style='width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #0d6efd; color: white;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for missing columns
        $required = ['report_id', 'report_name', 'report_type', 'generated_by', 'generated_date', 'report_data'];
        $missing = array_diff($required, $columns);
        
        if (!empty($missing)) {
            echo "<h3 style='color: #dc3545;'>Missing Columns: " . implode(", ", $missing) . "</h3>";
            
            // Add missing columns
            foreach ($missing as $col) {
                try {
                    switch($col) {
                        case 'report_name':
                            $conn->exec("ALTER TABLE reports ADD COLUMN report_name VARCHAR(200) NOT NULL AFTER report_id");
                            echo "<p style='color: green;'>✓ Added column: <strong>report_name</strong></p>";
                            break;
                        case 'report_type':
                            $conn->exec("ALTER TABLE reports ADD COLUMN report_type VARCHAR(100) AFTER report_name");
                            echo "<p style='color: green;'>✓ Added column: <strong>report_type</strong></p>";
                            break;
                        case 'generated_by':
                            $conn->exec("ALTER TABLE reports ADD COLUMN generated_by INT AFTER report_type");
                            echo "<p style='color: green;'>✓ Added column: <strong>generated_by</strong></p>";
                            break;
                        case 'generated_date':
                            $conn->exec("ALTER TABLE reports ADD COLUMN generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER generated_by");
                            echo "<p style='color: green;'>✓ Added column: <strong>generated_date</strong></p>";
                            break;
                        case 'report_data':
                            $conn->exec("ALTER TABLE reports ADD COLUMN report_data LONGTEXT AFTER generated_date");
                            echo "<p style='color: green;'>✓ Added column: <strong>report_data</strong></p>";
                            break;
                    }
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠ Warning for $col: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
            
            // Add foreign key if missing
            try {
                $conn->exec("ALTER TABLE reports ADD FOREIGN KEY (generated_by) REFERENCES users(user_id) ON DELETE SET NULL");
                echo "<p style='color: green;'>✓ Added foreign key constraint</p>";
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "<p style='color: orange;'>⚠ Foreign key: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        } else {
            echo "<p style='color: green;'><strong>✓ All required columns exist!</strong></p>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>This might mean the reports table doesn't exist. Creating it now...</p>";
        
        try {
            $sql = "CREATE TABLE IF NOT EXISTS reports (
                report_id INT AUTO_INCREMENT PRIMARY KEY,
                report_name VARCHAR(200) NOT NULL,
                report_type VARCHAR(100),
                generated_by INT,
                generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                report_data LONGTEXT,
                FOREIGN KEY (generated_by) REFERENCES users(user_id) ON DELETE SET NULL,
                INDEX idx_generated_date (generated_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $conn->exec($sql);
            echo "<p style='color: green;'><strong>✓ Reports table created successfully!</strong></p>";
            
            // Show new structure
            echo "<h3>Reports Table Created With:</h3>";
            $result = $conn->query("DESCRIBE reports");
            echo "<table border='1' cellpadding='8' style='width: 100%; margin: 10px 0;'>";
            echo "<tr style='background: #0d6efd; color: white;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'><strong>Error creating table:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Verify all tables
    echo "<h3>Database Tables Status:</h3>";
    $tables = ['users', 'students', 'companies', 'internships', 'applications', 'reports'];
    foreach ($tables as $table) {
        try {
            $result = $conn->query("SELECT COUNT(*) FROM $table");
            $count = $result->fetchColumn();
            echo "<p style='color: green;'>✓ <strong>$table</strong> - $count rows</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ <strong>$table</strong> - Does not exist</p>";
        }
    }
    
    echo "<hr>";
    echo "<p><a href='../admin/reports.php' style='padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>← Back to Reports</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            margin: 20px; 
            background: #f4f8fb;
            color: #2c3e50;
        }
        h2, h3 { color: #0d6efd; }
        table { 
            border-collapse: collapse; 
            margin-top: 10px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        td, th { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background: #0d6efd; 
            color: white; 
            font-weight: 600;
        }
        tr:hover { background: #f9f9f9; }
        p { line-height: 1.6; }
        hr { margin: 30px 0; border: 1px solid #ddd; }
    </style>
</head>
<body>
</body>
</html>
