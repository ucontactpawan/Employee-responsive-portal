<!-- responsive navbar  -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <button class="navbar-toggler sidebar-toggle" type="button">
      <i class="fas fa-bars"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <span class="nav-link user-name">
            <div class="user-avatar">
              <i class="fas fa-user"></i>
            </div>
            <span class="user-text"><?php echo $_SESSION['user_name']; ?></span>
          </span>
        </li>
      </ul>
    </div>
  </div>
</nav>