<?php
session_start();
$conn = new mysqli("localhost","root","","sameera_super");
$user_id = 1;

$res = $conn->query("
  SELECT c.product_id, c.quantity, i.price 
  FROM cart c JOIN inventory i ON c.product_id=i.inventory_id
  WHERE c.user_id=$user_id");

$total = 0;
while($c = $res->fetch_assoc()) $total += $c['quantity'] * $c['price'];

$conn->query("INSERT INTO orders(user_id,total_amount) VALUES($user_id,$total)");
$order_id = $conn->insert_id;
$res->data_seek(0);

while($c = $res->fetch_assoc()){
  $conn->query("INSERT INTO order_items(order_id, product_id, quantity, price)
    VALUES($order_id, {$c['product_id']}, {$c['quantity']}, {$c['price']})");
}

$conn->query("DELETE FROM cart WHERE user_id=$user_id");

header("Location: inventory.php"); // redirect to admin side orders.php
exit;
