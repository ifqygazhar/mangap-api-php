<?php

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

// Define the host and port for the Swoole server
$host = '127.0.0.1';
$port = 9501;

// Create a new Swoole HTTP server
$server = new Server($host, $port);

// Define the callback function for the server
$server->on('request', function (Request $request, Response $response) {
    // Start output buffering to capture script output
    ob_start();

    // Include the index.php file to handle the request
    require __DIR__ . '/index.php';

    // Get the content from the output buffer
    $content = ob_get_clean();

    // Send the content to the client
    $response->end($content);
});

// Start the server
$server->start();

