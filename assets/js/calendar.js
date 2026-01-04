(function($) {
    'use strict';
    
    let calendar;
    let flatpickrInstance;
    let filterParams = {};
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        initCalendar();
        initFilters();
        initModal();
    });
    
    function initCalendar() {
        const calendarEl = document.getElementById('acf-events-calendar');
        
        if (!calendarEl) {
            return;
        }
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            events: function(info, successCallback, failureCallback) {
                fetchEvents(successCallback, failureCallback);
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                showEventModal(info.event);
            },
            eventDidMount: function(info) {
                // Add custom classes based on event properties
                if (info.event.extendedProps.event_formats) {
                    info.event.extendedProps.event_formats.forEach(format => {
                        info.el.classList.add('format-' + format.toLowerCase().replace(/\s+/g, '-'));
                    });
                }
            }
        });
        
        calendar.render();
    }
    
    function fetchEvents(successCallback, failureCallback) {
        const params = new URLSearchParams(filterParams);
        
        $.ajax({
            url: acfEventsCalendar.restUrl + '?' + params.toString(),
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', acfEventsCalendar.nonce);
            },
            success: function(events) {
                successCallback(events);
            },
            error: function() {
                failureCallback();
                console.error('Failed to fetch events');
            }
        });
    }
    
    function initFilters() {
        // Event Type filter
        $('#event-type-filter').on('change', function() {
            const value = $(this).val();
            if (value) {
                filterParams.event_type = value;
            } else {
                delete filterParams.event_type;
            }
            refreshCalendar();
        });
        
        // Event Format filter
        $('#event-format-filter').on('change', function() {
            const value = $(this).val();
            if (value) {
                filterParams.event_format = value;
            } else {
                delete filterParams.event_format;
            }
            refreshCalendar();
        });
        
        // Date Range filter
        let dateRangeOutsideHandler;
        flatpickrInstance = flatpickr('#date-range-filter', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    filterParams.start_date = formatDate(selectedDates[0]);
                    filterParams.end_date = formatDate(selectedDates[1]);
                    if (calendar) {
                        calendar.gotoDate(selectedDates[0]);
                    }
                    refreshCalendar();
                }
            },
            onOpen: function(selectedDates, dateStr, instance) {
                dateRangeOutsideHandler = function(event) {
                    const clickedCalendar = event.target.closest('.flatpickr-calendar');
                    const clickedInput = instance.input.contains(event.target);
                    if (!clickedCalendar && !clickedInput) {
                        instance.close();
                    }
                };
                document.addEventListener('mousedown', dateRangeOutsideHandler);
                document.addEventListener('touchstart', dateRangeOutsideHandler);
            },
            onClose: function() {
                if (dateRangeOutsideHandler) {
                    document.removeEventListener('mousedown', dateRangeOutsideHandler);
                    document.removeEventListener('touchstart', dateRangeOutsideHandler);
                    dateRangeOutsideHandler = null;
                }
            }
        });
        
        // Search filter (manual submit)
        $('.filter-search-field .search-button').on('click', function() {
            const value = $('#event-search').val().trim();
            if (value) {
                filterParams.search = value;
            } else {
                delete filterParams.search;
            }
            refreshCalendar();
        });
        
        // Clear filters
        $('#clear-filters').on('click', function() {
            filterParams = {};
            $('#event-type-filter').val('');
            $('#event-format-filter').val('');
            $('#event-search').val('');
            if (flatpickrInstance) {
                flatpickrInstance.clear();
            }
            refreshCalendar();
        });
    }
    
    function refreshCalendar() {
        if (calendar) {
            calendar.refetchEvents();
        }
    }
    
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    function initModal() {
        // Close modal on overlay click
        $('.event-modal-overlay').on('click', function() {
            closeModal();
        });
        
        // Close modal on close button click
        $('.event-modal-close').on('click', function() {
            closeModal();
        });
        
        // Close modal on ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    }
    
    function showEventModal(event) {
        const props = event.extendedProps;
        let html = '';
        let addressHtml = '';

        if (props.event_address) {
            let address = '';
            if (typeof props.event_address === 'object') {
                address = props.event_address.address || '';
                if (props.event_address.lat && props.event_address.lng) {
                    const mapsUrl = 'https://www.google.com/maps?q=' + 
                                  props.event_address.lat + ',' + props.event_address.lng;
                    addressHtml += '<a href="' + mapsUrl + '" target="_blank" rel="noopener">' + 
                           escapeHtml(address) + ' <i class="fa-solid fa-arrow-up-right-from-square"></i></a>';
                } else {
                    addressHtml += escapeHtml(address);
                }
            } else {
                addressHtml += escapeHtml(props.event_address);
            }
        }

        html += '<div class="event-modal-layout">';
        html += '<div class="event-modal-row event-modal-row--top">';
        html += '<div class="event-modal-column event-modal-column--info">';

        // Event Title
        html += '<h2 class="event-modal-title">' + escapeHtml(event.title) + '</h2>';

        html += '<div class="event-info-grid">';

        // Event Types (taxonomies)
        if (props.event_types && props.event_types.length > 0) {
            html += '<div class="event-meta-item event-types">';
            html += '<i class="fa-solid fa-filter event-icon"></i>';
            html += '<span class="event-value">' + escapeHtml(props.event_types.join(', ')) + '</span>';
            html += '</div>';
        }

        // Event Formats (taxonomies)
        if (props.event_formats && props.event_formats.length > 0) {
            html += '<div class="event-meta-item event-formats">';
            html += '<i class="fa-solid fa-tag event-icon"></i>';
            html += '<span class="event-value">' + escapeHtml(props.event_formats.join(', ')) + '</span>';
            html += '</div>';
        }

        // Date
        html += '<div class="event-meta-item event-date">';
        html += '<i class="fa-solid fa-calendar-days event-icon"></i>';
        html += '<span class="event-value">' + formatEventDate(props.start_date, props.end_date) + '</span>';
        html += '</div>';

        // Time
        if (props.start_time || props.end_time) {
            html += '<div class="event-meta-item event-time">';
            html += '<i class="fa-solid fa-clock event-icon"></i>';
            html += '<span class="event-value">' + formatEventTime(props.start_time, props.end_time) + '</span>';
            html += '</div>';
        }

        // Location/Address
        if (addressHtml) {
            html += '<div class="event-meta-item event-location">';
            html += '<i class="fa-solid fa-location-dot event-icon"></i>';
            html += '<span class="event-value">' + addressHtml + '</span>';
            html += '</div>';
        }

        html += '</div>';

        // Description
        if (props.event_description) {
            html += '<div class="event-meta-item event-description">';
            html += '<div class="event-value">' + props.event_description + '</div>';
            html += '</div>';
        }

        // Event URL
        if (props.event_url) {
            html += '<div class="event-meta-item event-url">';
            html += '<a href="' + escapeHtml(props.event_url) + '" target="_blank" rel="noopener" class="event-link-btn">';
            html += '<i class="fa-solid fa-arrow-up-right-from-square"></i>';
            html += 'Event Website</a>';
            html += '</div>';
        }

        // Registration
        if (props.registration_required && props.registration_link) {
            const ctaText = props.registration_cta || 'Register';
            html += '<div class="event-meta-item event-registration">';
            html += '<a href="' + escapeHtml(props.registration_link) + '" target="_blank" rel="noopener" class="event-register-btn">';
            html += '<i class="fa-solid fa-arrow-up-right-from-square"></i>';
            html += escapeHtml(ctaText) + '</a>';
            html += '</div>';
        }

        html += '</div>';

        // Featured Image
        html += '<div class="event-modal-column event-modal-column--media">';
        if (props.featured_image) {
            html += '<div class="event-featured-image">';
            html += '<img src="' + escapeHtml(props.featured_image) + '" alt="' + escapeHtml(event.title) + '">';
            html += '</div>';
        }
        html += '</div>';
        html += '</div>';

        // Post Content
        if (props.post_content) {
            html += '<div class="event-modal-row event-modal-row--content">';
            html += '<div class="event-meta-item event-post-content">';
            html += '<div class="event-value">' + props.post_content + '</div>';
            html += '</div>';
            html += '</div>';
        }

        html += '</div>';
        
        $('.event-modal-body').html(html);
        $('#event-modal').fadeIn(300);
        $('body').addClass('modal-open');
    }
    
    function closeModal() {
        $('#event-modal').fadeOut(300);
        $('body').removeClass('modal-open');
    }
    
    function formatEventDate(startDate, endDate) {
        const start = new Date(startDate);
        const end = endDate ? new Date(endDate) : null;
        
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        const startFormatted = start.toLocaleDateString('en-US', options);
        
        // If single-day or no end date
        if (!end || startDate === endDate) {
            return startFormatted;
        }
        
        // Multi-day event
        const endFormatted = end.toLocaleDateString('en-US', options);
        return startFormatted + ' - ' + endFormatted;
    }
    
    function formatEventTime(startTime, endTime) {
        if (!startTime && !endTime) {
            return '';
        }
        
        let timeStr = '';
        
        if (startTime) {
            timeStr += formatTime12Hour(startTime);
        }
        
        if (endTime && endTime !== startTime) {
            timeStr += ' - ' + formatTime12Hour(endTime);
        }
        
        return timeStr;
    }
    
    function formatTime12Hour(time) {
        if (!time) return '';
        
        const [hours, minutes] = time.split(':');
        let hour = parseInt(hours);
        const ampm = hour >= 12 ? 'pm' : 'am';
        
        hour = hour % 12;
        hour = hour ? hour : 12; // 0 should be 12
        
        return hour + ':' + minutes + ' ' + ampm;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
})(jQuery);
