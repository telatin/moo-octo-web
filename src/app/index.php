<?php
/**
 * Simple PHP script to display a list of users from the database
 * 
 * This file should be placed in the src/public/ directory of your Docker setup
 */

// Display errors during development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection parameters (from environment variables)
$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'myapp';
$username = getenv('DB_USER') ?: 'myuser';
$password = getenv('DB_PASSWORD') ?: 'mypassword';

// Function to safely display user data
function safeEcho($str) {
    echo htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Set up the database connection
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Query to get all users with their line manager's username
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.email, u.admin, 
               m.username AS line_manager_name
        FROM users u
        LEFT JOIN users m ON u.line_manager_id = m.id
        ORDER BY u.username
    ");
    
    $users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .admin {
            color: #d35400;
            font-weight: bold;
        }
        .no-users {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <h1>User List</h1>
    
    <?php if (empty($users)): ?>
        <div class="no-users">No users found in the database.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Line Manager</th>
                    <th>Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php safeEcho($user['id']); ?></td>
                        <td><?php safeEcho($user['username']); ?></td>
                        <td><?php safeEcho($user['email']); ?></td>
                        <td>
                            <?php if ($user['line_manager_name']): ?>
                                <?php safeEcho($user['line_manager_name']); ?>
                            <?php else: ?>
                                <em>None</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['admin']): ?>
                                <span class="admin">Yes</span>
                            <?php else: ?>
                                No
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p>
        <strong>Connection Information:</strong><br>
        Connected to MySQL database '<?php safeEcho($dbname); ?>' on host '<?php safeEcho($host); ?>'
    </p>
</body>
</html>
