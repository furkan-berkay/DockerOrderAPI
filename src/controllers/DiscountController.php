<?php
require_once __DIR__ . "/../services/DiscountService.php";

class DiscountController {
    public static function handle() {
        header("Content-Type: application/json");

        $method = $_SERVER["REQUEST_METHOD"];

        if($method === "POST") {
            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data["orderId"])) {
                http_response_code(400);
                echo json_encode(["error" => "orderId is required"]);
                return;
            }

            // İndirim hesaplamak için DiscountService kullan
            $discountResponse = DiscountService::calculateDiscount($data["orderId"]);

            echo json_encode($discountResponse);
        }
        else {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
        }
    }
}
