<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sameera Super | Home</title>
  <link rel="stylesheet" href="home_u.css">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
<style>
  
</style>

</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg">
    Sameera Super
  </div>
  <ul class="nav-links" id="navLinks">
    <li><a href="home_u.php" class="active">Home</a></li>
    <li><a href="product.php">Products</a></li>
    <li>
      <a href="cart.php"> Cart
        <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
        <span class="cart-count"><?= $_SESSION['cart_count'] ?></span>
        <?php endif; ?>
      </a>
    </li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
  
</nav>

<!-- Hero -->
<section class="hero">
  <div class="hero-text">
    <h1>Groceries made easy.</h1>
    <p>Your local store, now online! Order fresh goods with a click.</p>
    <a href="product.php" class="cta-btn">Shop Now <i class='bx bx-right-arrow-alt'></i></a>
  </div>
  <div class="hero-img">
    <img src="../img/images/grocery-bag.png" alt="Grocery Bag">
  </div>
</section>

<!-- Features -->
<section class="features">
  <h2>Visit Us</h2>
  <div class="feature-grid">
    <a href="product.php" class="feature-box">
      📦 
      <h3>Products</h3>
      <p>Coordinate with suppliers easily.</p>
    </a>
    <a href="cart.php" class="feature-box">
      🛒 
      <h3>Cart</h3>
      <p>Track & manage stock in real-time.</p>
    </a>
    <a href="profile.php" class="feature-box">
      🤵‍♂ 
      <h3>Profile</h3>
      <p>Manage customer orders efficiently.</p>
    </a>
  </div>
</section>

<!-- Slider 1 -->
<div class="slider">
  <h2>Best Supermarket Experience </h2>
  <div class="sliders">
    <input type="radio" name="radio-btn1" id="radio1" checked>
    <input type="radio" name="radio-btn1" id="radio2">
    <input type="radio" name="radio-btn1" id="radio3">
    <input type="radio" name="radio-btn1" id="radio4">

    <div class="slide first">
      <img src="../img/slide_img/Gi_1.jpg" alt="">
    </div>
    <div class="slide">
      <img src="../img/slide_img/Gi_2.jpg" alt="">
    </div>
    <div class="slide">
      <img src="../img/slide_img/Gi_3.jpg" alt="">
    </div>
    <div class="slide">
      <img src="../img/slide_img/Gi_4.jpg" alt="">
    </div>

    <div class="navigation-auto">
      <div class="auto-btn1"></div>
      <div class="auto-btn2"></div>
      <div class="auto-btn3"></div>
      <div class="auto-btn4"></div>
    </div>
  </div>

  <div class="navigation-manual">
    <label for="radio1" class="manual-btn"></label>
    <label for="radio2" class="manual-btn"></label>
    <label for="radio3" class="manual-btn"></label>
    <label for="radio4" class="manual-btn"></label>
  </div>
</div>

<!-- Categories -->
<section class="categories">
  <h2>Explore Categories</h2>
  <div class="category-grid">
    <a href="product.php?category=Fruits" class="category-card">
      <img src="../img/images/fruits.png" alt="Fruits">
      <p>Fruits</p>
    </a>
    <a href="product.php?category=Vegetables" class="category-card">
      <img src="../img/images/vegetables.png" alt="Vegetables">
      <p>Vegetables</p>
    </a>
    <a href="product.php?category=Grocery" class="category-card">
      <img src="../img/images/grocery.png" alt="Grocery">
      <p>Grocery</p>
    </a>
    <a href="product.php?category=Dry Foods" class="category-card">
      <img src="../img/images/dairy.png" alt="Dry Foods">
      <p>Dry Foods</p>
    </a>
    <a href="product.php?category=Meat" class="category-card">
      <img src="../img/images/meat.png" alt="Meat">
      <p>Meat</p>
    </a>
  </div>
</section>


<!-- Slider 2 -->
<div class="slider">
  <h2>Get Our Offers</h2>
  <div class="sliders">
    <input type="radio" name="radio-btn2" id="radio5" checked>
    <input type="radio" name="radio-btn2" id="radio6">
    <input type="radio" name="radio-btn2" id="radio7">
    <input type="radio" name="radio-btn2" id="radio8">

    <div class="slide first">
      <img src="../img/slide_img/Yi_1.jpg" alt="">
    </div>
    <div class="slide">
      <img src="../img/slide_img/Yi_2.jpg" alt="">
    </div>
    <div class="slide">
      <img src="../img/slide_img/Yi_3.jpg" alt="">
    </div>
    <div class="slide">
      <img src="../img/slide_img/Yi_4.jpg" alt="">
    </div>

    <div class="navigation-auto">
      <div class="auto-btn5"></div>
      <div class="auto-btn6"></div>
      <div class="auto-btn7"></div>
      <div class="auto-btn8"></div>
    </div>
  </div>

  <div class="navigation-manual">
    <label for="radio5" class="manual-btn"></label>
    <label for="radio6" class="manual-btn"></label>
    <label for="radio7" class="manual-btn"></label>
    <label for="radio8" class="manual-btn"></label>
  </div>
</div>

<!-- Brand -->
<section class="brands">
  <h2>Shop by Brand</h2>
  <div class="brand-grid">
    <div class="brand-card">
      <img src="../img/images/Bi_1.png" alt="Maliban">
      <p>Maliban</p>
    </div>
    <div class="brand-card">
      <img src="../img/images/Bi_2.png" alt="KIST">
      <p>KIST</p>
    </div>
    <div class="brand-card">
      <img src="../img/images/Bi_3" alt="CBL Munchee">
      <p>CBL Munchee</p>
    </div>
    <div class="brand-card">
      <img src="../img/images/Bi_4.png" alt="Magic">
      <p>Magic</p>
    </div>
    <div class="brand-card">
      <img src="../img/images/Bi_5.jpg" alt="Kotmale">
      <p>Kotmale</p>
    </div>
    <div class="brand-card">
      <img src="../img/images/Bi_6.jpg" alt="Panda Baby">
      <p>Panda Baby</p>
    </div>
  </div>
</section>

<!-- Footer -->
<section class="footer">
  <div class="f-box">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg">
    <h2>Sameera Super</h2>
    <p style="max-width: 400px; text-align: justify;">Sameera Super is your trusted digital supermarket platform offering 
      smart, fast, and reliable shopping and inventory management solutions.</p>
    <div class="social">
      <a href="#"><i class='bx bxl-facebook-circle'></i></a>
      <a href="#"><i class='bx bxl-twitter' ></i></a>
      <a href="#"><i class='bx bxl-instagram-alt' ></i></a>
      <a href="#"><i class='bx bxl-whatsapp' ></i></a>
    </div>
  </div>

  <div class="f-box">
    <h3>Support</h3>
    <li><a href="product.php">Product</a></li>
    <li><a href="#">Help & Support</a></li>
    <li><a href="#">Feedback</a></li>
  </div>

  <div class="f-box">
    <h3>Contact</h3>
    <div class="contact">
      <span><i class='bx bxs-map' ></i> Ku/Dabadeniya </span>
      <span><i class='bx bxs-phone-call' ></i> 0011 155 555 </span>
      <span><i class='bx bxs-envelope' ></i> sameerasuper@gmail.com</span>
    </div>
  </div>
</section>

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

<!-- Slider 1 Auto-play -->
<script>
  let counter1 = 1;
  setInterval(function(){
    document.getElementById('radio' + counter1).checked = true;
    counter1++;
    if(counter1 > 4){
      counter1 = 1;
    }
  },5000);
</script>

<!-- Slider 2 Auto-play -->
<script>
  let counter2 = 5;
  setInterval(function(){
    document.getElementById('radio' + counter2).checked = true;
    counter2++;
    if(counter2 > 8){
      counter2 = 5;
    }
  },5000);
</script>

</body>
</html>
