<script>
(function() {
    let loadedTabs = {};
    let dropdownOpened = false;

    // Load notifications for a specific tab
    function loadNotifications(tabId, notificationType, isLike = false) {
        if (loadedTabs[tabId]) {
            return; // Already loaded
        }
        const tabPane = document.getElementById(tabId + '-notifications');
        if (!tabPane) return;

        // Make AJAX request to load notifications
        fetch('{{ route("admin.notifications.fetch") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                type: notificationType,
                like: isLike,
                limit: 20
            })
        })
        .then(response => response.json())
        .then(data => {
            tabPane.innerHTML = data.html;
            loadedTabs[tabId] = true;
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            tabPane.innerHTML = '<div class="text-center py-4 text-danger">"{{ translate("Failed to load notifications") }}"</div>';
        });
    }

    // When dropdown is opened, load the active tab
    document.getElementById('notification-dropdown-trigger').addEventListener('click', function() {
        if (!dropdownOpened) {
            dropdownOpened = true;
            // Load the first (active) tab
            const activeTab = document.querySelector('.tab-pane.active[data-notification-type]');
            if (activeTab) {
                const tabId = activeTab.id.replace('-notifications', '');
                const notificationType = activeTab.dataset.notificationType;
                const isLike = activeTab.dataset.like === 'true';

                loadNotifications(tabId, notificationType, isLike);
            }
        }
    });

    // When tab is clicked, load its notifications
    document.querySelectorAll('[data-toggle="tab"][data-type]').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.dataset.type;
            const tabPane = document.getElementById(tabId + '-notifications');

            if (tabPane && !loadedTabs[tabId]) {
                const notificationType = tabPane.dataset.notificationType;
                const isLike = tabPane.dataset.like === 'true';

                loadNotifications(tabId, notificationType, isLike);
            }
        });
    });
})();
</script>
