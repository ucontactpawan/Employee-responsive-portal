<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/anniversary_functions.php';

// Get month filter from URL parameter
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');

// Get anniversaries based on filter
$employees = getAnniversaries($conn, $selectedMonth);
list($todaysAnniversaries, $upcomingAnniversaries, $recentAnniversaries) = categorizeAnniversaries($employees);

$currentMonth = date('F');
$todayDay = date('d');
$todayMonth = date('m');
$currentYear = date('Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Anniversaries - Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Base styles -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Component styles -->
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/anniversary.css">
    <script src="js/notifications.js"></script>
</head>

<body>
    <?php include('includes/navbar.php'); ?>
    <?php include('includes/sidebar.php'); ?>
    <div class="sidebar-overlay"></div>
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-award"></i>
                </div>
                <div class="header-text">
                    <h1>Work Anniversaries</h1>
                    <p>Celebrating professional milestones and achievements</p>
                </div>
            </div>
        </div>

        <div class="content-section">
            <!-- Search and Filter Bar -->
            <div class="filters-bar">
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search employees by name, department, or ID..." class="search-input" id="searchEmployees">
                </div>
                <div class="filter-container">
                    <select class="filter-select" id="monthFilter">
                        <option value="all" <?php echo ($selectedMonth === 'all') ? 'selected' : ''; ?>>All Months</option>
                        <option value="<?php echo date('m'); ?>" <?php echo ($selectedMonth === date('m')) ? 'selected' : ''; ?>>Current Month</option>
                        <option value="01" <?php echo ($selectedMonth === '01') ? 'selected' : ''; ?>>January</option>
                        <option value="02" <?php echo ($selectedMonth === '02') ? 'selected' : ''; ?>>February</option>
                        <option value="03" <?php echo ($selectedMonth === '03') ? 'selected' : ''; ?>>March</option>
                        <option value="04" <?php echo ($selectedMonth === '04') ? 'selected' : ''; ?>>April</option>
                        <option value="05" <?php echo ($selectedMonth === '05') ? 'selected' : ''; ?>>May</option>
                        <option value="06" <?php echo ($selectedMonth === '06') ? 'selected' : ''; ?>>June</option>
                        <option value="07" <?php echo ($selectedMonth === '07') ? 'selected' : ''; ?>>July</option>
                        <option value="08" <?php echo ($selectedMonth === '08') ? 'selected' : ''; ?>>August</option>
                        <option value="09" <?php echo ($selectedMonth === '09') ? 'selected' : ''; ?>>September</option>
                        <option value="10" <?php echo ($selectedMonth === '10') ? 'selected' : ''; ?>>October</option>
                        <option value="11" <?php echo ($selectedMonth === '11') ? 'selected' : ''; ?>>November</option>
                        <option value="12" <?php echo ($selectedMonth === '12') ? 'selected' : ''; ?>>December</option>
                    </select>
                </div>
            </div>

            <!-- Anniversary Cards Container -->
            <div class="anniversary-cards-container">
                <?php
                // Function to render anniversary card
                function renderAnniversaryCard($employee, $currentYear, $todayDay, $todayMonth, $isToday = false, $daysText = '')
                {
                    $joiningDay = date('d', strtotime($employee['joining_date']));
                    $joiningMonth = date('m', strtotime($employee['joining_date']));
                    $joiningYear = date('Y', strtotime($employee['joining_date']));
                    $yearsOfService = $currentYear - $joiningYear;

                    $cardClass = $isToday ? 'employee-card today-card' : 'employee-card';

                    // Generate simple clean avatars
                    $initials = '';
                    $nameParts = explode(' ', isset($employee['employee_name']) ? $employee['employee_name'] : $employee['name']);
                    foreach ($nameParts as $part) {
                        if (!empty($part)) {
                            $initials .= strtoupper(substr($part, 0, 1));
                        }
                    }

                    // Color selection based on gender if available
                    if (isset($employee['gender'])) {
                        $gender = $employee['gender'];
                    } else {
                        $gender = 'neutral';
                    }

                    if ($gender == 'male') {
                        $bgColor = 'f97316'; // Orange for male
                    } elseif ($gender == 'female') {
                        $bgColor = 'fb923c'; // Light orange for female
                    } else {
                        $bgColor = '6b7280'; // Gray for neutral
                    }

                    $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=" . $bgColor . "&color=fff";

                    $name = isset($employee['employee_name']) ? $employee['employee_name'] : $employee['name'];
                    $empId = isset($employee['employee_id']) ? $employee['employee_id'] : $employee['id'];
                    $dept = isset($employee['department']) ? $employee['department'] : '';

                ?>
                    <div class="<?php echo $cardClass; ?>">
                        <?php if ($isToday): ?>
                            <div class="anniversary-badge today-badge">TODAY</div>
                        <?php elseif ($daysText): ?>
                            <div class="days-counter"><?php echo $daysText; ?></div>
                        <?php endif; ?>
                        <div class="employee-avatar">
                            <img src="<?php echo $avatarUrl; ?>" alt="<?php echo htmlspecialchars($name); ?>">
                        </div>
                        <h3><?php echo htmlspecialchars($name); ?></h3>
                        <p class="employee-id">EMP<?php echo str_pad($empId, 3, '0', STR_PAD_LEFT); ?> <?php if ($dept): ?>• <?php echo htmlspecialchars(ucfirst($dept)); ?><?php endif; ?></p>
                        <div class="anniversary-info">
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo date('j F', strtotime($employee['joining_date'])); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-award"></i>
                                <span><?php echo $yearsOfService; ?> years of service</span>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>

                <!-- Today's Anniversaries Section -->
                <?php if (!empty($todaysAnniversaries)): ?>
                    <div class="anniversary-section">
                        <div class="section-header">
                            <div class="section-icon today-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <h2>Today's Anniversaries</h2>
                        </div>
                        <div class="anniversary-grid">
                            <?php foreach ($todaysAnniversaries as $employee): ?>
                                <?php renderAnniversaryCard($employee, $currentYear, $todayDay, $todayMonth, true); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Upcoming Anniversaries Section -->
                <?php if (!empty($upcomingAnniversaries)): ?>
                    <div class="anniversary-section">
                        <div class="section-header">
                            <div class="section-icon upcoming-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h2>Upcoming Anniversaries</h2>
                        </div>
                        <div class="anniversary-grid">
                            <?php foreach ($upcomingAnniversaries as $employee): ?>
                                <?php renderAnniversaryCard($employee, $currentYear, $todayDay, $todayMonth, false, $employee['days_text']); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Anniversaries Section -->
                <?php if (!empty($recentAnniversaries)): ?>
                    <div class="anniversary-section">
                        <div class="section-header">
                            <div class="section-icon recent-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <h2>Recent Anniversaries</h2>
                        </div>
                        <div class="anniversary-grid">
                            <?php foreach ($recentAnniversaries as $employee): ?>
                                <?php renderAnniversaryCard($employee, $currentYear, $todayDay, $todayMonth, false, $employee['days_text']); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- No Anniversaries Message -->
                <?php if (empty($todaysAnniversaries) && empty($upcomingAnniversaries) && empty($recentAnniversaries)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <img src="images/work-anniversary.png" alt="No anniversaries">
                        </div>
                        <h3>No anniversaries found</h3>
                        <p>There are no work anniversaries in the selected timeframe.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.js"></script>
    <script src="js/anniversary.js"></script>
    <script src="js/script.js"></script>
</body>

</html>