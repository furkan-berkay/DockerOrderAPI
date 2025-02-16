<?php
class Order {
    public int $id;
    public int $customer_id;
    public array $items;
    public float $total;

    public function __construct($id, $customer_id, $items, $total = 0.0) {
        $this->id          = $id;
        $this->customer_id = $customer_id;
        $this->items       = $items;
        $this->total       = $total;
    }
}
