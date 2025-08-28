<?php
session_start();
require_once 'connection.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | Sameera Super</title>
  <link rel="stylesheet" href="style1.css"/>
  <style>
    .admin-info {
      margin: 30px auto;
      padding: 25px;
      max-width: 400px;
      background: #f9f9f9;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      text-align: center;
      font-family: Arial, sans-serif;
    }
    .admin-info h2 {
      margin-bottom: 15px;
      color: #333;
    }
    .admin-info p {
      margin: 8px 0;
      font-size: 15px;
      color: #555;
    }
    .admin-info a {
      display: inline-block;
      margin-top: 15px;
      padding: 10px 20px;
      background: #e63946;
      color: #fff;
      text-decoration: none;
      border-radius: 8px;
      transition: background 0.3s;
    }
    .admin-info a:hover {
      background: #c71c2c;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg">
    Sameera Super
  </div>
  <ul class="nav-links">
    <li><a href="index.html">Home</a></li>
    <li><a href="inventory.php">Inventory</a></li>
    <li><a href="suppliers.php">Suppliers</a></li>
    <li><a href="orders.php">Orders</a></li>
    <li><a href="customers.php">Customers</a></li>
    <li><a href="staff.php">Staff</a></li>
    <li><a href="login.php" class="active">Login</a></li>
  </ul>
</nav>

<div class="login-container">
  <h2>Sameera Super - Account Access</h2>

  <?php
  // 🔹 Show alerts if any
  if (isset($_SESSION['alert'])) {
      echo "<script>alert('" . $_SESSION['alert'] . "');</script>";
      unset($_SESSION['alert']);
  }

  // 🔹 If logged in → show only admin info
  if (isset($_SESSION['admin_id'])) {
      echo "
      <div class='admin-info'>
        <h2>Welcome, " . $_SESSION['admin_name'] . "</h2>
        <p><strong>Email:</strong> " . $_SESSION['admin_email'] . "</p>
        <a href='logout.php'>Logout</a>
      </div>";
  } else {
  ?>

  <!-- Show forms only if not logged in -->
  <div class="form-toggle">
    <button class="active" onclick="showForm('login')">Login</button>
    <button onclick="showForm('register')">Register</button>
  </div>

  <!-- Login Form -->
  <div id="loginForm" class="form-wrapper active">
    <form action="logHandler.php" method="POST">
      <div class="form-group">
        <label for="loginEmail">Email Address</label>
        <input type="email" id="loginEmail" name="loginEmail" required />
      </div>
      <div class="form-group">
        <label for="loginPassword">Password</label>
        <input type="password" id="loginPassword" name="loginPassword" required />
      </div>
      <button type="submit">Login</button>
    </form>
  </div>

  <!-- Register Form -->
  <div id="registerForm" class="form-wrapper">
    <form action="regHandler.php" method="POST" onsubmit="return validateRegisterForm()">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required/>
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required/>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="6" required/>
      </div>
      <div class="form-group">
        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required/>
      </div>
      <button type="submit">Register</button>
      <p id="errorMsg" class="error-message"></p>
    </form>
  </div>

  <?php } // close else ?>
</div>

<script>
  function showForm(formType) {
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const buttons = document.querySelectorAll(".form-toggle button");

    if (formType === "login") {
      loginForm.classList.add("active");
      registerForm.classList.remove("active");
      buttons[0].classList.add("active");
      buttons[1].classList.remove("active");
    } else {
      loginForm.classList.remove("active");
      registerForm.classList.add("active");
      buttons[0].classList.remove("active");
      buttons[1].classList.add("active");
    }
  }

  function validateRegisterForm() {
    const pwd = document.getElementById("password").value;
    const cpwd = document.getElementById("confirmPassword").value;
    const errorMsg = document.getElementById("errorMsg");
    if (pwd !== cpwd) {
      errorMsg.textContent = "Passwords do not match!";
      return false;
    }
    errorMsg.textContent = "";
    return true;
  }

  // Highlight active nav
  const currentPage = window.location.pathname.split("/").pop();
  const navLinks = document.querySelectorAll(".nav-links a");
  navLinks.forEach(link => {
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active");
    }
  });
</script>
</body>
</html>
