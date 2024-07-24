<?php


class DatabankCountry {
    private $id;
    private $count;

    public function __construct($id = null, $count) {
        $this->id = $id;
        $this->count = $count;
    }

    public function getId() {
        return $this->id;
    }

    public function getCount() {
        return $this->count;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCount($count) {
        $this->count = $count;
    }
}
