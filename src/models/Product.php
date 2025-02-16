<?php
class Product {
    public int $id;
    public string $name;
    public float $price;
    public int $stock;
    public int $category_id;

    public function __construct($id, $name, $price, $stock, $category_id) {
        $this->id          = $id;
        $this->name        = $name;
        $this->price       = $price;
        $this->stock       = $stock;
        $this->category_id = $category_id;
    }
}
?>
