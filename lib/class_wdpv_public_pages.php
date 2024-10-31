<?php
/**
 * Handles public functionality.
 */
class Wdpv_PublicPages {
	var $model;
	var $data;
	var $codec;

	function __construct () {
		$this->model = new Wdpv_Model;
		$this->data = new Wdpv_Options;
		$this->codec = new Wdpv_Codec;
	}
	function Wdpv_PublicPages () { $this->__construct(); }

	/**
	 * Main entry point.
	 *
	 * @static
	 */
	function serve () {
		$me = new Wdpv_PublicPages;
		$me->add_hooks();
	}

	function js_set_up_globals () {
		printf(
			'<script type="text/javascript">var _wdpv_root_url="%s"; var _wdpv_ajax_url="%s";</script>',
			WDPV_PLUGIN_URL, admin_url('admin-ajax.php')
		);
	}

	function js_load_scripts () {
		wp_enqueue_script('jquery');
		wp_enqueue_script('wdpv_voting', WDPV_PLUGIN_URL . '/js/wdpv_voting.js');
	}
	function css_load_styles () {
		wp_enqueue_style('wdpv_voting_style', WDPV_PLUGIN_URL . '/css/wdpv_voting.css');
	}

	function inject_voting_buttons ($body) {
		if (
			(is_home() && !$this->data->get_option('front_page_voting'))
			||
			(!is_home() && !is_singular())
		) return $body;
		$position = $this->data->get_option('voting_position');
		if ('top' == $position || 'both' == $position) {
			$body = $this->codec->get_code('vote_widget') . ' ' . $body;
		}
		if ('bottom' == $position || 'both' == $position) {
			$body .= " " . $this->codec->get_code('vote_widget');
		}
		return $body;
	}

	function add_hooks () {

		add_action('wp_head', array($this, 'js_set_up_globals'));
		add_action('wp_print_scripts', array($this, 'js_load_scripts'));
		add_action('wp_print_styles', array($this, 'css_load_styles'));

		// Automatic Voting buttons
		if ('manual' != $this->data->get_option('voting_position')) {
			add_filter('the_content', array($this, 'inject_voting_buttons'), 1); // Do this VERY early in content processing
		}

		$this->codec->register();
	}
}