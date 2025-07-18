<?php
session_start();

// Set content type to JSON
header("Content-Type: application/json");

// Include database connection
require_once "db.php";

try {
    // Check request method
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }

    // Validate required fields for employees table
    $required_fields = ["name", "email", "contact", "employee_type", "gender", "joining_date"];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate required fields for employee_details table
    $required_details = ["father_name", "mother_name", "dob", "city", "state", "address"];
    foreach ($required_details as $field) {
        if (!isset($_POST[$field])) {
            throw new Exception("Missing field in employee details: $field");
        }
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    // Check if email already exists
    $check_email = "SELECT id FROM employees WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_email);
    mysqli_stmt_bind_param($check_stmt, "s", $_POST["email"]);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        throw new Exception("Email already exists");
    }
    mysqli_stmt_close($check_stmt);

    // Insert into employees table
    $insert_query = "INSERT INTO employees (name, email, contact, employee_type, gender, joining_date) 
                    VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $_POST["name"],
        $_POST["email"],
        $_POST["contact"],
        $_POST["employee_type"],
        $_POST["gender"],
        $_POST["joining_date"]
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error saving employee: " . mysqli_error($conn));
    }

    $employee_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Insert into employee_details table
    $details_query = "INSERT INTO employee_details 
        (employee_id, father_name, mother_name, dob, city, state, address) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
    $details_stmt = mysqli_prepare($conn, $details_query);
    mysqli_stmt_bind_param(
        $details_stmt,
        "issssss",
        $employee_id,
        $_POST["father_name"],
        $_POST["mother_name"],
        $_POST["dob"],
        $_POST["city"],
        $_POST["state"],
        $_POST["address"]
    );

    if (!mysqli_stmt_execute($details_stmt)) {
        throw new Exception("Error saving employee details: " . mysqli_error($conn));
    }
    mysqli_stmt_close($details_stmt);

    // If we get here, commit the transaction
    mysqli_commit($conn);

    // Return success response
    echo json_encode([
        "status" => "success",
        "message" => "Employee added successfully",
        "employee_id" => $employee_id
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        mysqli_rollback($conn);
    }

    // Return error response
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    // Close database connection
    if (isset($conn)) {
        mysqli_close($conn);
    }
}