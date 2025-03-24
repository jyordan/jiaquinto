<?php

require_once("./autoload.php");

// Fetch the public IP address
$public_ip = @file_get_contents('https://api.ipify.org');

// Check if the request was successful
if ($public_ip === FALSE) {
  echoParagraph("Error fetching IP address.");
} else {
  // Display the public IP address
  echoParagraph("Public IP Address: " . $public_ip);
}
