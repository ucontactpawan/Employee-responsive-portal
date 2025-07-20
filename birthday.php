<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/birthday_functions.php';

// Get month filter from URL parameter
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');

// Get birthdays based on filter
if ($selectedMonth === 'all') {
    $birthdays = getAllBirthdays();
} else if ($selectedMonth === date('m')) {
    // For current month, use the organized getBirthdays function
    $birthdays = getBirthdays();
} else {
    // Get birthdays for specific month
    global $conn;
    $query = "SELECT DISTINCT
        e.id as employee_id,
        e.name as employee_name,
        e.email,
        e.contact,
        e.gender,
        e.employee_type as department,
        e.joining_date,
        ed.dob,
        ed.father_name,
        ed.mother_name,
        ed.city,
        ed.state,
        ed.address,
        DAY(ed.dob) as birth_day,
        MONTH(ed.dob) as birth_month,
        YEAR(ed.dob) as birth_year
    FROM employee_details ed
    JOIN employees e ON e.id = ed.employee_id
    WHERE ed.dob IS NOT NULL 
    AND MONTH(ed.dob) = ?
    GROUP BY e.id, e.name, e.email, e.contact, e.gender, e.employee_type, e.joining_date, ed.dob
    ORDER BY DAY(ed.dob)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $selectedMonth);
    $stmt->execute();
    $birthdays = $stmt->get_result();
}
if (!$birthdays) {
    echo "Error fetching birthdays.";
    exit;
}

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
    <title>Birthday Celebrations - Portal</title>
    <link rel="stylesheet" href="css/birthday.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Base styles -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Component styles -->
    <link rel="stylesheet" href="css/navbar.css">
    <script src="js/notifications.js"></script>
</head>

<body>


    <!-- "Today's Birthdays": Only employees whose birthday is today.
"Upcoming Birthdays": Only employees whose birthday is after today (in this month).
"Recent Birthdays": Only employees whose birthday is before today (in this month). -->


    <?php include('includes/navbar.php'); ?>
    <?php include('includes/sidebar.php'); ?>
    <div class="sidebar-overlay"></div>
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-birthday-cake"></i>
                </div>
                <div class="header-text">
                    <h1>Employee Birthdays</h1>
                    <p>Celebrate your team members' special days</p>
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

            <!-- Birthday Cards Container -->
            <div class="birthday-cards-container" id="birthdayResults">
                <?php
                // Separate birthdays into categories
                $todayBirthdays = [];
                $upcomingBirthdays = [];
                $recentBirthdays = [];
                $otherBirthdays = [];

                // Store all birthdays in arrays for processing
                $allBirthdays = [];
                while ($birthday = $birthdays->fetch_assoc()) {
                    $allBirthdays[] = $birthday;
                }

                // Categorize birthdays
                foreach ($allBirthdays as $birthday) {
                    $dobDay = date('d', strtotime($birthday['dob']));
                    $dobMonth = date('m', strtotime($birthday['dob']));

                    if ($selectedMonth === date('m') || $selectedMonth === 'current') {
                        // For current month, use the birthday_status if available
                        if (isset($birthday['birthday_status'])) {
                            switch ($birthday['birthday_status']) {
                                case 'today':
                                    $todayBirthdays[] = $birthday;
                                    break;
                                case 'upcoming':
                                    $upcomingBirthdays[] = $birthday;
                                    break;
                                case 'recent':
                                    $recentBirthdays[] = $birthday;
                                    break;
                                default:
                                    $otherBirthdays[] = $birthday;
                            }
                        } else {
                            // Fallback categorization
                            if ($dobDay == $todayDay && $dobMonth == $todayMonth) {
                                $todayBirthdays[] = $birthday;
                            } elseif ($dobMonth == $todayMonth && $dobDay > $todayDay) {
                                $upcomingBirthdays[] = $birthday;
                            } elseif ($dobMonth == $todayMonth && $dobDay < $todayDay) {
                                $recentBirthdays[] = $birthday;
                            } else {
                                $otherBirthdays[] = $birthday;
                            }
                        }
                    } else {
                        // For other months, show all as upcoming
                        $otherBirthdays[] = $birthday;
                    }
                }

                // Function to render birthday card
                function renderBirthdayCard($birthday, $currentYear, $todayDay, $todayMonth, $isToday = false, $daysText = '')
                {
                    $dobDay = date('d', strtotime($birthday['dob']));
                    $dobMonth = date('m', strtotime($birthday['dob']));
                    $dobYear = date('Y', strtotime($birthday['dob']));
                    $age = $currentYear - $dobYear;

                    $cardClass = $isToday ? 'employee-card today-card' : 'employee-card';

                    // Generate simple clean avatars based on gender
                    $initials = '';
                    $nameParts = explode(' ', $birthday['employee_name']);
                    foreach ($nameParts as $part) {
                        if (!empty($part)) {
                            $initials .= strtoupper(substr($part, 0, 1));
                        }
                    }

                    // Gender-based color schemes
                    if ($birthday['gender'] == 'male') {
                        $maleColors = ['4f46e5', '1e40af', '0891b2', '059669', 'dc2626', 'ea580c'];
                        $bgColor = $maleColors[abs(crc32($birthday['employee_name'])) % count($maleColors)];
                    } elseif ($birthday['gender'] == 'female') {
                        $femaleColors = ['ec4899', 'f59e0b', '8b5cf6', 'ef4444', '06b6d4', '10b981'];
                        $bgColor = $femaleColors[abs(crc32($birthday['employee_name'])) % count($femaleColors)];
                    } else {
                        $neutralColors = ['6b7280', '374151', '1f2937', '475569', '64748b', '52525b'];
                        $bgColor = $neutralColors[abs(crc32($birthday['employee_name'])) % count($neutralColors)];
                    }

                    $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&size=200&background=" . $bgColor . "&color=fff&font-size=0.6&bold=true&format=svg&rounded=true";

                    echo '<div class="' . $cardClass . '">';
                    if ($isToday) {
                        echo '<div class="birthday-badge today-badge">TODAY</div>';
                    } elseif ($daysText) {
                        echo '<div class="days-counter">' . $daysText . '</div>';
                    }
                    echo '<div class="employee-avatar">';
                    echo '<img src="' . $avatarUrl . '" alt="' . htmlspecialchars($birthday['employee_name']) . '">';
                    echo '</div>';
                    echo '<h3>' . htmlspecialchars($birthday['employee_name']) . '</h3>';
                    echo '<p class="employee-id">EMP' . str_pad($birthday['employee_id'], 3, '0', STR_PAD_LEFT) . ' • ' . htmlspecialchars(ucfirst($birthday['department'])) . '</p>';
                    echo '<div class="birthday-info">';
                    echo '<div class="info-item">';
                    echo '<i class="fas fa-calendar"></i>';
                    echo '<span>' . date('F j', strtotime($birthday['dob'])) . '</span>';
                    echo '</div>';
                    echo '<div class="info-item">';
                    echo '<i class="fas fa-birthday-cake"></i>';
                    echo '<span>Turning ' . $age . ' years old</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>

                <!-- Today's Birthdays Section -->
                <?php if (!empty($todayBirthdays)): ?>
                    <div class="birthday-section">
                        <div class="section-header">
                            <div class="section-icon today-icon">
                                <i class="fas fa-gift"></i>
                            </div>
                            <h2>Today's Birthdays</h2>
                        </div>
                        <div class="birthday-grid">
                            <?php foreach ($todayBirthdays as $birthday): ?>
                                <?php renderBirthdayCard($birthday, $currentYear, $todayDay, $todayMonth, true); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Upcoming Birthdays Section -->
                <?php if (!empty($upcomingBirthdays)): ?>
                    <div class="birthday-section">
                        <div class="section-header">
                            <div class="section-icon upcoming-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <h2>Upcoming Birthdays</h2>
                        </div>
                        <div class="birthday-grid">
                            <?php foreach ($upcomingBirthdays as $birthday): ?>
                                <?php
                                $dobDay = date('d', strtotime($birthday['dob']));
                                $daysUntil = $dobDay - $todayDay;
                                $daysText = $daysUntil . ' days';
                                renderBirthdayCard($birthday, $currentYear, $todayDay, $todayMonth, false, $daysText);
                                ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Birthdays Section -->
                <?php if (!empty($recentBirthdays)): ?>
                    <div class="birthday-section">
                        <div class="section-header">
                            <div class="section-icon recent-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h2>Recent Birthdays</h2>
                        </div>
                        <div class="birthday-grid">
                            <?php foreach ($recentBirthdays as $birthday): ?>
                                <?php
                                $dobDay = date('d', strtotime($birthday['dob']));
                                $daysPassed = $todayDay - $dobDay;
                                $daysText = $daysPassed . ' days ago';
                                renderBirthdayCard($birthday, $currentYear, $todayDay, $todayMonth, false, $daysText);
                                ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- All Birthdays Section (for non-current months) -->
                <?php if (!empty($otherBirthdays) && ($selectedMonth === 'all' || $selectedMonth !== date('m'))): ?>
                    <div class="birthday-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-gift"></i>
                            </div>
                            <h2><?php echo ($selectedMonth === 'all') ? 'All Birthdays' : 'Birthdays'; ?></h2>
                        </div>
                        <div class="birthday-grid">
                            <?php foreach ($otherBirthdays as $birthday): ?>
                                <?php
                                $dobDay = date('d', strtotime($birthday['dob']));
                                $dobMonth = date('m', strtotime($birthday['dob']));
                                $dobYear = date('Y', strtotime($birthday['dob']));

                                // Calculate days until birthday this year or next year
                                $birthdayThisYear = mktime(0, 0, 0, $dobMonth, $dobDay, $currentYear);
                                $today = mktime(0, 0, 0, $todayMonth, $todayDay, $currentYear);
                                $daysUntil = ceil(($birthdayThisYear - $today) / (60 * 60 * 24));

                                if ($daysUntil < 0) {
                                    $birthdayNextYear = mktime(0, 0, 0, $dobMonth, $dobDay, $currentYear + 1);
                                    $daysUntil = ceil(($birthdayNextYear - $today) / (60 * 60 * 24));
                                }

                                $daysText = $daysUntil . ' days';
                                renderBirthdayCard($birthday, $currentYear, $todayDay, $todayMonth, false, $daysText);
                                ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- No Birthdays Message -->
                <?php if (empty($todayBirthdays) && empty($upcomingBirthdays) && empty($recentBirthdays) && empty($otherBirthdays)): ?>
                    <div class="birthday-section">
                        <div class="birthday-grid">
                            <div class="no-birthdays">
                                <i class="fas fa-calendar-times"></i>
                                <p>No birthdays found for the selected period</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/birthday.js"></script>
    <script src="js/birthday_search.js"></script>

</body>

</html>