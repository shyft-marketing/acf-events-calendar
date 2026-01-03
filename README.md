# ACF Events Calendar Plugin

Custom WordPress plugin for displaying ACF-powered events in a FullCalendar.io interface with filtering capabilities.

## Features

- ✅ Full calendar view with month/week/list views
- ✅ Ajax-powered filtering (no page reloads)
- ✅ Filter by Event Type taxonomy
- ✅ Filter by Event Format taxonomy
- ✅ Date range picker for filtering
- ✅ Search by event name
- ✅ Event detail modal popup
- ✅ Responsive design
- ✅ Google Maps integration for addresses
- ✅ Conditional field display (only shows filled fields)

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Advanced Custom Fields (ACF) plugin
- Event post type with slug: `event`
- ACF fields configured as documented

## Installation

1. Upload the `acf-events-calendar` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the shortcode `[acf_events_calendar]` to any page

## Required Setup

### Custom Post Type
Post type slug must be: `event`

### ACF Fields
The following ACF fields must be configured on your event post type:

- `start_date` - Date Picker
- `end_date` - Date Picker
- `start_time` - Time Picker
- `end_time` - Time Picker
- `event_url` - URL
- `event_address` - Google Map
- `event_description` - WYSIWYG Editor
- `registration_required` - True/False
- `registration_link` - URL (conditional on registration_required)
- `registration_cta_text` - Text (conditional on registration_required)

### Taxonomies
The following taxonomies must be registered:

- `event-type` - Event Type taxonomy
- `event-format` - Event Format taxonomy

## Usage

### Basic Shortcode
```
[acf_events_calendar]
```

Place this shortcode on any page where you want the calendar to appear.

### How It Works

**Single-Day Events:**
- Set `start_date` and `end_date` to the same date
- Optionally add start/end times

**Multi-Day Events:**
- Set `start_date` and `end_date` to different dates
- Calendar will display the event across all days

**Filtering:**
- All filters update via Ajax (no page refresh)
- Multiple filters can be combined
- Date range picker allows selecting a custom range
- Search searches event titles

**Event Details:**
- Click any event to open the modal
- Only populated fields are displayed
- Registration button appears if registration is required
- Google Maps link appears if address is provided

## Customization

### CSS
Edit `/assets/css/calendar.css` to customize styles. The CSS uses clear class names:

- `.acf-events-calendar-wrapper` - Main wrapper
- `.acf-events-filters` - Filter container
- `.event-modal` - Modal container
- `.event-modal-body` - Modal content
- And many more...

### Colors by Event Format
Events are automatically colored based on format:

- Virtual Event: Green (#28a745)
- In-Person: Blue (#007bff)
- Hybrid: Purple (#6f42c1)

Edit the CSS to change these colors or add new format-specific styling.

### JavaScript
Edit `/assets/js/calendar.js` to customize functionality.

## File Structure

```
acf-events-calendar/
├── acf-events-calendar.php    # Main plugin file
├── README.md                   # This file
├── includes/
│   └── class-rest-api.php     # REST API endpoint
├── templates/
│   └── calendar.php           # Calendar template
├── assets/
│   ├── css/
│   │   └── calendar.css       # Styles
│   └── js/
│       └── calendar.js        # Calendar functionality
```

## Support

For issues or questions, contact your developer or submit feedback through your organization.

## Version

Current version: 1.0.0

## Credits

Built with:
- FullCalendar.io v6.1.10
- Flatpickr v4.6.13
- WordPress REST API
- Advanced Custom Fields
