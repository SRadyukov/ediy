<?php

class BaseController {

    protected $db, $section;

    //! Instantiate class
    function __construct() {
        $f3 = Base::instance();

        // Connect to the database
        $db = new DB\SQL($f3->get('db'), $f3->get('db_user'), $f3->get('db_pass'));

        $this->content_template = 'section_posts.html';

        // Use database-managed sessions
        //new DB\SQL\Session($db);

        // Save frequently used variables
        $f3->set('DB', $db);

        setlocale(LC_ALL, 'ru_RU.UTF-8');
    }

    //! HTTP route pre-processor
    function beforeroute($f3) {
        $db = $f3->get('DB');

        $f3->set('menu', []);
        $f3->set('posts', []);

        $f3->set('content_template', $this->content_template);

        $f3->set('menu', (new MenuModel($db))->find());
    }

    //! HTTP route post-processor
    function afterroute($f3) {
        // Render HTML layout
        echo Template::instance()->render('layout.htm');
    }

    function error($f3) {
        $log=new Log('error.log');
        $log->write($f3->get('ERROR.text'));
        foreach ($f3->get('ERROR.trace') as $frame)
            if (isset($frame['file'])) {
                // Parse each backtrace stack frame
                $line='';
                $addr=$f3->fixslashes($frame['file']).':'.$frame['line'];
                if (isset($frame['class']))
                    $line.=$frame['class'].$frame['type'];
                if (isset($frame['function'])) {
                    $line.=$frame['function'];
                    if (!preg_match('/{.+}/',$frame['function'])) {
                        $line.='(';
                        if (isset($frame['args']) && $frame['args'])
                            $line.=$f3->csv($frame['args']);
                        $line.=')';
                    }
                }
                // Write to custom log
                echo $addr; echo $line;
            }
        $f3->set('inc','error.htm');
    }

    function view($f3, $args) {

        $db = $f3->get('DB');
        $f3->set('posts', (new PostModel($db))->find(array('post_status=? and post_type=?','publish', 'post'), array('order'=>'ID DESC', 'offset'=>0, 'limit'=>10 )));


        //$tm = new wp_terms_model($db);

        //$tm->find(
        //    array('term_group<>?', '')
        //);


        //var_dump($posts);


        //echo $terms;

        //echo $model->loaded();

        //$model = new PostModel();

        //$posts = $model->load();

        //$f3->set('test', '---- test text ----');

        //$f3->set('posts', $posts);
    }
}