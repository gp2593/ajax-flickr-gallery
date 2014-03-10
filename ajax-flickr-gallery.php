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

	public function plugin_init() {
		wp_enqueue_style("afg-style", plugin_dir_url(__FILE__) . "css/style.css", true, "0.1");
	}

	public function list_sets($attr) {
		$pf = PhpFlickrCreator::get_php_flickr();
		$err = $pf -> photosets_getList(FLICKR_USERID);
		if ($err === false) {
			$ret = "<b>Flickr API error: (";
			$ret .= $pf -> getErrorCode();
			$ret .= ") ";
			$ret .= $pf-> getErrorMsg();
			$ret .= "</b>";
			return $ret;
		}
		$ret = $this -> _render_set_list($err['photoset']);

		return $ret;
	}

	public function show_set($attr) {
		return " ";
	}

	private function _get_set_primary_url($set) {
		return $this->_get_photo_url($set['farm'], $set['server'], $set['primary'],
			$set['secret']);
	}

	private function _get_photo_url($farm_id, $server_id, $id, $secret) {
		return "http://farm{$farm_id}.staticflickr.com/{$server_id}/{$id}"
			. "_{$secret}_z.jpg";
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

add_action('wp_enqueue_scripts', array($afg, "plugin_init"));
