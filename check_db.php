<?php
require_once 'includes/config.php';

echo "=== DATABASE TABLES ===\n\n";
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
    echo "- " . $row[0] . "\n";
}

echo "\n\n=== TABLE STRUCTURES ===\n\n";
foreach ($tables as $table) {
    echo "Table: $table\n";
    echo str_repeat("-", 50) . "\n";
    $result = $conn->query("DESCRIBE $table");
    while ($row = $result->fetch_assoc()) {
        echo sprintf("  %-20s %-15s %s\n", $row['Field'], $row['Type'], $row['Key']);
    }
    echo "\n";
}

echo "\n=== SAMPLE DATA COUNTS ===\n\n";
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $row = $result->fetch_assoc();
    echo sprintf("%-30s : %d rows\n", $table, $row['count']);
}
?>
