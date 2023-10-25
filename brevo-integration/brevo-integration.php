<?php
/*
Plugin Name: Brevo Integration
Description: Custom integration between WooCommerce and Brevo's API.
Version: 1.0
Author: Super0312
*/

// Include Composer's autoloader
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';


// Include the CSS in your settings page
function brevo_integration_settings_styles() {
    echo '<style>
        .brevo-settings-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .brevo-settings-label {
            display: inline-block;
            width: 150px;
            margin-right: 20px;
            text-align: left;
            font-weight: bold;
        }

        .brevo-settings-input {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
        }

        .brevo-settings-submit {
            margin-left: 150px;
        }
    </style>';
}

// Add the CSS to the settings page
add_action('admin_head', 'brevo_integration_settings_styles');


// Step 1: Define the plugin settings
function brevo_integration_settings() {
    add_option('brevo_api_key', ''); // API Key
    add_option('brevo_contact_list_id', ''); // Contact List ID
}

// Step 2: Create the settings page
function brevo_integration_settings_page() {
    add_options_page('Brevo Integration Settings', 'Brevo Integration', 'manage_options', 'brevo_integration_settings', 'brevo_integration_settings_form');
}

// Step 3: Display and Process the settings
function brevo_integration_settings_form() {
    if (isset($_POST['submit'])) {
        update_option('brevo_api_key', sanitize_text_field($_POST['brevo_api_key']));
        update_option('brevo_contact_list_id', absint($_POST['brevo_contact_list_id']));
        echo '<div class="updated"><p>Settings saved</p></div>';
    }

    echo '<div class="wrap brevo-settings-form">';
    echo '<h2>Brevo Integration Settings</h2>';
    echo '<form method="post" action="">';

    echo '<label for="brevo_api_key" class="brevo-settings-label">API Key:</label>';
    echo '<input type="text" name="brevo_api_key" class="brevo-settings-input" value="' . esc_attr(get_option('brevo_api_key')) . '">';

    echo '<label for="brevo_contact_list_id" class="brevo-settings-label">Contact List ID:</label>';
    echo '<input type="number" name="brevo_contact_list_id" class="brevo-settings-input" value="' . esc_attr(get_option('brevo_contact_list_id')) . '">';

    echo '<input type="submit" name="submit" class="button button-primary brevo-settings-submit" value="Save Settings">';
    echo '</form>';
    echo '</div>';
}

// Add the settings page and settings initialization
add_action('admin_menu', 'brevo_integration_settings_page');
add_action('admin_init', 'brevo_integration_settings');

// contact integration.
function brevo_integration($order_id) {
    // Get the API key from the settings
    $api_key = get_option('brevo_api_key');
    // Get the Contact List ID from the settings
    $contact_list_id = (int)get_option('brevo_contact_list_id');

    // Configure API key authorization
    $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $api_key);
    $apiInstance = new SendinBlue\Client\Api\ContactsApi(new GuzzleHttp\Client(), $config);

    // Get the order information from WooCommerce
    $order = wc_get_order($order_id);
    $customer_email = $order->get_billing_email();

    // Extract other data as needed
    $sms = ''; // Extract SMS information from the order, or set a default
    $first_name = ''; // Extract first name information from the order, or set a default
    $last_name = ''; // Extract last name information from the order, or set a default


    // Create a new contact
    $createContact = new \SendinBlue\Client\Model\CreateContact();
    $createContact->setEmail($customer_email);

    // Set the attributes
    $attributes = [
        'SMS' => $sms,
        'FNAME' => $first_name,
        'LNAME' => $last_name,
    ];
    $createContact->setAttributes($attributes);

    $createContact->setListIds([$contact_list_id]);
    $createContact->setEmailBlacklisted(false);
    $createContact->setSmsBlacklisted(false);
    $createContact->setUpdateEnabled(false);

    try {
        // Create the contact in Brevo's system
        $result = $apiInstance->createContact($createContact);
        // You can add further logic or logging here if needed
    } catch (Exception $e) {
        // Handle exceptions, such as failed API requests
        error_log('Exception when calling ContactsApi->createContact: ' . $e->getMessage());
    }
}

// Hook into WooCommerce events to trigger your integration
add_action('woocommerce_new_order', 'brevo_integration');
