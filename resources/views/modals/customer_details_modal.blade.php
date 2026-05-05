<div class="modal fade" id="userDetailsModal" tabindex="-1" role="dialog" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">{{ translate('Customer Details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            <a href="javascript:void(0);" id="viewFullDetailsButton" class="btn btn-primary" target="_blank">
                {{ translate('View Full Details') }}
            </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@section('script')
@parent
    <script>
         function showUserDetails(userId) {
        $('#userDetailsContent').html(`
             <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
        `);
        $('#userDetailsModal').modal('show');
        $('#userDetailsModal').modal('show');
        document.getElementById('viewFullDetailsButton').href = '/admin/customers/' + userId + '/details';

        $.ajax({
            url: '{{ route('customers.ajax.details') }}',
            type: 'GET',
            data: {
                id: userId
            },
            success: function(response) {
                var html = `
                    <div class="text-center mb-4">
                        <img src="${response.avatar_original ?? '{{ static_asset('assets/img/placeholder.jpg') }}'}"
                             class="img-fluid rounded-circle"
                             style="max-width: 100px;"
                             alt="User Avatar">
                    </div>
                    <table class="table">
                        <tr>
                            <th>{{ translate('Name') }}</th>
                            <td>${response.name}</td>
                        </tr>
                        <tr>
                            <th>{{ translate('Email') }}</th>
                            <td>${response.email ?? 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>{{ translate('Phone') }}</th>
                            <td>${response.phone ?? 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>{{ translate('Paid amount') }}</th>
                            <td>${response.total_spent ?? 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>{{ translate('Orders') }}</th>
                            <td>${response.order_count ?? 'N/A'}</td>
                        </tr>
                          <tr>
                            <th>{{ translate('Wallet Balance') }}</th>
                            <td>${response.balance ?? 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>{{ translate('Join Date') }}</th>
                            <td>${response.created_at}</td>
                        </tr>
                    </table>
                `;

                $('#userDetailsContent').html(html);
            },
            error: function() {
                $('#userDetailsContent').html('<div class="alert alert-danger">{{ translate("Error loading user details") }}</div>');
            }
        });
    }
    </script>
@endsection
