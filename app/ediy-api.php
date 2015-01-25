<?php
/**
 * Created by PhpStorm.
 * User: SRadyukov
 * Date: 25.01.2015
 * Time: 16:36
 */

class EdiApi extends Prefab {



    public function get_main_menu($db){
        return $db->exec('SELECT term_id,name, slug, term_group FROM wp_terms ;');
    }
}