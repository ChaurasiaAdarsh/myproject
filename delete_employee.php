<?php
session_start();
include 'db_connection.php';

// Redirect if not logged in or not admin
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "<p>No employee selected for deletion.</p>";
    exit();
}

$employee_id = $_GET['id'];

// Handle deletion
if (isset($_POST['confirm_delete'])) {
    $query = "DELETE FROM employees WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $employee_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?msg=employee_deleted");
        exit();
    } else {
        $error_msg = "Error deleting employee: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Employee</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 500px;
            margin: 60px auto;
            background: #fff;
            padding: 30px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            animation: fadeIn 0.4s ease-in-out;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        form {
            text-align: center;
        }
        button, .cancel-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px 5px;
            transition: background-color 0.3s;
        }
        button.delete-btn {
            background-color: #e74c3c;
            color: #fff;
        }
        button.delete-btn:hover {
            background-color: #c0392b;
        }
        .cancel-btn {
            background-color: #bdc3c7;
            color: #2c3e50;
            text-decoration: none;
            display: inline-block;
        }
        .cancel-btn:hover {
            background-color: #95a5a6;
            color: #fff;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            background-color: #3498db;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #2980b9;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>Are you sure you want to delete this employee?</h2>
        <?php if (!empty($error_msg)): ?>
            <p style="color: red; text-align: center;"><?= $error_msg ?></p>
        <?php endif; ?>
        <form method="POST">
            <button type="submit" name="confirm_delete" class="delete-btn">Yes, Delete</button>
            <a href="admin_dashboard.php" class="cancel-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
