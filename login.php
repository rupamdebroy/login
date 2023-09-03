<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/class/CryptoJSAES.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/class/ResourceIncluder/ResourceIncluder.php';

// Define constants
define('PASSPHRASE', 'my_secret_passphrase');
define('TIMEZONE', 'Asia/Kolkata');

// Function to redirect based on user type
function redirectToUserType($usertype) {
    $redirectMap = [
        'p' => 'patient/index.php',
        'a' => 'admin/index.php',
        'd' => 'doctor/index.php',
        'c' => 'clinic/index.php',
        'b' => 'agent/index.php'
    ];

    if (isset($redirectMap[$usertype])) {
        header("Location: {$redirectMap[$usertype]}");
        exit();
    } else {
        handleDefaultCase();
    }
}

function handleDefaultCase() {
    // Clean up session and cookies
    $_SESSION = [];
    session_regenerate_id(true);

    $cookie_name = "session_data";
    setcookie($cookie_name, "", time() - 3600, "/");

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 86400, '/');
    }

    session_destroy();
    session_start();
}

try {
    if (isset($_SESSION['usertype'])) {
        redirectToUserType($_SESSION['usertype']);
    } elseif (isset($_COOKIE['session_data'])) {
        // Retrieve the encrypted session data from the cookie
        $encrypted_data = $_COOKIE['session_data'];

        // Decrypt the encrypted data using the passphrase
        $decrypted_data = CryptoJSAES::decrypt($encrypted_data, PASSPHRASE);

        // Check if decryption was successful and data is in the expected format
        $session_data = json_decode($decrypted_data, true);
        if (is_array($session_data) && isset($session_data['usertype'])) {
            $_SESSION['usertype'] = $session_data['usertype'];
            redirectToUserType($_SESSION['usertype']);
        } else {
            handleDefaultCase();
        }
    } else {
        handleDefaultCase();
    }
} catch (Exception $e) {
    // Handle decryption errors or other exceptions
    handleDefaultCase();
}

// Set the timezone
date_default_timezone_set(TIMEZONE);

// Store the current date in the session
$date = date('Y-m-d');
$_SESSION["date"] = $date;
?>
