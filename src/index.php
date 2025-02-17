<?php
require_once __DIR__ . "/controllers/OrderController.php";
require_once __DIR__ . "/controllers/DiscountController.php";

// API nin URL yoluna ve HTTP metoduna göre işlemler

$requestUri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$method     = $_SERVER["REQUEST_METHOD"];

if($requestUri == "/order" && $method == "POST") { // Sipariş ekleme
    OrderController::handlePost();
}
elseif($requestUri == "/orders" && $method == "GET") { // Tüm siparişleri listeleme
    OrderController::handleGet();
}
elseif(preg_match('/^\/orders\/(\d+)$/', $requestUri, $matches) && $method == "GET") { // Müşteri siparişlerini listeleme
    $customerId = (int)$matches[1];
    OrderController::handleGet($customerId);
}
elseif(preg_match('/^\/order\/(\d+)$/', $requestUri, $matches) && $method == "DELETE") { // Sipariş silme
    $orderId = $matches[1];
    OrderController::handleDelete($orderId);
}
elseif($requestUri == "/discount" && $method == "POST") { // Tüm siparişleri listeleme
    DiscountController::handle();
}
elseif(preg_match('/^\/discount\/(\d+)$/', $requestUri, $matches) && $method == "DELETE") { // Sadece indirim silme
    $orderId = $matches[1];
    DiscountController::handleDelete($orderId);
}
else {
    http_response_code(404);
    echo json_encode(["error" => "Not Found"]);
}
?>
