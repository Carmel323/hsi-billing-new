@extends('layouts.admin_template')

@section('content')
<div class="mb-2">
    <h2 class="mt-2 mb-5">Enter Admin Details</h2>
</div>
<form action="/invite-admin" method="post">
    @csrf
    <div class="mb-3 row">
        <div class="col-lg-5 me-5 mb-2">
            <label for="admin_name" class="form-label fw-bold">Admin Name*</label>
            <input name="admin_name" class=" form-control" placeholder="Admin Name*" value="{{ old('admin_name') }}" required>
        </div>
        <div class="col-lg-5 ">
            <label for="admin_email" class="form-label fw-bold">Admin Email</label>
            <input name="email" class=" form-control" placeholder="Admin Email*" value="{{ old('email') }}">
        </div>
    </div>
    <div class="mb-3 row">
        <div class="col-lg-5 me-5 mb-2">
            <label for="admin_name" class="form-label fw-bold">Admin Role*</label>
            <select type="text" name="role" class="form-select" required>
                <option value="">Select Role*</option>
                <option value="Admin">Admin</option>
                <option value="SuperAdmin">Super Admin</option>
                <option value="AlertRecipient">Alert Recipient</option>
            </select>
        </div>
        <div class="col-lg-5 ">

            <label for="receive_mails" class="form-label fw-bold">
                Receive Mail Notifations*
            </label>
            <input type="checkbox" id="myCheckbox" name="receive_mails" class="form-check-input select-plans-input ms-4">
        </div>
    </div>
    <div id="myDiv">
        <div class="mb-3 row ">
            <label for="mail_notification" class="form-label fw-bold my-3">Select Mail Notifications*</label>

            <div class="col-lg-5 ">
                <label for="partner_signup_mail" class="form-label ">
                    Partner Signup Mail*
                </label>
                <input type="checkbox" id="myCheckbox" name="partner_signup_mail" class="form-check-input select-plans-input ms-4">
            </div>
            <div class="col-lg-5 ">
                <label for="plan_purchase_mail" class="form-label ">
                    Plan Purchase Mail*
                </label>
                <input type="checkbox" id="myCheckbox" name="plan_purchase_mail" class="form-check-input select-plans-input ms-4">
            </div>
        </div>
        <div class="mb-3 row ">
            <div class="col-lg-5 ">
                <label for="clicks_alert_mail" class="form-label ">
                    Clicks Alert Mail*
                </label>
                <input type="checkbox" id="myCheckbox" name="clicks_alert_mail" class="form-check-input select-plans-input ms-4">
            </div>
            <div class="col-lg-5 ">
                <label for="receive_mails" class="form-label ">
                    Support Ticket Mail*
                </label>
                <input type="checkbox" id="myCheckbox" name="support_ticket_mail" class="form-check-input select-plans-input ms-4">
            </div>
        </div>
        <div class="mb-3 row ">
            <div class="col-lg-5 ">
                <label for="data_submission_mail" class="form-label ">
                    Provider Data and Company Info Submission Mail*
                </label>
                <input type="checkbox" id="myCheckbox" name="data_submission_mail" class="form-check-input select-plans-input ms-4">
            </div>
        </div>
    </div>
    <input type="submit" class="btn btn-primary text-white px-3 py-2 rounded mt-3" value="Invite">
</form>

<script>
    const checkbox = document.getElementById('myCheckbox');
    const myDiv = document.getElementById('myDiv');
    if (checkbox.checked) {
        myDiv.style.display = "block";
    }
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            myDiv.style.display = 'block';
        } else {
            myDiv.style.display = 'none';
        }
    });
</script>


@endsection