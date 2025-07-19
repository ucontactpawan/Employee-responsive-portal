<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employees</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <!-- styles -->
  <link rel="stylesheet" href="css/style.css">
  <!-- navbar styles -->
  <link rel="stylesheet" href="css/navbar.css">
  <!-- Page specific styles -->
  <link rel="stylesheet" href="css/employee.css">
</head>

<body>
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/sidebar.php'; ?>
  <div class="sidebar-overlay"></div>
  <div class="main-content">
    <div class="table-header">
      <div class="search-wrapper">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-box" placeholder="Search employees..." id="searchEmployee">
      </div>
      <button class="add-employee-btn" onclick="showAddModal()">
        <i class="fas fa-plus"></i>
        Add Employee
      </button>
    </div>

    <div class="table-container">
      <table id="employeeTable">
        <thead>
          <tr>
            <th>Employee ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Position</th>
            <th>Gender</th>
            <th>Joining Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query = "SELECT * FROM employees ORDER BY id ASC";
          $result = mysqli_query($conn, $query);
          while ($row = mysqli_fetch_assoc($result)) {
          ?>
            <tr>
              <td><?php echo $row['id']; ?></td>
              <td><?php echo $row['name']; ?></td>
              <td><?php echo $row['email'] ?></td>
              <td><?php echo $row['contact']; ?></td>
              <td><?php echo ucfirst($row['employee_type']); ?></td>
              <td><?php echo ucfirst($row['gender']); ?></td>
              <td><?php echo $row['joining_date']; ?></td>
              <td>
                <div class="action-buttons">
                  <button class="edit-btn" onclick="showEditModal(<?php echo $row['id']; ?>)">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="delete-btn" onclick="deleteEmployee(<?php echo $row['id']; ?>)">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add/Edit Employee Modal -->
  <div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content employee-modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Progress Bar -->
          <div class="progress mb-3">
            <div class="progress-bar" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
              Step 1 of 2
            </div>
          </div>

          <form id="addEmployeeForm">
            <div id="step1">
              <!-- Step 1: Basic Information -->
              <div class="mb-3">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" class="form-control employee-input" id="name" name="name" required>
              </div>
              <!-- <div class="mb-3">
                <label class="form-label" for="position">Position</label>
                <input type="text" class="form-control employee-input" id="position" name="position" required>
              </div> -->
              <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control employee-input" id="email" name="email" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="contact">Contact</label>
                <input type="text" class="form-control employee-input" id="contact" name="contact" required>
              </div>

              <div class="mb-3">
                <label class="form-label" for="employee_type">Position</label>
                <select class="form-control employee-input" id="employee_type" name="employee_type" required>
                  <option value="employee">Employee</option>
                  <option value="admin">Admin</option>
                  <option value="hr">HR</option>
                  <option value="projectManager">Project Manager</option>
                  <option value="finance">Finance</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="gender">Gender</label>
                <select class="form-control employee-input" id="gender" name="gender" required>
                  <option value="" hidden>Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="joining_date">Joining Date</label>
                <input type="date" class="form-control employee-input" id="joining_date" name="joining_date" required>
              </div>
            </div>

            <div id="step2" style="display: none;">
              <!-- Step 2: Additional Information -->
              <div class="mb-3">
                <label class="form-label" for="father-name">Father's Name</label>
                <input type="text" class="form-control employee-input" id="father_name" name="father_name">
              </div>
              <div class="mb-3">
                <label class="fom-label" for="mother_name">Mother's Name</label>
                <input type="text" class="form-control employee-input" id="mother_name" name="mother_name">
              </div>
              <div class="mb-3">
                <label class="form-label" for="dob">Date Of Birth</label>
                <input type="date" class="form-control employee-input" id="dob" name="dob">
              </div>
              <div class="mb-3">
                <label class="form-label" for="city">City</label>
                <input type="text" class="form-control employee-input" id="city" name="city">
              </div>
              <div class="mb-3">
                <label class="form-label" for="state">State</label>
                <input type="text" class="form-control employee-input" id="state" name="state">
              </div>
              <div class="mb-3">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control employee-input" id="address" name="address" rows="3"></textarea>
              </div>
            </div>


            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">Previous</button>
              <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
              <button type="submit" class="btn btn-success" id="saveBtn" style="display: none;">Save Employee</button>
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/script.js"></script>
  <script src="js/employee_new.js"></script>
</body>

</html>