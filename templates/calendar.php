<?php
/**
 * Calendar Template
 * 
 * This template is loaded by the [acf_events_calendar] shortcode
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get all event types for filter
$event_types = get_terms([
    'taxonomy' => 'event-type',
    'hide_empty' => true
]);

// Get all event formats for filter
$event_formats = get_terms([
    'taxonomy' => 'event-format',
    'hide_empty' => true
]);
?>

<div class="acf-events-calendar-wrapper">
    
    <!-- Filters -->
    <div class="acf-events-filters">
        
        <div class="filter-group">
            <label for="event-type-filter">Event Type</label>
            <select id="event-type-filter" class="event-filter">
                <option value="">All Types</option>
                <?php if (!empty($event_types) && !is_wp_error($event_types)) : ?>
                    <?php foreach ($event_types as $term) : ?>
                        <option value="<?php echo esc_attr($term->slug); ?>">
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="event-format-filter">Event Format</label>
            <select id="event-format-filter" class="event-filter">
                <option value="">All Formats</option>
                <?php if (!empty($event_formats) && !is_wp_error($event_formats)) : ?>
                    <?php foreach ($event_formats as $term) : ?>
                        <option value="<?php echo esc_attr($term->slug); ?>">
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="date-range-filter">Date Range</label>
            <input type="text" id="date-range-filter" placeholder="Select date range..." readonly>
        </div>
        
        <div class="filter-group">
            <label for="event-search">Search</label>
            <input type="text" id="event-search" placeholder="Search events...">
        </div>
        
        <div class="filter-group">
            <button id="clear-filters" class="clear-filters-btn">Clear Filters</button>
        </div>
        
    </div>
    
    <!-- Calendar -->
    <div id="acf-events-calendar"></div>
    
    <!-- Event Detail Modal -->
    <div id="event-modal" class="event-modal" style="display: none;">
        <div class="event-modal-overlay"></div>
        <div class="event-modal-content">
            <button class="event-modal-close">&times;</button>
            <div class="event-modal-body">
                <!-- Content populated by JavaScript -->
            </div>
        </div>
    </div>
    
</div>
