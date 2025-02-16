<?php
require_once __DIR__ . "/../services/OrderService.php";

class OrderController {
    // Sipariş ekleme
    public static function handlePost() {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);

        // Yeni sipariş insert
        $response = OrderService::addOrder($data);
        echo json_encode($response);
    }

    // Siparişleri listeleme
    public static function handleGet($customerId = null) {
        header("Content-Type: application/json");
        $orders = OrderService::getOrders($customerId);
        echo json_encode($orders);
    }

    // Sipariş silme
    public static function handleDelete($orderId) {
        header("Content-Type: application/json");
        $response = OrderService::deleteOrder($orderId);
        echo json_encode($response);
    }
}
?>
