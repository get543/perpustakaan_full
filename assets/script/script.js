//! Theme Dark/Light Mode Script
// --- Persistence and Initialization ---

const themeToggle = document.getElementById("theme-toggle");
const htmlElement = document.documentElement;
const storedTheme = localStorage.getItem("theme");

// Function to update the button text based on the current theme
function updateButtonText(currentTheme) {
  const newText = currentTheme === "dark" ? "☀️" : "🌙";
  themeToggle.textContent = newText;
}

// Set the initial theme based on localStorage (or default to 'light')
if (storedTheme) {
  htmlElement.setAttribute("data-bs-theme", storedTheme);
  updateButtonText(storedTheme);
} else {
  // No stored theme, initialize to light
  htmlElement.setAttribute("data-bs-theme", "light");
  updateButtonText("light");
}

// --- The Toggle Functionality ---

themeToggle.addEventListener("click", () => {
  // 1. Get the current theme
  const currentTheme = htmlElement.getAttribute("data-bs-theme");

  // 2. Determine the new theme
  const newTheme = currentTheme === "dark" ? "light" : "dark";

  // 3. Apply the new theme to the <html> tag
  htmlElement.setAttribute("data-bs-theme", newTheme);

  // 4. Update localStorage for persistence
  localStorage.setItem("theme", newTheme);

  // 5. Update the button text/icon
  updateButtonText(newTheme);
});

//! Form Auto-Fill Script
// Auto-fill form from URL parameters when page loads
window.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);

  const title = urlParams.get("title");
  const author = urlParams.get("author");
  const year = urlParams.get("year");
  const cover = urlParams.get("cover");

  // If we have title parameter, fill the form
  if (title) {
    document.getElementById("title").value = title;
    document.getElementById("author").value = author || "";
    document.getElementById("year").value = year || "";

    if (cover) {
      // Note: Can't set file input value directly for security reasons
      // You might want to show the cover URL in a hidden field or preview instead
      document.getElementById("cover-preview").src = cover;
    }
  }
});

function fillForm(buttonElement) {
  // Read the data from the clicked button
  let title = buttonElement.getAttribute("data-title");
  let author = buttonElement.getAttribute("data-author");
  let year = buttonElement.getAttribute("data-year");
  let cover = buttonElement.getAttribute("data-cover");

  // Put that data into the form inputs using their IDs
  document.getElementById("title").value = title;
  document.getElementById("author").value = author;
  document.getElementById("year").value = year;

  // Note: Can't set file input value directly for security reasons
  // You might want to show the cover URL in a hidden field or preview instead
}