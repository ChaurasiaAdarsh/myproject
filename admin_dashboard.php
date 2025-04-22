<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Query to get total employees
$query = "SELECT COUNT(*) AS total FROM employees"; // Change 'employees' to your actual table name
$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    $totalEmployees = $row['total'];
} else {
    $totalEmployees = 0; // Default to 0 if query fails
}

// Query to get the count of pending leave requests
$query = "SELECT COUNT(*) AS total FROM leave_requests WHERE status = 'Pending'"; 
// Change 'leave_requests' to your actual table name and 'status' column name if different

$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    $pendingLeaves = $row['total'];
} else {
    $pendingLeaves = 0; // Default to 0 if query fails
}

// Query to count active tasks
// If you want to count both "In Progress" and "Pending", use:
$query = "SELECT COUNT(*) AS total FROM tasks WHERE status IN ('In Progress')";
// Change 'tasks' to your actual table name and 'status' column if different

$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    $activeTasks = $row['total'];
} else {
    $activeTasks = 0; // Default to 0 if query fails
}

$completedTasks = 0; // Default to avoid undefined errors
$query = "SELECT COUNT(*) AS total FROM tasks WHERE status = 'Completed'";
$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    $completedTasks = $row['total'];
}

if (isset($_POST['assign_task'])) {
    $employee_id = (int) $_POST['employee_id']; // Ensure it's an integer
    $task_name = $_POST['task_name'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];

    // Check if the employee exists
    $checkEmployee = $conn->prepare("SELECT id FROM employees WHERE id = ?");
    $checkEmployee->bind_param("i", $employee_id);
    $checkEmployee->execute();
    $result = $checkEmployee->get_result();

    if ($result->num_rows === 0) {
        die("<p style='color: red;'>Error: Selected employee does not exist!</p>");
    }

    // Insert the task into the database
    $query = "INSERT INTO tasks (task_name, employee_id, description, due_date, priority, status) 
              VALUES (?, ?, ?, ?, ?, 'Pending')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sisss", $task_name, $employee_id, $description, $due_date, $priority);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Task Assigned Successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error Assigning Task: " . $conn->error . "</p>";
    }
}


// Fetch counts for dashboard summary
$employee_count = $conn->query("SELECT COUNT(*) AS count FROM employees")->fetch_assoc()['count'];
$task_count = $conn->query("SELECT COUNT(*) AS count FROM tasks WHERE status != 'Completed'")->fetch_assoc()['count'];
$pending_leaves = $conn->query("SELECT COUNT(*) AS count FROM leave_requests WHERE status = 'Pending'")->fetch_assoc()['count'];
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>


#assign {
    background-image: url('https://img.freepik.com/premium-photo/yellow-notebook-with-pencil-blue-background-space-your-text_96727-2714.jpg');
    background-size: cover;
    background-position: center;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    background-attachment: fixed;
    background-repeat: no-repeat;
}

/* Add a overlay to make the text more readable */
#assign::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.5);
    z-index: -1;
    border-radius: 12px;
}


.btn {
 width: 7.5em;
 height: 2.3em;
 margin: 0.5em 0;
 background: #7776Bc;
 color: white;
 border: none;
 border-radius: 0.625em;
 font-size: 20px;
 font-weight: bold;
 cursor: pointer;
 position: relative;
 z-index: 1;
 overflow: hidden;
}

.btn:hover {
 color: black;
}

.btn:after {
 content: "";
 background: white;
 position: absolute;
 z-index: -1;
 left: -20%;
 right: -20%;
 top: 0;
 bottom: 0;
 transform: skewX(-45deg) scale(0, 1);
 transition: all 0.5s;
}

.btn:hover:after {
 transform: skewX(-45deg) scale(1, 1);
 -webkit-transition: all 0.5s;
 transition: all 0.5s;
}




.Btn {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  width: 45px;
  height: 45px;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.199);
  background-color: rgb(163, 142, 255);
  padding: 0 0px;
}

/* Icon container */
.sign {
  flex-shrink: 0;
  width: 45px;
  height: 45px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.sign svg {
  width: 17px;
}

.sign svg path {
  fill: white;
}

/* Text */
.text {
  white-space: nowrap;
  overflow: hidden;
  opacity: 0;
  width: 0;
  color: white;
  font-size: 1em;
  font-weight: 600;
  transition: all 0.3s ease;
  padding-left: 10px;
}

/* Hover expand */
.Btn:hover {
  width: 130px;
  border-radius: 40px;
}

/* Show text on hover */
.Btn:hover .text {
  width: auto;
  opacity: 1;
}

/* Optional click effect */
.Btn:active {
  transform: translate(1px, 1px);
}



    /* General Styles */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    background-image: url('https://d39l2hkdp2esp1.cloudfront.net/img/photo/131118/131118_00_2x.jpg?20170904131630');
    background-size: cover;
    background-position: center;
    color: #333;
}

/* Navbar */
.navbar {
    background: #7776bc;
    color: #fff;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.navbar h2 {
    margin: 0;
    font-size: 24px;
}

.navbar ul {
    list-style: none;
    display: flex;
    gap: 20px;
}

.navbar ul li a {
    color: #ecf0f1;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.navbar ul li a:hover {
    color: #1abc9c;
}

/* Toggle Button */
#toggleSidebar {
    background: none;
    border: none;
    font-size: 26px;
    cursor: pointer;
    color: #ecf0f1;
}

/* Sidebar */
.sidebar {
    width: 240px;
    height: 100vh;
    background: #7776bc;
    position: fixed;
    top: 0;
    left: -240px;
    transition: left 0.3s ease;
    padding-top: 60px;
    z-index: 999;
}

.sidebar.active {
    left: 0;
}

.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar ul li {
    padding: 15px 20px;
    border-bottom: 1px solid #7776Bc;
}

.sidebar ul li a {
    color: #ecf0f1;
    text-decoration: none;
    font-size: 16px;
    display: block;
    transition: background 0.3s, color 0.3s;
}

.sidebar ul li a:hover {
    background: #1abc9c;
    color: #2c3e50;
    border-radius: 4px;
}

/* Dashboard Container */
.dashboard-container {
    margin-left: 20px;
    padding: 20px;
    transition: margin-left 0.3s ease-in-out;
}

.sidebar.active + .dashboard-container {
    margin-left: 260px;
}

/* Sections */
section {
    margin: 40px 0;
    padding: 25px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

/* Cards */
.dashboard-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.card {
    flex: 1;
    min-width: 200px;
    background: #ffffff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.card h3 {
    margin-bottom: 10px;
    font-size: 20px;
    color: #2c3e50;
}

.card p {
    font-size: 24px;
    font-weight: bold;
    color: #1abc9c;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

th {
    background: #7776Bc;
    color: white;
}

tr:hover {
    background-color: #f1f1f1;
}

/* Form Styling */
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    max-width: 600px;
    margin-top: 20px;
}

form label {
    font-weight: 600;
}

form input[type="text"],
form input[type="date"],
form select,
form textarea {
    padding: 10px;
    border: 1px solid #bdc3c7;
    border-radius: 6px;
    font-size: 14px;
    width: 100%;
}

form textarea {
    resize: vertical;
    min-height: 80px;
}

form button {
    background: #1abc9c;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease;
}

form button:hover {
    background: #16a085;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-cards {
        flex-direction: column;
        align-items: center;
    }

    .navbar h2 {
        font-size: 20px;
    }

    .sidebar {
        width: 100%;
        left: -100%;
    }

    .sidebar.active {
        left: 0;
    }

    .sidebar.active + .dashboard-container {
        margin-left: 0;
    }
}
</style>
</head>
<body>

<div class="navbar">
    <button id="toggleSidebar" >☰</button>
    <h2>Admin Dashboard</h2>
    <form action="logout.php" method="post" style="margin: 0;">
    <button class="Btn" type="submit">
        <div class="sign">
            <svg viewBox="0 0 512 512">
                <path d="M217.9 105.9L340.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L217.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1L32 320c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM352 416l64 0c17.7 0 32-14.3 32-32l0-256c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32s14.3-32 32-32l64 0c53 0 96 43 96 96l0 256c0 53-43 96-96 96l-64 0c-17.7 0-32-14.3-32-32s14.3-32 32-32z"/>
            </svg>
        </div>
        <div class="text">Logout</div>
    </button>
</form>

</div>

<div class="layout-wrapper">
<div class="sidebar" id="sidebar">
    <ul>
        <li><a href="#dashboard">Dashboard</a></li>
        <li><a href="#employees">Manage Employees</a></li>
        <li><a href="#tasks">Manage Tasks</a></li>
        <li><a href="#assign">Assign Tasks</a></li>
    </ul>
</div>
</div>


<!-- Dashboard Overview -->

    <div>
    <section id="dashboard">
        <h2>Dashboard Overview</h2>
        <div class="dashboard-cards">
    
        <div class="card">
            <h3>Total Employees</h3>
            <p><?php echo $totalEmployees; ?></p>
        </div>
        <div class="card">
            <h3>Pending Leave Requests</h3>
            <p><?php echo $pendingLeaves; ?></p>
        </div>
        <div class="card">
            <h3>Active Tasks</h3>
            <p><?php echo $activeTasks; ?></p>
        </div>
        <div class="card">
            <h3>Completed Tasks</h3>
            <p><?php echo $completedTasks; ?></p>
            
        </div>
    </section>
</div>
    

    <!-- Employee Management -->
    <section id="employees">
    <h2>Employee Management</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = mysqli_query($conn, "SELECT * FROM employees");
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>
                        <a href='edit_employee.php?id={$row['id']}'>Edit</a> |
                        <a href='delete_employee.php?id={$row['id']}'>Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
    </section>
     
    
    <!-- Task Management -->
     <section id="tasks">
        <h2>Task Management</h2>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Task</th>
           
            <th>Status</th>
            <th>Actions</th>
            <th> </th>
        </tr>
        <?php
        $tasks = mysqli_query($conn, "SELECT * FROM tasks");
        while ($task = mysqli_fetch_assoc($tasks)) {
            echo "<tr>
                    <td>{$task['id']}</td>
                    <td>{$task['task_name']}</td>
                    
                    <td>{$task['status']}</td>
                    <td><a href='edit_task.php?id={$task['id']}'>Edit</a></td>
                    <td><a href='delete_task.php?id={$task['id']}'>Delete</a></td>
                  </tr>";
        }
        ?>
    </table>
    </section>

    <section id="assign">
    <h2>Assign Task</h2>
<form method="POST">
    <label for="employee">Assign To:</label>
    <select name="employee_id" required>
        <option value="">Select Employee</option>
        <?php
        $employees = mysqli_query($conn, "SELECT id, name FROM employees");
        while ($emp = mysqli_fetch_assoc($employees)) {
            echo "<option value='{$emp['id']}'>{$emp['name']}</option>";
        }
        ?>
    </select>

    <label for="task_name">Task Name:</label>
    <input type="text" name="task_name" required>

    <label for="description">Description:</label>
    <textarea name="description" required></textarea>

    <label for="due_date">Due Date:</label>
    <input type="date" name="due_date" required>

    <label for="priority">Priority:</label>
    <select name="priority" required>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
    </select>

    <button class="btn"  type="submit" name="assign_task">Assign Task</button>
</form>
</section>

    
</div>

<script>
    document.getElementById("toggleSidebar").addEventListener("click", function() {
        document.getElementById("sidebar").classList.toggle("active");
    });
    function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    sidebar.classList.toggle("active");
}
    // Auto-close sidebar when clicking a section link
    document.querySelectorAll(".sidebar ul li a").forEach(link => {
        link.addEventListener("click", function() {
            document.getElementById("sidebar").classList.remove("active");
        });
    });
</script>


</body>
</html>
