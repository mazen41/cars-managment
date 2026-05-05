<script>
    $(document).ready(function() {
        var $select = $('#customer-select');
        var showPhone = $select.data('show-phone');
        var showEmail = $select.data('show-email');
        var withPhone = $select.data('with-phone');
        var withPhoneVerified = $select.data('with-phone-verified');
        var withEmailVerified = $select.data('with-email-verified');
        var withEmail = $select.data('with-email');
        var page = 1;
        var isLoading = false;
        var currentOptions = new Map();

        $select.selectpicker({
            liveSearch: true,
            liveSearchPlaceholder: 'Search customers...',
            size: 10
        });

        // Initially store existing options
        $select.find('option').each(function() {
            if($(this).val()) {
                currentOptions.set($(this).val(), {
                    id: $(this).val(),
                    name: $(this).text(),
                    phone: $(this).data('phone'),
                    email: $(this).data('email')
                });
            }
        });

        // Handle search
        $select.on('shown.bs.select', function () {
            var searchBox = $(this).parent().find('.bs-searchbox input');
            var dropdownMenu = $(this).parent().find('.dropdown-menu');

            searchBox.on('input', debounce(function() {
                var searchTerm = $(this).val();
                page = 1; // Reset page number on new search
                loadUsers(searchTerm, 1);
            }, 300));

            // Handle scroll for pagination
            dropdownMenu.on('scroll', function() {
                if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight - 20) {
                    if(!isLoading) {
                        page++;
                        loadUsers($select.parent().find('.bs-searchbox input').val(), page);
                    }
                }
            });
        });

        function loadUsers(search, pageNum) {
            if(isLoading) return;
            isLoading = true;

            // Get currently selected values
            var selectedValues = $select.val() || [];
            if(!Array.isArray(selectedValues)) {
                selectedValues = [selectedValues];
            }

            $.ajax({
                url: $select.data('ajax-url'),
                data: {
                    search: search,
                    with_phone: withPhone,
                    with_email: withEmail,
                    with_phone_verified: withPhoneVerified,
                    with_email_verified: withEmailVerified,
                    page: pageNum
                },
                success: function(data) {
                    if(pageNum === 1) {
                        // Clear existing options but keep selected ones
                        $select.empty();
                        $select.append('<option value="">{{translate('Select')}}</option>');

                        // Reset currentOptions but keep selected ones
                        var tempOptions = new Map();
                        selectedValues.forEach(function(value) {
                            if(currentOptions.has(value)) {
                                tempOptions.set(value, currentOptions.get(value));
                            }
                        });
                        currentOptions = tempOptions;
                    }

                    // Add new options from search results
                    data.users.forEach(function(user) {
                        currentOptions.set(user.id.toString(), user);
                    });

                    // Rebuild select options
                    currentOptions.forEach(function(user) {
                        var isSelected = selectedValues.includes(user.id.toString());
                        $select.append(
                            '<option value="' + user.id + '"' + (isSelected ? ' selected' : '') + '>' +
                            user.name +
                            ((showPhone && user.phone) ? ' - ' + user.phone : '') +
                            ((showEmail && user.email) ? ' - ' + user.email : '') +
                            '</option>'
                        );
                    });

                    $select.selectpicker('refresh');
                    isLoading = false;
                },
                error: function() {
                    isLoading = false;
                }
            });
        }

        // Debounce function to limit API calls
        function debounce(func, wait) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        }
    });
</script>
