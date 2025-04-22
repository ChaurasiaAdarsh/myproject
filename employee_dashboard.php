<?php 
session_start();
include "db_connection.php"; 

// Redirect if not logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'employee') {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch Employee Details
$query = "SELECT * FROM employees WHERE BINARY username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    die("<div class='alert alert-danger text-center'>❌ No employee record found.</div>");
}

$attendanceMessage = "";
$date = date("Y-m-d");

// Check if Attendance is Already Marked
$check_query = "SELECT * FROM attendance WHERE employee_id = ? AND date = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("is", $employee['id'], $date);
$stmt->execute();
$check_result = $stmt->get_result();
$alreadyMarked = $check_result->num_rows > 0;

// Mark Attendance
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_attendance']) && !$alreadyMarked) {
    $status = "Present";
    $insert_query = "INSERT INTO attendance (employee_id, date, status) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iss", $employee['id'], $date, $status);
    $stmt->execute();
    $alreadyMarked = true;
    $attendanceMessage = "<div class='alert alert-success fade-in'>✅ Attendance Marked Successfully!</div>";
}

// Fetch Attendance Filter Data
$selected_month = $_POST['month'] ?? date('m');
$selected_year = $_POST['year'] ?? date('Y');

$history_query = "SELECT date, status FROM attendance WHERE employee_id = ? 
                  AND MONTH(date) = ? AND YEAR(date) = ? ORDER BY date DESC";
$stmt = $conn->prepare($history_query);
$stmt->bind_param("iss", $employee['id'], $selected_month, $selected_year);
$stmt->execute();
$history_result = $stmt->get_result();

// Fetch Task Counts
$employee_id = $employee['id'];

// Active Tasks (Pending)
$activeQuery = "SELECT COUNT(*) AS active_count FROM tasks WHERE employee_id = ? AND status = 'Pending'";
$stmt = $conn->prepare($activeQuery);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$activeResult = $stmt->get_result()->fetch_assoc();
$activeTasks = $activeResult['active_count'] ?? 0;

// Completed Tasks
$completedQuery = "SELECT COUNT(*) AS completed_count FROM tasks WHERE employee_id = ? AND status = 'Completed'";
$stmt = $conn->prepare($completedQuery);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$completedResult = $stmt->get_result()->fetch_assoc();
$completedTasks = $completedResult['completed_count'] ?? 0;

// In Progress Tasks
$inProgressQuery = "SELECT COUNT(*) AS in_progress_count FROM tasks WHERE employee_id = ? AND status = 'In Progress'";
$stmt = $conn->prepare($inProgressQuery);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$inProgressResult = $stmt->get_result()->fetch_assoc();
$inProgressTasks = $inProgressResult['in_progress_count'] ?? 0;

// Overdue Tasks (Pending + past due date)
$today = date("Y-m-d");
$overdueQuery = "SELECT COUNT(*) AS overdue_count FROM tasks WHERE employee_id = ? AND status = 'Pending' AND due_date < ?";
$stmt = $conn->prepare($overdueQuery);
$stmt->bind_param("is", $employee_id, $today);
$stmt->execute();
$overdueResult = $stmt->get_result()->fetch_assoc();
$overdueTasks = $overdueResult['overdue_count'] ?? 0;


$taskQuery = "SELECT task_name, description, due_date, status, priority FROM tasks WHERE employee_id = ? ORDER BY due_date ASC";
$stmt = $conn->prepare($taskQuery);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$taskResult = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        /* General Styles */
        body { display: flex; min-height: 100vh; background-image: url('https://d39l2hkdp2esp1.cloudfront.net/img/photo/131118/131118_00_2x.jpg?20170904131630');
    background-size: cover; background-position: center; font-family: Arial, sans-serif; }
        h1, h2 { color: #3B413C; margin-bottom: 15px; }
        .fade-in { animation: fadeIn 0.5s ease-in-out; }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #7776BC;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            left: -260px;
            transition: left 0.3s ease-in-out;
        }
        .sidebar h2 { text-align: center; }
        .sidebar a { display: block; color: white; padding: 10px; margin: 5px 0; text-decoration: none; transition: 0.3s; }
        .sidebar a:hover { background: #3B413C; transform: scale(1.05); }

       /* Top Right Profile Section */
.top-right-profile {
    position: absolute;
    top: 15px;
    right: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    z-index: 1000;
}

.profile-info {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    padding: 5px 10px;
    border-radius: 20px;
    box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
    transition: 0.3s;
}

.profile-info:hover {
    background: #f1f1f1;
}

.profile-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #343a40;
}

.arrow-down {
    border: solid #343a40;
    border-width: 0 2px 2px 0;
    display: inline-block;
    padding: 3px;
    transform: rotate(45deg);
    margin-left: 5px;
}

/* Dropdown Menu */
.dropdown-menu {
    display: none;
    position: absolute;
    top: 50px;
    right: 0;
    background: white;
    box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
    min-width: 180px;
    text-align: center;
}

/* Profile Info Inside Dropdown */
.profile-dropdown {
    padding: 10px;
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
}

.dropdown-profile-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-bottom: 5px;
}

.dropdown-name {
    font-weight: bold;
    margin: 0;
    font-size: 14px;
}

.dropdown-role {
    font-size: 12px;
    color: #666;
    margin: 0;
}

/* Dropdown Links */
.dropdown-menu a {
    display: block;
    padding: 10px;
    text-decoration: none;
    color: black;
    transition: 0.3s;
}

.dropdown-menu a:hover {
    background: #f1f1f1;
}

/* Show Dropdown on Click */
.top-right-profile.active .dropdown-menu {
    display: block;
}

.hidden {
    display: none;
}
.show {
    display: block !important;
}
#profile-section {
    position: fixed;
    bottom: 10px;
    right: 10px;
    width: 250px;
    background: #333;
    color: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
}
  
        /* Toggle Button */
        .menu-btn {
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 24px;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 1000;
        }


        .task-overview {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .task-card {
            background: #7776BC;
            padding: 20px;
            width: 220px;
            height: 200px;
            border-radius: 10px;
            color: white;
            text-align: center;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .task-card:hover {
            transform: scale(1.05);
        }

        .task-card .icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .task-card .count {
            font-size: 24px;
            font-weight: bold;
        }

        .task-card .label {
            font-size: 14px;
            opacity: 0.8;
        }

        /* Task Section Container */
.task-container {
    background:rgb(87, 73, 136);
    color: #E0E0E0;
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
}

/* Task Header */
.task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    font-size: 1.2rem;
    color: #FFFFFF;
}

.task-header .view-all {
    color:rgb(127, 90, 240);
    text-decoration: none;
    font-size: 0.9rem;
}

.task-header .view-all:hover {
    text-decoration: underline;
}

/* Task Table */
.task-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.task-table th, .task-table td {
    padding: 12px;
    text-align: left;
}

.task-table thead {
    border-bottom: 2px solid rgb(59, 212, 28);
}

.task-table tr:hover {
    background:rgb(129, 220, 234);
}

/* Status Badges */
.badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: bold;
}

/* Status Colors */
.status-to-do {
    background: #1E40AF;
    color: #C3DAFE;
}

.status-in-progress {
    background: #B08968;
    color: #FFF3E0;
}

.status-completed {
    background: #16A34A;
    color: #D1FAE5;
}

/* Priority Colors */
.priority-high {
    background: #B91C1C;
    color: #FECACA;
}

.priority-medium {
    background: #B08968;
    color: #FFE4B5;
}

.priority-low {
    background: #2563EB;
    color: #BFDBFE;
}

        .active .icon { color: #82C153; }
        .completed .icon { color: #3ac47d; }
        .in-progress .icon { color: #f4a62a; }
        .overdue .icon { color: #e74c3c; }

        /* Content */
        .content { margin-left: 20px; padding: 20px; width: 100%; transition: margin-left 0.3s ease-in-out; }
        .card { background: white; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .btn { transition: 0.3s; }
        .btn:hover { transform: scale(1.05); }

        /* Sidebar Active State */
        .sidebar.active { left: 0; }
        .content.active { margin-left: 270px; }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- Toggle Button -->
    <button class="menu-btn" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>EMS</h2>
        <a href="#" id="profile-btn">👤Profile</a>
        <a href="#attendance">📅 Attendance</a>
        <a href="#tasks">📋 Tasks</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

   <!-- Top Right Profile Section -->
<div class="top-right-profile">
    <div class="profile-info">
        <img src="https://pnghq.com/wp-content/uploads/pnghq.com-profile-avatar-png-768x804.png" alt="User" class="profile-icon">
        <span><?php echo htmlspecialchars($employee['name']); ?></span>
        <i class="arrow-down"></i>
    </div>

    

    
    <!-- Dropdown Menu -->
    <div class="dropdown-menu">
        <div class="profile-dropdown">
            <img src="https://pnghq.com/wp-content/uploads/pnghq.com-profile-avatar-png-768x804.png" alt="User" class="dropdown-profile-icon">
            <p class="dropdown-name"><?php echo htmlspecialchars($employee['name']); ?></p>
            <p class="dropdown-role"><?php echo htmlspecialchars($employee['position']); ?></p>
        </div>
        <a href="#profile">👤 View Profile</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</div>




    <!-- Main Content -->
    <div class="content">
        <h1 >Welcome, <?php echo htmlspecialchars($employee['name'] ?? "Employee"); ?>!</h1>

        <div id="profile-section" class="hidden">
    <h2>Profile Information</h2>
    <p><strong>Name:</strong> <?php echo $employee['name']; ?></p>
    <p><strong>Username:</strong> <?php echo $employee['username']; ?></p>
    <p><strong>Email:</strong> <?php echo $employee['email']; ?></p>
    <p><strong>Role:</strong> Employee</p>
    
</div>


        <!-- Task Overview Section -->
<div class="task-overview">
    <div class="task-card active">
        <div class="icon"><i class="fas fa-tasks"></i></div>
        <div class="count"><?php echo $activeTasks; ?></div>
        <div class="label">Active Tasks</div>
    </div>

    <div class="task-card completed">
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <div class="count"><?php echo $completedTasks; ?></div>
        <div class="label">Completed Tasks</div>
    </div>

    <div class="task-card in-progress">
        <div class="icon"><i class="fas fa-spinner"></i></div>
        <div class="count"><?php echo $inProgressTasks; ?></div>
        <div class="label">In Progress</div>
    </div>

    <div class="task-card overdue">
        <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="count"><?php echo $overdueTasks; ?></div>
        <div class="label">Overdue Tasks</div>
    </div>
</div>


        <!-- Attendance Section -->
        <div class="card fade-in" id="attendance">
            <h2>📅 Attendance</h2>
            <form method="post">
                <button class="btn btn-success" type="submit" name="mark_attendance" <?php echo $alreadyMarked ? 'disabled' : ''; ?>>
                    <?php echo $alreadyMarked ? '✅ Marked' : 'Mark Attendance'; ?>
                </button>
            </form>
            <?php echo $attendanceMessage; ?>

            <!-- Attendance Filter -->
            <h4 class="mt-3">Filter Attendance</h4>
            <form method="post" class="row g-2">
                <div class="col-md-5">
                    <select name="month" class="form-select">
                        <?php
                        for ($m = 1; $m <= 12; $m++) {
                            $monthValue = str_pad($m, 2, "0", STR_PAD_LEFT);
                            $selected = ($selected_month == $monthValue) ? "selected" : "";
                            echo "<option value='$monthValue' $selected>" . date("F", mktime(0, 0, 0, $m, 1)) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <select name="year" class="form-select">
                        <?php
                        $currentYear = date("Y");
                        for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                            $selected = ($selected_year == $y) ? "selected" : "";
                            echo "<option value='$y' $selected>$y</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
            

            <!-- Attendance History -->
            <h4 class="mt-3">Filtered Attendance</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $history_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
     
       <!-- Task List Section -->
<div class="task-container" id="tasks">
    <div class="task-header">
        <h5>📌 Upcoming Tasks</h5>
        <a href="#" class="view-all">View All</a>
    </div>
    <div class="task-body">
        <table class="task-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Priority</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($task = $taskResult->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($task['task_name']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($task['description']); ?></small>
                        </td>
                        <td><?php echo date("M j, Y", strtotime($task['due_date'])); ?></td>
                        <td>
                            <span class="badge status-<?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>">
                                <?php echo htmlspecialchars($task['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge priority-<?php echo strtolower($task['priority']); ?>">
                                <?php echo htmlspecialchars($task['priority']); ?>
                            </span>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
        
    </div>

    

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.content').classList.toggle('active');
        }
        
document.querySelector('.top-right-profile').addEventListener('click', function(event) {
    this.classList.toggle('active');
    event.stopPropagation();
});

// Close dropdown when clicking outside
document.addEventListener('click', function() {
    document.querySelector('.top-right-profile').classList.remove('active');
});

document.getElementById("profile-btn").addEventListener("click", function() {
    let profile = document.getElementById("profile-section");

    // Toggle profile visibility
    profile.classList.add("show");

    // Automatically hide after 5 seconds
    setTimeout(() => {
        profile.classList.remove("show");
    }, 5000);
});
    </script>

</body>
</html>
