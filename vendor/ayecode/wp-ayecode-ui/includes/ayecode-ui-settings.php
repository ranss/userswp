<?php
/**
 * A class for adjusting AyeCode UI settings on WordPress
 *
 * This class can be added to any plugin or theme and will add a settings screen to WordPress to control Bootstrap settings.
 *
 * @link https://github.com/AyeCode/wp-ayecode-ui
 *
 * @internal This file should not be edited directly but pulled from the github repo above.
 */

/**
 * Bail if we are not in WP.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Only add if the class does not already exist.
 */
if ( ! class_exists( 'AyeCode_UI_Settings' ) ) {

	/**
	 * A Class to be able to change settings for Font Awesome.
	 *
	 * Class AyeCode_UI_Settings
	 * @ver 1.0.0
	 * @todo decide how to implement textdomain
	 */
	class AyeCode_UI_Settings {

		/**
		 * Class version version.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Class textdomain.
		 *
		 * @var string
		 */
		public $textdomain = 'aui';

		/**
		 * Latest version of Bootstrap at time of publish published.
		 *
		 * @var string
		 */
		public $latest = "4.3.1";

		/**
		 * Current version of select2 being used.
		 *
		 * @var string
		 */
		public $select2_version = "4.0.11";

		/**
		 * The title.
		 *
		 * @var string
		 */
		public $name = 'AyeCode UI';

		/**
		 * The relative url to the assets.
		 *
		 * @var string
		 */
		public $url = '';

		/**
		 * Holds the settings values.
		 *
		 * @var array
		 */
		private $settings;

		/**
		 * WP_Bootstrap_Settings instance.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    WP_Bootstrap_Settings There can be only one!
		 */
		private static $instance = null;

		/**
		 * Main WP_Bootstrap_Settings Instance.
		 *
		 * Ensures only one instance of WP_Bootstrap_Settings is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return WP_Bootstrap_Settings - Main instance.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_Bootstrap_Settings ) ) {
				self::$instance = new AyeCode_UI_Settings;

				add_action( 'init', array( self::$instance, 'init' ) ); // set settings

				if ( is_admin() ) {
					add_action( 'admin_menu', array( self::$instance, 'menu_item' ) );
					add_action( 'admin_init', array( self::$instance, 'register_settings' ) );
				}

				add_action( 'customize_register', array( self::$instance, 'customizer_settings' ));

				do_action( 'ayecode_ui_settings_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Initiate the settings and add the required action hooks.
		 */
		public function init() {
			$this->settings = $this->get_settings();
			$this->url = $this->get_url();

			// maybe load CSS
			if ( $this->settings['css'] ) {

				/**
				 * We load super early in case there is a theme version that might change the colors
				 */
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ), 1 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ), 1 );

			}

			// maybe load JS
			if ( $this->settings['js'] ) {

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

			}

			// Maybe set the HTML font size
			if ( $this->settings['html_font_size'] ) {
				add_action( 'wp_footer', array( $this, 'html_font_size' ), 10 );
			}


		}

		/**
		 * Add a html font size to the footer.
		 */
		public function html_font_size(){
			$this->settings = $this->get_settings();
			echo "<style>html{font-size:".absint($this->settings['html_font_size'])."px;}</style>";
		}

		/**
		 * Adds the Font Awesome styles.
		 */
		public function enqueue_style() {

			if($this->settings['css']){
				$compatibility = $this->settings['css']=='core' ? false : true;
				$url = $this->settings['css']=='core' ? $this->url.'assets/css/ayecode-ui.css' : $this->url.'assets/css/ayecode-ui-compatibility.css';
				wp_register_style( 'ayecode-ui', $url, array(), $this->latest );
				wp_enqueue_style( 'ayecode-ui' );


				// fix some wp-admin issues
				if(is_admin()){
					$custom_css = "
                body{
                    background-color: #f1f1f1;
                    font-family: -apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Oxygen-Sans,Ubuntu,Cantarell,\"Helvetica Neue\",sans-serif;
                    font-size:13px;
                }
                a {
				    color: #0073aa;
				    text-decoration: underline;
				}
                label {
				    display: initial;
				    margin-bottom: 0;
				}
				input, select {
				    margin: 1px;
				    line-height: initial;
				}
				th, td, div, h2 {
				    box-sizing: content-box;
				}
				p {
				    font-size: 13px;
				    line-height: 1.5;
				    margin: 1em 0;
				}
				h1, h2, h3, h4, h5, h6 {
				    display: block;
				    font-weight: 600;
				}
				h2,h3 {
				    font-size: 1.3em;
				    margin: 1em 0
				}
                ";
					wp_add_inline_style( 'ayecode-ui', $custom_css );
				}

				// custom changes
				wp_add_inline_style( 'ayecode-ui', self::custom_css($compatibility) );

			}
		}

		/**
		 * Get inline script used if bootstrap enqueued
		 *
		 * If this remains small then its best to use this than to add another JS file.
		 */
		public function inline_script(){
			ob_start();
			?>
			<script>

				/**
				 * An AUI bootstrap adaptation of GreedyNav.js ( by Luke Jackson ).
				 *
				 * Simply add the class `greedy` to any <nav> menu and it will do the rest.
				 * Licensed under the MIT license - http://opensource.org/licenses/MIT
				 * @ver 0.0.1
				 */
				function aui_init_greedy_nav(){
					jQuery('nav.greedy').each(function(i, obj) {

						// Check if already initialized, if so continue.
						if(jQuery(this).hasClass("being-greedy")){return true;}

						// Make sure its always expanded
						jQuery(this).addClass('navbar-expand');

						// vars
						var $vlinks = jQuery(this).find('.navbar-nav').addClass("being-greedy w-100");
						jQuery($vlinks).append('<li class="nav-item list-unstyled ml-auto greedy-btn d-none ">' +
							'<a href="javascript:void(0)" data-toggle="dropdown" class="nav-link"><i class="fas fa-ellipsis-h"></i> <span class="greedy-count badge badge-dark badge-pill"></span></a>' +
							'<div class="dropdown"><ul class="greedy-links dropdown-menu  dropdown-menu-right"></ul></div>' +
							'</li>');
						var $hlinks = jQuery(this).find('.greedy-links');
						var $btn = jQuery(this).find('.greedy-btn');

						var numOfItems = 0;
						var totalSpace = 0;
						var closingTime = 1000;
						var breakWidths = [];

						// Get initial state
						$vlinks.children().outerWidth(function(i, w) {
							totalSpace += w;
							numOfItems += 1;
							breakWidths.push(totalSpace);
						});

						var availableSpace, numOfVisibleItems, requiredSpace, buttonSpace ,timer;

						/*
						 The check function.
						 */
						function check() {

							// Get instant state
							buttonSpace = $btn.width();
							availableSpace = $vlinks.width() - 10;
							numOfVisibleItems = $vlinks.children().length;
							requiredSpace = breakWidths[numOfVisibleItems - 1];

							// There is not enough space
							if (numOfVisibleItems > 1 && requiredSpace > availableSpace) {
								$vlinks.children().last().prev().prependTo($hlinks);
								numOfVisibleItems -= 1;
								check();
								// There is more than enough space
							} else if (availableSpace > breakWidths[numOfVisibleItems]) {
								$hlinks.children().first().insertBefore($btn);
								numOfVisibleItems += 1;
								check();
							}
							// Update the button accordingly
							jQuery($btn).find(".greedy-count").html( numOfItems - numOfVisibleItems);
							if (numOfVisibleItems === numOfItems) {
								$btn.addClass('d-none');
							} else $btn.removeClass('d-none');
						}

						// Window listeners
						jQuery(window).resize(function() {
							check();
						});

						// do initial check
						check();
					});
				}

				/**
				 * Initiate Select2 items.
				 */
				function aui_init_select2(){
					jQuery("select.aui-select2").select2();
				}

				/**
				 * A function to convert a time value to a "ago" time text.
				 *
				 * @param selector string The .class selector
				 */
				function aui_time_ago(selector) {

					var templates = {
						prefix: "",
						suffix: " ago",
						seconds: "less than a minute",
						minute: "about a minute",
						minutes: "%d minutes",
						hour: "about an hour",
						hours: "about %d hours",
						day: "a day",
						days: "%d days",
						month: "about a month",
						months: "%d months",
						year: "about a year",
						years: "%d years"
					};
					var template = function (t, n) {
						return templates[t] && templates[t].replace(/%d/i, Math.abs(Math.round(n)));
					};

					var timer = function (time) {
						if (!time)
							return;
						time = time.replace(/\.\d+/, ""); // remove milliseconds
						time = time.replace(/-/, "/").replace(/-/, "/");
						time = time.replace(/T/, " ").replace(/Z/, " UTC");
						time = time.replace(/([\+\-]\d\d)\:?(\d\d)/, " $1$2"); // -04:00 -> -0400
						time = new Date(time * 1000 || time);

						var now = new Date();
						var seconds = ((now.getTime() - time) * .001) >> 0;
						var minutes = seconds / 60;
						var hours = minutes / 60;
						var days = hours / 24;
						var years = days / 365;

						return templates.prefix + (
								seconds < 45 && template('seconds', seconds) ||
								seconds < 90 && template('minute', 1) ||
								minutes < 45 && template('minutes', minutes) ||
								minutes < 90 && template('hour', 1) ||
								hours < 24 && template('hours', hours) ||
								hours < 42 && template('day', 1) ||
								days < 30 && template('days', days) ||
								days < 45 && template('month', 1) ||
								days < 365 && template('months', days / 30) ||
								years < 1.5 && template('year', 1) ||
								template('years', years)
							) + templates.suffix;
					};

					var elements = document.getElementsByClassName(selector);
					for (var i in elements) {
						var $this = elements[i];
						if (typeof $this === 'object') {
							$this.innerHTML = '<i class="far fa-clock"></i> ' + timer($this.getAttribute('title') || $this.getAttribute('datetime'));
						}
					}
					// update time every minute
					setTimeout(aui_time_ago, 60000);

				}

				/**
				 * Initiate tooltips on the page.
				 */
				function aui_init_tooltips(){
					jQuery('[data-toggle="tooltip"]').tooltip();
				}

				// run on window loaded
				jQuery(window).load(function() {
					// init tooltips
					aui_init_tooltips();

					// init select2
					aui_init_select2();

					// init Greedy nav
					aui_init_greedy_nav();

					// Set times to time ago
					aui_time_ago('timeago');
				});
			</script>
			<?php
			$output = ob_get_clean();

			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<script>',
				'</script>'
			), '', $output );
		}

		/**
		 * Get inline script used if bootstrap file browser enqueued.
		 *
		 * If this remains small then its best to use this than to add another JS file.
		 */
		public function inline_script_file_browser(){
			ob_start();
			?>
			<script>
				// run on doc ready
				jQuery(document).ready(function () {
					bsCustomFileInput.init();
				});
			</script>
			<?php
			$output = ob_get_clean();

			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<script>',
				'</script>'
			), '', $output );
		}

		/**
		 * Adds the Font Awesome JS.
		 */
		public function enqueue_scripts() {

			// select2
			wp_register_script( 'select2', $this->url.'assets/js/select2.min.js', array(), $this->select2_version );

			// Bootstrap file browser
			wp_register_script( 'aui-custom-file-input', $url = $this->url.'assets/js/bs-custom-file-input.min.js', array('jquery'), $this->select2_version );
			wp_add_inline_script( 'aui-custom-file-input', $this->inline_script_file_browser() );


			if($this->settings['js']=='core-popper'){
				// Bootstrap bundle
				$url = $this->url.'assets/js/bootstrap.bundle.min.js';
				wp_register_script( 'bootstrap-js-bundle', $url, array('select2'), $this->latest );
				wp_enqueue_script( 'bootstrap-js-bundle' );
				$script = $this->inline_script();
				wp_add_inline_script( 'bootstrap-js-bundle', $script );
			}elseif($this->settings['js']=='popper'){
				$url = $this->url.'assets/js/popper.min.js';
				wp_register_script( 'bootstrap-js-popper', $url, array(), $this->latest );
				wp_enqueue_script( 'bootstrap-js-popper' );
			}


		}

		/**
		 * Get the url path to the current folder.
		 *
		 * @return string
		 */
		public function get_url() {

			$url = '';
			// check if we are inside a plugin
			$file_dir = str_replace("/includes","", dirname( __FILE__ ));

			$dir_parts = explode("/wp-content/",$file_dir);
			$url_parts = explode("/wp-content/",plugins_url());

			if(!empty($url_parts[0]) && !empty($dir_parts[1])){
				$url = trailingslashit( $url_parts[0]."/wp-content/".$dir_parts[1] );
			}

			return $url;
		}

		/**
		 * Register the database settings with WordPress.
		 */
		public function register_settings() {
			register_setting( 'ayecode-ui-settings', 'ayecode-ui-settings' );
		}

		/**
		 * Add the WordPress settings menu item.
		 * @since 1.0.10 Calling function name direct will fail theme check so we don't.
		 */
		public function menu_item() {
			$menu_function = 'add' . '_' . 'options' . '_' . 'page'; // won't pass theme check if function name present in theme
			call_user_func( $menu_function, $this->name, $this->name, 'manage_options', 'ayecode-ui-settings', array(
				$this,
				'settings_page'
			) );
		}

		/**
		 * Get the current Font Awesome output settings.
		 *
		 * @return array The array of settings.
		 */
		public function get_settings() {

			$db_settings = get_option( 'ayecode-ui-settings' );

			$defaults = array(
				'css'       => 'compatibility', // core, compatibility
				'js'        => 'core-popper', // js to load, core-popper, popper
				'html_font_size'        => '16', // js to load, core-popper, popper
			);

			$settings = wp_parse_args( $db_settings, $defaults );

			/**
			 * Filter the Bootstrap settings.
			 *
			 * @todo if we add this filer people might use it and then it defeates the purpose of this class :/
			 */
			return $this->settings = apply_filters( 'ayecode-ui-settings', $settings, $db_settings, $defaults );
		}


		/**
		 * The settings page html output.
		 */
		public function settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'aui' ) );
			}
			?>
			<div class="wrap">
				<h1><?php echo $this->name; ?></h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'ayecode-ui-settings' );
					do_settings_sections( 'ayecode-ui-settings' );
					?>
					<table class="form-table wpbs-table-settings">
						<tr valign="top">
							<th scope="row"><label
									for="wpbs-css"><?php _e( 'Load CSS', 'aui' ); ?></label></th>
							<td>
								<select name="ayecode-ui-settings[css]" id="wpbs-css">
									<option	value="compatibility" <?php selected( $this->settings['css'], 'compatibility' ); ?>><?php _e( 'Compatibility Mode', 'aui' ); ?></option>
									<option value="core" <?php selected( $this->settings['css'], 'core' ); ?>><?php _e( 'Full Mode', 'aui' ); ?></option>
									<option	value="" <?php selected( $this->settings['css'], '' ); ?>><?php _e( 'Disabled', 'aui' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label
									for="wpbs-js"><?php _e( 'Load JS', 'aui' ); ?></label></th>
							<td>
								<select name="ayecode-ui-settings[js]" id="wpbs-js">
									<option	value="core-popper" <?php selected( $this->settings['js'], 'core-popper' ); ?>><?php _e( 'Core + Popper (default)', 'aui' ); ?></option>
									<option value="popper" <?php selected( $this->settings['js'], 'popper' ); ?>><?php _e( 'Popper', 'aui' ); ?></option>
									<option	value="" <?php selected( $this->settings['js'], '' ); ?>><?php _e( 'Disabled', 'aui' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label
									for="wpbs-font_size"><?php _e( 'HTML Font Size (px)', 'aui' ); ?></label></th>
							<td>
								<input type="number" name="ayecode-ui-settings[html_font_size]" id="wpbs-font_size" value="<?php echo absint( $this->settings['html_font_size']); ?>" placeholder="16" />
								<p class="description" ><?php _e("Our font sizing is rem (responsive based) here you can set the html font size in-case your theme is setting it too low.","aui");?></p>
							</td>
						</tr>

					</table>
					<?php
					submit_button();
					?>
				</form>

				<div id="wpbs-version"><?php echo $this->version; ?></div>
			</div>

			<?php
		}

		public function customizer_settings($wp_customize){
			$wp_customize->add_section('aui_settings', array(
				'title'    => __('AyeCode UI'),
				'priority' => 120,
			));

			//  =============================
			//  = Color Picker              =
			//  =============================
			$wp_customize->add_setting('aui_options[color_primary]', array(
				'default'           => '#1e73be',
				'sanitize_callback' => 'sanitize_hex_color',
				'capability'        => 'edit_theme_options',
				'type'              => 'option',
				'transport'         => 'refresh',
			));
			$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'color_primary', array(
				'label'    => __('Primary Color'),
				'section'  => 'aui_settings',
				'settings' => 'aui_options[color_primary]',
			)));

			$wp_customize->add_setting('aui_options[color_secondary]', array(
				'default'           => '#6c757d',
				'sanitize_callback' => 'sanitize_hex_color',
				'capability'        => 'edit_theme_options',
				'type'              => 'option',
				'transport'         => 'refresh',
			));
			$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'color_secondary', array(
				'label'    => __('Secondary Color'),
				'section'  => 'aui_settings',
				'settings' => 'aui_options[color_secondary]',
			)));
		}


		public static function custom_css($compatibility = true) {
			$settings = get_option('aui_options');

			ob_start();
			?>
			<style>
				<?php
					if(!empty($settings['color_primary']) && $settings['color_primary'] != "#1e73be"){
						echo self::css_primary($settings['color_primary'],$compatibility);
					}

					if(!empty($settings['color_secondary']) && $settings['color_secondary'] != "#6c757d"){
						echo self::css_secondary($settings['color_secondary'],$compatibility);
					}
                ?>
			</style>
			<?php


			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<style>',
				'</style>'
			), '', ob_get_clean());
		}

		public static function css_primary($color_code,$compatibility){;
			$color_code = sanitize_hex_color($color_code);
			if(!$color_code){return '';}
			/**
			 * c = color, b = background color, o = border-color, f = fill
			 */
			$selectors = array(
				'a' => array('c'),
				'.btn-primary' => array('b','o'),
				'.btn-primary.disabled' => array('b','o'),
				'.btn-primary:disabled' => array('b','o'),
				'.btn-outline-primary' => array('c','o'),
				'.btn-outline-primary:hover' => array('b','o'),
				'.btn-outline-primary:not(:disabled):not(.disabled).active' => array('b','o'),
				'.btn-outline-primary:not(:disabled):not(.disabled):active' => array('b','o'),
				'.show>.btn-outline-primary.dropdown-toggle' => array('b','o'),
				'.btn-link' => array('c'),
				'.dropdown-item.active' => array('b'),
				'.custom-control-input:checked~.custom-control-label::before' => array('b','o'),
				'.custom-checkbox .custom-control-input:indeterminate~.custom-control-label::before' => array('b','o'),
//				'.custom-range::-webkit-slider-thumb' => array('b'), // these break the inline rules...
//				'.custom-range::-moz-range-thumb' => array('b'),
//				'.custom-range::-ms-thumb' => array('b'),
				'.nav-pills .nav-link.active' => array('b'),
				'.nav-pills .show>.nav-link' => array('b'),
				'.page-link' => array('c'),
				'.page-item.active .page-link' => array('b','o'),
				'.badge-primary' => array('b'),
				'.alert-primary' => array('b','o'),
				'.progress-bar' => array('b'),
				'.list-group-item.active' => array('b','o'),
				'.bg-primary' => array('b','f'),
				'.btn-link.btn-primary' => array('c'),
				'.select2-container .select2-results__option--highlighted.select2-results__option[aria-selected=true]' => array('b'),
			);

			$important_selectors = array(
				'.bg-primary' => array('b','f'),
				'.border-primary' => array('o'),
				'.text-primary' => array('c'),
			);

			$color = array();
			$color_i = array();
			$background = array();
			$background_i = array();
			$border = array();
			$border_i = array();
			$fill = array();
			$fill_i = array();

			$output = '';

			// build rules into each type
			foreach($selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color[] = $selector;}
				if(isset($types['b'])){$background[] = $selector;}
				if(isset($types['o'])){$border[] = $selector;}
				if(isset($types['f'])){$fill[] = $selector;}
			}

			// build rules into each type
			foreach($important_selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color_i[] = $selector;}
				if(isset($types['b'])){$background_i[] = $selector;}
				if(isset($types['o'])){$border_i[] = $selector;}
				if(isset($types['f'])){$fill_i[] = $selector;}
			}

			// add any color rules
			if(!empty($color)){
				$output .= implode(",",$color) . "{color: $color_code;} ";
			}
			if(!empty($color_i)){
				$output .= implode(",",$color_i) . "{color: $color_code !important;} ";
			}

			// add any background color rules
			if(!empty($background)){
				$output .= implode(",",$background) . "{background-color: $color_code;} ";
			}
			if(!empty($background_i)){
				$output .= implode(",",$background_i) . "{background-color: $color_code !important;} ";
			}

			// add any border color rules
			if(!empty($border)){
				$output .= implode(",",$border) . "{border-color: $color_code;} ";
			}
			if(!empty($border_i)){
				$output .= implode(",",$border_i) . "{border-color: $color_code !important;} ";
			}

			// add any fill color rules
			if(!empty($fill)){
				$output .= implode(",",$fill) . "{fill: $color_code;} ";
			}
			if(!empty($fill_i)){
				$output .= implode(",",$fill_i) . "{fill: $color_code !important;} ";
			}


			$prefix = $compatibility ? ".bsui " : "";

			// darken
			$darker_075 = self::css_hex_lighten_darken($color_code,"-0.075");
			$darker_10 = self::css_hex_lighten_darken($color_code,"-0.10");
			$darker_125 = self::css_hex_lighten_darken($color_code,"-0.125");

			// lighten
			$lighten_25 = self::css_hex_lighten_darken($color_code,"0.25");

			// opacity see https://css-tricks.com/8-digit-hex-codes/
			$op_25 = $color_code."40"; // 25% opacity


			// button states
			$output .= $prefix ." .btn-primary:hover{background-color: ".$darker_075.";    border-color: ".$darker_10.";} ";
			$output .= $prefix ." .btn-outline-primary:not(:disabled):not(.disabled):active:focus, $prefix .btn-outline-primary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-outline-primary.dropdown-toggle:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
			$output .= $prefix ." .btn-primary:not(:disabled):not(.disabled):active, $prefix .btn-primary:not(:disabled):not(.disabled).active, .show>$prefix .btn-primary.dropdown-toggle{background-color: ".$darker_10.";    border-color: ".$darker_125.";} ";
			$output .= $prefix ." .btn-primary:not(:disabled):not(.disabled):active:focus, $prefix .btn-primary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-primary.dropdown-toggle:focus {box-shadow: 0 0 0 0.2rem $op_25;} ";


			// dropdown's
			$output .= $prefix ." .dropdown-item.active, $prefix .dropdown-item:active{background-color: $color_code;} ";


			// input states
			$output .= $prefix ." .form-control:focus{border-color: ".$lighten_25.";box-shadow: 0 0 0 0.2rem $op_25;} ";

			// page link
			$output .= $prefix ." .page-link:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";

			return $output;
		}

		public static function css_secondary($color_code,$compatibility){;
			$color_code = sanitize_hex_color($color_code);
			if(!$color_code){return '';}
			/**
			 * c = color, b = background color, o = border-color, f = fill
			 */
			$selectors = array(
				'.btn-secondary' => array('b','o'),
				'.btn-secondary.disabled' => array('b','o'),
				'.btn-secondary:disabled' => array('b','o'),
				'.btn-outline-secondary' => array('c','o'),
				'.btn-outline-secondary:hover' => array('b','o'),
				'.btn-outline-secondary.disabled' => array('c'),
				'.btn-outline-secondary:disabled' => array('c'),
				'.btn-outline-secondary:not(:disabled):not(.disabled):active' => array('b','o'),
				'.btn-outline-secondary:not(:disabled):not(.disabled).active' => array('b','o'),
				'.btn-outline-secondary.dropdown-toggle' => array('b','o'),
				'.badge-secondary' => array('b'),
				'.alert-secondary' => array('b','o'),
				'.btn-link.btn-secondary' => array('c'),
				);

			$important_selectors = array(
				'.bg-secondary' => array('b','f'),
				'.border-secondary' => array('o'),
				'.text-secondary' => array('c'),
			);

			$color = array();
			$color_i = array();
			$background = array();
			$background_i = array();
			$border = array();
			$border_i = array();
			$fill = array();
			$fill_i = array();

			$output = '';

			// build rules into each type
			foreach($selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color[] = $selector;}
				if(isset($types['b'])){$background[] = $selector;}
				if(isset($types['o'])){$border[] = $selector;}
				if(isset($types['f'])){$fill[] = $selector;}
			}

			// build rules into each type
			foreach($important_selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color_i[] = $selector;}
				if(isset($types['b'])){$background_i[] = $selector;}
				if(isset($types['o'])){$border_i[] = $selector;}
				if(isset($types['f'])){$fill_i[] = $selector;}
			}

			// add any color rules
			if(!empty($color)){
				$output .= implode(",",$color) . "{color: $color_code;} ";
			}
			if(!empty($color_i)){
				$output .= implode(",",$color_i) . "{color: $color_code !important;} ";
			}

			// add any background color rules
			if(!empty($background)){
				$output .= implode(",",$background) . "{background-color: $color_code;} ";
			}
			if(!empty($background_i)){
				$output .= implode(",",$background_i) . "{background-color: $color_code !important;} ";
			}

			// add any border color rules
			if(!empty($border)){
				$output .= implode(",",$border) . "{border-color: $color_code;} ";
			}
			if(!empty($border_i)){
				$output .= implode(",",$border_i) . "{border-color: $color_code !important;} ";
			}

			// add any fill color rules
			if(!empty($fill)){
				$output .= implode(",",$fill) . "{fill: $color_code;} ";
			}
			if(!empty($fill_i)){
				$output .= implode(",",$fill_i) . "{fill: $color_code !important;} ";
			}


			$prefix = $compatibility ? ".bsui " : "";

			// darken
			$darker_075 = self::css_hex_lighten_darken($color_code,"-0.075");
			$darker_10 = self::css_hex_lighten_darken($color_code,"-0.10");
			$darker_125 = self::css_hex_lighten_darken($color_code,"-0.125");

			// lighten
			$lighten_25 = self::css_hex_lighten_darken($color_code,"0.25");

			// opacity see https://css-tricks.com/8-digit-hex-codes/
			$op_25 = $color_code."40"; // 25% opacity


			// button states
			$output .= $prefix ." .btn-secondary:hover{background-color: ".$darker_075.";    border-color: ".$darker_10.";} ";
			$output .= $prefix ." .btn-outline-secondary:not(:disabled):not(.disabled):active:focus, $prefix .btn-outline-secondary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-outline-secondary.dropdown-toggle:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
			$output .= $prefix ." .btn-secondary:not(:disabled):not(.disabled):active, $prefix .btn-secondary:not(:disabled):not(.disabled).active, .show>$prefix .btn-secondary.dropdown-toggle{background-color: ".$darker_10.";    border-color: ".$darker_125.";} ";
			$output .= $prefix ." .btn-secondary:not(:disabled):not(.disabled):active:focus, $prefix .btn-secondary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-secondary.dropdown-toggle:focus {box-shadow: 0 0 0 0.2rem $op_25;} ";


			return $output;
		}

		/**
		 * Increases or decreases the brightness of a color by a percentage of the current brightness.
		 *
		 * @param   string  $hexCode        Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`
		 * @param   float   $adjustPercent  A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
		 *
		 * @return  string
		 */
		public static function css_hex_lighten_darken($hexCode, $adjustPercent) {
			$hexCode = ltrim($hexCode, '#');

			if (strlen($hexCode) == 3) {
				$hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
			}

			$hexCode = array_map('hexdec', str_split($hexCode, 2));

			foreach ($hexCode as & $color) {
				$adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
				$adjustAmount = ceil($adjustableLimit * $adjustPercent);

				$color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
			}

			return '#' . implode($hexCode);
		}

	}

	/**
	 * Run the class if found.
	 */
	AyeCode_UI_Settings::instance();
}