<div class="modal fade" id="showAlertModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class="modal-header d-flex justify-content-end border-0 bg-popup">
                <button type="button" class="close border-0 bg-popup" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid text-dark fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body mb-5">
                <h3 class="message"> Please complete the following to create a Subscription </h3>
                <ul class="message">
                    <li class="d-flex justify-content-between"><span>Upload Logo (Company Info)</span>@if($company_info) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                    <li class="d-flex justify-content-between"><span>Add Company Name (Company Info) </span>@if($company_info) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                    <li class="d-flex justify-content-between"><span>Set Landing Page URL (Company Info)</span>@if($company_info) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                    <li class="d-flex justify-content-between"><span>Upload Provider Data</span>@if($availability_data) <i class="fa-solid fa-check text-check fs-3"></i>@endif</li>
                </ul>
            </div>
            <div class="modal-footer border-0">
            </div>
        </div>
    </div>
</div>