$(document).ready(function () {
  console.log("Dashboard.js loaded - v2");
  console.log("jQuery version:", $.fn.jquery);

  // Check if elements exist
  console.log("Sidebar elements found:", $(".sidebar").length);
  console.log("Toggle button found:", $(".sidebar-toggle").length);
  console.log("Overlay found:", $(".sidebar-overlay").length);

  // Sidebar toggle functionality
  $(".sidebar-toggle").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    console.log("Hamburger clicked!");

    var $sidebar = $(".sidebar");
    var $overlay = $(".sidebar-overlay");

    console.log(
      "Sidebar has show class before toggle:",
      $sidebar.hasClass("show")
    );

    if ($sidebar.hasClass("show")) {
      $sidebar.removeClass("show");
      $overlay.removeClass("show");
      console.log("Sidebar hidden");
    } else {
      $sidebar.addClass("show");
      $overlay.addClass("show");
      console.log("Sidebar shown");
    }

    console.log(
      "Sidebar has show class after toggle:",
      $sidebar.hasClass("show")
    );
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
