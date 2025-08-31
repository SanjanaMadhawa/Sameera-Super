<?php 
session_start();
require_once 'connection.php';

if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

// ================== ADD OR UPDATE STAFF ==================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['staffName'])) {
    $id    = isset($_POST['staffId']) ? intval($_POST['staffId']) : 0;
    $name  = trim($_POST['staffName']);
    $email = trim($_POST['staffEmail']);
    $phone = trim($_POST['staffPhone']);
    $role  = trim($_POST['staffRole']);
    $dept  = trim($_POST['staffDepartment']);

    if (!empty($name) && !empty($email) && !empty($phone) && !empty($role) && !empty($dept)) {
        if ($id > 0) {
            $sql = "UPDATE staff SET name=?, email=?, phone=?, role=?, department=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $name, $email, $phone, $role, $dept, $id);
        } else {
            $sql = "INSERT INTO staff (name, email, phone, role, department) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $email, $phone, $role, $dept);
        }
        $stmt->execute();
        header("Location: staff.php");
        exit();
    }
}

// ================== DELETE STAFF ==================
if (isset($_GET['delete_staff'])) {
    $id = intval($_GET['delete_staff']);
    $sql = "DELETE FROM staff WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "success";
    exit();
}

// ================== ADD OR UPDATE PERFORMANCE ==================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['perfName'])) {
    $id   = isset($_POST['perfId']) ? intval($_POST['perfId']) : 0;
    $name = trim($_POST['perfName']);
    $attendance = intval($_POST['perfAttendance']);
    $tasks = intval($_POST['perfTasks']);
    $rating = trim($_POST['perfRating']);

    if ($id > 0) {
        $sql = "UPDATE staff_performance SET staff_name=?, attendance=?, tasks_completed=?, rating=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siisi", $name, $attendance, $tasks, $rating, $id);
    } else {
        $sql = "INSERT INTO staff_performance (staff_name, attendance, tasks_completed, rating) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siis", $name, $attendance, $tasks, $rating);
    }
    $stmt->execute();
    header("Location: staff.php");
    exit();
}

// ================== DELETE PERFORMANCE ==================
if (isset($_GET['delete_performance'])) {
    $id = intval($_GET['delete_performance']);
    $sql = "DELETE FROM staff_performance WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "success";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Staff Management | Sameera Super</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f7f9;
      margin: 0;
      padding: 0;
    }

    .navbar {
      background-color: #2b6777;
      color: white;
      padding: 9px 10%;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .nav-links {
      list-style: none;
      display: flex;
      gap: 20px;
    }

    .nav-links li a {
      color: white;
        text-decoration: none;
      font-weight: 500;
    }

    .nav-links li a:hover {
      color: #ffdd00;
    }

    .nav-links a.active {
      border-bottom: 2px solid #ca6702;
      padding-bottom: 5px;
      color: #ee9b00;
    }

    .logo {
      display: flex;
      align-items: center;
      font-size: 26px;
      font-weight: bold;
      color: white;
    }

    .logoimg{
      height: 40px; vertical-align: middle; margin-right: 10px;
      margin-left: s0px;
      border-radius: 10px;
    }

    .container {
      max-width: 1000px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }

    h2 {
      text-align: center;
      color: #2b6777;
    }

    .form {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }

    .form input, .form select {
      flex: 1 1 200px;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .form button {
      background-color: #2b6777;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    th, td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: center;
    }

    th {
      background-color: #e1ecf4;
    }

    .edit-btn, .delete-btn {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 6px 12px;
      margin: 2px;
      border-radius: 4px;
      cursor: pointer;
    }

    .delete-btn {
      background-color: #dc3545;
    }

    .report-btn {
      margin-top: 10px;
      background: #ff9900;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg"> Sameera Super
  </div>
  <ul class="nav-links">
    <li><a href="index.html">Home</a></li>
    <li><a href="inventory.php">Inventory</a></li>
    <li><a href="suppliers.php">Suppliers</a></li>
    <li><a href="orders.php">Orders</a></li>
    <li><a href="customers.php">Customers</a></li>
    <li><a href="staff.php">Staff</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<div class="container">
  <h2>Staff Management</h2>
  <form id="staffForm" method="POST" class="form">
    <input type="hidden" name="staffId" id="staffId" />
    <input type="text" name="staffName" id="staffName" placeholder="Full Name" required />
    <input type="email" name="staffEmail" id="staffEmail" placeholder="Email" required />
    <input type="tel" name="staffPhone" id="staffPhone" placeholder="Phone Number" pattern="[0-9]{10}" required />
    <select name="staffRole" id="staffRole" required>
      <option value="">Select Role</option>
      <option>Cashier</option>
      <option>Inventory Manager</option>
      <option>HR</option>
      <option>Supervisor</option>
    </select>
    <input type="text" name="staffDepartment" id="staffDepartment" placeholder="Department" required />
    <button type="submit" id="staffSubmitBtn">Add Staff</button>
  </form>

  <table>
    <thead>
      <tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Department</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php
      $result = $conn->query("SELECT * FROM staff");
      while ($row = $result->fetch_assoc()) {
        echo "<tr>
          <td>{$row['name']}</td>
          <td>{$row['email']}</td>
          <td>{$row['phone']}</td>
          <td>{$row['role']}</td>
          <td>{$row['department']}</td>
          <td>
            <button class='edit-btn' onclick='editStaff(this, {$row['id']})'>Edit</button>
            <button class='delete-btn' onclick='deleteStaff({$row['id']}, this)'>Delete</button>
          </td>
        </tr>";
      }
      ?>
    </tbody>
  </table>
  <button class="report-btn" onclick="window.location.href='report_generate.php'">Generate Staff Report</button>
</div>

<div class="container" style="margin-top:40px;">
  <h2>Staff Performance</h2>
  <form id="performanceForm" method="POST" class="form">
    <input type="hidden" name="perfId" id="perfId" />
    <input type="text" name="perfName" id="perfName" placeholder="Staff Name" required />
    <input type="number" name="perfAttendance" id="perfAttendance" placeholder="Attendance (%)" min="0" max="100" required />
    <input type="number" name="perfTasks" id="perfTasks" placeholder="Tasks Completed" min="0" required />
    <select name="perfRating" id="perfRating" required>
      <option value="">Rating</option>
      <option>Excellent</option>
      <option>Good</option>
      <option>Average</option>
      <option>Poor</option>
    </select>
    <button type="submit">Add Record</button>
  </form>

  <table>
    <thead>
      <tr><th>Staff Name</th><th>Attendance</th><th>Tasks Completed</th><th>Rating</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php
      $perfResult = $conn->query("SELECT * FROM staff_performance");
      while ($prow = $perfResult->fetch_assoc()) {
        echo "<tr>
          <td>{$prow['staff_name']}</td>
          <td>{$prow['attendance']}</td>
          <td>{$prow['tasks_completed']}</td>
          <td>{$prow['rating']}</td>
          <td>
            <button class='edit-btn' onclick='editPerformance(this, {$prow['id']})'>Edit</button>
            <button class='delete-btn' onclick='deletePerformance({$prow['id']}, this)'>Delete</button>
          </td>
        </tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<script>
function editStaff(btn, id) {
  const row = btn.closest('tr').children;
  document.getElementById('staffId').value = id;
  document.getElementById('staffName').value = row[0].innerText;
  document.getElementById('staffEmail').value = row[1].innerText;
  document.getElementById('staffPhone').value = row[2].innerText;
  document.getElementById('staffRole').value = row[3].innerText;
  document.getElementById('staffDepartment').value = row[4].innerText;
  document.getElementById('staffSubmitBtn').textContent = "Update Staff";
}

function deleteStaff(id, btn) {
  if (confirm("Delete this staff?")) {
    fetch('staff.php?delete_staff=' + id)
      .then(res => res.text())
      .then(data => {
        if (data.includes("success")) btn.closest('tr').remove();
        else alert("Delete failed: " + data);
      });
  }
}

function editPerformance(btn, id) {
  const row = btn.closest('tr').children;
  document.getElementById('perfId').value = id;
  document.getElementById('perfName').value = row[0].innerText;
  document.getElementById('perfAttendance').value = row[1].innerText;
  document.getElementById('perfTasks').value = row[2].innerText;
  document.getElementById('perfRating').value = row[3].innerText;
  document.querySelector('#performanceForm button').textContent = "Update Record";
}

function deletePerformance(id, btn) {
  if (confirm("Delete this performance record?")) {
    fetch('staff.php?delete_performance=' + id)
      .then(res => res.text())
      .then(data => {
        if (data.includes("success")) btn.closest('tr').remove();
        else alert("Delete failed: " + data);
      });
  }
}

// Active navbar link
const currentPage = window.location.pathname.split("/").pop();
const navLinks = document.querySelectorAll(".nav-links a");
navLinks.forEach(link => {
  if (link.getAttribute("href") === currentPage) link.classList.add("active");
});

</script>
</body>
</html>