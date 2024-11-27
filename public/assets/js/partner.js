$(document).ready(function () {
  $(".download-presigned-url").on("click", function () {
    const fileUrl = $(this).data("url");

    const csrfToken = $(this).data("token");

    $.ajax({
      url: "/download-presigned-url",
      method: "POST",
      data: {
        url: fileUrl,
        _token: csrfToken,
      },
      success: function (response) {
        if (response.url) {
          window.location.href = response.url;
        } else {
          console.log("Failed to generate download URL");
        }
      },
      error: function () {
        console.log("Error generating download URL");
      },
    });
  });

  $(".download-presigned-logo").on("click", function () {
    const fileUrl = $(this).data("url");
    const csrfToken = $(this).data("token");

    $.ajax({
      url: "/download-presigned-url",
      method: "POST",
      data: {
        url: fileUrl,
        _token: csrfToken,
      },
      success: function (response) {
        if (response.url) {
          const fileName = fileUrl.split("/").pop();

          downloadFile(response.url, fileName);
        } else {
          console.log("No URL returned from the server.");
        }
      },
      error: function (xhr) {
        console.log("Error sending request to the server. Status:", xhr.status);
        console.log("Response text:", xhr.responseText);
      },
    });
  });

  $("#sidebarOpen").click(function () {
    var sidebar = $("#sidebar");
    if (sidebar.css("display") === "none" || sidebar.css("display") === "") {
      sidebar.css("display", "flex");
    } else {
      sidebar.css("display", "none");
    }
  });

  function downloadFile(presignedUrl, fileName) {
    const link = document.createElement("a");

    link.href = presignedUrl;

    link.download = fileName;

    link.target = "_blank";

    link.click();
  }

  $("#sidebarClose").click(function () {
    var sidebar = $("#sidebar");

    if (sidebar.css("display") === "none" || sidebar.css("display") === "") {
      sidebar.css("display", "flex");
    } else {
      sidebar.css("display", "none");
    }
  });

  function myFunction() {
    if ($(window).width() <= 800) {
      $("#sidebar").removeClass("expand");
    } else {
      $("#sidebar").addClass("expand");
    }
  }

  myFunction(); // Call the function initially
  $(window).resize(myFunction); // Call the function whenever the window is resized
});

// Get references to the DOM elements
const uploadInput = document.getElementById("uploadImage");
const previewImage = document.getElementById("previewImage");

if (uploadInput !== null && uploadInput !== undefined) {
  // Add an event listener to the input field
  uploadInput.addEventListener("change", function () {
    // Ensure that a file is selected
    if (uploadInput.files && uploadInput.files[0]) {
      // Create a FileReader object
      const reader = new FileReader();
      if (previewImage) {
        // Set up the FileReader onload function
        reader.onload = function (e) {
          // Set the preview image source to the uploaded image data

          previewImage.src = e.target.result;

          previewImage.style.display = "inline";
          document.getElementById("required").style.display = "none";
        };
      }

      // Read the uploaded file as a data URL
      reader.readAsDataURL(uploadInput.files[0]);
    }
  });
}

const uploadCSV = document.getElementById("csvFile");
if (uploadCSV !== null && uploadCSV !== undefined) {
  uploadCSV.addEventListener("change", function () {
    if (uploadCSV.files && uploadCSV.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const contents = e.target.result;
        console.log("Uploaded CSV contents:", contents);
        document.getElementById("filename").textContent =
          uploadCSV.files[0].name;
        document.getElementById("mandatory").style.display = "none";
        // You can process the CSV contents here
      };
      reader.readAsText(uploadCSV.files[0]);
    }
  });
}

$(document).ready(function () {
  $('[data-toggle="tooltip"]').tooltip();
});

const alert = document.getElementById("alert");
const overlay = document.getElementById("overlay");
if (alert !== null && alert !== undefined) {
  if (overlay !== null && overlay !== undefined) {
    if (alert) {
      setTimeout(function () {
        alert.style.display = "none";
        overlay.style.display = "none";
      }, 5000);

      if (alert.style.display !== "none") {
        overlay.style.display = "block";
      } else if (alert.style.display === "none") {
        overlay.style.display = "none";
      }
    }
  }
}

const close = document.getElementById("alert-close");
if (close !== null && close !== undefined) {
  close.addEventListener("click", () => {
    closeOverlay();
  });
}

function closeOverlay() {
  document.getElementById("overlay").style.display = "none";
}

document.addEventListener("DOMContentLoaded", function () {
  const passwordField = document.getElementById("password");
  const togglePassword = document.querySelector(".password-toggle-icon");

  if (togglePassword !== null && togglePassword !== undefined) {
    togglePassword.addEventListener("click", function () {
      if (passwordField.type === "password") {
        passwordField.type = "text";
        togglePassword.classList.remove("fa-eye");
        togglePassword.classList.add("fa-eye-slash");
      } else {
        passwordField.type = "password";
        togglePassword.classList.remove("fa-eye-slash");
        togglePassword.classList.add("fa-eye");
      }
    });
  }

  const passwordField1 = document.getElementById("password1");
  const togglePassword1 = document.querySelector(".password-toggle-icon1");
  if (togglePassword1 !== null && togglePassword1 !== undefined) {
    togglePassword1.addEventListener("click", function () {
      if (passwordField1.type === "password") {
        passwordField1.type = "text";
        togglePassword1.classList.remove("fa-eye");
        togglePassword1.classList.add("fa-eye-slash");
      } else {
        passwordField1.type = "password";
        togglePassword1.classList.remove("fa-eye-slash");
        togglePassword1.classList.add("fa-eye");
      }
    });
  }
});
