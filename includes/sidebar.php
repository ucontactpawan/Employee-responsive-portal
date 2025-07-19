<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>Attendance</h2>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php' ? 'active' : ''); ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="employee.php" class="<?php echo ($current_page == 'employee.php' ? 'active' : ''); ?>">
                <i class="fas fa-users"></i>
                <span>Employees</span>
            </a>
        </li>
        <li>
            <a href="attendance_sheet.php" class="<?php echo ($current_page == 'attendance_sheet.php' ? 'active' : ''); ?>">
                <i class="fas fa-clipboard-check"></i>
                <span>Attendance Sheet</span>
            </a>
        </li>
        <li>
            <a href="attendance_logs.php" class="<?php echo ($current_page == 'attendance_logs.php' ? 'active' : ''); ?>">
                <i class="fas fa-history"></i>
                <span>Attendance Logs</span>
            </a>
        </li>
        <li>
            <a href="birthday.php" class="<?php echo ($current_page == 'birthday.php' ? 'active' : ''); ?>">
                <i class="fas fa-birthday-cake"></i>
                <span>Birthday</span>
            </a>
        </li>
        <li>
            <a href="anniversary.php" class="<?php echo ($current_page == 'anniversary.php' ? 'active' : ''); ?>">
                <i class="fas fa-award"></i>
                <span>Anniversary</span>
            </a>
        </li>
    </ul>
</div>