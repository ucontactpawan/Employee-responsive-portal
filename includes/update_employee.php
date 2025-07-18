<?php

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $employee_type = $_POST['employee_type'];
    $gender = $_POST['gender'];
    $joining_date = $_POST['joining_date'];
    $father_name = $_POST['father_name'];
    $mother_name = $_POST['mother_name'];
    $dob = $_POST['dob'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $address = $_POST['address'];


    // now checking for  duplicate email , except for the current employee
    $stmt = $conn->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    } else {
        $stmt->close();
        // Update employee details
        $update_query = "UPDATE employees SET name = ?, email = ?, contact = ?, employee_type = ?, gender = ?, joining_date = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssssi", $name, $email, $contact, $employee_type, $gender, $joining_date, $id);

        if ($update_stmt->execute()) {
            $update_stmt->close();

            // Upsert employee_details
            $details_query = "INSERT INTO employee_details (employee_id, father_name, mother_name, dob, city, state, address) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE father_name = ?, mother_name = ?, dob = ?, city = ?, state = ?, address = ?";
            $details_stmt = $conn->prepare($details_query);
            $details_stmt->bind_param("issssssssssss", $id, $father_name, $mother_name, $dob, $city, $state, $address, $father_name, $mother_name, $dob, $city, $state, $address);

            if ($details_stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Employee updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update employee details']);
            }
            $details_stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update employee']);
        }
        $conn->close();
    }
}