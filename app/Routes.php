<?php

//! Front-end processor
class Routes extends BaseController {
    function index($f3, $args) {
        $model = new PostModel($this->db);

        $posts = $model->load();

        //$f3->set(posts, $posts);

        echo 'Hello White';
    }
}