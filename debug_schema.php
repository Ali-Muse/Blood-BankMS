<?php
require_once 'includes/config.php';
$result = $conn->query("DESCRIBE blood_units");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . "\n";
}
?>
