<?php
require_once 'includes/config.php';

echo "Starting database setup...\n\n";

// Read the SQL file
$sql_file = 'database_schema.sql';
$sql_content = file_get_contents($sql_file);

// Split by semicolons but be careful with delimiters
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

$success_count = 0;
$error_count = 0;
$errors = [];

foreach ($statements as $statement) {
    // Skip empty statements and comments
    if (empty($statement) || substr(trim($statement), 0, 2) === '--') {
        continue;
    }
    
    // Execute the statement
    if ($conn->query($statement) === TRUE) {
        $success_count++;
        // Show progress for important statements
        if (stripos($statement, 'CREATE TABLE') !== false) {
            preg_match('/CREATE TABLE `?(\w+)`?/i', $statement, $matches);
            if (isset($matches[1])) {
                echo "✓ Created table: {$matches[1]}\n";
            }
        } elseif (stripos($statement, 'INSERT INTO') !== false) {
            preg_match('/INSERT INTO `?(\w+)`?/i', $statement, $matches);
            if (isset($matches[1])) {
                echo "✓ Inserted data into: {$matches[1]}\n";
            }
        }
    } else {
        $error_count++;
        $error_msg = $conn->error;
        $errors[] = "Error in statement: " . substr($statement, 0, 100) . "...\nError: " . $error_msg;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Database Setup Complete!\n";
echo "Successful statements: $success_count\n";
echo "Failed statements: $error_count\n";

if ($error_count > 0) {
    echo "\nErrors encountered:\n";
    foreach ($errors as $error) {
        echo "\n" . $error . "\n";
    }
}

// Verify tables
echo "\n" . str_repeat("=", 50) . "\n";
echo "Verifying tables...\n\n";
$result = $conn->query("SHOW TABLES");
$table_count = 0;
while ($row = $result->fetch_array()) {
    echo "  - " . $row[0] . "\n";
    $table_count++;
}

echo "\nTotal tables created: $table_count\n";

// Show user counts
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
echo "Total users: " . $row['count'] . "\n";

$result = $conn->query("SELECT COUNT(*) as count FROM donors");
$row = $result->fetch_assoc();
echo "Total donors: " . $row['count'] . "\n";

echo "\n✓ Database setup successful!\n";
?>
