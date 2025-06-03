<?php
return [
    'client_id' => 'AXZDE4y62YpYoU7N5sbcM-twqgHZarexeFWpzTrOFrAaqyaWaqWoJkPlDM8JwuzMzZirKwiFq9fzcLDa',
    'client_secret' => 'ELc_ngUxuSMRfP59Ieppd0zqleb3hHoctGWqL_k7GCZQfEtcR7Q5T_m8oESNwyXyut7ft7lNOnVNmF2u',
    'sandbox' => true, // set to false in production
    
    // API URLs
    'api_url' => 'https://api.sandbox.paypal.com', // Use https://api.paypal.com for production
    
    // Redirect URLs after payment
    'return_url' => 'http://localhost/E-BOOK--EVENT-MANAGEMENT-SYSTEM-/Source%20Code/API/paypal/execute_payment.php',
    'cancel_url' => 'http://localhost/E-BOOK--EVENT-MANAGEMENT-SYSTEM-/Source%20Code/pages/payment_cancel.php',
    
    // Currency settings
    'currency' => 'USD', // Change to PHP for Philippine Peso if supported
    
    // Application settings
    'app_name' => 'Event Management System',
    'webhook_url' => 'http://localhost/E-BOOK--EVENT-MANAGEMENT-SYSTEM-/Source%20Code/API/paypal/webhook.php',
    
    // Error handling
    'log_errors' => true,
    'debug_mode' => true // set to false in production
];
?>