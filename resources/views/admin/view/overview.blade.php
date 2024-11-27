@extends('layouts.view-partner-template')

@section('child-content')

<div style="width:80%" class="d-flex flex-row justify-content-between mt-5">
    <div>
        <h5 class="fw-bold">Overview</h5>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 mb-4 bg-clearlink">
            <div class="card-body">
                <h4 class="right-margin">Account Details
                    <!-- @if($partner->is_approved)
                    (Approved)
                    @else
                    <a href="/approve-partner/{{$partner->id}}" class="btn btn-primary btn-sm">Approve</a>
                    @endif -->
                </h4>

                <div class="d-flex flex-row justify-content-between">
                    <div class="col-lg-10 ">
                        <p class="m-0"><i class="fa text-primary fa-building right-margin fw-bold" aria-hidden="true"></i> <strong>{{ $partner->company_name ?? '' }}</strong></p>

                    </div>

                    <div class="col-lg mt-2">
                        <a style="cursor:pointer;" class="text-primary" data-toggle="tooltip" title="Edit" data-bs-toggle="modal" data-bs-target="#partnerUpdateModal"><i class="fa-regular fa-pen-to-square"></i></a>
                        @if($partner->status === 'active')
                        <a href="disable-partner/{{$partner->id}}" data-toggle="tooltip" title="Mark as Inactive" class="text-primary ms-3">

                            <i class="fa-solid fa-user-slash"></i>
                        </a>
                        @else
                        <a href="reactivate-partner/{{$partner->id}}" data-toggle="tooltip" title="Mark as Active" class="text-primary ms-3">
                            <i class="fa-solid fa-user"></i>
                        </a>
                        @endif

                    </div>
                </div>
                <div class="right-margin mb-0 w-100">
                    <div class="row ">
                        <div class="d-flex flex-row col-lg">
                            <p class="fw-bold left-margin ">Adveriser Id:</p><span class="ms-2">{{$partner->isp_advertiser_id}}</span>
                        </div>

                        <div class="d-flex flex-row col-lg">
                            @if($partner->tax_number)
                            <p class="fw-bold left-margin">EIN ID:</p><span class="ms-2">{{$partner->tax_number}}</span>
                            @endif
                        </div>

                    </div>

                </div>

                <div class="right-margin">
                    <div class="d-flex flex-row justify-content-between">
                        <div class="col-lg-10">
                            <p class="fw-bold left-margin ">Affiliate Ids:</p>
                        </div>

                        <div class="d-flex flex-row col-lg">
                            <p><a data-bs-toggle="modal" data-toggle="tooltip" title="Add Affiliate" data-bs-target="#addAffiliateModal" style="cursor:pointer;" class="text-dark fw-bold"><i class="fa-solid fa-circle-plus text-primary ms-3"></i></a></p>
                            @if($subscription === null)
                            <p><a data-bs-toggle="modal" data-toggle="tooltip" title="Remove Affiliate" data-bs-target="#removeAffiliateModal" style="cursor:pointer;" class="text-dark  fw-bold"><i class="fa-solid fa-circle-minus text-primary  ms-4"></i></a></p>
                            @endif
                        </div>

                    </div>
                    <ul class="right-margin">
                        @foreach($isp_affiliates as $isp_affiliate)
                        <li class="billing">{{$isp_affiliate->isp_affiliate_id}}({{$isp_affiliate->domain_name}})</li>
                        @endforeach
                    </ul>
                </div>





                <div class="accordion accordion-flush border-0 shadow-none right-margin mb-0 " id="accordionExample">
                    <div class="accordion-item bg-clearlink">
                        <h2 class="accordion-header">
                            <button class="accordion-button p-0 fw-bold border-0 outline-0 border-bottom fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <h4> Address</h4>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                            <div class="accordion-body p-0 pt-2 ">
                                <div class="d-flex flex-row justify-content-between">
                                    <div class="d-flex flex-row ">
                                        <div class="m-0"><i class="fa fa-address-card  text-primary" aria-hidden="true"></i></div>
                                        <div class="right-margin"><strong> Billing Address</strong><br>{{ $partner_address->street ?? '' }}<br>{{ $partner_address->city ?? '' }},<br>{{ $partner_address->state ?? '' }},{{ $partner_address->zip_code ?? '' }}<br>{{ $partner_address->country ?? '' }}</div>
                                    </div>
                                    <div class="text-primary fs-5">
                                        <a style="cursor:pointer;" data-toggle="tooltip" title="Edit" data-bs-toggle="modal" data-bs-target="#addressUpdateModal"><i class="fa-regular fa-pen-to-square"></i></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>


                <div class="row right-margin">
                    <div class="d-flex flex-row  justify-content-between">
                        <div>
                            @if($paymentmethod)
                            @if($paymentmethod->type === "bank_account")
                            <h4>Bank Details</h4>
                            @elseif($paymentmethod->type === "card")
                            <h4>Card Details</h4>
                            @endif
                            @else
                            <h4>Payment Details</h4>
                            @endif

                        </div>
                        <div>
                            @if($paymentmethod === null)
                            <a href="add-payment-method/{{$partner->id}}"><i data-toggle="tooltip" title="Associate a payment method" class="fa-solid fa-circle-plus"></i></a>
                            @endif
                        </div>

                    </div>
                    <div class="col-lg">
                        <div class="card w-100 border-1 rounded mb-1 bg-clearlink">
                            <div class="card-body d-flex flex-row justify-content-between">
                                <div class="text-dark fw-bold">
                                    @if($paymentmethod)
                                    @if($paymentmethod->type === "bank_account")
                                    <i class="fa-solid fa-building-columns text-primary me-3"></i>
                                    @elseif($paymentmethod->type === "card")
                                    <i class="fa-regular fa-credit-card text-primary me-3"></i>
                                    @endif
                                    {{ $paymentmethod ? 'XXXX XXXX XXXX ' . $paymentmethod->last_four_digits : '-' }}
                                    @else
                                    <span class="fw-normal"> No details found</span>
                                    @endif
                                </div>
                                <!-- <div>
                                    @if($paymentmethod)
                                    <a href="/delete-payment-method/{{$partner->zoho_cust_id}}" class="text-dark fw-bold">Remove</a>
                                    @endif
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="col-lg me-4 ">

        @if($users->isNotEmpty())
        <div class="card border-0 rounded me-4 mb-5 table-responsive bg-clearlink w-100">
            <div class="card-body">
                <div class="d-flex flex-row mb-5 justify-content-between">
                    <h4 class="ms-3">Users</h4>

                    <a data-bs-toggle="modal" data-bs-target="#addUserModal" class=" btn btn-primary btn-sm me-3">Invite User</a>
                </div>

                @foreach($users as $index => $user)


                @if($index > 0)
                <hr class="borders-clearlink">
                @endif
                <div class="d-flex flex-row justify-content-between w-100">

                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg ms-3">
                                <p class="p-0 m-0"><strong>{{$user->first_name}}&nbsp;{{$user->last_name}}@if($user->is_primary)<span>(Primary)</span>@endif</strong></p>
                                <p class="p-0 m-0">{{$user->email}}</p>
                            </div>
                            <div class="col-lg ms-2">
                                @if($user->invitation_status === 'Invited')
                                <td data-label="Invitation Status">
                                    <form action="{{ route('invite-again',[],false) }}" method="post">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $partner->id }}">
                                        <input type="hidden" name="zoho_cpid" value="{{ $user->zoho_cpid }}">
                                        <button type="submit" class="btn btn-sm rounded button-clearlink text-primary fw-bold">Resend Invite</button>
                                    </form>
                                </td>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg mt-2 ms-5">
                        <a style="cursor:pointer;" class="text-primary ms-3" data-toggle="tooltip" title="Edit" data-bs-toggle="modal" data-bs-target="#userUpdateModal{{$user->zoho_cpid}}"><i class="fa-regular fa-pen-to-square"></i></a>
                        @if($user->status === 'active')
                        <a href="disable-user/{{$user->zoho_cpid}}" data-toggle="tooltip" title="Mark as Inactive" class="text-primary ms-3">

                            <i class="fa-solid fa-user-slash"></i>
                        </a>
                        @else
                        <a href="reactivate-user/{{$user->zoho_cpid}}" data-toggle="tooltip" title="Mark as Active" class="text-primary ms-3">
                            <i class="fa-solid fa-user"></i>
                        </a>
                        @endif
                        @if(!$user->is_primary)
                        <a href="mark-primary/{{$user->zoho_cpid}}" data-toggle="tooltip" title="Mark as Primary" class="text-primary ms-3">
                            <i class="fa-solid fa-check"></i>
                        </a>
                        @endif
                    </div>

                </div>


                <div class="modal fade" id="userUpdateModal{{$user->zoho_cpid}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content bg-popup">
                            <div class=" modal-header">
                                <h3 class="modal-title" id="exampleModalLabel">Enter User Details</h3>
                                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                            </div>
                            <div class="modal-body">
                                <form action="/update-user" method="post">
                                    @csrf
                                    <div>
                                        <div class=" mb-3 row">
                                            <div class="col-lg">
                                                <input name="first_name" class="ms-2 form-control" value="{{$user->first_name}}" required placeholder="First Name">
                                            </div>
                                            <div class="col-lg">
                                                <input name="last_name" class="ms-2 form-control" value="{{$user->last_name}}" required placeholder="Last Name">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <div class="col-lg">
                                            <input name="email" class="ms-2 form-control" value="{{$user->email}}" required placeholder="Email">
                                        </div>
                                        <div class="col-lg">
                                            <input name="phone_number" class="ms-2 form-control" value="{{$user->phone_number}}" required placeholder="Phone Number">
                                        </div>
                                    </div>
                                    <input type="text" value="{{$user->zoho_cpid}}" name="zoho_cpid" hidden />
                                    <input type="text" value="{{$partner->zoho_cust_id}}" name="zoho_cust_id" hidden />
                                    <input type="submit" class="btn btn-primary px-3 py-2 rounded" value="Save Changes">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="removeAffiliateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content bg-popup">
                            <div class=" modal-header">
                                <h3 class="modal-title" id="exampleModalLabel">Remove Affiliate Id</h3>
                                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                            </div>
                            <div class="modal-body">
                                <form action="/remove-affiliate" method="post">
                                    @csrf
                                    <div class=" row">
                                        <label for="affiliate_id" class="form-label fw-bold">Select Affiliate Ids*</label>
                                        <div class="custom-dropdown-remove">
                                            <div class="custom-dropdown-button-remove rounded">
                                                <div class="tags-container-remove"><input type="hidden" id="hiddenSelect-remove" name="affiliate_ids[]" /></div>
                                                <i class="fas fa-chevron-down dropdown-icon text-secondary"></i>
                                            </div>
                                            <div class="custom-dropdown-content-remove">
                                                @foreach($isp_affiliates as $affiliate)
                                                <option class="custom-option-remove" data-value="{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})">{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})</option>
                                                @endforeach
                                            </div>
                                        </div>
                                        <input name="partner_id" value="{{$partner->id}}" hidden />
                                    </div>
                                    <div class="modal-footer border-0 p-0 mt-3">
                                        <input type="submit" class="btn btn-primary py-1 rounded " value="Save">
                                        <button type="button" id="cancel" onclick="reload()" class="btn button-clearlink text-primary fw-bold">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="addAffiliateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content bg-popup">
                            <div class=" modal-header">
                                <h3 class="modal-title" id="exampleModalLabel">Add Affiliate Id</h3>
                                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
                            </div>
                            <div class="modal-body">
                                <form action="/add-affiliate-id" method="post">
                                    @csrf
                                    <div class=" row">
                                        <label for="affiliate_id" class="form-label fw-bold">Select Affiliate Ids*</label>
                                        <div class="custom-dropdown">
                                            <div class="custom-dropdown-button rounded">
                                                <div class="tags-container"><input type="hidden" id="hiddenSelect" name="affiliate_ids[]" /></div>
                                                <i class="fas fa-chevron-down dropdown-icon text-secondary"></i>
                                            </div>
                                            <div class="custom-dropdown-content">
                                                @foreach($remaining_affiliates as $affiliate)
                                                <option class="custom-option" data-value="{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})">{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})</option>
                                                @endforeach
                                            </div>
                                        </div>
                                        <input name="partner_id" value="{{$partner->id}}" hidden />
                                    </div>
                                    <div class="modal-footer border-0 p-0 mt-3">
                                        <input type="submit" class="btn btn-primary py-1 rounded " value="Save">
                                        <button type="button" id="cancel" onclick="reload()" class="btn button-clearlink text-primary fw-bold">Cancel</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @endforeach
                @endif

            </div>
        </div>
    </div>

</div>

<!-- <div class="row">
    <div class="col-lg-6 ">
        <div class="card border  border-dark ms-4 me-4  mb-5">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb1"><strong>OUTSTANDING INVOICES </strong><br> </h6>
                        <p class="mb4">
                            {{ $partner->outstanding_invoices ?? 0 }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb1"><strong>AVAILABLE CREDITS</strong> </h6>
                        <p class="mb4">
                            {{ $partner->unused_credits ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->



<div class="modal fade" id="partnerUpdateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content bg-popup">
            <div class=" modal-header">
                <h3 class="modal-title " id="exampleModalLabel">Enter Partner Details</h3>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">
                <form action="/update-partner" method="post">
                    @csrf
                    <div class="mb-3 row">
                        <div class="col-lg">
                            <label for="company_name" class="form-label fw-bold">Company Name*</label>
                            <input name="company_name" class="form-control" value="{{$partner->company_name}}" required placeholder="Company Name">
                        </div>
                        <div class="col-lg">
                            <label for="advertiser_id" class="form-label fw-bold">Advertiser Id*</label>
                            <input name="advertiser_id" class=" form-control" value="{{$partner->isp_advertiser_id}}" placeholder="Advertiser ID" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-lg-6">
                            <label for="tax_number" class="form-label fw-bold">EIN ID</label>
                            <input name="tax_number" class=" form-control" value="{{$partner->tax_number}}" placeholder="EIN ID">
                        </div>
                    </div>
                    <input type="text" value="{{$partner->zoho_cust_id}}" name="zoho_cust_id" hidden />
                    <input type="text" value="{{$partner->id}}" name="partner_id" hidden />
                    <input type="submit" class="btn btn-primary px-3 py-2 rounded " value="Save Changes">
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addressUpdateModal" tabindex="-1" aria-labelledby="addressUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class="modal-header">
                <h3 class="modal-title " id="addressUpdateModalLabel">Kindly Enter Your Address Details</h3>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">
                <form action="/update-billing-address" method="post">
                    @csrf
                    <div class="mb-3 row">
                        <div class="col-lg">
                            <input name="address" class="ms-2 form-control" placeholder="Address*">
                        </div>
                        <div class="col-lg">
                            <input name="city" class="ms-2 form-control" placeholder="City*">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-lg">
                            <input name="state" class="ms-2 form-control" placeholder="State*">
                        </div>
                        <div class="col-lg">
                            <input name="zip_code" class="ms-2 form-control" placeholder="Zip Code*">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-lg-6">
                            <input name="country" class="ms-2 form-control" placeholder="Country*">
                        </div>
                    </div>
                    <input type="text" value="{{$partner->zoho_cust_id}}" name="zoho_cust_id" hidden />
                    <input type="submit" class="btn btn-primary text-white px-3 py-2 rounded" value="Save Changes">
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-popup">
            <div class=" modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Invite User </h3>
                <button type="button" class="close border-0" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fs-3"></i></button>
            </div>
            <div class="modal-body">
                <form action="/admin/invite-user/{{$partner->id}}" method="post">
                    @csrf
                    <div>
                        <div class=" mb-3 row">
                            <div class="col-lg">
                                <input name="first_name" class="ms-2 form-control" placeholder="First Name*" required>
                            </div>
                            <div class="col-lg">
                                <input name="last_name" class="ms-2 form-control" placeholder="Last Name*" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-lg">
                            <input name="email" class="ms-2 form-control" placeholder="Email*" required>
                        </div>
                        <div class="col-lg">
                            <input name="phone_number" class="ms-2 form-control" placeholder="Phone Number*" required>
                        </div>
                    </div>
                    <input name="zoho_cust_id" value="{{$partner->zoho_cust_id}}" hidden />
                    <input type="submit" class="btn btn-primary text-white px-3 py-2 rounded " value="Save Changes">
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const dropdownButton = document.querySelector(".custom-dropdown-button");
    const dropdownContent = document.querySelector(
        ".custom-dropdown-content"
    );
    const tagsContainer = dropdownButton.querySelector(".tags-container");
    const buttonText = dropdownButton.querySelector(".button-text");
    const hiddenSelect = document.getElementById("hiddenSelect");

    dropdownButton.addEventListener("click", () => {
        const isVisible = dropdownContent.style.display === "block";
        dropdownContent.style.display = isVisible ? "none" : "block";
    });

    document.addEventListener("click", (event) => {
        if (
            !dropdownButton.contains(event.target) &&
            !dropdownContent.contains(event.target)
        ) {
            dropdownContent.style.display = "none";
        }
    });

    dropdownContent.addEventListener("click", (event) => {
        const selectedDiv = event.target;
        if (selectedDiv && selectedDiv.dataset.value) {
            const value = selectedDiv.dataset.value;

            // Create a tag element
            const tag = document.createElement("div");
            tag.classList.add("tag");
            tag.innerHTML = `${value} <span class="remove-tag">&times;</span>`;

            // Append the tag to the container
            tagsContainer.appendChild(tag);

            const currentValues = hiddenSelect.value ? hiddenSelect.value.split(',') : [];
            if (!currentValues.includes(value)) {
                currentValues.push(value);
                hiddenSelect.value = currentValues.join(',');
            }

            selectedDiv.remove();

            // Hide the dropdown content
            dropdownContent.style.display = "none";
        }
    });

    tagsContainer.addEventListener("click", (event) => {
        if (event.target.classList.contains("remove-tag")) {
            const tagToRemove = event.target.parentElement;
            tagsContainer.removeChild(tagToRemove);

            const valueToRemove = tagToRemove.textContent.trim().slice(0, -1); // Remove the '×'
            const currentValues = hiddenSelect.value.split(',');
            const updatedValues = currentValues.filter(value => value !== valueToRemove);
            hiddenSelect.value = updatedValues.join(',');

            const optionDiv = document.createElement("div");
            optionDiv.dataset.value = valueToRemove;
            optionDiv.textContent = valueToRemove;
            dropdownContent.appendChild(optionDiv);

            // If no tags remain, reset the button text
            if (tagsContainer.children.length === 0) {
                buttonText.textContent = "Select an option";
            }
        }
    });
</script>
<script>
    const dropdownButton1 = document.querySelector(".custom-dropdown-button-remove");
    const dropdownContent1 = document.querySelector(
        ".custom-dropdown-content-remove"
    );
    const tagsContainer1 = dropdownButton1.querySelector(".tags-container-remove");
    const buttonText1 = dropdownButton1.querySelector(".button-text-remove");
    const hiddenSelect1 = document.getElementById("hiddenSelect-remove");

    dropdownButton1.addEventListener("click", () => {
        const isVisible = dropdownContent1.style.display === "block";
        dropdownContent1.style.display = isVisible ? "none" : "block";
    });

    document.addEventListener("click", (event) => {
        if (
            !dropdownButton1.contains(event.target) &&
            !dropdownContent1.contains(event.target)
        ) {
            dropdownContent1.style.display = "none";
        }
    });

    dropdownContent1.addEventListener("click", (event) => {
        const selectedDiv1 = event.target;
        if (selectedDiv1 && selectedDiv1.dataset.value) {
            const value1 = selectedDiv1.dataset.value;

            // Create a tag element
            const tag1 = document.createElement("div");
            tag1.classList.add("tag-remove");
            tag1.innerHTML = `${value1} <span class="remove-tag-remove">&times;</span>`;


            // Append the tag to the container
            tagsContainer1.appendChild(tag1);

            const currentValues1 = hiddenSelect1.value ? hiddenSelect1.value.split(',') : [];
            if (!currentValues1.includes(value1)) {
                currentValues1.push(value1);
                hiddenSelect1.value = currentValues1.join(',');
            }

            selectedDiv1.remove();

            // Hide the dropdown content
            dropdownContent1.style.display = "none";
        }
    });

    tagsContainer1.addEventListener("click", (event) => {
        if (event.target.classList.contains("remove-tag-remove")) {
            const tagToRemove1 = event.target.parentElement;
            tagsContainer1.removeChild(tagToRemove1);

            const valueToRemove1 = tagToRemove1.textContent.trim().slice(0, -1); // Remove the '×'
            const currentValues1 = hiddenSelect1.value.split(',');
            const updatedValues1 = currentValues1.filter(value => value !== valueToRemove1);
            hiddenSelect1.value = updatedValues1.join(',');

            const optionDiv1 = document.createElement("div");
            optionDiv1.dataset.value = valueToRemove1;
            optionDiv1.textContent = valueToRemove1;
            dropdownContent1.appendChild(optionDiv1);

            // If no tags remain, reset the button text
            if (tagsContainer1.children.length === 0) {
                buttonText1.textContent = "Select an option";
            }
        }
    });

    function reload() {
        window.location.reload();
    }
</script>
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#affiliateDropdownAdd").change(function() {
            var selectedAffiliateAdd = $(this).val();

            if (selectedAffiliateAdd) {
                // Append to textarea
                var currentText = $("#selectedAffiliatesDisplayAdd").val();
                var newText =
                    currentText.length > 0 ?
                    currentText + "," + selectedAffiliateAdd :
                    selectedAffiliateAdd;
                $("#selectedAffiliatesDisplayAdd").val(newText);

                // Remove selected option from dropdown
                $(this)
                    .find('option[value="' + selectedAffiliateAdd + '"]')
                    .remove();
            }
        });

        $("#affiliateDropdownRemove").change(function() {
            var selectedAffiliateRemove = $(this).val();

            if (selectedAffiliateRemove) {
                // Append to textarea
                var currentText = $("#selectedAffiliatesDisplayRemove").val();
                var newText =
                    currentText.length > 0 ?
                    currentText + "," + selectedAffiliateRemove :
                    selectedAffiliateRemove;
                $("#selectedAffiliatesDisplayRemove").val(newText);

                // Remove selected option from dropdown
                $(this)
                    .find('option[value="' + selectedAffiliateRemove + '"]')
                    .remove();
            }
        });
    });

    function reload() {
        window.location.reload();
    }
</script> -->



@endsection