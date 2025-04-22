<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Initialize feedback message
$feedback = "";

// Check if employee ID is passed in URL
if (isset($_GET['id'])) {
    $employee_id = $_GET['id'];

    // Fetch the employee data
    $query = "SELECT * FROM employees WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $feedback = "<p class='error'>Employee not found!</p>";
    } else {
        $employee = $result->fetch_assoc();
    }
} else {
    $feedback = "<p class='error'>No employee ID provided!</p>";
}

// Handle update
if (isset($_POST['update_employee'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $query = "UPDATE employees SET name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $name, $email, $employee_id);

    if ($stmt->execute()) {
        $feedback = "<p class='success'>Employee updated successfully!</p>";
        $employee['name'] = $name;
        $employee['email'] = $email;
    } else {
        $feedback = "<p class='error'>Error updating employee: " . htmlspecialchars($conn->error) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Employee</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }

        h2 {
            font-size: 26px;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: #fff;
            padding: 12px 24px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            font-size: 14px;
            color: #007bff;
            text-decoration: none;
        }

        .back-link:hover {
            color: #0056b3;
        }

        .success {
            color: #28a745;
            font-weight: bold;
            margin-top: 15px;
        }

        .error {
            color: #dc3545;
            font-weight: bold;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>

    <h2>Edit Employee</h2>

    <?php echo $feedback; ?>

    <?php if (isset($employee)) : ?>
        <form method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>

            <button type="submit" name="update_employee">Update Employee</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
