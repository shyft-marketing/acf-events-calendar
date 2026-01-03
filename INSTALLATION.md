# Quick Installation & Setup Guide

## Step 1: Install the Plugin

1. Download `acf-events-calendar.zip`
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**
3. Choose the zip file and click **Install Now**
4. Click **Activate**

## Step 2: Verify Your Setup

Before using the calendar, make sure you have:

### ✅ ACF Fields
Go to **Custom Fields** and verify you have a field group for events with these fields:
- start_date (Date Picker)
- end_date (Date Picker)
- start_time (Time Picker)
- end_time (Time Picker)
- event_url (URL)
- event_address (Google Map)
- event_description (WYSIWYG Editor)
- registration_required (True/False)
- registration_link (URL) - shows if registration_required is Yes
- registration_cta_text (Text) - shows if registration_required is Yes

### ✅ Taxonomies
Make sure these taxonomies are registered for your event post type:
- event-type
- event-format

If you don't have these, I can help you add them!

## Step 3: Add the Calendar to a Page

1. Edit any page (or create a new one)
2. Add the shortcode: `[acf_events_calendar]`
3. Publish/Update the page
4. View the page to see your calendar!

## Step 4: Test It Out

1. **View the calendar** - Events should appear automatically
2. **Click an event** - Modal should open with details
3. **Try filtering** - Use the dropdowns and date picker
4. **Search** - Type in the search box to find events by name

## Customization Tips

### Change Event Colors
Edit `/wp-content/plugins/acf-events-calendar/assets/css/calendar.css`

Look for these lines (around line 135):
```css
.fc-event.format-virtual-event {
    background-color: #28a745; /* Change this color */
}
```

### Add More Event Formats
Just create new terms in the "event-format" taxonomy. The plugin will automatically show them in the filter dropdown.

### Modify the Modal Layout
Edit `/wp-content/plugins/acf-events-calendar/assets/js/calendar.js`

Look for the `showEventModal()` function (around line 145) to customize what displays in the popup.

## Troubleshooting

**Calendar not showing?**
- Make sure you have at least one published event with a start_date
- Check browser console for JavaScript errors

**Events not appearing?**
- Verify your post type slug is exactly: `event`
- Make sure events have the start_date field filled in
- Check that ACF field names match exactly

**Filters not working?**
- Clear your browser cache
- Make sure taxonomies are registered with correct slugs: `event-type` and `event-format`

**Modal not opening?**
- Check browser console for errors
- Make sure jQuery is loading

## Need Help?

If you run into issues:
1. Check the browser console for errors (F12 → Console tab)
2. Verify all ACF fields are set up correctly
3. Make sure you have some test events with dates filled in

Let me know if you need any modifications!
