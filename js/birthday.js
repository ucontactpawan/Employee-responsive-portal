document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchEmployees");
  const monthFilter = document.getElementById("monthFilter");

  // Search functionality
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase();
      const employeeCards = document.querySelectorAll(".employee-card");

      employeeCards.forEach((card) => {
        const name = card.querySelector("h3").textContent.toLowerCase();
        const employeeId = card
          .querySelector(".employee-id")
          .textContent.toLowerCase();

        if (name.includes(searchTerm) || employeeId.includes(searchTerm)) {
          card.style.display = "block";
        } else {
          card.style.display = "none";
        }
      });

      // Check if no cards are visible
      checkNoResults();
    });
  }

  // Month filter functionality
  if (monthFilter) {
    monthFilter.addEventListener("change", function () {
      const selectedMonth = this.value;

      // Reload page with selected month filter
      const url = new URL(window.location);
      if (selectedMonth === "all") {
        url.searchParams.delete("month");
      } else {
        url.searchParams.set("month", selectedMonth);
      }
      window.location.href = url.toString();
    });
  }

  // Function to check if no results are shown
  function checkNoResults() {
    const employeeCards = document.querySelectorAll(".employee-card");
    const visibleCards = Array.from(employeeCards).filter(
      (card) => card.style.display !== "none"
    );
    const noBirthdaysDiv = document.querySelector(".no-birthdays");

    if (visibleCards.length === 0 && !noBirthdaysDiv) {
      const birthdayGrid = document.querySelector(".birthday-grid");
      const noResultsDiv = document.createElement("div");
      noResultsDiv.className = "no-birthdays";
      noResultsDiv.innerHTML =
        '<i class="fas fa-search"></i><p>No employees found matching your search</p>';
      birthdayGrid.appendChild(noResultsDiv);
    } else if (visibleCards.length > 0) {
      const noResultsDiv = document.querySelector(".no-birthdays");
      if (
        noResultsDiv &&
        noResultsDiv.textContent.includes("No employees found")
      ) {
        noResultsDiv.remove();
      }
    }
  }

  // Add smooth hover animations
  const employeeCards = document.querySelectorAll(".employee-card");
  employeeCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-8px) scale(1.02)";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)";
    });
  });
});
