$(document).ready(function () {
  // Store chart instances so we can destroy and recreate them
  let attendanceDistributionChart = null;
  let weeklyAttendanceTrendChart = null;

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
          if (response.data.chart_data) {
            updateCharts(response.data.chart_data);
          }
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

    // Update legend values
    $("#legendOnTime").text(data.on_time_today);
    $("#legendLate").text(data.late_today);
    $("#legendAbsent").text(data.absent_today);

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

  function updateCharts(chartData) {
    if (!chartData) return;

    // Update Today's Attendance Distribution chart
    updateAttendanceDistributionChart(chartData.today_distribution);

    // Update Weekly Attendance Trend chart
    updateWeeklyAttendanceTrendChart(chartData.weekly_trend);
  }

  function updateAttendanceDistributionChart(data) {
    if (!data) return;

    const ctx = document.getElementById("attendanceDistribution");
    if (!ctx) return;

    // Destroy previous chart instance if it exists
    if (attendanceDistributionChart) {
      attendanceDistributionChart.destroy();
    }

    // Create new chart
    attendanceDistributionChart = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["On Time", "Late", "Absent"],
        datasets: [
          {
            data: [data.on_time, data.late, data.absent],
            backgroundColor: ["#10B981", "#F59E0B", "#EF4444"],
            borderWidth: 0,
            cutout: "70%",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const label = context.label || "";
                const value = context.raw || 0;
                const total = context.chart.data.datasets[0].data.reduce(
                  (a, b) => a + b,
                  0
                );
                const percentage = Math.round((value / total) * 100);
                let tooltipText = `${label}: ${value}`;

                // Add descriptions based on label
                if (label === "On Time") {
                  tooltipText += " (Before 9:30 AM)";
                } else if (label === "Late") {
                  tooltipText += " (After 9:30 AM)";
                }

                return `${tooltipText} (${percentage}%)`;
              },
            },
          },
        },
      },
    });
  }

  function updateWeeklyAttendanceTrendChart(data) {
    if (!data) return;

    const ctx = document.getElementById("weeklyAttendanceTrend");
    if (!ctx) return;

    // Prepare data for the chart
    const labels = Object.keys(data);
    const onTimeData = labels.map((day) => data[day].on_time);
    const lateData = labels.map((day) => data[day].late);
    const absentData = labels.map((day) => data[day].absent);

    // Destroy previous chart instance if it exists
    if (weeklyAttendanceTrendChart) {
      weeklyAttendanceTrendChart.destroy();
    }

    // Create new chart
    weeklyAttendanceTrendChart = new Chart(ctx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "On Time",
            data: onTimeData,
            backgroundColor: "#10B981",
            borderWidth: 0,
          },
          {
            label: "Late",
            data: lateData,
            backgroundColor: "#F59E0B",
            borderWidth: 0,
          },
          {
            label: "Absent",
            data: absentData,
            backgroundColor: "#EF4444",
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            stacked: true,
            grid: {
              display: false,
            },
          },
          y: {
            stacked: true,
            grid: {
              color: "#f0f0f0",
            },
            ticks: {
              stepSize: 2,
            },
          },
        },
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            mode: "index",
            callbacks: {
              title: function (context) {
                return context[0].label; // Day of week
              },
              label: function (context) {
                const label = context.dataset.label || "";
                const value = context.raw || 0;

                if (label === "On Time") {
                  return `${label}: ${value} (Before 9:30 AM)`;
                } else if (label === "Late") {
                  return `${label}: ${value} (After 9:30 AM)`;
                } else {
                  return `${label}: ${value}`;
                }
              },
            },
          },
        },
      },
    });
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
