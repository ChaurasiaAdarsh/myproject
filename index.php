<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ems");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        if ($row['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: employee_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Page</title>
  <link href="https://rsms.me/inter/inter-ui.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
  <style>


.blob {
  position: absolute;
  width: 100px;
  height: 100px;
  background-color:rgb(205, 25, 70);
  border-radius: 80%;
  filter: blur(10px);
  opacity: 0.7;
  animation: loopAround 4s linear infinite;
  z-index: 0;
}

@keyframes loopAround {
  0% {
    transform: translate(440px, 0px); /* top right */
  }
  25% {
    transform: translate(640px, 160px); /* bottom right */
  }
  50% {
    transform: translate(440px, 320px); /* bottom left of right div */
  }
  75% {
    transform: translate(240px, 160px); /* top left of right div */
  }
  100% {
    transform: translate(440px, 0px); /* back to top */
  }
}


    ::selection {
      background:rgb(105, 114, 147);
    }
    body {
      background: white;
      font-family: 'Inter UI', sans-serif;
      margin: 0;
      padding: 20px;
    }
    .page {
      background: url('https://powerconsulting.com/wp-content/uploads/2020/02/business-it-strategy.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      flex-direction: column;
      height: calc(100% - 40px);
      position: absolute;
      place-content: center;
      width: calc(100% - 40px);
    }
    .container {
      display: flex;
      height: 320px;
      margin: 0 auto;
      width: 640px;
    }
    .left {
      background: white;
      height: calc(100% - 40px);
      position: relative;
      width: 50%;
    }
    .login {
      font-size: 50px;
      font-weight: 900;
      margin: 50px 40px 40px;
    }
    .eula {
      color: #474A59;
      font-size: 18px;
      line-height: 1.5;
      margin: 40px;
    }
    .right {
      background: #474A59;
      box-shadow: 0px 0px 40px 16px rgba(0,0,0,0.22);
      color: #F1F1F2;
      position: relative;
      width: 50%;
    }
    svg {
      position: absolute;
      width: 320px;
    }
    path {
      fill: none;
      stroke: url(#linearGradient);
      stroke-width: 4;
      stroke-dasharray: 240 1386;
    }
    .form {
      margin: 40px;
      position: absolute;
      width: 240px;
    }
    label {
      color: #c2c2c5;
      display: block;
      font-size: 14px;
      margin-top: 20px;
      margin-bottom: 5px;
    }
    input {
      background: transparent;
      border: 0;
      color: #f2f2f2;
      font-size: 20px;
      height: 30px;
      width: 100%;
    }
    #submit {
      background: #ff3366;
      border: none;
      color: white;
      padding: 10px;
      font-size: 18px;
      margin-top: 20px;
      cursor: pointer;
      width: 100%;
    }
    .error {
      color: red;
      text-align: center;
      font-size: 14px;
    }
  </style>
</head>
<body>

  <div class="page">
    <div class="container">

    <div class="blob"></div>
      <div class="left">
        <div class="login">Login</div>
        <div class="eula">To login to either Employee or Admin page by respected credentials</div>
        
      </div>
      <div class="right">
        <svg viewBox="0 0 320 300">
          <defs>
            <linearGradient id="linearGradient" x1="13" y1="193.49992" x2="307" y2="193.49992" gradientUnits="userSpaceOnUse">
              <stop style="stop-color:#ff00ff;" offset="0" />
              <stop style="stop-color:#ff0000;" offset="1" />
            </linearGradient>
          </defs>
          <path d="m 40,120.00016 239.99984,-3.2e-4 c 0,0 24.99263,0.79932 25.00016,35.00016 0.008,34.20084 -25.00016,35 -25.00016,35 h -239.99984 c 0,-0.0205 -25,4.01348 -25,38.5 0,34.48652 25,38.5 25,38.5 h 215 c 0,0 20,-0.99604 20,-25 0,-24.00396 -20,-25 -20,-25 h -190 c 0,0 -20,1.71033 -20,25 0,24.00396 20,25 20,25 h 168.57143" />
        </svg>
        <div class="form">
          <?php if ($error) echo "<p class='error'>$error</p>"; ?>
          <form method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" id="submit" value="Login">
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    var current = null;
    document.querySelector('#username').addEventListener('focus', function() {
      if (current) current.pause();
      current = anime({
        targets: 'path',
        strokeDashoffset: {
          value: 0,
          duration: 700,
          easing: 'easeOutQuart'
        },
        strokeDasharray: {
          value: '240 1386',
          duration: 700,
          easing: 'easeOutQuart'
        }
      });
    });

    document.querySelector('#password').addEventListener('focus', function() {
      if (current) current.pause();
      current = anime({
        targets: 'path',
        strokeDashoffset: {
          value: -336,
          duration: 700,
          easing: 'easeOutQuart'
        },
        strokeDasharray: {
          value: '240 1386',
          duration: 700,
          easing: 'easeOutQuart'
        }
      });
    });

    document.querySelector('#submit').addEventListener('focus', function() {
      if (current) current.pause();
      current = anime({
        targets: 'path',
        strokeDashoffset: {
          value: -730,
          duration: 700,
          easing: 'easeOutQuart'
        },
        strokeDasharray: {
          value: '530 1386',
          duration: 700,
          easing: 'easeOutQuart'
        }
      });
    });
  </script>

</body>
</html>
