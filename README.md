# WordPress Plugin for WooCommerce-Brevo Integration

Custom integration between WooCommerce and Brevo's API.

## Installation

1. extract the brevo-integration.zip to public_html/wp-content/plugins/ directory.
2. go to the brevo-integration directory and execute following command.
```bash
composer require sendinblue/api-v3-sdk
```
3. go to public_html/ directory and open the wp-config.php and add following file at the end of the file.
```bash
define('BREVO_API_KEY', 'Your_Brevo_API_Key');
```



## Functionality
When a new order created in Woocommerce, the email address of the customer is added to the Brevo Contact List if the email address does not exist in the Contacts

