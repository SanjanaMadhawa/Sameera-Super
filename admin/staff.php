<?php 
session_start();
require_once 'connection.php';

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
      background: #2b6777;
      color: white;
      padding: 15px 10%;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      display: flex;
      align-items: center;
      font-size: 26px;
      font-weight: bold;
      color: white;
    }

    .logoimg {
      height: 40px;
      vertical-align: middle;
      margin-right: 10px;
      border-radius: 10px;
    }

    .nav-links {
      list-style: none;
      display: flex;
      gap: 20px;
    }

    .nav-links li a {
      color: white;
      text-decoration: none;
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
      background: #6c63ff;
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
    <img src="img.png" alt="Sameera Super Logo" class="logoimg">
    Sameera Super
  </div>
  <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="inventory.php">Inventory</a></li>
        <li><a href="suppliers.php">Suppliers</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="customers.html">Customers</a></li>
        <li><a href="staff.php">Staff</a></li>
        <li><a href="login.html">Login</a></li>
      </ul>
</nav>

<!-- Content -->
<div class="container">
  <h2>Staff Management</h2>

  <!-- Staff Form -->
<form id="staffForm" action="add_staff.php" method="POST" class="form">
    <input type="hidden" name="staffId" id="staffId" />
    <input type="name" name="staffName" id="staffName" placeholder="Full Name" required />
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

  <!-- Staff Table -->
  <table id="staffTable">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Department</th>
        <th>Actions</th>
      </tr> 
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
              <button class='edit-btn' onclick='editStaff(this)'>Edit</button>
              <button class='delete-btn' onclick='deleteStaff({$row['id']}, this)'>Delete</button>
            </td>
          </tr>";
}
?>
    </tbody>
  </table>

  <!-- Generate Report -->
  <button class="report-btn" onclick="generateReport()">Generate Staff Report</button>
</div>

<!-- Script -->
<script>
function deleteStaff(id, btn) {
  console.log("Delete button clicked for ID: " + id);
  if (confirm("Are you sure you want to delete this staff member?")) {
    fetch('delete_staff.php?id=' + id)
      .then(response => response.text())
      .then(data => {
        btn.closest('tr').remove();
      });
  }
}



  function editStaff(btn) {
    const row = btn.closest('tr');
    // Find the staff ID from the delete button's onclick argument
    const id = row.querySelector('.delete-btn').getAttribute('onclick').match(/deleteStaff\((\d+),/)[1];
    document.getElementById('staffId').value = id;
    document.getElementById('staffName').value = row.cells[0].innerText;
    document.getElementById('staffEmail').value = row.cells[1].innerText;
    document.getElementById('staffPhone').value = row.cells[2].innerText;
    document.getElementById('staffRole').value = row.cells[3].innerText;
    document.getElementById('staffDepartment').value = row.cells[4].innerText;
    document.getElementById('staffSubmitBtn').textContent = 'Update Staff';
    document.getElementById('staffForm').setAttribute('action', 'edit.php');
  }

  // Reset form to add mode after submit or when cleared
  document.getElementById('staffForm').addEventListener('submit', function(e) {
    setTimeout(() => {
      document.getElementById('staffId').value = '';
      document.getElementById('staffSubmitBtn').textContent = 'Add Staff';
      document.getElementById('staffForm').setAttribute('action', 'add_staff.php');
    }, 500);
  });

  function generateReport() {
    let content = "Staff Report - Sameera Super\n\n";
    const rows = document.querySelectorAll('#staffTable tbody tr');
    rows.forEach((row, index) => {
      const cells = row.querySelectorAll('td');
      if (cells.length > 0) {
        content += `Staff ${index + 1}:\n`;
        content += `Name: ${cells[0].innerText}\n`;
        content += `Email: ${cells[1].innerText}\n`;
        content += `Phone: ${cells[2].innerText}\n`;
        content += `Role: ${cells[3].innerText}\n`;
        content += `Department: ${cells[4].innerText}\n\n`;
      }
    });

    const blob = new Blob([content], { type: "text/plain;charset=utf-8" });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'staff_report.txt';
    a.click();
  }
</script>

</body>
</html>
