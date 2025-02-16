<?php
require_once __DIR__ . "/../storage/JsonDB.php";
require_once __DIR__ . "/DiscountService.php";

class OrderService {
    // Yeni sipariş ekleme
    public static function addOrder($orderData) {
        // Payload validasyonu
        $validationErrors = self::validateOrderPayload($orderData);
        if (!empty($validationErrors)) {
            return ["error" => $validationErrors]; // Geçersiz verilerle işlem yapma
        }

        // Siparişteki her ürün için stok kontrolü ve toplam hesaplama
        $totalOrder = 0;
        $orders     = JsonDB::read("orders.json");

        foreach ($orderData["items"] as &$item) {
            $product = JsonDB::getJsonById($item["productId"], "products", "id");
            if (!$product) {
                return ["error" => "Product with ID {$item["productId"]} not found."];
            }

            // Tüm siparişlerde bu üründen kaç tane sipariş edildiğini hesapla
            $orderedQuantity = 0;
            foreach ($orders as $order) {
                foreach ($order["items"] as $orderedItem) {
                    if ($orderedItem["productId"] == $item["productId"]) {
                        $orderedQuantity += $orderedItem["quantity"];
                    }
                }
            }

            // Stok kontrolü: Ürünün mevcut stoğu - önceki siparişlerde kullanılan miktar
            $remainingStock = $product["stock"] - $orderedQuantity;
            if ($remainingStock < $item["quantity"]) {
                return ["error" => "Insufficient stock for product ID {$item["productId"]}. Remaining: {$remainingStock}"];
            }

            // Her ürün için toplam tutarı hesapla
            $item["total"] = $item["quantity"] * (float) $item["unitPrice"];
            $totalOrder   += $item["total"];

            // String formatına çevir
            $item["total"] = (string) $item["total"];
        }

        // Siparişin toplam tutarını string olarak güncelle
        $orderData["total"] = (string) $totalOrder;

        // Yeni sipariş ID'si
        $orderData["id"] = count($orders) + 1;

        // id'yi en başa al format bozulmasın
        $orderData = ["id" => $orderData["id"]] + $orderData;

        // Siparişi ekle
        $orders[] = $orderData;
        JsonDB::write("orders.json", $orders);

        // İndirim hesapla ve ekle
        $discountResponse      = DiscountService::calculateDiscount($orderData["id"]);
        $orderData["discount"] = $discountResponse;

        if ($discountResponse) {
            DiscountService::addDiscountReps($discountResponse);
        }

        return $orderData;
    }

    public static function addOrderx($orderData) {
        // Payload validasyonu
        $validationErrors = self::validateOrderPayload($orderData);
        if(!empty($validationErrors)) {
            return ["error" => $validationErrors]; // Geçersiz verilerle işlem yapma
        }

        // Siparişteki her ürün için stok kontrolü ve total hesaplama
        $totalOrder = 0;
        foreach($orderData["items"] as &$item) {
            $product = JsonDB::getJsonById($item["productId"], "products", "id");
            if(!$product) {
                return ["error" => "Product with ID {$item["productId"]} not found."];
            }

            if($product["stock"] < $item["quantity"]) {
                return ["error" => "Insufficient stock for product ID {$item["productId"]}."];
            }

            // Her ürün için toplam tutarı hesapla
            $item["total"] = $item["quantity"] * (float)$item["unitPrice"];
            $totalOrder   += $item["total"];

            $item["total"] = (string) $item["total"];
        }


        // Siparişin toplam tutarını string olarak güncelle
        $orderData["total"] = (string) $totalOrder;

        // Yeni sipariş ID si
        $orders = JsonDB::read("orders.json");
        $orderData["id"] = count($orders) + 1;

        // id yi en başa al format bozulmasın
        $orderData = ["id" => $orderData["id"]] + $orderData;

        // Siparişi ekle
        $orders[] = $orderData;
        JsonDB::write("orders.json", $orders);

        // İndirim
        $discountResponse      = DiscountService::calculateDiscount($orderData["id"]);
        $orderData["discount"] = $discountResponse; // İndirimleri çıktıya ekle.

        if($discountResponse) {
            DiscountService::addDiscountReps($discountResponse); // İndirimleri kaydet
        }
        return $orderData;
    }

    // Sipariş silme
    public static function deleteOrder($orderId) {
        $orders = JsonDB::read("orders.json");
        foreach($orders as $key => $order) {
            if($order["id"] == $orderId) {
                unset($orders[$key]);
                JsonDB::write("orders.json", array_values($orders)); // Güncel siparişleri kaydet
                $discMess = DiscountService::deleteDiscount($orderId);
                return ["message" => "Order deleted successfully." . $discMess["message"]];
            }
        }
        return ["message" => "Order not found."];
    }

    // Siparişleri listele
    public static function getOrders($customerId = null) {

        $validationError = self::validateCustomer($customerId);
        if($validationError) {
            return $validationError; // Eğer hata varsa dön dur
        }

        // JSON dan tüm siparişleri al
        $orders = JsonDB::read("orders.json");

        // Eğer customerId 0 değilse ve bir customerId varsa, o customerId a ait siparişleri getir
        if($customerId !== null && $customerId != 0) {
            $orders = array_filter($orders, function($order) use ($customerId) {
                return $order["customerId"] == $customerId;
            });
        }

        // Siparişleri listelerken indirimleri de göstermek gerekir
        foreach ($orders as &$order) {
            $order["discount"] = JsonDB::getJsonById($order["id"], "discount.response", "orderId");
        }

        // Dizi index lerini yeniden düzenle
        return array_values($orders);
    }

    private static function validateOrderPayload($data) {
        $errors = [];

        if(!isset($data["customerId"]) || !is_numeric($data["customerId"])) {
            //müşteri id var mı kontrolü eklenecek
            $errors[] = "customerId must be a numeric value.";
        }
        else {
            $validationError = self::validateCustomer($data["customerId"]);
            if($validationError) {
                $errors[] = $validationError["error"];
            }
        }

        if(!isset($data["items"]) || !is_array($data["items"]) || count($data["items"]) == 0) {
            $errors[] = "items must be a non-empty array.";
        }
        else {
            foreach($data["items"] as $item) {
                // her bir item için validasyon
                if(!isset($item["productId"]) || !is_numeric($item["productId"])) {
                    $errors[] = "productId must be a numeric value.";
                }
                if(!isset($item["quantity"]) || !is_numeric($item["quantity"]) || $item["quantity"] <= 0) {
                    $errors[] = "quantity must be a positive numeric value.";
                }
                if(!isset($item["unitPrice"]) || !is_numeric($item["unitPrice"]) || $item["unitPrice"] <= 0) {
                    $errors[] = "unitPrice must be a positive numeric value.";
                }
            }
        }
        return $errors;
    }

    // Böyle bir müşteri var mı ?
    public static function validateCustomer($customerId) {
        if($customerId !== null) {
            $customer = JsonDB::getJsonById($customerId, "customers", "id");
            if(!$customer) {
                return ["error" => "Customer with ID {$customerId} not found."];
            }
        }
        return null;
    }
}
?>
