<?php
// Simple health check endpoint for debugging from device
require_once __DIR__ . '/core/Response.php';

// Reuse corsHeaders + ok from Response.php
corsHeaders();

ok(['pong' => true, 'timestamp' => time()]);
