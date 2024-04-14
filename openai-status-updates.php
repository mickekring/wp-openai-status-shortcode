<?php
/**
 * Plugin Name: OpenAI Status Updates
 * Plugin URI: https://mickekring.se
 * Description: Displays recent problems from the OpenAI status page via shortcode.
 * Version: 1.1
 * Author: Micke Kring
 * Author URI: https://mickekring.se
 */

// Register the shortcode handler
add_shortcode('openai_status', 'display_openai_status');

/**
 * Handle the shortcode to display OpenAI status updates.
 */
function display_openai_status() {
    $output = "<div class='openai-status-updates'>";
    $unresolved_incidents = fetch_openai_status_data('https://status.openai.com/api/v2/incidents/unresolved.json');

    if (!empty($unresolved_incidents['incidents'])) {
        foreach ($unresolved_incidents['incidents'] as $incident) {
            $output .= "<div class='incident'>";
            $output .= sprintf("<h3>%s</h3>", esc_html($incident['name']));
            $output .= sprintf("<p><strong>Status:</strong> %s<br>", esc_html($incident['status']));
            $output .= sprintf("<strong>Impact:</strong> %s<br>", esc_html($incident['impact']));
            
            // Convert the updated_at to a DateTime object with the appropriate timezone
            $date = new DateTime($incident['updated_at']);
            $date->setTimezone(new DateTimeZone('Europe/Stockholm')); // Set the timezone to Stockholm
            $formatted_date = $date->format('Y-m-d H:i'); // Format the date and time

            $output .= sprintf("<strong>Updated at:</strong> %s</p>", $formatted_date);

            // $output .= sprintf("<p><strong>Updated at:</strong> %s</p>", esc_html($incident['updated_at']));

            if (!empty($incident['incident_updates'])) {
                $latest_update = $incident['incident_updates'][0]; // Assuming the first one is the latest
                $output .= sprintf("<p><strong>Latest Update:</strong> %s</p>", esc_html($latest_update['body']));
            }

            $output .= "</div>"; // Close .incident
        }
    } else {
        $output .= "<p>No unresolved incidents at this time.</p>";
    }

    $output .= "</div>"; // Close .openai-status-updates

    return $output;
}


/**
 * Fetch data from the OpenAI Status API.
 * 
 * @param string $url API endpoint URL.
 * @return array Decoded JSON response.
 */
function fetch_openai_status_data($url) {
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return []; // Return empty array in case of error
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true); // Decode the JSON response into an array
}
