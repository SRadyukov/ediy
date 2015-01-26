<?php

class PostController extends BaseController {
    public function __construct(){
        parent::__construct();

        $this->content_template = 'section_post.htm';
    }

    function view($f3, $args) {
        $db = $f3->get('DB');

        $pats = explode('/', $args[0]);

        $post = (new PostModel($db))->load(array('post_name=?', $pats[2]));

        $f3->set('post', $post);

        $f3->set('menu', (new MenuModel($db))->find());
        $f3->set('test', 'Hello White!');

        //var_dump($post);
    }
}