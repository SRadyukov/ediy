<?php

//! Base controller
class Controller {

	protected
		$db;

	//! HTTP route pre-processor
	function beforeroute($f3) {
		$db=$this->db;
		// Prepare user menu
		$f3->set('menu', $db->exec('SELECT post_content, post_title FROM wp_posts WHERE post_status = \'publish\';'));
	}

	//! HTTP route post-processor
	function afterroute() {
		// Render HTML layout
		echo Template::instance()->render('layout.htm');
	}

	//! Instantiate class
	function __construct() {

		$f3 = Base::instance();

		// Connect to the database
		$db = new DB\SQL($f3->get('db'),$f3->get('db_user'),$f3->get('db_pass'));

//		if (file_exists('setup.sql')) {
//			// Initialize database with default setup
//			$db->exec(explode(';',$f3->read('setup.sql')));
//			// Make default setup inaccessible
//			rename('setup.sql','setup.$ql');
//		}
		// Use database-managed sessions
		new DB\SQL\Session($db);
		// Save frequently used variables

		$this->db = $db;
	}

}
