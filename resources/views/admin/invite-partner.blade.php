@extends('layouts.admin_template')

@section('content')
<div class="mb-2">
    <h2 class="mt-2 mb-5">Invite Partners</h2>
</div>
<form action="/invite-partner" method="post">
    @csrf
    <h4 class=" mb-4">Company Details</h4>
    <div class="mb-3 row">
        <div class="col-lg-5 me-5 mb-2">
            <label for="company_name" class="form-label fw-bold">Company Name*</label>
            <input name="company_name" class=" form-control" placeholder="Company Name*" value="{{ old('company_name',isset($lead) ? $lead->company_name : '') }}">
            <span class="text-danger">@error('company_name'){{ $message }} @enderror</span>
        </div>
        <div class="col-lg-5 ">
            <label for="tax_number" class="form-label fw-bold">EIN ID</label>
            <input name="tax_number" class=" form-control" placeholder="EIN ID" value="{{ old('tax_number',isset($lead) ? $lead->tax_number : '' ) }}">
            <span class="text-danger">@error('tax_number'){{ $message }} @enderror</span>
        </div>
    </div>
    <div class="mb-3 row">
        <div class="col-lg-5 me-5 mb-2">
            <div class="d-flex flex-column">
                <label for="affiliate_ids" class="form-label fw-bold">Select Affiliate Ids*</label>
                <div class="custom-dropdown">
                    <div class="custom-dropdown-button rounded">
                        <div class="tags-container"><input type="hidden" id="hiddenSelect" name="affiliate_ids[]" /></div>
                        <i class="fas fa-chevron-down dropdown-icon text-secondary"></i>
                    </div>
                    <div class="custom-dropdown-content">
                        @foreach($affiliates as $affiliate)
                        <option class="custom-option" data-value="{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})">{{$affiliate->isp_affiliate_id}}({{$affiliate->domain_name}})</option>
                        @endforeach
                    </div>
                </div>
            </div>
            <span class="text-danger">@error('affiliate_ids.*'){{ $message }} @enderror</span>
        </div>
        <div class="col-lg-5 mb-3">
            <label for="advertiser_id" class="form-label fw-bold">Advertiser ID*</label>
            <input name="advertiser_id" class=" form-control" placeholder="Advertiser ID*" value="{{old('advertiser_id')}}">
            <span class="text-danger">@error('advertiser_id'){{ $message }} @enderror</span>
        </div>
    </div>
    <div class="mb-3 row">
        <div class="col-lg-5 me-5 mb-2">
            <div class="d-flex flex-column">
                <label for="payment_gateway" class="form-label fw-bold">Select Payment Gateway*</label>
                <select name="payment_gateway" id="per_page" class="form-select">
                    <option value="">Select Payment Gateway</option>
                    <option value="stripe">Stripe</option>
                </select>
            </div>
            <span class="text-danger">@error('affiliate_ids.*'){{ $message }} @enderror</span>
        </div>
    </div>
    <hr class="borders-clearlink">
    <h4 class=" mb-4">Primary Contact Details</h4>
    <div>
        <div class=" mb-3 row ">
            <div class="col-lg-5 me-5 mb-2">
                <label for="first_name" class="form-label fw-bold">First Name*</label>
                <input name="first_name" class=" form-control" placeholder="First Name*" value="{{ old('first_name',isset($lead) ? $lead->first_name : '') }}">
                <span class="text-danger">@error('first_name'){{ $message }} @enderror</span>
            </div>
            <div class="col-lg-5 ">
                <label for="last_name" class="form-label fw-bold">Last Name*</label>
                <input name="last_name" class=" form-control" placeholder="Last Name*" value="{{ old('last_name', isset($lead) ? $lead->last_name : '') }}">
                <span class="text-danger">@error('last_name'){{ $message }} @enderror</span>
            </div>
        </div>
    </div>

    <div class="mb-3 row">
        <div class="col-lg-5 me-5 mb-2">
            <label for="email" class="form-label fw-bold">Email*</label>
            <input name="email" class=" form-control" placeholder="Email*" value="{{ old('email', isset($lead) ? $lead->email : '') }}">
            <span class="text-danger">@error('email'){{ $message }} @enderror</span>
        </div>
        <div class="col-lg-5 mb-3">
            <label for="phone_number" class="form-label fw-bold">Phone Number*</label>
            <input name="phone_number" class=" form-control" placeholder="Phone Number*" value="{{ old('phone_number', isset($lead) ? $lead->phone_number : '') }}">
            <span class="text-danger">@error('phone_number'){{ $message }} @enderror</span>
        </div>
    </div>

    <hr class="borders-clearlink">
    <h4 class=" mb-4">Company Address Details</h4>

    <div class="mb-3 row">
        <div class="col-lg-5 me-5 mb-2">
            <label for="address" class="form-label fw-bold">Address*</label>

            <input name="address" class=" form-control" placeholder="Address*" value="{{ old('address', isset($lead) ? $lead->street : '') }}">
            <span class="text-danger">@error('address'){{ $message }} @enderror</span>
        </div>
        <div class="col-lg-5 ">
            <label for="city" class="form-label fw-bold">City*</label>

            <input name="city" class=" form-control" placeholder="City*" value="{{ old('city', isset($lead) ? $lead->city : '') }}">
            <span class="text-danger">@error('city'){{ $message }} @enderror</span>
        </div>
    </div>
    <div class="mb-3 row">
        <div class="col-lg-5 me-5 mb-2">
            <label for="state" class="form-label fw-bold">State*</label>

            <input name="state" class=" form-control" placeholder="State*" value="{{ old('state', isset($lead) ? $lead->state : '' ) }}">
            <span class="text-danger">@error('state'){{ $message }} @enderror</span>
        </div>
        <div class="col-lg-5 mb-3">
            <label for="zip_code" class="form-label fw-bold">Zip Code*</label>

            <input name="zip_code" class=" form-control" placeholder="Zip Code*" value="{{ old('zip_code',isset($lead) ? $lead->zip_code : '' ) }}">
            <span class="text-danger">@error('zip_code'){{ $message }} @enderror</span>
        </div>
        <input name="country" class=" form-control" value="United States" hidden>
        <input name="lead_id" class=" form-control" value="{{ isset($lead) ? $lead->id : ''}}" hidden>
    </div>
    <hr class="borders-clearlink">
    <h4 class=" mb-4">Select Plans</h4>
    <div class="tables w-100">
        <table class="text-center mt-2 table table-bordered partner-card mb-3">
            <thead class="bg-clearlink fw-bold">
                <tr>
                    <th>S.No</th>
                    <th>Plan Name</th>
                    <th>Price</th>
                    <th>Select</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>Custom Enterprise</td>
                    <td>Contact Us</td>
                    <td>
                        <input type="checkbox" name="options[]" value="custom_plans" class="form-check-input select-plans-input">
                    </td>
                </tr>
                @foreach($plans as $index => $plan)
                <tr>
                    <td>{{$index+2}}</td>
                    <td>{{$plan->plan_name}}</td>
                    <td>{{$plan->price}}</td>
                    <td>
                        <input type="checkbox" name="options[]" value="{{$plan->plan_id}}" class="form-check-input select-plans-input" />
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
    <input type="submit" class="btn btn-primary  px-3 py-2 mb-5 rounded " value="Invite">
</form>
<script>
    const dropdownButton = document.querySelector(".custom-dropdown-button");
    const dropdownContent = document.querySelector(
        ".custom-dropdown-content"
    );
    const tagsContainer = dropdownButton.querySelector(".tags-container");
    const buttonText = dropdownButton.querySelector(".button-text");
    const hiddenSelect = document.getElementById("hiddenSelect");

    window.onload = function() {
        var affiliateValues = @json($values);
        affiliateValues.forEach(function(value) {
            if (value) {
                addTag(value);
            }
            const divs = dropdownContent.querySelectorAll('option');
            divs.forEach(div => {
                if (div.innerText.trim() === value.trim()) {
                    div.remove();
                }
            });
        });
    };

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

            addTag(value);
            selectedDiv.remove();

            dropdownContent.style.display = "none";
        }
    });

    function addTag(value) {
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

    }

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

@endsection