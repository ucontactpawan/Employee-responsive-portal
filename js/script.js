// login and register js code start here

$(document).ready(function () {
  // Only run this code if the elements exist (login/register pages)
  const eyeIcon = document.getElementById("eye");
  const passwordField = document.getElementById("password");

  if (eyeIcon && passwordField) {
    eyeIcon.addEventListener("click", () => {
      if (passwordField.type === "password" && passwordField.value) {
        passwordField.type = "text";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
      } else {
        passwordField.type = "password";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
      }
    });
  }

  //  login and register js code end here

  // Sidebar toggle functionality for responsive design
  console.log("Sidebar functionality initialized");

  // Sidebar toggle functionality
  $(document).on("click", ".sidebar-toggle", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var $sidebar = $(".sidebar");
    var $overlay = $(".sidebar-overlay");

    if ($sidebar.hasClass("show")) {
      $sidebar.removeClass("show");
      $overlay.removeClass("show");
    } else {
      $sidebar.addClass("show");
      $overlay.addClass("show");
    }
  });

  // Click overlay to close sidebar
  $(".sidebar-overlay").on("click", function () {
    console.log("Overlay clicked");
    $(".sidebar").removeClass("show");
    $(".sidebar-overlay").removeClass("show");
  });

  // Close sidebar when clicking outside on mobile
  $(document).on("click", function (e) {
    if ($(window).width() <= 768) {
      if (!$(e.target).closest(".sidebar, .sidebar-toggle").length) {
        $(".sidebar").removeClass("show");
        $(".sidebar-overlay").removeClass("show");
      }
    }
  });

  // Handle window resize
  $(window).on("resize", function () {
    if ($(window).width() > 768) {
      $(".sidebar").removeClass("show");
      $(".sidebar-overlay").removeClass("show");
    }
  });

  // Debug function to test manually
  window.testSidebar = function () {
    var $sidebar = $(".sidebar");
    var $overlay = $(".sidebar-overlay");

    $sidebar.toggleClass("show");
    $overlay.toggleClass("show");

    console.log("Manual test - sidebar show class:", $sidebar.hasClass("show"));
    console.log("Manual test - current transform:", $sidebar.css("transform"));
  };

  // Function to simulate click on hamburger
  window.clickHamburger = function () {
    $(".sidebar-toggle").click();
  };
});
