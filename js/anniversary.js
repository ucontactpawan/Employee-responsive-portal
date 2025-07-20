document.addEventListener("DOMContentLoaded", function () {
  // Month filter functionality
  const monthFilter = document.getElementById("monthFilter");
  if (monthFilter) {
    monthFilter.addEventListener("change", function () {
      window.location.href = `anniversary.php?month=${this.value}`;
    });
  }

  // Search functionality
  const searchInput = document.getElementById("searchEmployees");
  if (searchInput) {
    searchInput.addEventListener("keyup", function () {
      const searchTerm = this.value.toLowerCase();
      filterEmployeeCards(searchTerm);
    });
  }

  // Function to filter employee cards
  function filterEmployeeCards(searchTerm) {
    const cards = document.querySelectorAll(".employee-card");
    let visibleCards = 0;

    cards.forEach((card) => {
      const name = card.querySelector("h3").textContent.toLowerCase();
      const details = card
        .querySelector(".employee-id")
        .textContent.toLowerCase();

      if (name.includes(searchTerm) || details.includes(searchTerm)) {
        card.style.display = "";
        visibleCards++;
      } else {
        card.style.display = "none";
      }
    });

    // Show/hide sections based on visible cards
    document.querySelectorAll(".anniversary-section").forEach((section) => {
      const sectionCards = section.querySelectorAll(".employee-card");
      let visibleSectionCards = 0;

      sectionCards.forEach((card) => {
        if (card.style.display !== "none") {
          visibleSectionCards++;
        }
      });

      if (visibleSectionCards === 0) {
        section.style.display = "none";
      } else {
        section.style.display = "";
      }
    });

    // Show empty state if no cards are visible
    const emptyState = document.querySelector(".empty-state");
    if (emptyState) {
      if (visibleCards === 0) {
        emptyState.style.display = "block";
      } else {
        emptyState.style.display = "none";
      }
    }
  }

  // Initialize tooltips if Bootstrap is loaded
  if (typeof bootstrap !== "undefined" && bootstrap.Tooltip) {
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }
});
