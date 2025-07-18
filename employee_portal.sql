-- Create the database
CREATE DATABASE IF NOT EXISTS attendance_portal;

-- Use the database
USE attendance_portal;

-- Drop tables if they exist (in correct order due to foreign key constraints)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS attendance_history;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS employee_details;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS notifications;
SET FOREIGN_KEY_CHECKS = 1;

-- Create employees table
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    contact VARCHAR(20) DEFAULT NULL,
    gender ENUM('male', 'female', 'other') DEFAULT NULL,
    status ENUM('0', '1') DEFAULT '1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    joining_date VARCHAR(20) DEFAULT NULL,
    employee_type ENUM('admin', 'employee', 'hr', 'projectManager') DEFAULT 'employee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create employee_details table
CREATE TABLE employee_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    father_name VARCHAR(100),
    mother_name VARCHAR(100),
    dob DATE,
    city VARCHAR(100),
    state VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create attendance table(without date column)
CREATE TABLE attendance (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL,
    in_time VARCHAR(50),
    out_time VARCHAR(50),
    in_image VARCHAR(250),
    out_image VARCHAR(250),
    comments TEXT,
    status ENUM('0', '1') DEFAULT '0',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create attendance_history table
CREATE TABLE attendance_history (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    creator_id INT(11) DEFAULT '0',
    attendance_id INT(11),
    employee_id INT(11) NOT NULL,
    action ENUM('IN', 'OUT') DEFAULT 'IN',
    date_time VARCHAR(50),
    image VARCHAR(255),
    comments TEXT,
    status ENUM('1', '0') DEFAULT '1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (attendance_id) REFERENCES attendance(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create notifications table
CREATE TABLE notification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_date DATE NOT NULL,
    notification_type ENUM('birthday', 'anniversary') NOT NULL,
    status ENUM('0', '1') DEFAULT '1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



