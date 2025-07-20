<?php

function getBirthdays()
{
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
        YEAR(ed.dob) as birth_year,
        CASE 
            WHEN (MONTH(ed.dob) = MONTH(CURDATE()) AND DAY(ed.dob) = DAY(CURDATE())) THEN 'today'
            WHEN (MONTH(ed.dob) = MONTH(CURDATE()) AND DAY(ed.dob) > DAY(CURDATE())) THEN 'upcoming'
            WHEN (MONTH(ed.dob) = MONTH(CURDATE()) AND DAY(ed.dob) < DAY(CURDATE())) THEN 'recent'
            ELSE 'other'
        END as birthday_status,
        CASE 
            WHEN (MONTH(ed.dob) = MONTH(CURDATE()) AND DAY(ed.dob) >= DAY(CURDATE()))
            THEN DATEDIFF(
                STR_TO_DATE(
                    CONCAT(YEAR(CURDATE()), '-', MONTH(ed.dob), '-', DAY(ed.dob)),
                    '%Y-%m-%d'
                ),
                CURDATE()
            )
            WHEN (MONTH(ed.dob) = MONTH(CURDATE()) AND DAY(ed.dob) < DAY(CURDATE()))
            THEN DATEDIFF(
                CURDATE(),
                STR_TO_DATE(
                    CONCAT(YEAR(CURDATE()), '-', MONTH(ed.dob), '-', DAY(ed.dob)),
                    '%Y-%m-%d'
                )
            ) * -1
            ELSE DATEDIFF(
                STR_TO_DATE(
                    CONCAT(YEAR(CURDATE()), '-', MONTH(ed.dob), '-', DAY(ed.dob)),
                    '%Y-%m-%d'
                ),
                CURDATE()
            )
        END as days_until_birthday
    FROM employee_details ed
    JOIN employees e ON e.id = ed.employee_id
    WHERE ed.dob IS NOT NULL 
    GROUP BY e.id, e.name, e.email, e.contact, e.gender, e.employee_type, e.joining_date, ed.dob
    ORDER BY 
        CASE 
            WHEN (MONTH(ed.dob) = MONTH(CURDATE()) AND DAY(ed.dob) = DAY(CURDATE())) THEN 0
            WHEN (MONTH(ed.dob) = MONTH(CURDATE()) AND DAY(ed.dob) > DAY(CURDATE())) THEN 1
            WHEN (MONTH(ed.dob) = MONTH(CURDATE()) AND DAY(ed.dob) < DAY(CURDATE())) THEN 2
            ELSE 3
        END,
        DAY(ed.dob)";

    $result = $conn->query($query);

    if (!$result) {
        error_log("SQL Error in getBirthdays: " . $conn->error);
        return false;
    }

    return $result;
}

function getAllBirthdays()
{
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
    GROUP BY e.id, e.name, e.email, e.contact, e.gender, e.employee_type, e.joining_date, ed.dob
    ORDER BY MONTH(ed.dob), DAY(ed.dob)";

    $result = $conn->query($query);

    if (!$result) {
        error_log("SQL Error in getAllBirthdays: " . $conn->error);
        return false;
    }

    return $result;
}

function formatBirthdayDate($days_until)
{
    if ($days_until == 0) {
        return "Today";
    } else if ($days_until == 1) {
        return "Tomorrow";
    } else if ($days_until == -1) {
        return "Yesterday";
    } else if ($days_until < 0) {
        return abs($days_until) . " days ago";
    } else {
        return "In " . $days_until . " days";
    }
}

function formatFullDOB($dob)
{
    return date('d M Y', strtotime($dob));
}
