<?php
require_once 'config.php';
require_once 'classes/Appointment.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['date'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Date parameter is required']);
    exit();
}

try {
    $appointment = new Appointment();
    $timeSlots = $appointment->getAvailableTimeSlots($_GET['date']);
    
    header('Content-Type: application/json');
    echo json_encode($timeSlots);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?> 