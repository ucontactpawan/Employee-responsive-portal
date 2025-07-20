<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

try {
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/auth.php';

    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not authenticated");
    }

    $today = date('Y-m-d');

    $totalQuery = "SELECT COUNT(*) as total FROM employees WHERE status = '1'";
    $totalResult = $conn->query($totalQuery);
    $totalEmployees = $totalResult ? $totalResult->fetch_assoc()['total'] : 0;

    // Get today's attendance data only
    $attendanceQuery = "SELECT 
        a.employee_id,
        e.name,
        a.in_time,
        CASE 
            WHEN a.in_time LIKE '%:%:%' THEN 
                CASE 
                    WHEN TIME(a.in_time) <= '09:30:00' THEN 'on_time'
                    ELSE 'late'
                END
            ELSE 'invalid_format'
        END as time_status
    FROM attendance a
    INNER JOIN employees e ON a.employee_id = e.id
    WHERE DATE(a.created_at) = ? 
    AND e.status = '1' 
    AND a.in_time IS NOT NULL 
    AND a.in_time != ''";

    $stmt = $conn->prepare($attendanceQuery);
    if (!$stmt) {
        throw new Exception("Failed to prepare attendance query: " . $conn->error);
    }

    $stmt->bind_param("s", $today);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute attendance query: " . $stmt->error);
    }

    $attendanceResult = $stmt->get_result();

    $totalPresent = 0;
    $onTime = 0;
    $late = 0;

    while ($row = $attendanceResult->fetch_assoc()) {
        $totalPresent++;

        if ($row['time_status'] == 'on_time') {
            $onTime++;
        } elseif ($row['time_status'] == 'late') {
            $late++;
        }
    }

    // Calculate on-time percentage
    $onTimePercent = $totalEmployees > 0 ? round(($onTime / $totalEmployees) * 100, 1) : 0;

    // Calculate absent count
    $absentToday = $totalEmployees - $totalPresent;

    // Get weekly attendance data (last 7 days)
    $weeklyQuery = "SELECT 
        DAYNAME(DATE(a.created_at)) as day_name,
        COUNT(DISTINCT a.employee_id) as total_present,
        SUM(CASE WHEN TIME(a.in_time) <= '09:30:00' THEN 1 ELSE 0 END) as on_time,
        SUM(CASE WHEN TIME(a.in_time) > '09:30:00' THEN 1 ELSE 0 END) as late
    FROM attendance a
    INNER JOIN employees e ON a.employee_id = e.id
    WHERE DATE(a.created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE() 
    AND e.status = '1'
    AND DAYOFWEEK(DATE(a.created_at)) BETWEEN 2 AND 6 
    GROUP BY DAYNAME(DATE(a.created_at))
    ORDER BY FIELD(DAYNAME(DATE(a.created_at)), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";

    $weeklyResult = $conn->query($weeklyQuery);
    $weeklyData = [];
    $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

    // Initialize data for all days
    foreach ($daysOfWeek as $day) {
        $weeklyData[$day] = [
            'on_time' => 0,
            'late' => 0,
            'absent' => $totalEmployees
        ];
    }

    // Fill in actual data where available
    if ($weeklyResult) {
        while ($row = $weeklyResult->fetch_assoc()) {
            $dayName = substr($row['day_name'], 0, 3);
            if (isset($weeklyData[$dayName])) {
                $weeklyData[$dayName]['on_time'] = (int)$row['on_time'];
                $weeklyData[$dayName]['late'] = (int)$row['late'];
                $weeklyData[$dayName]['absent'] = $totalEmployees - ((int)$row['on_time'] + (int)$row['late']);
            }
        }
    }

    // Get recent attendance for real-time updates
    $recentQuery = "SELECT 
        e.name,
        a.in_time,
        a.out_time,
        a.created_at
    FROM attendance a
    INNER JOIN employees e ON a.employee_id = e.id
    WHERE DATE(a.created_at) = ? 
    AND e.status = '1'
    ORDER BY a.created_at DESC
    LIMIT 5";

    $stmt2 = $conn->prepare($recentQuery);
    if ($stmt2) {
        $stmt2->bind_param("s", $today);
        $stmt2->execute();
        $recentResult = $stmt2->get_result();

        $recentActivity = [];
        while ($row = $recentResult->fetch_assoc()) {
            $recentActivity[] = [
                'name' => $row['name'],
                'in_time' => $row['in_time'],
                'out_time' => $row['out_time'],
                'timestamp' => $row['created_at']
            ];
        }
    } else {
        $recentActivity = [];
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_employees' => (int)$totalEmployees,
            'on_time_today' => (int)$onTime,
            'late_today' => (int)$late,
            'absent_today' => (int)$absentToday,
            'on_time_percent' => $onTimePercent,
            'total_present' => (int)$totalPresent,
            'recent_activity' => $recentActivity,
            'last_updated' => date('Y-m-d H:i:s'),
            'chart_data' => [
                'today_distribution' => [
                    'on_time' => (int)$onTime,
                    'late' => (int)$late,
                    'absent' => (int)$absentToday
                ],
                'weekly_trend' => $weeklyData
            ]
        ]
    ]);
} catch (Exception $e) {
    error_log("Error in get_dashboard_data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($stmt2)) {
        $stmt2->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
exit();
