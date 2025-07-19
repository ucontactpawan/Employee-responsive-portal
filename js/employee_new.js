function showAddModal() {
  resetForm();
  $("#addEmployeeModal").modal("show");
}

function updateProgressBar(step) {
  let width = step === 1 ? "50%" : "100%";
  $(".progress-bar").css("width", width);
  $(".progress-bar").text("Step " + step + " of 2");
}

function resetForm() {
  $("#addEmployeeForm")[0].reset();
  $("#addEmployeeModal").removeData("employee-id");
  $(".modal-title").text("Add New Employee");
  initializeSteps();
}

function initializeSteps() {
  $("#step1").show();
  $("#step2").hide();
  $("#prevBtn").hide();
  $("#saveBtn").hide();
  $("#nextBtn").show();
  $(".btn-danger").show();
  updateProgressBar(1);
}

function showEditModal(employeeId) {
  resetForm();
  $.ajax({
    url: "includes/get_employee.php",
    type: "GET",
    data: { id: employeeId },
    dataType: "json",
    success: function (employee) {
      if (employee && employee.id) {
        $("#addEmployeeModal").data("employee-id", employee.id);

        // Populate form fields
        $("#name").val(employee.name);
        $("#email").val(employee.email);
        $("#contact").val(employee.contact);
        $("#employee_type").val(employee.employee_type);
        $("#gender").val(employee.gender);
        $("#joining_date").val(employee.joining_date);
        $("#father_name").val(employee.father_name);
        $("#mother_name").val(employee.mother_name);
        $("#dob").val(employee.dob);
        $("#city").val(employee.city);
        $("#state").val(employee.state);
        $("#address").val(employee.address);

        // Set up modal for editing
        $(".modal-title").text("Edit Employee");
        $("#step1").show();
        $("#step2").hide();
        $("#nextBtn").show();
        $("#prevBtn").hide();
        $("#saveBtn").hide();
        $(".btn-danger").show();
        updateProgressBar(1);

        $("#addEmployeeModal").modal("show");
      } else {
        alert("Employee not found");
      }
    },
    error: function () {
      alert("Error fetching employee data");
    },
  });
}

function deleteEmployee(employeeId) {
  if (confirm("Are you sure you want to delete this employee?")) {
    $.ajax({
      url: "includes/delete_employee.php",
      type: "POST",
      data: { id: employeeId },
      success: function (response) {
        try {
          var result = JSON.parse(response);
          if (result.status === "success") {
            alert("Employee deleted successfully.");
            location.reload();
          } else {
            alert("Error: " + result.message);
          }
        } catch (e) {
          alert("Unexpected error occurred");
        }
      },
      error: function (xhr, status, error) {
        alert("AJAX error occurred: " + error);
      },
    });
  }
}

$(document).ready(function () {
  let currentStep = 1;

  // Initialize steps
  initializeSteps();

  // Handle Next button
  $("#nextBtn").click(function () {
    if (validateStep1()) {
      $("#step1").hide();
      $("#step2").show();
      $("#nextBtn").hide();
      $("#prevBtn").show();
      $("#saveBtn").show();
      $(".btn-danger").hide();
      currentStep = 2;
      updateProgressBar(2);
      $(".modal-title").text("Add Employee Details");
    }
  });

  // Handle Previous button
  $("#prevBtn").click(function () {
    $("#step2").hide();
    $("#step1").show();
    $("#nextBtn").show();
    $("#prevBtn").hide();
    $("#saveBtn").hide();
    $(".btn-danger").show();
    currentStep = 1;
    updateProgressBar(1);
    $(".modal-title").text("Add New Employee");
  });

  // Validate Step 1 fields
  function validateStep1() {
    let isValid = true;
    $("#step1 input[required], #step1 select[required]").each(function () {
      if (!$(this).val()) {
        $(this).addClass("is-invalid");
        isValid = false;
      } else {
        $(this).removeClass("is-invalid");
      }
    });
    return isValid;
  }

  // Form submission
  $("#addEmployeeForm").on("submit", function (e) {
    e.preventDefault();

    var employeeId = $("#addEmployeeModal").data("employee-id");
    var url = employeeId
      ? "includes/update_employee.php"
      : "includes/save_employee.php";

    var formData = {
      name: $("#name").val().trim(),
      email: $("#email").val().trim(),
      contact: $("#contact").val().trim(),
      employee_type: $("#employee_type").val(),
      gender: $("#gender").val(),
      joining_date: $("#joining_date").val(),
      father_name: $("#father_name").val().trim(),
      mother_name: $("#mother_name").val().trim(),
      dob: $("#dob").val(),
      city: $("#city").val().trim(),
      state: $("#state").val().trim(),
      address: $("#address").val().trim(),
    };

    if (employeeId) {
      formData.id = employeeId;
    }

    $.ajax({
      url: url,
      type: "POST",
      data: formData,
      dataType: "json",
      beforeSend: function () {
        $("#saveBtn").prop("disabled", true).text("Saving...");
      },
      success: function (response) {
        if (response.status === "success") {
          alert(response.message);
          $("#addEmployeeModal").modal("hide");
          location.reload();
        } else {
          alert("Error: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        alert("Error saving employee data.");
      },
      complete: function () {
        $("#saveBtn").prop("disabled", false).text("Save Employee");
      },
    });
  });

  // Modal close handlers
  $(".btn-close, .btn-danger").click(function () {
    $("#addEmployeeModal").modal("hide");
  });

  $("#addEmployeeModal").on("hidden.bs.modal", function () {
    resetForm();
  });

  // Search functionality
  $("#searchEmployee").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#employeeTable tbody tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
  });
});
