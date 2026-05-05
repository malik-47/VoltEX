<footer class="footer">
  <div class="container">
    <div class="footer-main">
      <div>
        <div class="footer-brand">VOLT<span>EX</span></div>
        <p class="footer-desc">Pakistan's most advanced electronics store. Cutting-edge gadgets, unbeatable prices, and lightning-fast delivery to your door.</p>
        <div class="footer-social">
          <div class="social-btn">📘</div>
          <div class="social-btn">🐦</div>
          <div class="social-btn">📸</div>
          <div class="social-btn">▶️</div>
        </div>
      </div>
      <div class="footer-col">
        <h4>Shop</h4>
        <ul>
          <li><a href="<?= SITE_URL ?>/shop.php">All Products</a></li>
          <li><a href="<?= SITE_URL ?>/shop.php?cat=smartphones">Smartphones</a></li>
          <li><a href="<?= SITE_URL ?>/shop.php?cat=laptops">Laptops</a></li>
          <li><a href="<?= SITE_URL ?>/shop.php?cat=headphones">Headphones</a></li>
          <li><a href="<?= SITE_URL ?>/shop.php?featured=1">New Arrivals</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Account</h4>
        <ul>
          <li><a href="<?= SITE_URL ?>/login.php">Sign In</a></li>
          <li><a href="<?= SITE_URL ?>/register.php">Create Account</a></li>
          <li><a href="<?= SITE_URL ?>/account.php">My Orders</a></li>
          <li><a href="<?= SITE_URL ?>/wishlist.php">Wishlist</a></li>
          <li><a href="<?= SITE_URL ?>/cart.php">Cart</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="#">Help Center</a></li>
          <li><a href="#">Shipping Info</a></li>
          <li><a href="#">Returns</a></li>
          <li><a href="#">Warranty</a></li>
          <li><a href="#">Contact Us</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> VOLTEX Electronics. All Rights Reserved.</span>
      <span style="display:flex;gap:20px;">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="<?= SITE_URL ?>/admin/login.php" style="color:var(--text2);">Admin</a>
      </span>
    </div>
  </div>
</footer>

<script src="<?= SITE_URL ?>/js/main.js"></script>
</body>
</html>
