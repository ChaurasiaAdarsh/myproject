<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Check if task ID is passed in URL
if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    // Fetch the task data
    $query = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<p>Task not found!</p>";
        exit();
    }

    $task = $result->fetch_assoc();
}

if (isset($_POST['update_task'])) {
    $task_name = $_POST['task_name'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];

    // Update task data
    $query = "UPDATE tasks SET task_name = ?, description = ?, due_date = ?, priority = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $task_name, $description, $due_date, $priority, $task_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Task updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error updating task: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
    <style>
        body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
  background-color: #f9f9f9;
}

.container {
  max-width: 800px;
  margin: 40px auto;
  padding: 20px;
  background-color: #fff;
  border: 1px solid #ddd;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h2 {
  font-size: 24px;
  margin-bottom: 10px;
}

form {
  margin-top: 20px;
}

label {
  display: block;
  margin-bottom: 10px;
}

input[type="text"], textarea {
  width: 100%;
  padding: 10px;
  margin-bottom: 20px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

input[type="date"] {
  width: 100%;
  padding: 10px;
  margin-bottom: 20px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

select {
  width: 100%;
  padding: 10px;
  margin-bottom: 20px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

button[type="submit"] {
  background-color: #007bff;
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button[type="submit"]:hover {
  background-color: #0056b3;
}

a {
  text-decoration: none;
  color: #337ab7;
}

a:hover {
  color: #23527c;
}

.error {
  color: #red;
}

.success {
  color: #green;
}
    </style>
</head>
<body>
<div class="container">
<a href="admin_dashboard.php" class="btn btn-primary">← Back to Dashboard</a>


<h2>Edit Task</h2>
<form method="POST">
    <label for="task_name">Task Name:</label>
    <input type="text" name="task_name" value="<?php echo htmlspecialchars($task['task_name']); ?>" required>

    <label for="description">Description:</label>
    <textarea name="description" required><?php echo htmlspecialchars($task['description']); ?></textarea>

    <label for="due_date">Due Date:</label>
    <input type="date" name="due_date" value="<?php echo htmlspecialchars($task['due_date']); ?>" required>

    <label for="priority">Priority:</label>
    <select name="priority" required>
        <option value="Low" <?php echo $task['priority'] == 'Low' ? 'selected' : ''; ?>>Low</option>
        <option value="Medium" <?php echo $task['priority'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
        <option value="High" <?php echo $task['priority'] == 'High' ? 'selected' : ''; ?>>High</option>
    </select>

    <button type="submit" name="update_task" class="btn btn-primary">Update Task</button>
</form>

<?php if (isset($_POST['update_task'])) : ?>
      <?php if ($stmt->execute()) : ?>
        <p class="success">Task updated successfully!</p>
      <?php else : ?>
        <p class="error">Error updating task: <?php echo $conn->error; ?></p>
      <?php endif; ?>
    <?php endif; ?>
  </div>

</body>
</html>
