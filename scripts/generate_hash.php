<?php
// Password Hash Generator Utility
// To use: run from the command line: php generate_hash.php "your_password_here"
// Or visit locally if using a local server.

if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $password = $argv[1];
} elseif (isset($_GET['password'])) {
    $password = $_GET['password'];
} else {
    echo "Please provide a password as an argument (CLI) or as a 'password' GET parameter (Web).\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";
?>
