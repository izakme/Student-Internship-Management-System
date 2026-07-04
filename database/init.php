<?php
/**
 * Database Initialization Script
 * Run this script once to set up the database properly
 * Access via: http://localhost/S-I-M-S/database/init.php
 */

require_once "../backend/config/database.php";

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h2>Database Initialization</h2>";
    
    // Read schema file
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    
    if (!$schema) {
        die("Error: Could not read schema.sql file");
    }
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    $executed = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            $conn->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Ignore "already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errors[] = $e->getMessage();
            }
        }
    }
    
    echo "<p style='color: green;'><strong>✓ Database setup completed!</strong></p>";
    echo "<p>Executed: $executed statements</p>";
    
    if (!empty($errors)) {
        echo "<p style='color: orange;'><strong>⚠ Warnings:</strong></p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
    }
    
    // Verify tables exist
    echo "<h3>Table Verification:</h3>";
    $tables = ['users', 'students', 'companies', 'internships', 'applications', 'reports'];
    
    foreach ($tables as $table) {
        try {
            $result = $conn->query("SELECT COUNT(*) FROM $table");
            $count = $result->fetchColumn();
            echo "<p style='color: green;'>✓ Table <strong>$table</strong> exists ($count rows)</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Table <strong>$table</strong> does not exist</p>";
        }
    }
    
    echo "<h3>Reports Table Structure:</h3>";
    try {
        $result = $conn->query("DESCRIBE reports");
        echo "<table border='1' cellpadding='8'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h3 { color: #333; }
        table { border-collapse: collapse; margin-top: 10px; }
        td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #0d6efd; color: white; }
    </style>
</head>
<body>
</body>
</html>
