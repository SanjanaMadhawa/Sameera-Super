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
  <link rel="stylesheet" href="style_u.css">
  
</head>
<body>

    <!-- Navbar -->
<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg">
    Sameera Super
  </div>
  <ul class="nav-links">
    <li><a href="home_u.php">Home</a></li>
    <li><a href="product.php">Products</a></li>
    <li><a href="cart.php">Cart</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="login.php" class="active">Login</a></li>
  </ul>
</nav>
  <div class="login-container">
    <h2>Sameera Super - Account Access</h2>

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
        <button type="submit" name="loginSubmit">Login</button>
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
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" pattern=\"[0-9]{10}\" required/>
        </div>
        <div class="form-group">
          <label for="address">Address</label>
          <textarea id="address" name="address" rows="2" required></textarea>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" minlength="6" required/>
        </div>
        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <input type="password" id="confirmPassword" name="confirmPassword" required/>
        </div>
        <button type="submit" name="signUpSubmit">Register</button>
        <p id="errorMsg" class="error-message"></p>
      </form>
    </div>
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
  </script>
</body>
</html>
