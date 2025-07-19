function initializeNotifications() {
  if (!("Notification" in window)) {
    return;
  }

  if (Notification.permission !== "granted") {
    Notification.requestPermission().then(function (permission) {
      if (permission === "granted") {
        checkForNotifications();
      }
    });
  } else {
    checkForNotifications();
  }
}

function checkForNotifications() {
  fetch("birthday_notifications.php")
    .then((response) => response.json())
    .then((data) => {
      if (data && data.length > 0) {
        data.forEach((notification) => {
          showNotification(notification);
        });
      }
    })
    .catch((error) => {});
}

function showNotification(data) {
  if (Notification.permission === "granted") {
    // Create a unique tag for each notification
    const uniqueId = Math.random().toString(36).substring(7);
    const tag = `${data.title}-${uniqueId}`;

    const notification = new Notification(data.title, {
      body: data.body,
      icon: data.icon,
      requireInteraction: true, // Notification will remain until user clicks or dismisses it
      tag: tag, // Each notification gets a unique tag
      silent: false, // Allow sound
    });

    notification.onclick = function () {
      window.focus();
      // Open the link in a new tab to allow clicking multiple notifications
      if (data.title.includes("Birthday")) {
        window.open(
          "http://localhost/attendance-management/birthday.php",
          "_blank"
        );
      } else if (data.title.includes("Anniversary")) {
        window.open(
          "http://localhost/attendance-management/anniversary.php",
          "_blank"
        );
      }
      // Close the clicked notification
      notification.close();
    };
  }
}

// Initialize when page loads
document.addEventListener("DOMContentLoaded", function () {
  initializeNotifications();

  // Check at 10 AM
  const now = new Date();
  const scheduledTime = new Date(
    now.getFullYear(),
    now.getMonth(),
    now.getDate(),
    10,
    0,
    0
  );

  if (now > scheduledTime) {
    scheduledTime.setDate(scheduledTime.getDate() + 1);
  }

  setTimeout(() => {
    checkForNotifications();
    // Check daily at 10 AM
    setInterval(checkForNotifications, 86400000);
  }, scheduledTime.getTime() - now.getTime());
});
