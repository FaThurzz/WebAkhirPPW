<?php
$base_url = '/ProjectAkhir/public_html/'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($page_title) ? $page_title : 'ThurzShop Marketplace'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Rowdies:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/style.css" />
</head>
<body>
<nav>
  <div class="nav-inner">
    <a href="<?php echo $base_url; ?>index.php" class="nav-logo">Thurz<span>Shop</span></a>
    <ul class="nav-links">
      <li><a href="<?php echo $base_url; ?>index.php" 
             class="<?php echo (isset($active_page) && $active_page == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
      <li><a href="#"
             class="<?php echo (isset($active_page) && $active_page == 'cektransaksi') ? 'active' : ''; ?>">Cek Transaksi</a></li>
      <li><a href="#"
             class="<?php echo (isset($active_page) && $active_page == 'review') ? 'active' : ''; ?>">Reviews</a></li>
    </ul>
    <div class="nav-right">
      <a href="<?php echo $base_url; ?>pages/login.php" class="btn btn-primary">Login</a>
    </div>
  </div>
</nav>