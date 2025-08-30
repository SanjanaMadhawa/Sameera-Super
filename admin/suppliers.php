<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

// ---------------- SUPPLIER CRUD ----------------
if (isset($_POST['add_supplier'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $products = $_POST['products'];

    $stmt = $conn->prepare("INSERT INTO suppliers (name, contact, address, products) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $contact, $address, $products);
    $stmt->execute();
    header("Location: suppliers.php");
    exit;
}

if (isset($_POST['update_supplier'])) {
    $id = $_POST['supplier_id'];
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $products = $_POST['products'];

    $stmt = $conn->prepare("UPDATE suppliers SET name=?, contact=?, address=?, products=? WHERE supplier_id=?");
    $stmt->bind_param("ssssi", $name, $contact, $address, $products, $id);
    $stmt->execute();
    header("Location: suppliers.php");
    exit;
}

if (isset($_GET['delete_supplier'])) {
    $id = intval($_GET['delete_supplier']);
    $conn->query("DELETE FROM suppliers WHERE supplier_id = $id");
    header("Location: suppliers.php");
    exit;
}

// ---------------- PURCHASE HISTORY ----------------
if (isset($_POST['add_purchase'])) {
    $supplier_id = $_POST['supplier_id'];
    $date = $_POST['date'];
    $items = $_POST['items'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO supplier_purchases (supplier_id, date, items, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $supplier_id, $date, $items, $price);
    $stmt->execute();
    header("Location: suppliers.php");
    exit;
}

if (isset($_POST['update_purchase'])) {
    $id = $_POST['purchase_id'];
    $supplier_id = $_POST['supplier_id'];
    $date = $_POST['date'];
    $items = $_POST['items'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("UPDATE supplier_purchases SET supplier_id=?, date=?, items=?, price=? WHERE purchase_id=?");
    $stmt->bind_param("issdi", $supplier_id, $date, $items, $price, $id);
    $stmt->execute();
    header("Location: suppliers.php");
    exit;
}

if (isset($_GET['delete_purchase'])) {
    $id = intval($_GET['delete_purchase']);
    $conn->query("DELETE FROM supplier_purchases WHERE purchase_id = $id");
    header("Location: suppliers.php");
    exit;
}

// ---------------- SUPPLIER PERFORMANCE ----------------
if (isset($_POST['add_performance'])) {
    $supplier_id = $_POST['supplier_id'];
    $on_time = $_POST['on_time'];
    $issues = $_POST['issues'];
    $rating = $_POST['rating'];

    $stmt = $conn->prepare("INSERT INTO supplier_performance (supplier_id, on_time_deliveries, issue_reports, rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $supplier_id, $on_time, $issues, $rating);
    $stmt->execute();
    header("Location: suppliers.php");
    exit;
}

if (isset($_POST['update_performance'])) {
    $id = $_POST['performance_id'];
    $supplier_id = $_POST['supplier_id'];
    $on_time = $_POST['on_time'];
    $issues = $_POST['issues'];
    $rating = $_POST['rating'];

    $stmt = $conn->prepare("UPDATE supplier_performance SET supplier_id=?, on_time_deliveries=?, issue_reports=?, rating=? WHERE performance_id=?");
    $stmt->bind_param("iiisi", $supplier_id, $on_time, $issues, $rating, $id);
    $stmt->execute();
    header("Location: suppliers.php");
    exit;
}

if (isset($_GET['delete_performance'])) {
    $id = intval($_GET['delete_performance']);
    $conn->query("DELETE FROM supplier_performance WHERE performance_id = $id");
    header("Location: suppliers.php");
    exit;
}

// ---------------- FETCH DATA ----------------
$suppliers = $conn->query("SELECT * FROM suppliers");
$purchases = $conn->query("SELECT p.purchase_id, p.date, p.items, p.price, s.name 
                           FROM supplier_purchases p 
                           JOIN suppliers s ON p.supplier_id = s.supplier_id");
$performances = $conn->query("SELECT pf.performance_id, s.name, pf.on_time_deliveries, pf.issue_reports, pf.rating 
                              FROM supplier_performance pf 
                              JOIN suppliers s ON pf.supplier_id = s.supplier_id");

// ---------------- EDIT FLAGS ----------------
$editing_supplier = $editing_purchase = $editing_perf = false;
$editSupplierData = $editPurchaseData = $editPerfData = null;

if (isset($_GET['edit_supplier'])) {
    $id = intval($_GET['edit_supplier']);
    $res = $conn->query("SELECT * FROM suppliers WHERE supplier_id = $id");
    if ($res->num_rows > 0) { $editSupplierData = $res->fetch_assoc(); $editing_supplier = true; }
}

if (isset($_GET['edit_purchase'])) {
    $id = intval($_GET['edit_purchase']);
    $res = $conn->query("SELECT * FROM supplier_purchases WHERE purchase_id = $id");
    if ($res->num_rows > 0) { $editPurchaseData = $res->fetch_assoc(); $editing_purchase = true; }
}

if (isset($_GET['edit_performance'])) {
    $id = intval($_GET['edit_performance']);
    $res = $conn->query("SELECT * FROM supplier_performance WHERE performance_id = $id");
    if ($res->num_rows > 0) { $editPerfData = $res->fetch_assoc(); $editing_perf = true; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sameera Super | Suppliers</title>
    <link rel="stylesheet" href="style1.css">
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
    <li><a href="suppliers.php" class="active">Suppliers</a></li>
    <li><a href="orders.php">Orders</a></li>
    <li><a href="customers.php">Customers</a></li>
    <li><a href="staff.php">Staff</a></li>
    <li><a href="login.html">Login</a></li>
  </ul>
</nav>
<div class="container">

<!-- Supplier Management -->
<div class="section-box">
  <h2>Supplier Management</h2>
  <form class="form" method="POST">
    <input type="hidden" name="supplier_id" value="<?= $editing_supplier ? $editSupplierData['supplier_id'] : '' ?>">
    <input type="text" name="name" placeholder="Supplier Name" required value="<?= $editing_supplier ? $editSupplierData['name'] : '' ?>">
    <input type="text" name="contact" placeholder="Contact Number" required value="<?= $editing_supplier ? $editSupplierData['contact'] : '' ?>">
    <input type="text" name="address" placeholder="Address" required value="<?= $editing_supplier ? $editSupplierData['address'] : '' ?>">
    <input type="text" name="products" placeholder="Products Supplied" required value="<?= $editing_supplier ? $editSupplierData['products'] : '' ?>">
    <button type="submit" name="<?= $editing_supplier ? 'update_supplier' : 'add_supplier' ?>">
      <?= $editing_supplier ? 'Update Supplier': 'Add Supplier' ?>
    </button>
  </form>
  <table>
    <thead><tr><th>ID</th><th>Name</th><th>Contact</th><th>Address</th><th>Products</th><th>Actions</th></tr></thead>
    <tbody>
<?php while($row = $suppliers->fetch_assoc()): ?>
    <tr>
      <td><?= $row['supplier_id'] ?></td>
      <td><?= $row['name'] ?></td>
      <td><?= $row['contact'] ?></td> 
      <td><?= $row['address'] ?></td>
      <td><?= $row['products'] ?></td>
      <td>
        <a href="?edit_supplier=<?= $row['supplier_id'] ?>"><button class="action-btn">Edit</button></a>
        <a href="?delete_supplier=<?= $row['supplier_id'] ?>" onclick="return confirm('Are you sure?')"><button class="action-btn delete-btn">Delete</button></a>
      </td>
    </tr>
<?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Supplier Purchase History -->
<div class="section-box">
  <h2>Supplier Purchase History</h2>
  <form class="form" method="POST">
    <input type="hidden" name="purchase_id" value="<?= $editing_purchase ? $editPurchaseData['purchase_id'] : '' ?>">
    <select name="supplier_id" required>
      <option value="">Select Supplier</option>
      <?php $suppliersList = $conn->query("SELECT * FROM suppliers");
      while($s = $suppliersList->fetch_assoc()): ?>
      <option value="<?= $s['supplier_id'] ?>" <?= $editing_purchase && $s['supplier_id']==$editPurchaseData['supplier_id'] ? 'selected':'' ?>><?= $s['name'] ?></option>
    <?php endwhile; ?>
    </select>
    <input type="date" name="date" required value="<?= $editing_purchase ? $editPurchaseData['date'] : '' ?>">
    <input type="text" name="items" placeholder="Items Purchased" required value="<?= $editing_purchase ? $editPurchaseData['items'] : '' ?>">
    <input type="number" step="0.01" name="price" placeholder="Price" required value="<?= $editing_purchase ? $editPurchaseData['price'] : '' ?>">
    <button type="submit" name="<?= $editing_purchase ? 'update_purchase' : 'add_purchase' ?>">
      <?= $editing_purchase ? 'Update Purchase': 'Add Purchase' ?>
    </button>
  </form>
  <table>
    <thead><tr><th>Date</th><th>Supplier</th><th>Items</th><th>Price</th><th>Actions</th></tr></thead>
    <tbody>
  <?php while($p = $purchases->fetch_assoc()): ?>
    <tr>
      <td><?= $p['date'] ?></td>
      <td><?= $p['name'] ?></td>
      <td><?= $p['items'] ?></td>
      <td><?= number_format($p['price'],2) ?></td>
      <td>
        <a href="?edit_purchase=<?= $p['purchase_id'] ?>"><button class="action-btn">Edit</button></a>
        <a href="?delete_purchase=<?= $p['purchase_id'] ?>" onclick="return confirm('Delete this record?')"><button class="action-btn delete-btn">Delete</button></a>
      </td>
    </tr>
  <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Supplier Performance -->
<div class="section-box">
  <h2>Supplier Performance</h2>
  <form class="form" method="POST">
    <input type="hidden" name="performance_id" value="<?= $editing_perf ? $editPerfData['performance_id'] : '' ?>">
    <select name="supplier_id" required>
      <option value="">Select Supplier</option>
  <?php 
      $suppliersList2 = $conn->query("SELECT * FROM suppliers");
      while($s2 = $suppliersList2->fetch_assoc()): ?>
      <option value="<?= $s2['supplier_id'] ?>" <?= $editing_perf && $s2['supplier_id']==$editPerfData['supplier_id'] ? 'selected':'' ?>>
        <?= $s2['name'] ?>
      </option>
  <?php endwhile; ?>
    </select>
    <input type="number" name="on_time" placeholder="On-time Deliveries" required value="<?= $editing_perf ? $editPerfData['on_time_deliveries'] : '' ?>">
    <input type="number" name="issues" placeholder="Issue Reports" required value="<?= $editing_perf ? $editPerfData['issue_reports'] : '' ?>">
    <select name="rating" required>
      <option value="">Select Rating</option>
      <option value="Excellent" <?= $editing_perf && $editPerfData['rating']=='Excellent' ? 'selected' : '' ?>>Excellent</option>
      <option value="Good" <?= $editing_perf && $editPerfData['rating']=='Good' ? 'selected' : '' ?>>Good</option>
      <option value="Bad" <?= $editing_perf && $editPerfData['rating']=='Bad' ? 'selected' : '' ?>>Bad</option>
    </select>
    <button type="submit" name="<?= $editing_perf ? 'update_performance' : 'add_performance' ?>">
      <?= $editing_perf ? 'Update Performance': 'Add Performance' ?>
    </button>
  </form>
  <table>
    <thead>
      <tr>
        <th>Supplier</th>
        <th>On-time Deliveries</th>
        <th>Issue Reports</th>
        <th>Rating</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
  <?php while($pf = $performances->fetch_assoc()): ?>
    <tr>
      <td><?= $pf['name'] ?></td>
      <td><?= $pf['on_time_deliveries'] ?></td>
      <td><?= $pf['issue_reports'] ?></td>
      <td><?= $pf['rating'] ?></td>
      <td>
        <a href="?edit_performance=<?= $pf['performance_id'] ?>"><button class="action-btn">Edit</button></a>
        <a href="?delete_performance=<?= $pf['performance_id'] ?>" onclick="return confirm('Delete this record?')"><button class="action-btn delete-btn">Delete</button></a>
      </td>
    </tr>
  <?php endwhile; ?>
    </tbody>
  </table>  
</div>    

<!-- Supplier Report Section -->
<div class="section-box">
  <div class="report-section">
    <h3>Generate Supplier Reports</h3>
    <form method="POST" action="suppliersreport.php">
      <label for="report_type">Choose report type:</label>
      <select name="report_type" id="report_type" required>
        <option value="suppliers">Supplier List</option>
        <option value="purchases">Supplier Purchases</option>
        <option value="performance">Supplier Performance</option>
      </select>
      <button type="submit" name="report_csv">Download CSV Report</button>
    </form>
  </div>
</div>  

<!-- Active navbar link -->
<script>
  const currentPage = window.location.pathname.split("/").pop();
  const navLinks = document.querySelectorAll(".nav-links a");

  navLinks.forEach(link => {
    const linkPage = link.getAttribute("href");
    if (linkPage === currentPage) {
      link.classList.add("active");
    }
  });
</script>
</body>
</html>
