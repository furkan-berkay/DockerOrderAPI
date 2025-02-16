<?php
// Sanırım model kullanmayacağım
class Customer {
    public int $id;
    public string $name;
    public float $revenue;

    public function __construct($id, $name, $revenue) {
        $this->id      = $id;
        $this->name    = $name;
        $this->revenue = $revenue;
    }
}

?>
