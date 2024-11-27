@extends('layouts.admin_template')

@section('content')

<div id="content" style="box-sizing: border-box; margin-left:300px; width:100%" class="p-3">

    <div class="d-flex flex-column  w-100">
        <div class="mb-2 mt-4 w-100">
            <div class="d-flex flex-row justify-content-between">

                <div>
                    <h2 class="mt-2 mb-5">All Partner Affiliates</h2>
                </div>
                <div>
                    <div class=" d-flex justify-content-center align-items-center mt-3 ">
                    </div>
                </div>
            </div>

            <div class="tables w-100">
                <table class="table table-bordered" style="width: 50% !important;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Partner ID</th>
                            <th>Affiliate ID</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partnerAffiliates as $partnerAffiliate)
                        <tr>
                            <td>{{ $partnerAffiliate->id }}</td>
                            <td>{{ $partnerAffiliate->partner_id }}</td>
                            <td>{{ $partnerAffiliate->affiliate_id }}</td>
                            <td>{{ $partnerAffiliate->created_at }}</td>
                            <td>{{ $partnerAffiliate->updated_at }}</td>
                            <td>
                                <!-- Edit Button -->
                                <button
                                    class="btn btn-sm btn-primary edit-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal"
                                    data-id="{{ $partnerAffiliate->id }}"
                                    data-partner-id="{{ $partnerAffiliate->partner_id }}"
                                    data-affiliate-id="{{ $partnerAffiliate->affiliate_id }}">
                                    Edit
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Partner Affiliate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST" action="{{ route('partnersAffiliates.update') }}">
                    @csrf
                    <input type="hidden" id="old-id" name="old_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit-id">ID</label>
                            <input type="text" class="form-control" id="edit-id" name="id">
                        </div>
                        <div class="form-group">
                            <label for="edit-partner-id">Partner ID</label>
                            <input type="text" class="form-control" id="edit-partner-id" name="partner_id" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-affiliate-id">Affiliate ID</label>
                            <input type="text" class="form-control" id="edit-affiliate-id" name="affiliate_id" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@section('scripts')

<script>
    $(document).ready(function() {
        $('#editModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id');
            var partnerId = button.data('partner-id');
            var affiliateId = button.data('affiliate-id');

            var modal = $(this);
            modal.find('#edit-id').val(id);
            modal.find('#edit-partner-id').val(partnerId);
            modal.find('#edit-affiliate-id').val(affiliateId);
            modal.find('#old-id').val(id);

        });
    });
</script>

@endsection