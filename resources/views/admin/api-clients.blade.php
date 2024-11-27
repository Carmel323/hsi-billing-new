@extends('layouts.admin_template')
@section("content")
<div class="container mt-5">
  <h1 class="mb-4">API Clients</h1>
  <!-- Add Button -->
  <button class="btn btn-primary mb-4" id="AddClientButton" data-bs-toggle="modal" data-bs-target="#addModal">Add Client</button>

  <!-- Modal -->
  <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="modalLabel">Add New API Client</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
              </div>
          </div>
      </div>
  </div>
  <table class="table table-bordered api-client-table">
      <thead>
          <tr>
              <th></th>
              <th>Client ID</th>
              <th>Client Name</th>
              <th>Expires At</th>
              <th>Created At</th>
              <th>Updated At</th>
          </tr>
      </thead>
      <tbody id="clientTableBody">
          @foreach ($clients as $client)
              <tr id="client-row-{{ $client->id }}">
                  <td class="action-buttons">
                    <!--<button class="btn btn-sm btn-outline-secondary unhide-button" data-client-id="{{ $client->id }}"" data-client-id="{{ $client->id }}"><i class="fas fa-eye"></i></button> -->
                    <button class="btn btn-sm btn-outline-danger revoke-button" data-client-id="{{ $client->id }}"><i class="fas fa-trash-alt"></i></button>
                  </td>
                  <td><span class="api-client" id="api-client-{{ $client->id }}">{{ $client->id }}<span></td>
                  <td>{{ $client->name }}</td>
                  <td>{{ $client->expires_at ? $client->expires_at->format('Y-m-d') : 'Never' }}</td>
                  <td>{{ $client->created_at->format('Y-m-d H:i:s') }}</td>
                  <td>{{ $client->updated_at->format('Y-m-d H:i:s') }}</td>
              </tr>
          @endforeach
      </tbody>
  </table>

  <!-- Pagination links -->
  <div class="d-flex justify-content-center">
      {{ $clients->links() }}
  </div>
  <!-- Bootstrap JS and AJAX script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Revoke token functionality
    $('.revoke-button').on('click', function() {
        const clientId = $(this).data('client-id');
        const clientRow = $('#client-row-' + clientId);

        if (confirm('Are you sure you want to revoke this client?')) {
            const url = "{{ route('api-client.revoke', ':id') }}".replace(':id', clientId);
            $.ajax({
                url: url,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    client_id: clientId
                },
                success: function(response) {
                    clientRow.remove(); // Remove the client row from the table
                },
                error: function(xhr) {
                    alert('An error occurred while revoking the client.');
                }
            });
        }
    });

    $('#AddClientButton').on('click', function() {
        addClientForm();
    });
});

function centerModal() {
    const modalContent = $('.modal-content');
    const windowHeight = $(window).height();
    const contentHeight = modalContent.outerHeight();

    // Center vertically
    const topOffset = Math.max((windowHeight - contentHeight) / 2, 20); // 20px minimum margin

    modalContent.css({
        'margin-top': `${topOffset}px`,
        'margin-left': 'auto',
        'margin-right': 'auto'
    });
}

function addClientForm(){
    
        // Create the form content
        addForm = `    <div class="modal-header">
                  <h5 class="modal-title" id="modalLabel">Add New API Client</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <form id="addForm">
                      @csrf <!-- Include CSRF token for security -->
                      <div class="mb-3">
                          <label for="name" class="form-label">Client Name</label>
                          <input type="text" class="form-control" id="name" name="name" required>
                      </div>
                      <div class="mb-3">
                          <label for="expires_in_days" class="form-label">Expiration (in days, optional)</label>
                          <input type="number" class="form-control" id="expires_in_days" name="expires_in_days" min="1">
                      </div>
                      <button type="submit" class="btn btn-primary">Submit</button>
                  </form>
              </div> `;
    
        // add form to modal    
        $('.modal-content').html(addForm);
    
        // Attach the submit form functionality
        $('#addForm').on('submit', function(e) {
            e.preventDefault();
            console.log("test");
            data = $(this).serialize();
            console.log(data);
            $.ajax({
                url: "{{ route('api-client.store') }}", 
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {

                    // Create the new row for the client table 
                    const newRow = `
                        <tr id="client-row-${response.client_id}">
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-outline-danger revoke-button" data-client-id="${response.client_id}"><i class="fas fa-trash-alt"></i></button>
                            </td>
                            <td><span class="api-client" id="api-client-${response.client_id}">${response.client_id}</span></td>
                            <td>${response.name}</td>
                            <td>${response.expires_at ? response.expires_at : 'Never'}</td>
                            <td>${new Date().toISOString().slice(0, 19).replace('T', ' ')}</td>
                            <td>${new Date().toISOString().slice(0, 19).replace('T', ' ')}</td>
                        </tr>
                    `;

                    // Append the new row to the table
                    $('#clientTableBody').prepend(newRow);

                    $('#modalLabel').html('Client Succesfully Created');
                    $('.modal-body').html(`<span style="color:red;">Save your client credentials. You will not be able to get the client secret after you close this window.</span><br/><br/><div class="client-credentials"><b>Client ID:</b> ${response.client_id} <br/><b>Client Secret:</b> ${response.client_secret}</div>`);
                    $('.modal-content').css({
                        'margin-left': 'auto',
                        'margin-right': 'auto',
                        'text-align': 'left',
                        'width': '900px'
                    });
                    $('.modal-dialog').css({
                        'max-width': '900px'
                    });
                    $('.client-credentials').css({
                        'text-align': 'left',
                        'width': '600px',
                    });
                    centerModal();
                },
                error: function(xhr) {
                    console.log('An error occurred while adding the client.');
                }
            });
        });

}

</script>
</div>
@endsection