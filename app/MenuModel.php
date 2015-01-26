<?php

class MenuModel extends DB\SQL\Mapper {
    public function __construct($db) {
        parent::__construct($db, 'wp_menu');
    }
}