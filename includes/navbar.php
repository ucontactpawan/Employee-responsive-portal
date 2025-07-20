<!-- responsive navbar  -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <button class="navbar-toggler sidebar-toggle" type="button">
      <i class="fas fa-bars"></i>
    </button>



    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto align-items-center">
        <!-- Notification Bell -->
        <li class="nav-item notification-item me-3">
          <a href="#" class="notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notification-badge"></span>
          </a>
        </li>

        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle user-profile" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="user-avatar">
              <?php
              $userName = $_SESSION['user_name'];
              // Generate a random seed based on username for consistent avatar
              $seed = crc32($userName);
              $avatarUrl = "https://i.pravatar.cc/150?u=" . urlencode($userName) . "&img=" . ($seed % 70 + 1);
              ?>
              <img src="<?php echo $avatarUrl; ?>" alt="<?php echo htmlspecialchars($userName); ?>" />
            </div>
            <div class="user-info">
              <div class="user-name"><?php echo $_SESSION['user_name']; ?></div>

            </div>
            <i class="fas fa-chevron-down dropdown-arrow"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> My Profile</a></li>
            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
            <li><a class="dropdown-item" href="#"><i class="fas fa-question-circle me-2"></i> Help & Support</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>