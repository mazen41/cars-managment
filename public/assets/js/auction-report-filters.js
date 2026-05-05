/**
 * Auction Room Report AJAX Filtering
 * Handles dynamic filtering for bids, offers, audit logs, and items
 */

(function($) {
    'use strict';

    // Store the current auction room ID
    let currentRoomId = null;

    /**
     * Initialize filtering functionality
     */
    function initializeFilters(roomId) {
        currentRoomId = roomId;

        // Initialize bid filters
        initializeBidFilters();

        // Initialize offer filters
        initializeOfferFilters();

        // Initialize audit log filters
        initializeAuditLogFilters();

        // Initialize item filters (if needed in future)
        // initializeItemFilters();
    }

    /**
     * Initialize bid filtering
     */
    function initializeBidFilters() {
        const $form = $('#bid-filter-form');
        const $table = $('#bids-table tbody');
        const $countDisplay = $('.card-header h5:contains("All Bids")');

        if ($form.length === 0) return;

        $form.off('submit').on('submit', function(e) {
            e.preventDefault();

            // Show loading state
            showLoadingState($table, 'bids');

            // Get form data
            const formData = $form.serialize();

            // Make AJAX request
            $.ajax({
                url: `/admin/auction-rooms/${currentRoomId}/report/bids?${formData}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update table with filtered data
                        updateBidsTable($table, response.data);

                        // Update count display
                        updateCountDisplay($countDisplay, 'All Bids', response.count, response.total);

                        // Show success message
                        showFilterMessage('success', `Showing ${response.count} of ${response.total} bids`);
                    }
                },
                error: function(xhr) {
                    hideLoadingState($table);
                    showFilterMessage('error', 'Failed to filter bids. Please try again.');
                    console.error('Bid filtering error:', xhr);
                }
            });
        });
    }

    /**
     * Initialize offer filtering
     */
    function initializeOfferFilters() {
        const $form = $('#offer-filter-form');
        const $table = $('#offers-table tbody');
        const $countDisplay = $('.card-header h5:contains("All Offers")');

        if ($form.length === 0) return;

        $form.off('submit').on('submit', function(e) {
            e.preventDefault();

            // Show loading state
            showLoadingState($table, 'offers');

            // Get form data
            const formData = $form.serialize();

            // Make AJAX request
            $.ajax({
                url: `/admin/auction-rooms/${currentRoomId}/report/offers?${formData}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update table with filtered data
                        updateOffersTable($table, response.data);

                        // Update count display
                        updateCountDisplay($countDisplay, 'All Offers', response.count, response.total);

                        // Show success message
                        showFilterMessage('success', `Showing ${response.count} of ${response.total} offers`);
                    }
                },
                error: function(xhr) {
                    hideLoadingState($table);
                    showFilterMessage('error', 'Failed to filter offers. Please try again.');
                    console.error('Offer filtering error:', xhr);
                }
            });
        });
    }

    /**
     * Initialize audit log filtering
     */
    function initializeAuditLogFilters() {
        const $form = $('#audit-filter-form');
        const $table = $('#audit-log-table tbody');
        const $countDisplay = $('.card-header h5:contains("Audit Log")');

        if ($form.length === 0) return;

        $form.off('submit').on('submit', function(e) {
            e.preventDefault();

            // Show loading state
            showLoadingState($table, 'audit-logs');

            // Get form data
            const formData = $form.serialize();

            // Make AJAX request
            $.ajax({
                url: `/admin/auction-rooms/${currentRoomId}/report/audit-logs?${formData}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update table with filtered data
                        updateAuditLogTable($table, response.data);

                        // Update count display
                        updateCountDisplay($countDisplay, 'Audit Log', response.count, response.total);

                        // Show success message
                        showFilterMessage('success', `Showing ${response.count} of ${response.total} log entries`);
                    }
                },
                error: function(xhr) {
                    hideLoadingState($table);
                    showFilterMessage('error', 'Failed to filter audit logs. Please try again.');
                    console.error('Audit log filtering error:', xhr);
                }
            });
        });
    }

    /**
     * Update bids table with filtered data
     */
    function updateBidsTable($tbody, bids) {
        $tbody.empty();

        if (bids.length === 0) {
            $tbody.html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="las la-hand-paper la-3x text-muted mb-3"></i>
                        <p class="text-muted">No bids found matching the filters</p>
                    </td>
                </tr>
            `);
            return;
        }

        bids.forEach(function(bid) {
            const row = buildBidRow(bid);
            $tbody.append(row);
        });
    }

    /**
     * Build a single bid table row
     */
    function buildBidRow(bid) {
        const createdAt = new Date(bid.created_at);
        const dateStr = createdAt.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        const timeStr = createdAt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

        const bidderInfo = bid.bidder ? `
            <div class="small">
                <div><strong>${escapeHtml(bid.bidder.name)}</strong></div>
                <div class="text-muted">ID: ${bid.bidder.id}</div>
                <div class="text-muted">${escapeHtml(bid.bidder.email)}</div>
                ${bid.bidder.phone ? `<div class="text-muted">${escapeHtml(bid.bidder.phone)}</div>` : ''}
            </div>
        ` : '<span class="text-muted">Unknown</span>';

        const itemInfo = bid.auction_item && bid.auction_item.car ? `
            <div class="small">
                <div class="text-truncate" style="max-width: 200px;">
                    <strong>${escapeHtml(bid.auction_item.car.car_name)}</strong>
                </div>
                <div class="text-muted">Seq: ${bid.auction_item.sequence_order}</div>
            </div>
        ` : '<span class="text-muted">-</span>';

        const statusBadge = getBidStatusBadge(bid.status);

        return `
            <tr>
                <td>
                    <div class="small">
                        <div>${dateStr}</div>
                        <div class="text-muted">${timeStr}</div>
                    </div>
                </td>
                <td>${bidderInfo}</td>
                <td>${itemInfo}</td>
                <td><strong class="text-primary">${formatPrice(bid.amount)}</strong></td>
                <td>${statusBadge}</td>
                <td><span class="small text-muted">${bid.ip_address || '-'}</span></td>
                <td>
                    <div class="small text-muted text-truncate" style="max-width: 200px;" title="${escapeHtml(bid.user_agent || '-')}">
                        ${escapeHtml(bid.user_agent || '-')}
                    </div>
                </td>
            </tr>
        `;
    }

    /**
     * Update offers table with filtered data
     */
    function updateOffersTable($tbody, offers) {
        $tbody.empty();

        if (offers.length === 0) {
            $tbody.html(`
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="las la-handshake la-3x text-muted mb-3"></i>
                        <p class="text-muted">No offers found matching the filters</p>
                    </td>
                </tr>
            `);
            return;
        }

        offers.forEach(function(offer) {
            const row = buildOfferRow(offer);
            $tbody.append(row);
        });
    }

    /**
     * Build a single offer table row
     */
    function buildOfferRow(offer) {
        const itemInfo = offer.auction_item && offer.auction_item.car ? `
            <div class="small">
                <div class="text-truncate" style="max-width: 150px;">
                    <strong>${escapeHtml(offer.auction_item.car.car_name)}</strong>
                </div>
                <div class="text-muted">Seq: ${offer.auction_item.sequence_order}</div>
            </div>
        ` : '<span class="text-muted">-</span>';

        const buyerInfo = offer.buyer ? `
            <div class="small">
                <div><strong>${escapeHtml(offer.buyer.name)}</strong></div>
                <div class="text-muted">${escapeHtml(offer.buyer.email)}</div>
                ${offer.buyer.phone ? `<div class="text-muted">${escapeHtml(offer.buyer.phone)}</div>` : ''}
            </div>
        ` : '<span class="text-muted">Unknown</span>';

        const sellerInfo = offer.seller ? `
            <div class="small">
                <div><strong>${escapeHtml(offer.seller.name)}</strong></div>
                <div class="text-muted">${escapeHtml(offer.seller.email)}</div>
            </div>
        ` : '<span class="text-muted">Unknown</span>';

        const statusBadge = getOfferStatusBadge(offer.status);

        const sellerResponse = (offer.status === 'accepted' || offer.status === 'rejected') && offer.seller_response ? `
            <div class="small">
                <div class="text-muted">Response:</div>
                <div class="text-truncate" style="max-width: 200px;" title="${escapeHtml(offer.seller_response)}">
                    ${escapeHtml(offer.seller_response)}
                </div>
            </div>
        ` : '<span class="text-muted">-</span>';

        const createdAt = new Date(offer.created_at);
        const createdStr = createdAt.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' +
                          createdAt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

        let timestampHtml = `
            <div class="small">
                <div class="text-muted">Created:</div>
                <div>${createdStr}</div>
        `;

        if (offer.responded_at) {
            const respondedAt = new Date(offer.responded_at);
            const respondedStr = respondedAt.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' +
                                respondedAt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            timestampHtml += `
                <div class="text-muted mt-1">Responded:</div>
                <div>${respondedStr}</div>
            `;
        }

        timestampHtml += '</div>';

        return `
            <tr>
                <td>${itemInfo}</td>
                <td>${buyerInfo}</td>
                <td>${sellerInfo}</td>
                <td><strong class="text-primary">${formatPrice(offer.amount)}</strong></td>
                <td>
                    <div class="small text-truncate" style="max-width: 200px;" title="${escapeHtml(offer.message || '-')}">
                        ${escapeHtml(offer.message || '-')}
                    </div>
                </td>
                <td>${statusBadge}</td>
                <td>${sellerResponse}</td>
                <td>${timestampHtml}</td>
            </tr>
        `;
    }

    /**
     * Update audit log table with filtered data
     */
    function updateAuditLogTable($tbody, logs) {
        $tbody.empty();

        if (logs.length === 0) {
            $tbody.html(`
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="las la-history la-3x text-muted mb-3"></i>
                        <p class="text-muted">No audit log entries found matching the filters</p>
                    </td>
                </tr>
            `);
            return;
        }

        logs.forEach(function(log) {
            const row = buildAuditLogRow(log);
            $tbody.append(row);
        });
    }

    /**
     * Build a single audit log table row
     */
    function buildAuditLogRow(log) {
        const createdAt = new Date(log.created_at);
        const dateStr = createdAt.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        const timeStr = createdAt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

        const criticalActions = ['room_started', 'room_completed', 'item_sold'];
        const isCritical = criticalActions.includes(log.action);
        const rowClass = isCritical ? 'table-warning' : '';

        const actionBadge = getAuditActionBadge(log.action, isCritical);

        const userInfo = log.user ? `
            <div class="small">
                <div><strong>${escapeHtml(log.user.name)}</strong></div>
                <div class="text-muted">ID: ${log.user.id}</div>
            </div>
        ` : '<span class="text-muted">System</span>';

        const itemInfo = log.auction_item && log.auction_item.car ? `
            <div class="small">
                <div class="text-truncate" style="max-width: 150px;">
                    ${escapeHtml(log.auction_item.car.car_name)}
                </div>
                <div class="text-muted">Seq: ${log.auction_item.sequence_order}</div>
            </div>
        ` : '<span class="text-muted">-</span>';

        const detailsHtml = buildAuditLogDetails(log.details);

        return `
            <tr class="${rowClass}">
                <td>
                    <div class="small">
                        <div>${dateStr}</div>
                        <div class="text-muted">${timeStr}</div>
                    </div>
                </td>
                <td>${actionBadge}</td>
                <td>${userInfo}</td>
                <td>${itemInfo}</td>
                <td>${detailsHtml}</td>
                <td><span class="small text-muted">${log.ip_address || '-'}</span></td>
            </tr>
        `;
    }

    /**
     * Build audit log details HTML
     */
    function buildAuditLogDetails(details) {
        if (!details) {
            return '<span class="text-muted">-</span>';
        }

        let detailsObj;
        try {
            detailsObj = typeof details === 'string' ? JSON.parse(details) : details;
        } catch (e) {
            return `<span class="text-muted small">${escapeHtml(details)}</span>`;
        }

        if (!detailsObj || typeof detailsObj !== 'object') {
            return '<span class="text-muted">-</span>';
        }

        let html = '<div class="small">';
        const priceFields = ['amount', 'price', 'starting_price', 'final_price'];

        for (const [key, value] of Object.entries(detailsObj)) {
            if (typeof value !== 'object' && value !== null) {
                const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                const displayValue = priceFields.includes(key) ? formatPrice(value) : escapeHtml(String(value));
                html += `<div><strong>${label}:</strong> ${displayValue}</div>`;
            }
        }

        html += '</div>';
        return html;
    }

    /**
     * Get bid status badge HTML
     */
    function getBidStatusBadge(status) {
        const badges = {
            'accepted': '<span class="badge badge-inline badge-success"><i class="las la-check-circle"></i> Accepted</span>',
            'rejected': '<span class="badge badge-inline badge-danger"><i class="las la-times-circle"></i> Rejected</span>',
            'outbid': '<span class="badge badge-inline badge-warning"><i class="las la-exclamation-circle"></i> Outbid</span>'
        };

        return badges[status] || `<span class="badge badge-inline badge-secondary">${escapeHtml(status)}</span>`;
    }

    /**
     * Get offer status badge HTML
     */
    function getOfferStatusBadge(status) {
        const badges = {
            'accepted': '<span class="badge badge-inline badge-success"><i class="las la-check-circle"></i> Accepted</span>',
            'rejected': '<span class="badge badge-inline badge-danger"><i class="las la-times-circle"></i> Rejected</span>',
            'expired': '<span class="badge badge-inline badge-warning"><i class="las la-clock"></i> Expired</span>',
            'pending': '<span class="badge badge-inline badge-info"><i class="las la-hourglass-half"></i> Pending</span>'
        };

        return badges[status] || `<span class="badge badge-inline badge-secondary">${escapeHtml(status)}</span>`;
    }

    /**
     * Get audit action badge HTML
     */
    function getAuditActionBadge(action, isCritical) {
        const badgeClasses = {
            'room_started': 'badge-success',
            'room_completed': 'badge-primary',
            'item_started': 'badge-info',
            'item_sold': 'badge-success',
            'item_unsold': 'badge-warning',
            'bid_placed': 'badge-secondary',
            'bid_accepted': 'badge-success',
            'bid_rejected': 'badge-danger',
            'timer_extended': 'badge-warning'
        };

        const badgeClass = badgeClasses[action] || 'badge-secondary';
        const label = action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        const star = isCritical ? '<i class="las la-star"></i> ' : '';

        return `<span class="badge badge-inline ${badgeClass}">${star}${label}</span>`;
    }

    /**
     * Show loading state
     */
    function showLoadingState($tbody, type) {
        const colspan = type === 'bids' ? 7 : (type === 'offers' ? 8 : 6);
        $tbody.html(`
            <tr>
                <td colspan="${colspan}" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Applying filters...</p>
                </td>
            </tr>
        `);
    }

    /**
     * Hide loading state
     */
    function hideLoadingState($tbody) {
        // Loading state will be replaced by actual data or error message
    }

    /**
     * Update count display
     */
    function updateCountDisplay($element, label, count, total) {
        if ($element.length > 0) {
            $element.html(`${label} (${count}${count !== total ? ` of ${total}` : ''})`);
        }
    }

    /**
     * Show filter message
     */
    function showFilterMessage(type, message) {
        // Use AIZ notification system if available
        if (typeof AIZ !== 'undefined' && AIZ.plugins && AIZ.plugins.notify) {
            AIZ.plugins.notify(type, message);
        } else {
            console.log(`[${type}] ${message}`);
        }
    }

    /**
     * Format price
     */
    function formatPrice(amount) {
        // Use existing format_price function if available
        if (typeof format_price === 'function') {
            return format_price(amount);
        }
        // Fallback formatting
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // Expose initialization function globally
    window.AuctionReportFilters = {
        init: initializeFilters
    };

})(jQuery);
