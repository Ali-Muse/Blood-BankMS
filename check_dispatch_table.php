<?php
require_once 'includes/config.php';

$result = $conn->query("SHOW TABLES LIKE 'dispatch'");
if ($result->num_rows > 0) {
    echo "Table 'dispatch' exists.\n";
    $res = $conn->query("DESCRIBE dispatch");
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
} else {
    echo "Table 'dispatch' DOES NOT exist.\n";
}
?>
