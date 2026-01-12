<?php
// Simple test file to check if basic PHP and database connection works
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing Blood Bank System</h1>";

// Test 1: Check if config.php exists and loads
echo "<h2>Test 1: Loading config.php</h2>";
if (file_exists('../../includes/config.php')) {
    echo "✓ config.php exists<br>";
    require_once '../../includes/config.php';
    echo "✓ config.php loaded successfully<br>";
} else {
    echo "✗ config.php NOT found<br>";
    die();
}

// Test 2: Check database connection
echo "<h2>Test 2: Database Connection</h2>";
if (isset($conn) && $conn->ping()) {
    echo "✓ Database connected successfully<br>";
    echo "Database: " . DB_NAME . "<br>";
} else {
    echo "✗ Database connection FAILED<br>";
    die();
}

// Test 3: Check if users table exists
echo "<h2>Test 3: Users Table</h2>";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result && $result->num_rows > 0) {
    echo "✓ Users table exists<br>";
    
    // Count users
    $count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    echo "Total users in database: <strong>$count</strong><br>";
} else {
    echo "✗ Users table NOT found<br>";
}

// Test 4: Check if auth.php loads
echo "<h2>Test 4: Loading auth.php</h2>";
if (file_exists('../../includes/auth.php')) {
    echo "✓ auth.php exists<br>";
    require_once '../../includes/auth.php';
    echo "✓ auth.php loaded successfully<br>";
} else {
    echo "✗ auth.php NOT found<br>";
}

// Test 5: Check session
echo "<h2>Test 5: Session Check</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✓ User is logged in<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Name: " . $_SESSION['full_name'] . "<br>";
    echo "User Role: " . $_SESSION['role_name'] . "<br>";
} else {
    echo "✗ User is NOT logged in<br>";
    echo "<strong>You need to log in first!</strong><br>";
    echo "<a href='../../login.php'>Go to Login Page</a><br>";
}

// Test 6: Check if sidebar.php loads
echo "<h2>Test 6: Loading sidebar.php</h2>";
if (file_exists('../../includes/sidebar.php')) {
    echo "✓ sidebar.php exists<br>";
} else {
    echo "✗ sidebar.php NOT found<br>";
}

// Test 7: Test a simple query
echo "<h2>Test 7: Sample Query</h2>";
try {
    $stmt = $conn->prepare("SELECT user_id, full_name, email, role_name FROM users LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "✓ Query executed successfully<br>";
    echo "Sample users:<br>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role_name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "✗ Query failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<p>If all tests passed, try accessing: <a href='users.php'>users.php</a></p>";
echo "<p>If you see errors above, please share them with me.</p>";
?>
