<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('includes/db.php');

?>
<!-- Dashboard page -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <?php include('includes/navbar.php'); ?>
    <?php include('includes/sidebar.php'); ?>
    <div class="sidebar-overlay"></div>

    <div class="main-content">
        <div class="header">
            <h2>Dashboard</h2>
            <p>Welcome to Simple Attendance Management System</p>
        </div>

        <div class="card-container">
            <div class="card">
                <div class="card-icon"><i class="fa fa-user fa-3x"></i></div>
                <p class="card-label">TOTAL EMPLOYEES</p>
                <h3 id="totalEmployees">-</h3>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa fa-check-square fa-3x"></i></div>
                <p class="card-label">ON TIME TODAY</p>
                <div class="circle-progress">
                    <svg width="60" height="60">
                        <circle cx="30" cy="30" r="25" stroke="#e0e0e0" stroke-width="5" fill="none" />
                        <circle cx="30" cy="30" r="25" stroke="#009688" stroke-width="5" fill="none" stroke-dasharray="157" stroke-dashoffset="157" />
                    </svg>
                </div>
                <h3 id="onTimeToday">-</h3>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa fa-exclamation-triangle fa-3x"></i></div>
                <p class="card-label">LATE TODAY</p>
                <div class="circle-progress">
                    <svg width="60" height="60">
                        <circle cx="30" cy="30" r="25" stroke="#e0e0e0" stroke-width="5" fill="none" />
                        <circle cx="30" cy="30" r="25" stroke="#009688" stroke-width="5" fill="none" stroke-dasharray="157" stroke-dashoffset="157" />
                    </svg>
                </div>
                <h3 id="lateToday">-</h3>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa fa-clock fa-3x"></i></div>
                <p class="card-label">ON TIME PERCENTAGE</p>
                <div class="circle-progress">
                    <svg width="60" height="60">
                        <circle cx="30" cy="30" r="25" stroke="#e0e0e0" stroke-width="5" fill="none" />
                        <circle cx="30" cy="30" r="25" stroke="#009688" stroke-width="5" fill="none" stroke-dasharray="157" stroke-dashoffset="157" />
                    </svg>
                </div>
                <h3 id="onTimePercent">-</h3>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mt-4">
            <!-- Today's Attendance Distribution Chart -->
            <div class="col-md-6 mb-4">
                <div class="chart-container">
                    <div class="chart-header">
                        <span class="chart-icon"><i class="fas fa-chart-pie"></i></span>
                        <h4>Today's Attendance Distribution</h4>
                    </div>
                    <div class="chart-body">
                        <canvas id="attendanceDistribution"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-color on-time"></span>
                            <span class="legend-label">On Time: <span id="legendOnTime">0</span></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color late"></span>
                            <span class="legend-label">Late: <span id="legendLate">0</span></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color absent"></span>
                            <span class="legend-label">Absent: <span id="legendAbsent">0</span></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Weekly Attendance Trend Chart -->
            <div class="col-md-6 mb-4">
                <div class="chart-container">
                    <div class="chart-header">
                        <span class="chart-icon"><i class="fas fa-chart-bar"></i></span>
                        <h4>Weekly Attendance Trend</h4>
                    </div>
                    <div class="chart-body">
                        <canvas id="weeklyAttendanceTrend"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('includes/footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/script.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/notifications.js"></script>
</body>

</html>