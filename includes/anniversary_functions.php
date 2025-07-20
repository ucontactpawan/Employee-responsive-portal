<?php

function getAnniversaries($conn, $month = null)
{
    $employees = [];

    // Prepare base SQL query
    $sql = "SELECT e.id, e.name, e.joining_date, e.employee_type as department, e.gender 
            FROM employees e";

    // Add month filter if provided
    if ($month && $month !== 'all') {
        // Filter by specific month
        $sql .= " WHERE MONTH(e.joining_date) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $month);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Get all employees if no month filter or 'all' selected
        $result = $conn->query($sql);
    }

    // Process query results
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
    }

    return $employees;
}

function categorizeAnniversaries($employees)
{
    $today = date('m-d');
    $todayTimestamp = strtotime(date('Y-m-d'));
    $todayDay = (int)date('d');
    $todayMonth = (int)date('m');
    $currentYear = (int)date('Y');

    $todays = [];
    $upcoming = [];
    $recent = [];

    foreach ($employees as $emp) {
        $joiningDate = $emp['joining_date'];
        $joiningMonthDay = date('m-d', strtotime($joiningDate));
        $joiningMonth = (int)date('m', strtotime($joiningDate));
        $joiningDay = (int)date('d', strtotime($joiningDate));

        // Create anniversary date for current year
        $anniversaryDate = $currentYear . '-' . date('m-d', strtotime($joiningDate));
        $anniversaryTimestamp = strtotime($anniversaryDate);

        // Calculate days difference
        $daysDiff = round(($anniversaryTimestamp - $todayTimestamp) / (60 * 60 * 24));

        if ($joiningMonthDay == $today) {
            // Today's anniversary
            $todays[] = $emp;
        } elseif ($daysDiff > 0 && $daysDiff <= 30) {
            // Upcoming anniversary (next 30 days)
            if ($daysDiff == 1) {
                $emp['days_text'] = 'Tomorrow';
            } else {
                $emp['days_text'] = 'In ' . $daysDiff . ' days';
            }
            $upcoming[] = $emp;
        } elseif ($daysDiff < 0 && $daysDiff >= -30) {
            // Recent anniversary (last 30 days)
            $daysDiff = abs($daysDiff);
            if ($daysDiff == 1) {
                $emp['days_text'] = 'Yesterday';
            } else {
                $emp['days_text'] = $daysDiff . ' days ago';
            }
            $recent[] = $emp;
        }
    } // Closing the foreach loop

    // Sort by proximity to today
    usort($upcoming, function ($a, $b) {
        $a_date = date('Y-m-d', strtotime(date('Y') . '-' . date('m-d', strtotime($a['joining_date']))));
        $b_date = date('Y-m-d', strtotime(date('Y') . '-' . date('m-d', strtotime($b['joining_date']))));
        return strtotime($a_date) - strtotime($b_date);
    });

    usort($recent, function ($a, $b) {
        $a_date = date('Y-m-d', strtotime(date('Y') . '-' . date('m-d', strtotime($a['joining_date']))));
        $b_date = date('Y-m-d', strtotime(date('Y') . '-' . date('m-d', strtotime($b['joining_date']))));
        return strtotime($b_date) - strtotime($a_date);
    });

    return [$todays, $upcoming, $recent];
}
