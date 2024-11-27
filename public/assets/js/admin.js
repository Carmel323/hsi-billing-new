$(document).ready(function () {
  $(".toggle-btn").click(function () {
    $("#sidebar").toggleClass("expand");
  });

  function sidebarExpand() {
    if ($(window).width() <= 800) {
      $("#sidebar").removeClass("expand");
    } else {
      $("#sidebar").addClass("expand");
    }
  }

  sidebarExpand();

  function showTextbox() {
    $("#textbox").css("display", "block").prop("required", true);
  }

  function hideTextbox() {
    $("#textbox").css("display", "none");
  }

  $("#autoRenew").on("click", hideTextbox);

  $("#expires").on("click", showTextbox);

  function checkPlanCode(planCode) {
    $.ajax({
      url: "/check-plan-code",
      method: "POST",
      data: {
        plan_code: planCode,
      },
      success: function (response) {
        if (response.exists) {
          console.log("Plan code already exists");
        } else {
          console.log("Plan code is available");
        }
      },
      error: function (xhr, status, error) {
        console.error(error);
      },
    });
  }

  $('#add-plans form input[name="name"]').on("input", function () {
    var planName = $(this).val();

    var sanitizedPlanName = planName.replace(/[^a-zA-Z0-9\s]/g, "");

    $(this).val(sanitizedPlanName);

    var planName = sanitizedPlanName
      .trim()
      .toLowerCase()
      .replace(/\s{2,}/g, "-")
      .replace(/\s+/g, "-")
      .replace(/[^a-z0-9-]+/g, "");

    var planCode = planName.replace(/-{2,}/g, "-");

    $('input[name="plan_code"]').val(planCode);

    checkPlanCode(planCode);
  });

  $("#add-plans form").submit(function (event) {
    var name = $('input[name="name"]').val().trim();
    var recurringPrice = $('input[name="recurring_price"]').val().trim();
    var interval = $('input[name="interval"]').val().trim();
    var billingCycles = $('input[name="billing_cycles"]:checked').val();
    var billingCyclesNo = $('input[name="billing_cycles_no"]').val().trim();
    var productType = $('input[name="product_type"]:checked').val();

    var isValid = true;

    if (name === "") {
      $('input[name="name"]').addClass("is-invalid");

      isValid = false;
    } else {
      $('input[name="name"]').removeClass("is-invalid");
    }

    if (recurringPrice === "") {
      $('input[name="recurring_price"]').addClass("is-invalid");
      isValid = false;
    } else {
      $('input[name="recurring_price"]').removeClass("is-invalid");
    }

    if (billingCycles === "1" && billingCyclesNo === "") {
      $('input[name="billing_cycles_no"]').addClass("is-invalid");

      isValid = false;
    } else {
      $('input[name="billing_cycles_no"]').removeClass("is-invalid");
    }

    if (!isValid) {
      event.preventDefault(); // Prevent form submission if validation fails
    }
  });
});

$('input[name="addon_name"]').on("change", function () {
  var addonName = $(this).val();

  var sanitizedAddonName = addonName.replace(/[^a-zA-Z0-9\s]/g, "");

  $(this).val(sanitizedAddonName);

  var addonName = sanitizedAddonName
    .trim()
    .toLowerCase()
    .replace(/\s{2,}/g, "-")
    .replace(/\s+/g, "-")
    .replace(/[^a-z0-9-]+/g, "");

  var addonCode = addonName.replace(/-{2,}/g, "-");

  $('input[name="addon_code"]').val(addonCode);

  checkAddonCode(addonCode);
});

function checkAddonCode(addonCode) {
  $.ajax({
    url: "/check-addon-code",
    method: "POST",
    data: {
      addon_code: addonCode,
    },
    success: function (response) {
      if (response.exists) {
        console.log("Add-On code already exists");
      } else {
        console.log("Add-On code is available");
      }
    },
    error: function (xhr, status, error) {
      console.error(error);
    },
  });
}
const yourForm = document.getElementById("yourForm");
if (yourForm) {
  yourForm.addEventListener("submit", function () {
    // Get all checkboxes in the form
    const checkboxes = document.querySelectorAll(".form-check-input");

    checkboxes.forEach(function (checkbox) {
      if (checkbox.disabled) {
        // Create a hidden input to hold the value of disabled checkboxes
        let hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = checkbox.name;
        hiddenInput.value = checkbox.value;

        // Append hidden input to the form
        this.appendChild(hiddenInput);
      }
    }, this);
  });
}
