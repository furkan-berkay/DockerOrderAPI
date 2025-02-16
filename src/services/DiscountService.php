<?php
require_once __DIR__ . "/../storage/JsonDB.php";

class DiscountService {

    // İndirim kurallarını ayrı fonksiyonlar
    public static function applyDiscounts($order) {
        $total           = self::calculateTotal($order);
        $discountedTotal = $total;
        $discounts       = [];

        // Kuralları sırayla uygula
        $discounts = array_merge($discounts, self::apply10PercentOver1000($order, $discountedTotal));
        $discounts = array_merge($discounts, self::applyBuy5Get1Category2($order, $discountedTotal));
        $discounts = array_merge($discounts, self::apply20PercentLowestProductCategory1($order, $discountedTotal));

        return [
            'totalDiscount'   => number_format($total - $discountedTotal, 2, ".", ""),
            'discountedTotal' => number_format($discountedTotal, 2, ".", ""),
            'discounts'       => $discounts
        ];
    }

    // Siparişin toplamı
    public static function calculateTotal($order) {
        if (isset($order["total"]) && $order["total"] > 0) {
            return $order["total"];
        }

        $total = 0;
        foreach ($order["items"] as $item) {
            $total += $item["unitPrice"] * $item["quantity"];
        }
        return $total;
    }

    // 1000 TL ve üzeri %10 İndirim
    public static function apply10PercentOver1000($order, &$discountedTotal) {
        $total = self::calculateTotal($order);
        $discounts = [];
        if ($total >= 1000) {
            $discountAmount   = $total * 0.1;
            $discountedTotal -= $discountAmount;

            $discounts[] = [
                "discountReason" => "10_PERCENT_OVER_1000",
                "discountAmount" => number_format($discountAmount, 2, ".", ""),
                "subtotal"       => number_format($discountedTotal, 2, ".", "")
            ];
        }
        return $discounts;
    }

    // Eğer ürün kategori 2 ise ve adet >= 6 adet ise biri bedeva
    public static function applyBuy5Get1Category2($order, &$discountedTotal) {
        $discounts = [];
        foreach ($order["items"] as $item) {
            $product = JsonDB::getJsonById($item["productId"], "products", "id");
            if ($product["category"] == 2 && $item["quantity"] >= 6) {
                $freeItemPrice    = $product["price"];
                $discountedTotal -= $freeItemPrice;

                $discounts[] = [
                    "discountReason" => "BUY_5_GET_1_CATEGORY_2",
                    "discountAmount" => number_format($freeItemPrice, 2, ".", ""),
                    "subtotal"       => number_format($discountedTotal, 2, ".", "")
                ];
            }
        }
        return $discounts;
    }

    // category si 1 olan ürünlerden 2 veya daha fazla ürün alındığında en ucuz ürüne %20 indirim
    public static function apply20PercentLowestProductCategory1($order, &$discountedTotal) {
        $category1Items = [];
        foreach ($order["items"] as $item) {
            $product = JsonDB::getJsonById($item["productId"], "products", "id");
            if ($product["category"] == 1) {
                $category1Items[] = [
                    "productId" => $item["productId"],
                    "quantity"  => $item["quantity"],
                    "unitPrice" => (float)$item["unitPrice"]
                ];
            }
        }

        $discounts = [];
        // category si 1 olan ürünlerin adedi 1 den fazla mı ?
        if (count($category1Items) >= 2) {
            // Ürünlerin fiyatlarını küçükten büyüğe sırala
            usort($category1Items, function ($a, $b) {
                return $a["unitPrice"] <=> $b["unitPrice"];
            });

            $lowestPriceItem  = $category1Items[0];
            $discountAmount   = $lowestPriceItem["unitPrice"] * 0.2;
            $discountedTotal -= $discountAmount;

            $discounts[] = [
                "discountReason" => "20_PERCENT_LOWEST_PRODUCT_CATEGORY_1",
                "discountAmount" => number_format($discountAmount, 2, ".", ""),
                "subtotal"       => number_format($discountedTotal, 2, ".", "")
            ];
        }
        return $discounts;
    }

    public static function calculateDiscount($orderId) {
        $order = JsonDB::getJsonById($orderId, "orders", "id");
        if (empty($order)) { return null; }

        $discountData = self::applyDiscounts($order);
        return [
            "orderId"         => $order["id"],
            "discounts"       => $discountData['discounts'],
            "totalDiscount"   => $discountData['totalDiscount'],
            "discountedTotal" => $discountData['discountedTotal']
        ];
    }

    // İndirim bilgileri kaydetme
    public static function addDiscountReps($discountData) {
        $existingDiscounts   = JsonDB::read("discount.response.json");
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
