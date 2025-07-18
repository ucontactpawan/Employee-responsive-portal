<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include 'db.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Start transaction for data integrity
    $conn->begin_transaction();

    try {
        // Delete related attendance records first using prepared statement
        $stmt1 = $conn->prepare("DELETE FROM attendance WHERE employee_id = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();

        // Delete related attendance_history records
        $stmt2 = $conn->prepare("DELETE FROM attendance_history WHERE employee_id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();

        // Delete employee_details records (though it should cascade automatically)
        $stmt3 = $conn->prepare("DELETE FROM employee_details WHERE employee_id = ?");
        $stmt3->bind_param("i", $id);
        $stmt3->execute();
        $stmt3->close();

        // Delete employee record
        $stmt4 = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt4->bind_param("i", $id);

        if ($stmt4->execute()) {
            if ($stmt4->affected_rows > 0) {
                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => 'Employee deleted successfully']);
            } else {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Employee not found or already deleted']);
            }
        } else {
            $conn->rollback();
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete employee: ' . $stmt4->error
            ]);
        }
        $stmt4->close();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
