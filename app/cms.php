<?php


//! Front-end processor
class CMS extends Controller {
	
	//! Display content page
	function show($f3,$args) {
		$menuQuery = 'SELECT `wpt`.*, `wt`.* FROM `wp_term_taxonomy` `wpt`, `wp_terms` `wt` where wt.term_id = wpt.term_id and `wpt`.taxonomy = \'category\'';

		$db=$this->db;
//		$ediy = EdiApi::instance();
		$posts=new DB\SQL\Mapper($db,'wp_posts');

		$main_posts=$posts->find(array('post_status=? and post_type=?','publish', 'post'),
			array(
				'order'=>'ID DESC',
				'offset'=>0,
				'limit'=>1
			));



//		$terms = new DB\SQL\Mapper($db, 'wp_terms');
//		$terms->load();
//		$terms->copyto('menu');
		$slug=empty($args['slug'])?'':$args['slug'];
		//$page->load(array('slug=?',$slug));
		$posts->load(array('post_status=? and post_type=?','publish', 'post'),
			array(
				'order'=>'ID DESC',
				'offset'=>0,
				'limit'=>1
			));
		//$main_posts->load(array('post_status=?','publish', 'ORDER BY ID desc'));
		$f3->set('menu',$db->exec($menuQuery));

		if ($posts->dry()) {
			$f3->error(404);
			die;
		}
		else {
			$posts->copyto('page');
			$f3->set('mainposts', $main_posts);
			$f3->set('comments','');
			$f3->set('inc','page.htm');
		}
	}

	function post($f3, $args) {
//		$logger = new Log('test.log');
		$db=$this->db;
		$post_id =$args['post'];
		$menuQuery = 'SELECT `wpt`.*, `wt`.* FROM `wp_term_taxonomy` `wpt`, `wp_terms` `wt` where wt.term_id = wpt.term_id and `wpt`.taxonomy = \'category\'';
		$f3->set('menu',$db->exec($menuQuery));
		$f3->set('comments','');
		$posts=new DB\SQL\Mapper($db,'wp_posts');
		$main_posts=$posts->find(array('post_status=? and post_type=?','publish', 'post'),
			array(
				'order'=>'ID DESC',
				'offset'=>0,
				'limit'=>1
			));
		$f3->set('mainposts', $main_posts);
//		$logger->write('ooops');
		$f3->set('page', $db->exec('select * from wp_posts where ID=?', $post_id)[0]);
		$f3->set('inc','post.htm');

	}

	//! Process comment form
	function comment($f3) {
		$slug=($f3->get('POST.slug')?:'');
		if (!$f3->exists('POST.name') || !strlen($f3->get('POST.name')))
			$f3->set('message','Name is required');
		elseif (!$f3->exists('POST.email') ||
			!strlen($email=$f3->get('POST.email')) ||
			!Audit::instance()->email($email))
			$f3->set('message','Invalid e-mail address');
		elseif (!$f3->exists('POST.contents') ||
			!strlen($f3->get('POST.contents')))
			$f3->set('message','Comment cannot be blank');
		else {
			$db=$this->db;
			$comment=new DB\SQL\Mapper($db,'comments');
			$comment->copyfrom('POST');
			$img=new Image;
			$comment->set('identicon',
				$f3->base64($img->identicon($f3->get('POST.email'),48)->
				dump(),'image/png'));
			$comment->set('slug',$slug);
			$comment->set('posted',time());
			$comment->save();
			$f3->reroute('/'.$slug);
		}
		$args=array('slug'=>$slug);
		$this->show($f3,$args);
	}

	//! Custom error page
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
				$log->write($addr.' '.$line);
			}
		$f3->set('inc','error.htm');
	}

	//! Display login form
	function login($f3) {
		$f3->clear('SESSION');
		if ($f3->get('eurocookie')) {
			$loc=Web\Geo::instance()->location();
			if (isset($loc['continent_code']) && $loc['continent_code']=='EU')
				$f3->set('message',
					'The administrator pages of this Web site uses cookies '.
					'for identification and security. Without these '.
					'cookies, these pages would simply be inaccessible. By '.
					'using these pages you agree to this safety measure.');
		}
		$f3->set('COOKIE.sent',TRUE);
		if ($f3->get('message')) {
			$img=new Image;
			$f3->set('captcha',$f3->base64(
				$img->captcha('fonts/thunder.ttf',18,5,'SESSION.captcha')->
					dump(),'image/png'));
		}
		$f3->set('inc','login.htm');
	}

	//! Process login form
	function auth($f3) {
		if (!$f3->get('COOKIE.sent'))
			$f3->set('message','Cookies must be enabled to enter this area');
		else {
			$crypt=$f3->get('password');
			$captcha=$f3->get('SESSION.captcha');
			if ($captcha && strtoupper($f3->get('POST.captcha'))!=$captcha)
				$f3->set('message','Invalid CAPTCHA code');
			elseif ($f3->get('POST.user_id')!=$f3->get('user_id') ||
				crypt($f3->get('POST.password'),$crypt)!=$crypt)
				$f3->set('message','Invalid user ID or password');
			else {
				$f3->clear('COOKIE.sent');
				$f3->clear('SESSION.captcha');
				$f3->set('SESSION.user_id',$f3->get('POST.user_id'));
				$f3->set('SESSION.crypt',$crypt);
				$f3->set('SESSION.lastseen',time());
				$f3->reroute('/admin/pages');
			}
		}
		$this->login($f3);
	}

	//! Terminate session
	function logout($f3) {
		$f3->clear('SESSION');
		$f3->reroute('/login');
	}

}
