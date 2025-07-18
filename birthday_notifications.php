<?php
include 'includes/db.php';
header('Content-Type: application/json');

$today = date('Y-m-d');
$data = [];

// Fetch today's birthdays
$birthdayQuery = "SELECT GROUP_CONCAT(e.name) as names 
    FROM employee_details ed 
    JOIN employees e ON e.id = ed.employee_id 
    WHERE DATE_FORMAT(ed.dob, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d')
    AND e.status = '1'";
$birthdayResult = $conn->query($birthdayQuery);

// Fetch upcoming birthdays (next 7 days)
$upcomingBirthdayQuery = "SELECT GROUP_CONCAT(e.name) as names 
    FROM employee_details ed 
    JOIN employees e ON e.id = ed.employee_id 
    WHERE DATE_FORMAT(ed.dob, '%m-%d') 
    BETWEEN DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '%m-%d')
    AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '%m-%d')
    AND e.status = '1'";
$upcomingBirthdayResult = $conn->query($upcomingBirthdayQuery);

// Check for birthdays today
if ($birthdayResult && $birthdayResult->num_rows > 0) {
    $check = $conn->prepare("SELECT id FROM notification WHERE notification_date = ? AND notification_type = 'birthday' AND status = '1'");
    $check->bind_param("s", $today);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows == 0) {
        $row = $birthdayResult->fetch_assoc();
        $todayNames = $row['names'];
        
        // Get upcoming birthdays text
        $upcomingText = '';
        if ($upcomingBirthdayResult && $upcomingBirthdayResult->num_rows > 0) {
            $upcomingRow = $upcomingBirthdayResult->fetch_assoc();
            if ($upcomingRow['names']) {
                $upcomingText = "\n\nUpcoming Birthdays: " . $upcomingRow['names'];
            }
        }

        if ($todayNames) {
            $data[] = [
                "title" => "🎂 Birthday Alert",
                "body" => "$todayNames has a birthday today!" . $upcomingText,
                "icon" => "images/birthday.png",
                "url" => "http://localhost/attendance-management/birthday.php"
            ];
        }

        // Log birthday notification
        $insert = $conn->prepare("INSERT INTO notification (notification_date, notification_type, status) VALUES (?, 'birthday', '1')");
        $insert->bind_param("s", $today);
        $insert->execute();
    }
}

// Fetch today's anniversaries
$annivQuery = "SELECT GROUP_CONCAT(e.name) as names 
    FROM employees e 
    WHERE DATE_FORMAT(e.joining_date, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d')
    AND e.status = '1'";
$annivResult = $conn->query($annivQuery);

// Fetch upcoming anniversaries
$upcomingAnnivQuery = "SELECT GROUP_CONCAT(e.name) as names 
    FROM employees e 
    WHERE DATE_FORMAT(e.joining_date, '%m-%d') 
    BETWEEN DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '%m-%d')
    AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '%m-%d')
    AND e.status = '1'";
$upcomingAnnivResult = $conn->query($upcomingAnnivQuery);

// Check for anniversaries today
if ($annivResult && $annivResult->num_rows > 0) {
    $check = $conn->prepare("SELECT id FROM notification WHERE notification_date = ? AND notification_type = 'anniversary' AND status = '1'");
    $check->bind_param("s", $today);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows == 0) {
        $row = $annivResult->fetch_assoc();
        $todayNames = $row['names'];

        // Get upcoming anniversaries text
        $upcomingText = '';
        if ($upcomingAnnivResult && $upcomingAnnivResult->num_rows > 0) {
            $upcomingRow = $upcomingAnnivResult->fetch_assoc();
            if ($upcomingRow['names']) {
                $upcomingText = "\n\nUpcoming Work Anniversaries: " . $upcomingRow['names'];
            }
        }

        if ($todayNames) {
            $data[] = [
                "title" => "🎊 Work Anniversary",
                "body" => "$todayNames has a work anniversary today!" . $upcomingText,
                "icon" => "images/work-anniversary.png",
                "url" => "http://localhost/attendance-management/anniversary.php"
            ];
        }

        // Log anniversary notification
        $insert = $conn->prepare("INSERT INTO notification (notification_date, notification_type, status) VALUES (?, 'anniversary', '1')");
        $insert->bind_param("s", $today);
        $insert->execute();
    }
}

// Return notification array
echo json_encode($data);
