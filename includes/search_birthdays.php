<?php
session_start();
include 'db.php';
include 'auth.php';
include 'birthday_functions.php';

header('Content-Type: application/json');


$searchTerm = isset($_POST['search']) ? trim($_POST['search']) : '';
$selectedMonth = isset($_POST['month']) ? $_POST['month'] : date('m');

try {
    // Get birthdays based on filters
    if ($selectedMonth === 'all') {
        $birthdays = getAllBirthdays();
    } else if ($selectedMonth === date('m')) {
        $birthdays = getBirthdays();
    } else {
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
        throw new Exception("Error fetching birthdays");
    }

    // Store all birthdays in arrays for processing
    $allBirthdays = [];
    while ($birthday = $birthdays->fetch_assoc()) {
        $allBirthdays[] = $birthday;
    }

    // Filter by search term if provided
    if (!empty($searchTerm)) {
        $allBirthdays = array_filter($allBirthdays, function ($birthday) use ($searchTerm) {
            $searchLower = strtolower($searchTerm);
            return (
                strpos(strtolower($birthday['employee_name']), $searchLower) !== false ||
                strpos(strtolower($birthday['department']), $searchLower) !== false ||
                strpos('emp' . str_pad($birthday['employee_id'], 3, '0', STR_PAD_LEFT), $searchLower) !== false
            );
        });
    }

    // Categorize birthdays
    $todayBirthdays = [];
    $upcomingBirthdays = [];
    $recentBirthdays = [];
    $otherBirthdays = [];

    $todayDay = date('d');
    $todayMonth = date('m');
    $currentYear = date('Y');

    foreach ($allBirthdays as $birthday) {
        $dobDay = date('d', strtotime($birthday['dob']));
        $dobMonth = date('m', strtotime($birthday['dob']));

        if ($selectedMonth === date('m') || $selectedMonth === 'current') {
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
            $otherBirthdays[] = $birthday;
        }
    }

    // Generate HTML response
    ob_start();

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

    // Today's Birthdays Section
    if (!empty($todayBirthdays)) {
        echo '<div class="birthday-section">';
        echo '<div class="section-header">';
        echo '<div class="section-icon today-icon">';
        echo '<i class="fas fa-gift"></i>';
        echo '</div>';
        echo '<h2>Today\'s Birthdays</h2>';
        echo '</div>';
        echo '<div class="birthday-grid">';
        foreach ($todayBirthdays as $birthday) {
            renderBirthdayCard($birthday, $currentYear, $todayDay, $todayMonth, true);
        }
        echo '</div>';
        echo '</div>';
    }

    // Upcoming Birthdays Section
    if (!empty($upcomingBirthdays)) {
        echo '<div class="birthday-section">';
        echo '<div class="section-header">';
        echo '<div class="section-icon upcoming-icon">';
        echo '<i class="fas fa-calendar-plus"></i>';
        echo '</div>';
        echo '<h2>Upcoming Birthdays</h2>';
        echo '</div>';
        echo '<div class="birthday-grid">';
        foreach ($upcomingBirthdays as $birthday) {
            $dobDay = date('d', strtotime($birthday['dob']));
            $daysUntil = $dobDay - $todayDay;
            $daysText = $daysUntil . ' days';
            renderBirthdayCard($birthday, $currentYear, $todayDay, $todayMonth, false, $daysText);
        }
        echo '</div>';
        echo '</div>';
    }

    // Recent Birthdays Section
    if (!empty($recentBirthdays)) {
        echo '<div class="birthday-section">';
        echo '<div class="section-header">';
        echo '<div class="section-icon recent-icon">';
        echo '<i class="fas fa-calendar-check"></i>';
        echo '</div>';
        echo '<h2>Recent Birthdays</h2>';
        echo '</div>';
        echo '<div class="birthday-grid">';
        foreach ($recentBirthdays as $birthday) {
            $dobDay = date('d', strtotime($birthday['dob']));
            $daysPassed = $todayDay - $dobDay;
            $daysText = $daysPassed . ' days ago';
            renderBirthdayCard($birthday, $currentYear, $todayDay, $todayMonth, false, $daysText);
        }
        echo '</div>';
        echo '</div>';
    }

    // All Birthdays Section 
    if (!empty($otherBirthdays) && ($selectedMonth === 'all' || $selectedMonth !== date('m'))) {
        echo '<div class="birthday-section">';
        echo '<div class="section-header">';
        echo '<div class="section-icon">';
        echo '<i class="fas fa-gift"></i>';
        echo '</div>';
        echo '<h2>' . (($selectedMonth === 'all') ? 'All Birthdays' : 'Birthdays') . '</h2>';
        echo '</div>';
        echo '<div class="birthday-grid">';
        foreach ($otherBirthdays as $birthday) {
            $dobDay = date('d', strtotime($birthday['dob']));
            $dobMonth = date('m', strtotime($birthday['dob']));
            $dobYear = date('Y', strtotime($birthday['dob']));

         
            $birthdayThisYear = mktime(0, 0, 0, $dobMonth, $dobDay, $currentYear);
            $today = mktime(0, 0, 0, $todayMonth, $todayDay, $currentYear);
            $daysUntil = ceil(($birthdayThisYear - $today) / (60 * 60 * 24));

            if ($daysUntil < 0) {
                $birthdayNextYear = mktime(0, 0, 0, $dobMonth, $dobDay, $currentYear + 1);
                $daysUntil = ceil(($birthdayNextYear - $today) / (60 * 60 * 24));
            }

            $daysText = $daysUntil . ' days';
            renderBirthdayCard($birthday, $currentYear, $todayDay, $todayMonth, false, $daysText);
        }
        echo '</div>';
        echo '</div>';
    }

    if (empty($todayBirthdays) && empty($upcomingBirthdays) && empty($recentBirthdays) && empty($otherBirthdays)) {
        echo '<div class="birthday-section">';
        echo '<div class="birthday-grid">';
        echo '<div class="no-birthdays">';
        echo '<i class="fas fa-calendar-times"></i>';
        echo '<p>No birthdays found for the selected criteria</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    $html = ob_get_clean();

    echo json_encode([
        'success' => true,
        'html' => $html,
        'total' => count($allBirthdays)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
