<?php

if (!defined('ABSPATH')) {
    exit;
}

class ACF_Events_Calendar_REST_API {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    public function register_routes() {
        register_rest_route('acf-events/v1', '/events', [
            'methods' => 'GET',
            'callback' => [$this, 'get_events'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    public function get_events($request) {
        $params = $request->get_params();
        
        // Build query args
        $args = [
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'start_date',
                    'compare' => 'EXISTS'
                ]
            ],
            'orderby' => 'meta_value',
            'meta_key' => 'start_date',
            'order' => 'ASC'
        ];
        
        // Filter by event type
        if (!empty($params['event_type'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'event-type',
                'field' => 'slug',
                'terms' => $params['event_type']
            ];
        }
        
        // Filter by event format
        if (!empty($params['event_format'])) {
            if (!isset($args['tax_query'])) {
                $args['tax_query'] = [];
            }
            $args['tax_query'][] = [
                'taxonomy' => 'event-format',
                'field' => 'slug',
                'terms' => $params['event_format']
            ];
        }
        
        // Set relation for tax_query if we have multiple
        if (isset($args['tax_query']) && count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
        
        // Search by event name
        if (!empty($params['search'])) {
            $args['s'] = sanitize_text_field($params['search']);
        }
        
        // Date range filter
        if (!empty($params['start_date']) || !empty($params['end_date'])) {
            $date_query = [];
            
            if (!empty($params['start_date'])) {
                $date_query[] = [
                    'key' => 'start_date',
                    'value' => sanitize_text_field($params['start_date']),
                    'compare' => '>=',
                    'type' => 'DATE'
                ];
            }
            
            if (!empty($params['end_date'])) {
                $date_query[] = [
                    'key' => 'start_date',
                    'value' => sanitize_text_field($params['end_date']),
                    'compare' => '<=',
                    'type' => 'DATE'
                ];
            }
            
            if (!empty($date_query)) {
                $args['meta_query'][] = [
                    'relation' => 'AND',
                    ...$date_query
                ];
            }
        }
        
        $query = new WP_Query($args);
        $events = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $event_id = get_the_ID();
                
                // Get all ACF fields
                $start_date = get_field('start_date', $event_id);
                $end_date = get_field('end_date', $event_id);
                $start_time = get_field('start_time', $event_id);
                $end_time = get_field('end_time', $event_id);
                $event_url = get_field('event_url', $event_id);
                $event_address = get_field('event_address', $event_id);
                $event_description = get_field('event_description', $event_id);
                $registration_required = get_field('registration_required', $event_id);
                $registration_link = get_field('registration_link', $event_id);
                $registration_cta = get_field('registration_cta_text', $event_id);
                
                // Get taxonomies
                $event_types = wp_get_post_terms($event_id, 'event-type', ['fields' => 'names']);
                $event_formats = wp_get_post_terms($event_id, 'event-format', ['fields' => 'names']);
                
                // Format dates for FullCalendar
                $start_datetime = $start_date;
                if ($start_time) {
                    $start_datetime .= 'T' . $start_time;
                }
                
                $end_datetime = $end_date ?: $start_date;
                if ($end_time) {
                    $end_datetime .= 'T' . $end_time;
                } elseif (!$end_date && $start_time) {
                    // Single day event with start time but no end time
                    $end_datetime .= 'T' . $start_time;
                }
                
                $event_data = [
                    'id' => $event_id,
                    'title' => get_the_title(),
                    'start' => $start_datetime,
                    'end' => $end_datetime,
                    'allDay' => empty($start_time),
                    'url' => 'javascript:void(0)', // Prevent default link behavior
                    'extendedProps' => [
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                        'event_url' => $event_url,
                        'event_address' => $event_address,
                        'event_description' => $event_description,
                        'registration_required' => $registration_required,
                        'registration_link' => $registration_link,
                        'registration_cta' => $registration_cta,
                        'event_types' => $event_types,
                        'event_formats' => $event_formats,
                        'permalink' => get_permalink($event_id)
                    ]
                ];
                
                $events[] = $event_data;
            }
            wp_reset_postdata();
        }
        
        return rest_ensure_response($events);
    }
}
