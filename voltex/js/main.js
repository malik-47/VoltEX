// ── Toast ─────────────────────────────────────────────────
function showToast(msg, type = 'success') {
  const toast = document.getElementById('toast');
  const toastMsg = document.getElementById('toast-msg');
  if (!toast) return;
  toastMsg.textContent = msg;
  toast.style.borderColor = type === 'error' ? 'rgba(255,59,92,0.4)' : 'rgba(0,212,255,0.3)';
  toast.style.color = type === 'error' ? '#fca5b5' : '#00D4FF';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

// ── Add to Cart ───────────────────────────────────────────
function addToCart(productId, qty = 1) {
  fetch('ajax/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&product_id=${productId}&qty=${qty}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('Added to cart! 🛒');
      // Update badge
      document.querySelectorAll('.cart-badge').forEach(b => b.textContent = data.count);
      if (data.count > 0) {
        document.querySelectorAll('.cart-badge').forEach(b => b.style.display = 'flex');
      }
    } else {
      showToast(data.message || 'Please login first', 'error');
      if (data.redirect) setTimeout(() => window.location = data.redirect, 1000);
    }
  });
}

// ── Toggle Wishlist ───────────────────────────────────────
function toggleWishlist(productId, btn) {
  fetch('ajax/wishlist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `product_id=${productId}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.classList.toggle('active', data.wishlisted);
      btn.textContent = data.wishlisted ? '❤️' : '🤍';
      showToast(data.wishlisted ? 'Added to wishlist ❤️' : 'Removed from wishlist');
    } else {
      showToast(data.message || 'Please login first', 'error');
      if (data.redirect) setTimeout(() => window.location = data.redirect, 1000);
    }
  });
}

// ── Quantity Controls ─────────────────────────────────────
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('qty-up')) {
    const span = e.target.parentElement.querySelector('span');
    const input = e.target.parentElement.querySelector('input');
    const max = parseInt(e.target.dataset.max || 99);
    let val = parseInt(span.textContent) + 1;
    if (val > max) val = max;
    span.textContent = val;
    if (input) input.value = val;
  }
  if (e.target.classList.contains('qty-down')) {
    const span = e.target.parentElement.querySelector('span');
    const input = e.target.parentElement.querySelector('input');
    let val = parseInt(span.textContent) - 1;
    if (val < 1) val = 1;
    span.textContent = val;
    if (input) input.value = val;
  }
});

// ── Cart Item Remove ──────────────────────────────────────
function removeCartItem(productId) {
  fetch('ajax/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=remove&product_id=${productId}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const row = document.getElementById('cart-row-' + productId);
      if (row) row.style.display = 'none';
      document.querySelectorAll('.cart-badge').forEach(b => b.textContent = data.count);
      showToast('Item removed');
      setTimeout(() => location.reload(), 500);
    }
  });
}

// ── Price Filter ──────────────────────────────────────────
const priceRange = document.getElementById('price-range');
const priceVal   = document.getElementById('price-val');
if (priceRange && priceVal) {
  priceRange.addEventListener('input', function() {
    priceVal.textContent = 'Rs. ' + parseInt(this.value).toLocaleString();
  });
}

// ── Scroll reveal ─────────────────────────────────────────
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.product-card, .cat-card, .dash-stat').forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(20px)';
  el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
  observer.observe(el);
});
