<?php
/**
 * Plugin Name: ACF Events Calendar
 * Description: Custom events calendar with FullCalendar.io integration for ACF-powered events
 * Version: 1.0.4
 * Author: SHYFT
 * Requires PHP: 7.4
 * GitHub Plugin URI: shyft-marketing/acf-events-calendar
 * Primary Branch: main
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ACF_EVENTS_CALENDAR_VERSION', '1.0.0');
define('ACF_EVENTS_CALENDAR_PATH', plugin_dir_path(__FILE__));
define('ACF_EVENTS_CALENDAR_URL', plugin_dir_url(__FILE__));

class ACF_Events_Calendar {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->include_files();
        $this->init_hooks();
    }
    
    private function include_files() {
        require_once ACF_EVENTS_CALENDAR_PATH . 'includes/class-rest-api.php';
    }
    
    private function init_hooks() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_shortcode('acf_events_calendar', [$this, 'render_calendar']);
        
        // Initialize REST API
        ACF_Events_Calendar_REST_API::get_instance();
    }
    
    public function enqueue_assets() {
        // Only enqueue if shortcode is present
        global $post;
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'acf_events_calendar')) {
            return;
        }
        
        // Font Awesome
        wp_enqueue_script(
            'font-awesome',
            'https://kit.fontawesome.com/501cbc8a37.js',
            [],
            null,
            true
        );
        
        // FullCalendar CSS
        wp_enqueue_style(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css',
            [],
            '6.1.10'
        );
        
        // Flatpickr for date range picker
        wp_enqueue_style(
            'flatpickr',
            'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
            [],
            '4.6.13'
        );
        
        // Custom CSS
        wp_enqueue_style(
            'acf-events-calendar',
            ACF_EVENTS_CALENDAR_URL . 'assets/css/calendar.css',
            [],
            ACF_EVENTS_CALENDAR_VERSION
        );
        
        // FullCalendar JS
        wp_enqueue_script(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js',
            [],
            '6.1.10',
            true
        );
        
        // Flatpickr JS
        wp_enqueue_script(
            'flatpickr',
            'https://cdn.jsdelivr.net/npm/flatpickr',
            [],
            '4.6.13',
            true
        );
        
        // Custom JS
        wp_enqueue_script(
            'acf-events-calendar',
            ACF_EVENTS_CALENDAR_URL . 'assets/js/calendar.js',
            ['jquery', 'fullcalendar', 'flatpickr'],
            ACF_EVENTS_CALENDAR_VERSION,
            true
        );
        
        // Localize script with REST API endpoint
        wp_localize_script('acf-events-calendar', 'acfEventsCalendar', [
            'restUrl' => rest_url('acf-events/v1/events'),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }
    
    public function render_calendar($atts) {
        $atts = shortcode_atts([
            'default_view' => 'dayGridMonth'
        ], $atts);
        
        ob_start();
        include ACF_EVENTS_CALENDAR_PATH . 'templates/calendar.php';
        return ob_get_clean();
    }
}

// Initialize plugin
function acf_events_calendar_init() {
    ACF_Events_Calendar::get_instance();
}
add_action('plugins_loaded', 'acf_events_calendar_init');
