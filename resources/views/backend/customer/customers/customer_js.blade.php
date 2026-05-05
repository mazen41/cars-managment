<script>
    function verifyPhone(userId) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('customers.verify-phone') }}",
            type: 'POST',
            data: {
                user_id: userId
            },
            success: function(response) {
                if(response.success) {
                    AIZ.plugins.notify('success', response.message);
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', response.message);
                }
            },
            error: function(xhr) {
                AIZ.plugins.notify('danger', '{{ translate("Something went wrong") }}');
            }
        });
    }

    function unverifyPhone(userId) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('customers.unverify-phone') }}",
            type: 'POST',
            data: {
                user_id: userId
            },
            success: function(response) {
                if(response.success) {
                    AIZ.plugins.notify('success', response.message);
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', response.message);
                }
            },
            error: function(xhr) {
                AIZ.plugins.notify('danger', '{{ translate("Something went wrong") }}');
            }
        });
    }

function verifyEmail(userId) {
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "{{ route('customers.verify-email') }}",
        type: 'POST',
        data: {
            user_id: userId
        },
        success: function(response) {
            if(response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function(xhr) {
            AIZ.plugins.notify('danger', '{{ translate("Something went wrong") }}');
        }
    });
}

function unverifyEmail(userId) {
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "{{ route('customers.unverify-email') }}",
        type: 'POST',
        data: {
            user_id: userId
        },
        success: function(response) {
            if(response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', response.message);
            }
        },
        error: function(xhr) {
            AIZ.plugins.notify('danger', '{{ translate("Something went wrong") }}');
        }
    });
}
</script>
