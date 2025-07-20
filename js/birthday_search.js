$(document).ready(function () {
  loadInitialContent();

  performSearch(false);

  // Search on Enter key press
  $("#searchEmployees").on("keypress", function (e) {
    if (e.which === 13) {
      performSearch();
    }
  });

  // Real-time search with debounce
  let searchTimeout;
  $("#searchEmployees").on("input", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function () {
      performSearch();
    }, 500); // 500ms delay
  });

  // Month filter change
  $("#monthFilter").on("change", function () {
    performSearch();
  });

  function loadInitialContent() {
    const birthdayContainer = $("#birthdayResults");
    const existingContent = $(".birthday-cards-container")
      .not("#birthdayResults")
      .html();
    if (existingContent) {
      birthdayContainer.html(existingContent);
    }
  }

  function performSearch(showLoading = true) {
    const searchTerm = $("#searchEmployees").val().trim();
    const selectedMonth = $("#monthFilter").val();
    const searchInput = $("#searchEmployees");

    // Show loading state
    if (showLoading) {
      searchInput.css(
        "background",
        "#f3f4f6 url('data:image/gif;base64,R0lGODlhEAAQAPIAAP///wAAAMLCwkJCQgAAAGJiYoKCgpKSkiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQJCgAAACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkECQoAAAAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkECQoAAAAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkECQoAAAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQJCgAAACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQJCgAAACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAkKAAAALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==') no-repeat right 10px center"
      );
    }

    // AJAX request
    $.ajax({
      url: "includes/search_birthdays.php",
      type: "POST",
      data: {
        search: searchTerm,
        month: selectedMonth,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $("#birthdayResults").html(response.html);

          // Show success animation
          $("#birthdayResults").hide().fadeIn(300);

          // Update URL without reload
          const newUrl = new URL(window.location);
          if (selectedMonth !== "all") {
            newUrl.searchParams.set("month", selectedMonth);
          } else {
            newUrl.searchParams.delete("month");
          }
          window.history.replaceState({}, "", newUrl);
        } else {
          showError("Error loading birthdays: " + response.error);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
        showError("Failed to load birthdays. Please try again.");
      },
      complete: function () {
        // Reset loading state
        if (showLoading) {
          searchInput.css("background", "");
        }
      },
    });
  }

  function showError(message) {
    const errorHtml = `
            <div class="birthday-section">
                <div class="birthday-grid">
                    <div class="no-birthdays">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>${message}</p>
                    </div>
                </div>
            </div>
        `;
    $("#birthdayResults").html(errorHtml);
  }

  // Clear search functionality
  function clearSearch() {
    $("#searchEmployees").val("");
    $("#monthFilter").val("all");
    performSearch();
  }


  window.clearBirthdaySearch = clearSearch;
});

$(document)
  .on("mouseenter", ".employee-card", function () {
    $(this).addClass("card-hover");
  })
  .on("mouseleave", ".employee-card", function () {
    $(this).removeClass("card-hover");
  });
