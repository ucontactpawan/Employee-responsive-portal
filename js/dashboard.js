$(document).ready(function () {
  function loadDashboardData() {
    $(".card").addClass("loading");

    $.ajax({
      url: "includes/get_dashboard_data.php",
      type: "GET",
      dataType: "json",
      timeout: 10000,
      success: function (response) {
        $(".card").removeClass("loading");
        if (response.status === "success") {
          updateDashboardUI(response.data);
        }
      },
      error: function (xhr, status, error) {
        $(".card").removeClass("loading");
        if (status === "timeout") {
          setTimeout(loadDashboardData, 5000);
        }
      },
    });
  }

  function updateDashboardUI(data) {
    $("#totalEmployees").text(data.total_employees);
    $("#onTimeToday").text(data.on_time_today);
    $("#lateToday").text(data.late_today);
    $("#onTimePercent").text(data.on_time_percent + "%");

    updateProgressCircle(
      ".card:nth-child(2) .circle-progress svg circle:last-child",
      data.on_time_today,
      data.total_employees
    );
    updateProgressCircle(
      ".card:nth-child(3) .circle-progress svg circle:last-child",
      data.late_today,
      data.total_employees
    );
    updateProgressCircle(
      ".card:nth-child(4) .circle-progress svg circle:last-child",
      data.on_time_percent,
      100
    );
  }

  function updateProgressCircle(selector, value, max) {
    const circle = $(selector);
    if (circle.length > 0) {
      const percentage = max > 0 ? (value / max) * 100 : 0;
      const circumference = 2 * Math.PI * 25;
      const offset = circumference - (percentage / 100) * circumference;

      circle.css({
        "stroke-dasharray": circumference,
        "stroke-dashoffset": offset,
      });
    }
  }

  loadDashboardData();
  setInterval(loadDashboardData, 30000);

  window.refreshDashboard = function () {
    loadDashboardData();
  };

  $(".sidebar-toggle").on("click", function (e) {
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
