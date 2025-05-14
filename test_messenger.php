<?php
// Test script to verify MessengerController works correctly
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controller/MessengerController.php';

try {
    // Create a controller instance
    $controller = new MessengerController();
    
    // Try to get unread count for user 456
    $unreadCount = $controller->getUnreadCount(456);
    
    echo "MessengerController is working correctly! Unread count: " . $unreadCount;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "\nTrace: " . $e->getTraceAsString();
}
?> 