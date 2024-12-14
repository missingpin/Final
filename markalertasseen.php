<?php
session_start(); 

$data = json_decode(file_get_contents('php://input'), true);
$alert_id = $data['alert_id'];

if (!isset($_SESSION['seen_alerts'])) {
    $_SESSION['seen_alerts'] = [];
}

if (!in_array($alert_id, $_SESSION['seen_alerts'])) {
    $_SESSION['seen_alerts'][] = $alert_id;
}

echo json_encode(['status' => 'success']);
?>
