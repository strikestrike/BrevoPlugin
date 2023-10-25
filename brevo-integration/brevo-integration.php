<?php
/*
Plugin Name: Brevo Integration
Description: Custom integration between WooCommerce and Brevo's API.
Version: 1.0
Author: Your Name
*/

// Include the Brevo API SDK
require_once(__DIR__ . '/vendor/autoload.php');

// Hook into WooCommerce events to trigger your integration
add_action('woocommerce_new_order', 'brevo_integration');

function brevo_integration($order_id) {
    // Get the API key from the wp-config.php file
    $api_key = defined('BREVO_API_KEY') ? BREVO_API_KEY : 'YOUR_API_KEY';

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

    $createContact->setListIds([10]);
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
