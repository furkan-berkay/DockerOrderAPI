<?php
require_once __DIR__ . "/../storage/JsonDB.php";

class DiscountService {
    public static function calculateDiscount($orderId) {
        $total = 0;
        $discounts = [];

        $order = JsonDB::getJsonById($orderId, "orders", "id");
        if(empty($order)) { return null; }

        // Siparişteki ürünleri döngüyle kontrol et total bul
        if(isset($order["total"]) && $order["total"] > 0) { $total = $order["total"]; }
        else {
            foreach($order["items"] as $item) {
                $total += $item["unitPrice"] * $item["quantity"];
            }
        }

        $discountedTotal = $total;

        // 1000 TL ve üzerinde alışverişe %10 indirim
        if($total >= 1000) {
            $discountAmount  = $total * 0.1;
            $discountedTotal = $total - $discountAmount;

            $discounts[] = [
                "discountReason" => "10_PERCENT_OVER_1000",
                "discountAmount" => number_format($discountAmount, 2, ".", ""),
                "subtotal"       => number_format($discountedTotal, 2, ".", "")
            ];
        }

        // category si 2 olan bir üründen 6 adet alındığında bir tanesi bedava
        foreach($order["items"] as $item) {
            $product = JsonDB::getJsonById($item["productId"], "products", "id");

            // Eğer ürün kategori 2 ise ve adet >= 6 adet ise
            if($product["category"] == 2 && $item["quantity"] >= 6) {
                $freeItemPrice    = $product["price"]; // Bedava ürün fiyatını çıakr
                $discountedTotal -= $freeItemPrice;

                $discounts[] = [
                    "discountReason" => "BUY_5_GET_1_CATEGORY_2",
                    "discountAmount" => number_format($freeItemPrice, 2, ".", ""),
                    "subtotal"       => number_format($discountedTotal, 2, ".", "")
                ];
            }
        }

        // category si 1 olan ürünlerden 2 veya daha fazla ürün alındığında en ucuz ürüne %20 indirim
        $category1Items = [];
        foreach($order["items"] as $item) {
            $product = JsonDB::getJsonById($item["productId"], "products", "id");

            // Ürün category 1 ise
            if($product["category"] == 1) {
                $category1Items[] = [
                    "productId" => $item["productId"],
                    "quantity"  => $item["quantity"],
                    "unitPrice" => (float)$item["unitPrice"]
                ];
            }
        }
        
        // category si 1 olan ürünlerin adedi 1 den fazla mı ?
        if(count($category1Items) >= 2) {
            // ürünlerin fiyatlarını küçükten büyüğe sırala
            usort($category1Items, function ($a, $b) {
                return $a["unitPrice"] <=> $b["unitPrice"];
            });
            
            // En ucuz ürüne %20 indirim
            $lowestPriceItem  = $category1Items[0];
            $discountAmount   = $lowestPriceItem["unitPrice"] * 0.2;
            $discountedTotal -= $discountAmount;

            $discounts[] = [
                "discountReason" => "20_PERCENT_LOWEST_PRODUCT_CATEGORY_1",
                "discountAmount" => number_format($discountAmount, 2, ".", ""),
                "subtotal"       => number_format($discountedTotal, 2, ".", "")
            ];
        }

        if(empty($discounts)) { return null; }

        return [
            "orderId"         => $order["id"],
            "discounts"       => $discounts,
            "totalDiscount"   => number_format($total - $discountedTotal, 2, ".", ""),
            "discountedTotal" => number_format($discountedTotal, 2, ".", "")
        ];
    }

    // İndirim bilgileri kaydetme
    public static function addDiscountReps($discountData) {
        $existingDiscounts = JsonDB::read("discount.response.json");
        $existingDiscounts[] = $discountData;
        JsonDB::write("discount.response.json", $existingDiscounts);
        return $discountData;
    }

    // İndirim silme
    public static function deleteDiscount($orderId) {
        $discount = JsonDB::read("discount.response.json");
        foreach($discount as $key => $value) {
            if($value["orderId"] == $orderId) {
                unset($discount[$key]);
                JsonDB::write("discount.response.json", array_values($discount));
                return ["message" => "Discount data deleted successfully."];
            }
        }
        return ["message" => "Discount data not found."];
    }
}
?>
