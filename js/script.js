document.addEventListener("DOMContentLoaded", function () {
  const eyeIcon = document.getElementById("eye");
  const passwordField = document.getElementById("password");

  if (eyeIcon && passwordField) {
    eyeIcon.addEventListener("click", function () {
      if (passwordField.type === "password") {
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
});

$(document).ready(function () {
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

  $(".sidebar-overlay").on("click", function () {
    $(".sidebar").removeClass("show");
    $(".sidebar-overlay").removeClass("show");
  });

  $(document).on("click", function (e) {
    if ($(window).width() <= 768) {
      if (!$(e.target).closest(".sidebar, .sidebar-toggle").length) {
        $(".sidebar").removeClass("show");
        $(".sidebar-overlay").removeClass("show");
      }
    }
  });

  $(window).on("resize", function () {
    if ($(window).width() > 768) {
      $(".sidebar").removeClass("show");
      $(".sidebar-overlay").removeClass("show");
    }
  });
});
