<?php

class PostModel extends DB\SQL\Mapper {
    public function __construct($db) {
        parent::__construct($db, 'wp_posts');
    }
}