<?php
/**
 * Plugin Name: AJAX Flickr Gallery
 * Plugin URI: http://passer-byb.com
 * Version: 0.1
 * Author: passer-byb
 * Author URI: http://passer-byb.com
 * License: GPL2
 */

/*  Copyright 2014  passer-byb  (email : gaopeng.pb@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define("FLICKR_API_KEY", "aab6c3ba65fdee40cc630b7f5edba7fd");
define("FLICKR_SECRET", "a2d44b3feafd8530");
define("FLICKR_USERID", "100299196@N07");

include(plugin_dir_path(__FILE__) . "third_party/phpFlickr-3.1/phpFlickr.php");

class PhpFlickrCreator {
	private static $php_flickr = NULL;
	public static function get_php_flickr() {
		if (self::$php_flickr === NULL) {
			self::$php_flickr = new phpFlickr(FLICKR_API_KEY, FLICKR_SECRET);
		//	self::$php_flickr -> enableCache('fs', plugin_dir_path(__FILE__) .
	//			"cache", 3600);
		}
		return self::$php_flickr;
	}
}

class AjaxFlickrGallery {
	public $input = "abc";

	private $_the_set = NULL;

	public function plugin_init() {
		wp_enqueue_style("afg-style", plugin_dir_url(__FILE__) . "css/style.css", true, "0.1");
		wp_enqueue_script("afg-script", plugin_dir_url(__FILE__) . "js/main.js", array('jquery'), "0.1");
		wp_localize_script('afg-script', 'afg_ajax_url', admin_url('admin-ajax.php'));
		wp_localize_script('afg-script', 'afg_img_base', plugin_dir_url(__FILE__));
	}

	public function get_photo() {
		$ret = array('errno' => 500, 'data' => 'Null');
		$id = $_POST['id'];
		if (!$id)
			die();

		$pf = PhpFlickrCreator::get_php_flickr();
		$err = $pf -> photos_getInfo($id);
		if ($err === false) {
			$ret['errno'] = 1;
			$ret['data'] = $this -> _err_msg($pf);
			echo json_encode($ret);
			die();
		}
		
		$ret['data'] = array();
		$ret['data']['meta'] = $err;

		$err = $pf -> photos_getExif($id);
		if ($err === false) {
			$ret['errno'] = 1;
			$ret['data'] = $this -> _err_msg($fp);
			echo json_encode($ret);
			die();
		}
		$ret['errno'] = 0;
		$ret['data']['exif'] = $err;
		echo json_encode($ret);
		die();
	}
	
	public function process_the_title($title) {
		$set_id = get_query_var('sid');
		if ($set_id == "")
			return $title;
		$pf = PhpFlickrCreator::get_php_flickr();
		if ($this -> _the_set === NULL) {
			$err = $pf -> photosets_getPhotos($set_id, 'url_z,o_dims,data_taken');
			$this -> _the_set = $err;
		}
		if ($title == 'Photo Set')
			$title = $this -> _the_set['photoset']['title'];
		return $title;
	}
	public function process_wp_title($title, $sep) {
		$set_id = get_query_var('sid');
		if ($set_id == "")
			return $title;
		$pf = PhpFlickrCreator::get_php_flickr();
		if ($this -> _the_set === NULL) {
			$err = $pf -> photosets_getPhotos($set_id, 'url_z,o_dims,data_taken');
			$this -> _the_set = $err;
		}
		$title = $this -> _the_set['photoset']['title'] . ' ' . $sep . ' ' . $title;
		return $title;
	}

	public function list_photos($attr) {
		return $this -> _list_photos_process($attr);
	}

	private function _list_photos_process($attr = NULL) {
		$set_id = get_query_var('sid');
		if ($set_id == "")
			return $this -> _err_msg(NULL, "no valid set id provided!");
		$pf = PhpFlickrCreator::get_php_flickr();
		if ($this -> _the_set === NULL)
			$err = $pf -> photosets_getPhotos($set_id, 'url_z,o_dims,data_taken');
		else
			$err = $this -> _the_set;
		if ($err === false) {
			return $this -> _err_msg($pf);
		}
		$ret = $this -> _render_photo_set($err['photoset']);
		return $ret;
	}

	public function list_sets($attr) {
		return $this -> _list_sets_process($attr);
	}
	private function _list_sets_process($attr = NULL) {
		$pf = PhpFlickrCreator::get_php_flickr();
		$err = $pf -> photosets_getList(FLICKR_USERID);
		if ($err === false) {
			return $this -> _err_msg($pf);
		}
		$res = $err;
		$sets = array();
		foreach($res['photoset'] as $set) {
			$err = $pf -> photosets_getPhotos($set['id'], 'url_z,o_dims,date_taken', NULL, 2, 0);
			if ($err === false) 
				return $this -> _err_msg($pf);
			$set['first_two'] = $err;
			// need change to conf
			$tmp = "http://localhost:8080/photo-set/";
			$set['photo_set_url'] = add_query_arg('sid', $set['id'], $tmp);
			$sets[] = $set;
		}
		$ret = $this -> _render_set_list($sets);
		return $ret;
	}
	private function _err_msg($pf, $msg = NULL) {
		if ($msg === NULL) {
			$ret = "<b>Flickr API error: (";
			$ret .= $pf -> getErrorCode();
			$ret .= ") ";
			$ret .= $pf-> getErrorMsg();
			$ret .= "</b>";
		} else {
			$ret = "<b>Error: $msg</b>";
		}
		return $ret;
	}

	private function _get_user_info() {
		$pf = PhpFlickrCreator::get_php_flickr();
		$err = $pf -> people_getInfo(FLICKR_USERID);
		if ($err === false) {
			return false;
		}

		return $err;
	}

	private function _get_set_primary_url($set) {
		return $this->_get_photo_url($set['farm'], $set['server'], $set['primary'],
			$set['secret'], 'z');
	}

	private function _get_photo_big_url($photo) {
		return $this->_get_photo_url($photo['farm'], $photo['server'],
			$photo['id'], $photo['secret'], 'b');
	}

	private function _get_photo_url($farm_id, $server_id, $id, $secret, $size) {
		return "http://farm{$farm_id}.staticflickr.com/{$server_id}/{$id}"
			. "_{$secret}_$size.jpg";
	}

	private function _render_photo_set($photos) {
		$this->input = $photos;
		return $this->_render("photo_set");
	}

	private function _render_set_list($sets) {
		$this->input = $sets;
		return $this->_render("set_list");
	}

	private function _render($name) {
		if (!is_string($name)) {
			return "<b>Internal Error</b>";
		}
		if (!file_exists(plugin_dir_path(__FILE__) . $name . ".php")) {
			return "<b>Internal Error</b>";
		}
		ob_start();
		include(plugin_dir_path(__FILE__) . $name . ".php");
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}

}
$afg = new AjaxFlickrGallery();
add_shortcode("afg_list_sets", array($afg, "list_sets"));
add_shortcode("afg_list_photos", array($afg, "list_photos"));

add_action('wp_enqueue_scripts', array($afg, "plugin_init"));

function add_query_vars_filter($vars){
	  $vars[] = "sid";
	    return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );
add_filter( 'wp_title', array($afg, "process_wp_title"), 10, 2 );
add_filter( 'the_title', array($afg, "process_the_title"), 10, 1 );

add_action('wp_ajax_afg_get_photo', array($afg, 'get_photo'));
add_action('wp_ajax_nopriv_afg_get_photo', array($afg, 'get_photo'));
