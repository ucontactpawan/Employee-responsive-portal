<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $query = "SELECT e.*, ed.father_name, ed.mother_name, ed.dob, ed.city, ed.state, ed.address FROM employees e LEFT JOIN employee_details ed ON e.id = ed.employee_id WHERE e.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        echo json_encode($employee);
    } else {
        echo json_encode(['error' => 'Employee not found']);
    }

    $stmt->close();
    $conn->close();
}
?>