<?php
/**
 * Profile Template related functions
 *
 * This class defines all code necessary for Profile template.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Profile {

    /**
     * Prints the profile page header section.
     *
     * @since       1.1.2
     * @package     userswp
     */
    public function get_profile_body($user){
        if(!$user){
            $user = uwp_get_user_by_author_slug();
        }
        $profile_access = apply_filters('uwp_profile_access', true, $user);
        if ($profile_access) {
            echo do_shortcode("[uwp_profile_header]\n[uwp_profile_section position='left' type='open']\n[uwp_user_title]\n[uwp_profile_social]\n[uwp_output_location location='profile_side']\n[uwp_profile_actions]\n[uwp_profile_section position='left' type='close']\n[uwp_profile_section position='right' type='open']\n[uwp_profile_tabs]\n[uwp_profile_section position='right' type='close']");
        } else {
            do_action('uwp_profile_access_denied', $user);
        }
    }
    
    /**
     * Prints the profile page header section.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @param       bool      $hide_cover   Hide cover image.
     * @param       bool      $hide_avatar   Hide avatar image.
     * @param       bool      $allow_change   Allow to change cover and avatar image.
     * @deprecated 1.2.0 Use uwp_locate_template()
     */
    public function get_profile_header($user, $hide_cover = false, $hide_avatar = false, $allow_change = true) {
        if(!$user){
            return;
        }

        add_filter( 'upload_dir', 'uwp_handle_multisite_profile_image', 10, 1 );
        $uploads = wp_upload_dir();
        remove_filter( 'upload_dir', 'uwp_handle_multisite_profile_image' );
        $upload_url = $uploads['baseurl'];
        $class = "";

        if($hide_cover) {
            $class = "uwp-avatar-only";
        }
        
        ?>

        <div class="uwp-profile-header <?php echo $class; ?> clearfix">
            <?php if(!$hide_cover) {
                $banner = uwp_get_usermeta($user->ID, 'banner_thumb', '');
                if (empty($banner)) {
                    $banner = uwp_get_option('profile_default_banner', '');
                    if(empty($banner)){
                        $banner = uwp_get_default_banner_uri();
                    } else {
                        $banner = wp_get_attachment_url($banner);
                    }
                } else {
                    $banner = $upload_url.$banner;
                }
            ?>
            <div class="uwp-profile-header-img clearfix">
                <?php
                if (!is_uwp_profile_page()) {
                    echo '<a href="'.apply_filters('uwp_profile_link', get_author_posts_url($user->ID), $user->ID).'" title="'.$user->display_name.'">';
                }
                ?>
                <img src="<?php echo $banner; ?>" alt="" class="uwp-profile-header-img-src" data-recalc-dims="0" />
                <?php
                if (!is_uwp_profile_page()) {
                    echo '</a>';
                }
                ?>
            <?php if (is_user_logged_in() && is_uwp_profile_page() && $allow_change && (get_current_user_id() == $user->ID)) { ?>
                <div class="uwp-banner-change-icon">
                    <i class="fas fa-camera" aria-hidden="true"></i>
                    <div data-type="banner" class="uwp-profile-banner-change uwp-profile-modal-form-trigger">
                    <span class="uwp-profile-banner-change-inner">
                        <?php echo __( 'Update Cover Photo', 'userswp' ); ?>
                    </span>
                    </div>
                </div>
            <?php } ?>
            </div>
            <?php }

            // avatar of user
            ?>
            <div class="uwp-profile-avatar clearfix">
                <?php
                if(!$hide_avatar) {
                    if (!is_uwp_profile_page()) {
                        echo '<a href="' . apply_filters('uwp_profile_link', get_author_posts_url($user->ID), $user->ID) . '" title="' . $user->display_name . '">';
                    }
                    $avatar = uwp_get_usermeta($user->ID, 'avatar_thumb', '');
                    if (empty($avatar)) {
                        $avatar = get_avatar($user->user_email, 150);
                    } else {
                        // check the image is not a full url before adding the local upload url
                        if (strpos($avatar, 'http:') === false && strpos($avatar, 'https:') === false) {
                            $avatar = $upload_url . $avatar;
                        }
                        $avatar = '<img src="' . $avatar . '" class="avatar avatar-150 photo" width="150" height="150">';
                    }
                    ?>
                    <div class="uwp-profile-avatar-inner">
                        <?php echo $avatar; ?>
                        <?php if (is_user_logged_in() && (get_current_user_id() == $user->ID) && is_uwp_profile_page() && $allow_change) { ?>
                            <div class="uwp-profile-avatar-change">
                                <div class="uwp-profile-avatar-change-inner">
                                    <i class="fas fa-camera" aria-hidden="true"></i>
                                    <a id="uwp-profile-picture-change" data-type="avatar"
                                       class="uwp-profile-modal-form-trigger"
                                       href="#"><?php echo __('Update', 'userswp'); ?></a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                    if (!is_uwp_profile_page()) {
                        echo '</a>';
                    }
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Prints the profile page title section. 
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     */
    public function get_profile_title($user, $tag = 'h2',$title_class = '',$link_class = '', $link = true) {
        ?>
        <div class="uwp-profile-name">
            <<?php echo esc_attr($tag); ?> class="uwp-user-title <?php echo $title_class;?>" data-user="<?php echo $user->ID; ?>">
               <?php if($link){?><a href="<?php echo apply_filters('uwp_profile_link', get_author_posts_url($user->ID), $user->ID); ?>" class="<?php echo $link_class;?>"><?php }?>
                    <?php echo apply_filters('uwp_profile_display_name', $user->display_name); ?>
	           <?php if($link){?></a><?php }?>
	    <?php do_action('uwp_profile_after_title', $user->ID ); ?>
	    </<?php echo esc_attr($tag); ?>>
        </div>
        <?php
    }

	/**
	 * Displays edit account button
	 */
	public function edit_profile_button(){
		global $uwp_in_user_loop;
		$account_page          = uwp_get_page_id( 'account_page', false );
		$user = uwp_get_displayed_user();
		$can_user_edit_account = apply_filters( 'uwp_user_can_edit_own_profile', true, $user->ID );

		if (!$uwp_in_user_loop && $account_page && is_user_logged_in() && ( get_current_user_id() == $user->ID ) && $can_user_edit_account ) { ?>
			<a href="<?php echo get_permalink( $account_page ); ?>"
			   class="btn btn-sm btn-outline-dark btn-circle mt-n1" data-toggle="tooltip"
			   title="<?php esc_attr_e( 'Edit Account', 'userswp' ); ?>"><i
					class="fas fa-pencil-alt fa-fw fa-lg"></i></a>
		<?php }
	}

    /**
     * Prints the profile page social links section.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @param       string, array  $exclude     Fields to exclude from displaying.
     */
    public function get_profile_social($user, $exclude = '') {

        if (is_uwp_profile_page()) {
            $show_type = '[profile_side]';
        } elseif (is_uwp_users_page()) {
            $show_type = '[users]';
        } else {
            $show_type = false;
        }

        if (!$show_type) {
            return;
        }

        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE ( form_type = 'register' OR form_type = 'account' ) AND field_type = 'url' AND css_class LIKE '%uwp_social%' ORDER BY sort_order ASC");

        $usermeta = isset( $user->ID ) ? uwp_get_usermeta_row( $user->ID ) : array();
        $privacy = ! empty( $usermeta ) && ! empty( $usermeta->user_privacy ) ? explode( ',', $usermeta->user_privacy ) : array();

        if(!empty($exclude) && !is_array($exclude)){
            $exclude = explode(',', $exclude);
            $exclude = array_map('trim', $exclude);
        }
        ?>
        <div class="uwp-profile-social">
            <ul class="uwp-profile-social-ul">
                <?php
                foreach($fields as $field) {
                    $show_in = explode(',',$field->show_in);

                    if (!in_array($show_type, $show_in)) {
                        continue;
                    }

                    $display = false;
                    if ( $field->is_public == '0' ) {
                        $display = false;
                    } else if ( $field->is_public == '1' ) {
                        $display = true;
                    } else {
                        if ( ! in_array( $field->htmlvar_name . '_privacy', $privacy ) ) {
                            $display = true;
                        }
                    }
                    if ( ! $display ) {
                        continue;
                    }

                    $key = $field->htmlvar_name;
                    // see UsersWP_Forms -> save_user_extra_fields reason for replacing key
                    $key = str_replace('uwp_register_', '', $key);
                    $exclude_key = str_replace('uwp_account_', '', $key);
                    $value = uwp_get_usermeta($user->ID, $key, false);
                    $icon = uwp_get_field_icon($field->field_icon);

                    if(isset($icon) && !empty($icon)){
                        $title = $icon;
                    } else {
                        $title = $field->site_title;
                    }

                    if (!empty($exclude) && in_array($exclude_key, $exclude)) {
                        continue;
                    }

                    if ($value) {
                        echo '<li><a target="_blank" rel="nofollow" href="'.$value.'">'.$title.'</a></li>';
                    }
                }
                ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Default user icon
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @return      string  FA icon.
     */
    public function get_profile_count_icon() {
        return '<i class="fas fa-user"></i>';
    }

    /**
     * Returns the custom fields content of profile page more info tab.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @param       string      $show_type  Location of field to display.
     */
    public function show_output_location_data($user, $show_type) {
        if(!$show_type){
            return;
        }
        echo $this->get_extra_fields($user, '['.$show_type.']');
    }

    /**
     * Returns the custom fields content of profile page more info tab.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      string                  More info tab content.
     */
    public function get_profile_extra($user) {
        return $this->get_extra_fields($user, '[more_info]');
    }

    /**
     * Returns the custom fields content of profile page sidebar.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     */
    public function get_profile_side_extra($user) {
        echo $this->get_extra_fields($user, '[profile_side]');
    }

    /**
     * Returns the custom fields content of users page.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     */
    public function get_users_extra($user) {
        echo $this->get_extra_fields($user, '[users]');
    }

    /**
     * Returns the custom fields content based on type.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @param       string      $show_type  Filter type. 
     * @return      string                  Custom fields content.
     */
    public function get_extra_fields($user, $show_type) {

        ob_start();
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND css_class NOT LIKE '%uwp_social%' ORDER BY sort_order ASC");
        $wrap_html = false;
        if ($fields) {
			$usermeta = isset( $user->ID ) ? uwp_get_usermeta_row( $user->ID ) : array();
			$privacy = ! empty( $usermeta ) && ! empty( $usermeta->user_privacy ) ? explode( ',', $usermeta->user_privacy ) : array();

            foreach ($fields as $field) {
                $show_in = explode(',',$field->show_in);
                if (!in_array($show_type, $show_in)) {
                    continue;
                }

                $display = false;
				if ( $field->is_public == '0' ) {
					$display = false;
				} else if ( $field->is_public == '1' ) {
					$display = true;
				} else {
					if ( ! in_array( $field->htmlvar_name . '_privacy', $privacy ) ) {
						$display = true;
					}
				}
				if ( ! $display ) {
					continue;
				}

                $value = $this->get_field_value($field, $user);

                // Icon
                $icon = uwp_get_field_icon($field->field_icon);

                if ($field->field_type == 'fieldset') {
                    $icon = '';
                    ?>
                    <div class="uwp-profile-extra-wrap" style="margin: 0; padding: 0">
                        <div class="uwp-profile-extra-key uwp-profile-extra-full" style="margin: 0; padding: 0"><h3 style="margin: 10px 0;"><?php echo $icon.$field->site_title; ?></h3></div>
                    </div>
                    <?php
                } else {
                    if ($value) {
                        $wrap_html = true;
                        ?>
                        <div class="uwp-profile-extra-wrap">
                            <div class="uwp-profile-extra-key d-inline-block"><?php echo $icon." ".$field->site_title; ?><span class="uwp-profile-extra-sep">:</span></div>
                            <div class="uwp-profile-extra-value d-inline-block">
                                <?php
                                if ($field->htmlvar_name == 'bio') {
                                    $is_profile_page = is_uwp_profile_page();
                                    $value = get_user_meta($user->ID, 'description', true);
                                    $value = stripslashes($value);
                                    $limit_words = apply_filters('uwp_profile_bio_content_limit', 20);
                                    if ($value) {
                                        ?>
                                        <div class="uwp-profile-bio <?php if ($is_profile_page) { echo "uwp_more"; } ?>">
                                            <?php
                                            if ($is_profile_page) {
                                                echo $value;
                                            } else {
                                                echo wp_trim_words( $value, $limit_words, '...' );
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo $value;
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
        }
        $output = ob_get_contents();
        $wrapped_output = '';
        if ($wrap_html) {
            $wrapped_output .= '<div class="uwp-profile-extra"><div class="uwp-profile-extra-div form-table">';
            $wrapped_output .= $output;
            $wrapped_output .= '</div></div>';
        }
        ob_end_clean();
        return trim($wrapped_output);
    }

	/**
     *
     * Returns profile tabs
     *
	 * @return array $tabs Profile tabs
	 */
	public function get_tab_settings(){
		global $wpdb, $uwp_profile_tabs_array;

		if($uwp_profile_tabs_array){
			$tabs = $uwp_profile_tabs_array;
		}else{
			$tabs_table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';
			$uwp_profile_tabs_array = $tabs = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$tabs_table_name." WHERE form_type=%s ORDER BY sort_order ASC", 'profile-tabs'));
		}

		/**
		 * Get the tabs output settings.
		 *
		 * @param array $tabs The array of stdClass settings for the tabs output.
		 */
		return apply_filters('uwp_profile_tab_settings', $tabs);
	}

	/**
     *
     * Returns profile page tabs
     *
	 * @return array
	 */
	public function get_tabs(){

		global $uwp_profile_tabs_array;
		// check if we have been here before
		$tabs_array = !empty($uwp_profile_tabs_array) ? $uwp_profile_tabs_array : array();



//		$tabs = array();

//		$tabs = $settings;

		if(empty($tabs_array)){

			// get the settings
			$tabs = $this->get_tab_settings();

			if(!empty($tabs)){
				// get the tab contents first so we can decide to output the tab head
				$tabs_content = array();
				foreach($tabs as $tab){
					$tabs_content[$tab->id."tab"] = $this->tab_content($tab);
				}

				// setup the array
				if(!empty($tabs)){
					foreach($tabs as $tab) {
						if ( isset($tab->tab_level) && $tab->tab_level > 0 ) {
							continue;
						}
						if ( empty( $tabs_content[ $tab->id . "tab" ] ) ) {
							continue;
						}

						$tab->tab_content_rendered = $tabs_content[$tab->id."tab"];
						$tabs_array[] = (array) $tab;
					}
				}

//				echo '@@@';
//				print_r($tabs_array);
			}



		}
		
		return $tabs_array;

	}

	/**
     * Returns profile tab content
     *
     * @since 2.0.0
     *
	 * @param array $tab Tab array
	 *
	 * @return string
	 */
	public function tab_content($tab) {

		ob_start();
		// main content
		if(!empty($tab->tab_content)){ // override content
			$content = stripslashes( $tab->tab_content );
			echo do_shortcode( $content );
		}elseif($tab->tab_type=='meta'){ // meta info
			echo do_shortcode('[uwp_user_meta key="'.$tab->tab_key.'" show="value"]');
		}elseif($tab->tab_type=='standard'){ // meta info
			$user = uwp_get_displayed_user();
			do_action( 'uwp_profile_tab_content', $user , $tab );
			do_action('uwp_profile_'.$tab->tab_key.'_tab_content', $user , $tab);
		}

//		echo '###';

		echo self::tab_content_child($tab); // child elements

		return ob_get_clean();
	}

	/**
	 * Returns profile child tab content
	 *
	 * @since 2.0.0
	 *
	 * @param array $tab Child tab array
	 *
	 * @return string
	 */
	public function tab_content_child($tab) {
		ob_start();
		$tabs = self::get_tab_settings();
		$parent_id = $tab->id;

		foreach($tabs as $child_tab){
			if(isset($child_tab->tab_parent) && $child_tab->tab_parent==$parent_id){
				if(!empty($child_tab->tab_content)){ // override content
					echo stripslashes( $child_tab->tab_content );
				}elseif($child_tab->tab_type=='meta'){ // meta info
					echo do_shortcode('[uwp_user_meta key="'.$child_tab->tab_key.'"]');
				}elseif($child_tab->tab_type=='fieldset'){ // meta info
					self::output_fieldset($child_tab);
				}elseif($child_tab->tab_type=='standard'){ // meta info
					if($child_tab->tab_key=='reviews'){
						comments_template();
					}
				}
			}
		}
		return ob_get_clean();

	}

	/**
     *
     * Displays fieldset content
     *
	 * @param array $tab Tab array
	 *
	 * @return string
	 */
	public function output_fieldset($tab){
		ob_start();
		echo '<div class="uwp_post_meta  uwp-fieldset">';
		echo "<h4>";
		if($tab->tab_icon){
			echo '<i class="fas '.esc_attr($tab->tab_icon).'" aria-hidden="true"></i>';
		}
		if($tab->tab_name){
			esc_attr_e($tab->tab_name,'userswp');
		}
		echo "</h4>";
		echo "</div>";

		return ob_get_clean();
	}

	/**
     * Returns counts of a user posts
     *
	 * @param $user_id
	 *
	 * @return array
	 */
	public function get_user_post_counts($user_id){

		$counts = array();
		$post_types = array();
		if($user_id){

			$post_types = get_post_types( array('public'=>true,'publicly_queryable'=>true), 'objects');

			if(!empty($post_types)){
				foreach($post_types as $cpt => $post_type){
					$count = count_user_posts( $user_id , $cpt);
					if($count){
						$counts[$cpt] = array('name'=> $post_type->labels->name,'singular_name'=> $post_type->labels->singular_name,'count'=>$count);
					}

				}
			}

		}

//		print_r($counts);exit;

		return apply_filters( 'uwp_get_user_post_counts', $counts, $post_types );

	}


    /**
     * Returns enabled profile tabs
     *
     * @package     userswp
     * @param       object      $user       The User.
     *
     * @return      array                   Profile tabs
     */
    public function get_profile_tabs($user = '') {

	    global $wpdb, $uwp_profile_tabs;

	    $tabs_table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';
	    $tabs = array();

	    if(!isset($uwp_profile_tabs)){
		    $uwp_profile_tabs = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$tabs_table_name." WHERE form_type=%s ORDER BY sort_order ASC", 'profile-tabs'));
	    }

	    if(!empty($uwp_profile_tabs)) {
		    foreach ($uwp_profile_tabs as $tab) {

			    $icon = isset($tab->tab_icon) ? $tab->tab_icon : $this->get_profile_count_icon();
			    if ( uwp_is_fa_icon( $icon ) ) {
				    $tab_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
			    } elseif ( uwp_is_icon_url( $icon ) ) {
				    $tab_icon = '<b style="background-image: url("' . $icon . '")"></b>';
			    } else {
				    $tab_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
			    }

			    $tab_icon = apply_filters('uwp_profile_tab_icon', $tab_icon, $tab, $user );

			    if(isset($tab_icon) && ($tab_icon === 0 || $tab_icon === '0')){
				    $class = 'uwp_no_counts';
			    } else {
				    $class = '';
			    }

			    $tab->count = $tab_icon;
			    $tab->class = $class;
			    $tabs[$tab->tab_key] = $tab;

		    }
	    }

	    $tabs = apply_filters('uwp_profile_tabs1', $tabs, $user);

	    return $tabs;
    }


    /**
     * Prints the profile tab content template
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      void
     */
    public function get_profile_tabs_content($user) {

	    $active_tab = get_query_var('uwp_tab');
        $account_page = uwp_get_page_id('account_page', false);
	    $tabs_array = array();
	    $active_tab_content = '';
        $tabs = $this->get_profile_tabs($user);
        $tab_keys = array_keys($tabs);

        if (!empty($tab_keys)) {
            $default_key = $tab_keys[0];
        } else {
            $default_key = false;
        }

	    $default_key = apply_filters('uwp_profile_default_tab_display', $default_key, $user, $tabs);

        $active_tab = !empty( $active_tab ) && array_key_exists( $active_tab, $tabs ) ? $active_tab : $default_key;
        if (!$active_tab) {
            return;
        }

	    if(!empty($tabs)) {
		    foreach ($tabs as $tab) {
			    $tab_content = $this->tab_content($tab);
			    if ( !empty( $tab_content ) ) {
				    $tab->tab_content_rendered = $tab_content;
				    $tabs_array[] = (array) $tab;
			    }
		    }
	    }

        ?>
        <div class="uwp-profile-content">
            <div class="uwp-profile-nav">
                <ul class="item-list-tabs-ul">
                    <?php
                    if(!empty($tabs_array)) {
	                    foreach ($tabs_array as $tab) {
		                    $tab_id = $tab['tab_key'];
		                    $tab_url = uwp_build_profile_tab_url($user->ID, $tab_id, false);

		                    $active = $active_tab == $tab_id ? ' active' : '';

		                    if ($active_tab == $tab_id) {
			                    $active_tab_content = $tab['tab_content_rendered'];
		                    }

		                    ?>
                            <li id="uwp-profile-<?php echo $tab_id; ?>"
                                class="<?php echo $active . ' ' . $tab['class']; ?>">
                                <a href="<?php echo esc_url($tab_url); ?>">
                                    <span class="uwp-profile-tab-label uwp-profile-<?php echo $tab_id; ?>-label "><?php echo esc_html($tab['tab_name']); ?></span>
                                    <span class="uwp-profile-tab-count uwp-profile-<?php echo $tab_id; ?>-count"><?php echo $tab['count']; ?></span>
                                </a>
                            </li>
		                    <?php
	                    }
                    }
                    ?>
                </ul>
                <?php
                $can_user_edit_account = apply_filters('uwp_user_can_edit_own_profile', true, $user->ID);
                ?>
                <?php if ($account_page && is_user_logged_in() && (get_current_user_id() == $user->ID) && $can_user_edit_account) { ?>
                    <div class="uwp-edit-account">
                        <a href="<?php echo get_permalink( $account_page ); ?>" title="<?php echo  __( 'Edit Account', 'userswp' ); ?>"><i class="fas fa-cog"></i></a>
                    </div>
                <?php } ?>
            </div>

            <div class="uwp-profile-entries">
                <?php
                if(isset($active_tab_content) && !empty($active_tab_content)){
                    echo $active_tab_content;
                }
                ?>
            </div>
        </div>
    <?php }

	/**
     * Returns count for a tab
     *
	 * @param $tab_icon
	 * @param $tab
	 * @param $user
	 *
	 * @return int
	 */
    public function get_profile_tab_icon($tab_icon, $tab, $user){

        switch ($tab->tab_key){
            case 'posts' :
	            $tab_icon = uwp_post_count($user->ID, 'post');
                break;
            case 'comments' :
	            $tab_icon = uwp_comment_count($user->ID);
                break;
	        default :
		        break;
        }

        return $tab_icon;

    }

    /**
     * Prints the tab content pagination section.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       int         $total       Total items.
     * @return      void
     */
    public function get_profile_pagination($total) {
        ?>
        <div class="uwp-pagination">
            <?php
            $design_style = uwp_get_option("design_style",'bootstrap');
            if($design_style=='bootstrap'){
	            self::get_bootstrap_pagination($total);
            }else{
	            $big = 999999999; // need an unlikely integer
	            $translated = __( 'Page', 'userswp' ); // Supply translatable string

	            $args = array(
		            'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		            'format' => '?paged=%#%',
		            'current' => max( 1, get_query_var('paged') ),
		            'total' => $total,
		            'before_page_number' => '<span class="screen-reader-text">'.$translated.' </span>',
		            'type' => 'list'
	            ) ;

	            echo paginate_links( $args );
            }


            ?>
        </div>
        <?php
    }

	/**
     * Displays boostrap pagination
     *
     * @since       2.0.0
     *
	 * @param int $total Total count
	 */
	public function get_bootstrap_pagination($total) {

		$navigation = '';
		$args = array(
			'mid_size'  => 2,
			'type' => 'array',
			'prev_text' => sprintf(
				'%s <span class="nav-prev-text sr-only">%s</span>',
				'<i class="fas fa-chevron-left"></i>',
				__( 'Newer posts', 'ayetheme' )
			),
			'next_text' => sprintf(
				'<span class="nav-next-text sr-only">%s</span> %s',
				__( 'Older posts', 'ayetheme' ),
				'<i class="fas fa-chevron-right"></i>'
			),
		);

		// Don't print empty markup if there's only one page.
		if ( $total > 1 ) {
			$args = wp_parse_args( $args, array(
				'mid_size'           => 1,
				'prev_text'          => _x( 'Previous', 'previous set of posts' ),
				'next_text'          => _x( 'Next', 'next set of posts' ),
				'screen_reader_text' => __( 'Posts navigation' ),
				'total' => $total,
			) );

			// Set up paginated links.
			$links = paginate_links( $args );

			// make the output bootstrap ready
			$links_html = "<ul class='pagination m-0 p-0'>";
			if(!empty($links)){
				foreach($links as $link){
					$active = strpos($link, 'current') !== false ? 'active' : '';
					$links_html .= "<li class='page-item $active'>";
					$links_html .= str_replace("page-numbers","page-link",$link);
					$links_html .= "</li>";
				}
			}
			$links_html .= "</ul>";

			if ( $links ) {
				$navigation .= '<section class="px-0 py-2 w-100">';
				$navigation .= _navigation_markup($links_html, 'pagination', $args['screen_reader_text'] );
				$navigation .= '</section>';
			}

			$navigation = str_replace( "nav-links", "uwp-nav-links", $navigation );
		}

		echo  $navigation;

	}

    /**
     * Prints the profile page more info tab content.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      void
     */
    public function get_profile_more_info($user) {
        $extra = $this->get_profile_extra($user);
        echo $extra;
    }

    /**
     * Prints the profile page "posts" tab content.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      void
     */
    public function get_profile_posts($user) {
        uwp_generic_tab_content($user, 'post', __('Posts', 'userswp'));
    }

    /**
     * Prints the profile page "comments" tab content.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     * @return      void
     */
    public function get_profile_comments($user,$post_type='post') {


	    global $uwp_widget_args;
	    $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
	    $number = uwp_get_option('profile_no_of_items', 10);
	    $offset = ( $paged - 1 ) * $number;

	    $total_comments = uwp_comment_count($user->ID);
	    $maximum_pages = ceil($total_comments / $number);

	    $args = array(
		    'number' => $number,
		    'offset' => $offset,
		    'author_email' => $user->user_email,
		    'paged' => $paged,
		    'post_type' => $post_type,
	    );
	    // The Query
	    $the_query = new WP_Comment_Query();
	    $comments = $the_query->query( $args );

	    $uwp_widget_args['template_args']['the_query'] = $comments;
	    $uwp_widget_args['template_args']['user'] = $user;
	    $uwp_widget_args['template_args']['title'] = __("Comments");
	    $uwp_widget_args['template_args']['maximum_pages'] = $maximum_pages;

//	    $uwp_widget_args['template_args'] = array(
//		    'the_query' => $comments,
//		    'user'      => $user,
//		    'maximum_pages' => $maximum_pages
//	    );


	    $design_style = !empty($uwp_widget_args['design_style']) ? esc_attr($uwp_widget_args['design_style']) : uwp_get_option("design_style",'bootstrap');
	    $template = $design_style ? $design_style."/loop-comments" : "loop-comments";
	    uwp_locate_template($template);

    }

    /**
     * Rewrites profile page links
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function rewrite_profile_link() {

        $page_id = uwp_get_page_id('profile_page', false);
        if ($page_id && !isset($_REQUEST['page_id'])) {
            $link = get_page_link($page_id);
            $uwp_profile_link = trailingslashit(substr(str_replace(home_url(), '', $link), 1));
            $uwp_profile_page_id = $page_id;

            // {home_url}/profile/1
            $uwp_profile_link_empty_slash = '^' . $uwp_profile_link . '([^/]+)?$';
            add_rewrite_rule($uwp_profile_link_empty_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]', 'top');

            // {home_url}/profile/1/
            $uwp_profile_link_with_slash = '^' . $uwp_profile_link . '([^/]+)/?$';
            add_rewrite_rule($uwp_profile_link_with_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]', 'top');

            // {home_url}/profile/1/page/1
            $uwp_profile_link_empty_slash_paged = '^' . $uwp_profile_link . '([^/]+)/page/([0-9]+)?$';
            add_rewrite_rule($uwp_profile_link_empty_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&paged=$matches[2]', 'top');

            // {home_url}/profile/1/page/1/
            $uwp_profile_link_with_slash_paged = '^' . $uwp_profile_link . '([^/]+)/page/([0-9]+)/?$';
            add_rewrite_rule($uwp_profile_link_with_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&paged=$matches[2]', 'top');

            // {home_url}/profile/1/tab-slug
            $uwp_profile_tab_empty_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]', 'top');

            // {home_url}/profile/1/tab-slug/
            $uwp_profile_tab_with_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]', 'top');

            // {home_url}/profile/1/tab-slug/page/1
            $uwp_profile_tab_empty_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/page/([0-9]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&paged=$matches[3]', 'top');

            // {home_url}/profile/1/tab-slug/page/1/
            $uwp_profile_tab_with_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/page/([0-9]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&paged=$matches[3]', 'top');

            // {home_url}/profile/1/tab-slug/subtab-slug
            $uwp_profile_tab_empty_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]', 'top');

            // {home_url}/profile/1/tab-slug/subtab-slug/
            $uwp_profile_tab_with_slash = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]', 'top');

            // {home_url}/profile/1/tab-slug/subtab-slug/page/1
            $uwp_profile_tab_empty_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)/page/([0-9]+)?$';
            add_rewrite_rule($uwp_profile_tab_empty_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]&paged=$matches[4]', 'top');

            // {home_url}/profile/1/tab-slug/subtab-slug/page/1/
            $uwp_profile_tab_with_slash_paged = '^' . $uwp_profile_link . '([^/]+)/([^/]+)/([^/]+)/page/([0-9]+)/?$';
            add_rewrite_rule($uwp_profile_tab_with_slash_paged, 'index.php?page_id=' . $uwp_profile_page_id . '&uwp_profile=$matches[1]&uwp_tab=$matches[2]&uwp_subtab=$matches[3]&paged=$matches[4]', 'top');
            
            if (get_option('uwp_flush_rewrite')) {
                //Ensure the $wp_rewrite global is loaded
                global $wp_rewrite;
                //Call flush_rules() as a method of the $wp_rewrite object
                $wp_rewrite->flush_rules( false );
                delete_option('uwp_flush_rewrite');
            }
        }
    }

    /**
     * Adds profile page query variables.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array       $query_vars     Query variables.
     * @return      array                       Modified Query variables array.
     */
    public function profile_query_vars($query_vars) {
        $query_vars[] = 'uwp_profile';
        $query_vars[] = 'uwp_tab';
        $query_vars[] = 'uwp_subtab';
        return $query_vars;
    }

    /**
     * Returns user profile link based on user id.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string          $link           Unmodified link.
     * @param       int             $user_id        User id.
     * @return      string                          Modified link.
     */
    public function get_profile_link($link, $user_id) {

        if (1 == uwp_get_option('uwp_disable_author_link')) {
            return $link;
        }

        $page_id = uwp_get_page_id('profile_page', false);

        if (!$page_id) {
            return $link;
        }

        $link = get_permalink($page_id);

        if ($link != '') {

            if (isset($_REQUEST['page_id'])) {
                $permalink_structure = 'DEFAULT';
            } else {
                $permalink_structure = 'CUSTOM';
                // Add forward slash if not available
                $link = user_trailingslashit($link);
            }

            $url_type = apply_filters('uwp_profile_url_type', 'slug');

            if ($url_type && 'id' == $url_type) {
                if ('DEFAULT' == $permalink_structure) {
                    return add_query_arg(array('viewuser' => $user_id), $link);
                } else {
                    return user_trailingslashit($link . $user_id);
                }
            } else {
                $user = get_userdata($user_id);
                if ( !empty($user->user_nicename) ) {
                    $username = $user->user_nicename;
                } else {
                    $username = $user->user_login;
                }

                if ('DEFAULT' == $permalink_structure) {
                    return add_query_arg(array('username' => $username), $link);
                } else {
                    return user_trailingslashit($link . $username);
                }
            }
        } else {
            return $link;
        }
    }

    /**
     * Modifies profile page title to include username.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string          $title          Original title
     * @param       int|null        $id             Page id.
     * @return      string                          Modified page title.
     */
    public function modify_profile_page_title( $title, $id = null ) {

        global $wp_query;
        $page_id = uwp_get_page_id('profile_page', false);

        if ($page_id == $id && isset($wp_query->query_vars['uwp_profile']) && in_the_loop()) {

            $url_type = apply_filters('uwp_profile_url_type', 'slug');

            $author_slug = $wp_query->query_vars['uwp_profile'];

            if ($url_type == 'id') {
                $user = get_user_by('id', $author_slug);
            } else {
                $user = get_user_by('slug', $author_slug);
            }
            $title = $user->display_name;
        }

        return $title;
    }

    /**
     * Returns json content for image crop.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string              $image_url      Image url
     * @param       string              $type           popup type. Avatar or Banner
     * @return      string                              Json
     */
    public function image_crop_popup($image_url, $type) {

        add_filter( 'upload_dir', 'uwp_handle_multisite_profile_image', 10, 1 );
        $uploads = wp_upload_dir();
        remove_filter( 'upload_dir', 'uwp_handle_multisite_profile_image' );
        $upload_url = $uploads['baseurl'];
        $upload_path = $uploads['basedir'];
        $image_path = str_replace($upload_url, $upload_path, $image_url);

        $image = apply_filters( 'uwp_'.$type.'_cropper_image', getimagesize( $image_path ) );
        if ( empty( $image ) ) {
            return "";
        }

        // Get avatar full width and height.
        if ($type == 'avatar') {
            $full_width  = apply_filters('uwp_avatar_image_width', 150);
            $full_height = apply_filters('uwp_avatar_image_height', 150);
        } else {
            $full_width  = apply_filters('uwp_banner_image_width', uwp_get_option('profile_banner_width', 1000));
            $full_height = apply_filters('uwp_banner_image_height', 300);
        }
        

        $values = array(
            'error' => '',
            'image_url' => $image_url,
            'uwp_popup_type' => $type,
            'uwp_full_width' => $full_width,
            'uwp_full_height' => $full_height,
            'uwp_true_width' => $image[0],
            'uwp_true_height' => $image[1],
            'uwp_popup_content' => $this->image_crop_modal_html($type, $image_url, $full_width, $full_height),
        );
        
        $json = json_encode($values);


        return $json;
    }

    /**
     * Initializes image crop js.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user       The User ID.
     */
    public function image_crop_init($user) {
        if (is_user_logged_in()) {
            add_action( 'wp_footer', array($this,'modal_loading_html'));
            add_action( 'wp_footer', array($this,'modal_close_js'));
            if(is_admin()) {
                add_action('admin_footer', array($this, 'modal_loading_html'));
                add_action('admin_footer', array($this, 'modal_close_js'));
            }
        }
    }

    /**
     * Returns modal content loading html.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      string      Modal loding html.
     */
    public function modal_loading_html() {
        ob_start();
        ?>
        <div id="uwp-popup-modal-wrap" style="display: none;">
            <div class="uwp-bs-modal uwp_fade uwp_show">
                <div class="uwp-bs-modal-dialog">
                    <div class="uwp-bs-modal-content">
                        <div class="uwp-bs-modal-header">
                            <h4 class="uwp-bs-modal-title">
                                <?php echo __( "Loading Form ...", "userswp" ); ?>
                            </h4>
                        </div>
                        <div class="uwp-bs-modal-body">
                            <div class="uwp-bs-modal-loading-icon-wrap">
                                <div class="uwp-bs-modal-loading-icon"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        echo trim(preg_replace("/\s+|\n+|\r/", ' ', $output));
    }

    /**
     * Adds modal close js.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function modal_close_js() {
        ?>
        <script type="text/javascript">
            (function( $, window, undefined ) {
                $(document).ready(function () {
                    $('.uwp-modal-close').click(function(e) {
                        e.preventDefault();
                        var uwp_popup_type = $( this ).data( 'type' );
                        // $('#uwp-'+uwp_popup_type+'-modal').hide();
                        var mod_shadow = jQuery('#uwp-modal-backdrop');
                        var container = jQuery('#uwp-popup-modal-wrap');
                        container.hide();
                        container.replaceWith('<?php echo $this->modal_loading_html(); ?>');
                        mod_shadow.remove();
                    });
                });
            }( jQuery, window ));
        </script>
        <?php
    }

    /**
     * Returns avatar and banner crop modal html.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string          $type           Avatar or Banner
     * @param       string          $image_url      Image url to crop
     * @param       int             $full_width     Full image width
     * @param       int             $full_height    Full image height
     * @return      string                          Html.
     */
    public function image_crop_modal_html($type, $image_url, $full_width, $full_height) {
	    global $uwp_template_args;
	    $uwp_template_args = array(
		    'type' => $type,
		    'image_url' => $image_url,
		    'full_width' => $full_width,
		    'full_height' => $full_height,
	    );
        ob_start();

	    $design_style = uwp_get_option("design_style",'bootstrap');
	    $template = $design_style ? $design_style."/modal-profile-image-crop" : "modal-profile-image-crop";

	    uwp_locate_template($template);
	    $uwp_template_args = array();
        ?>
        <script type="text/javascript">
            (function( $, window, undefined ) {
                $(document).ready(function () {
                    $('.uwp-modal-close').click(function(e) {
                        e.preventDefault();
                        var uwp_popup_type = $( this ).data( 'type' );
                        // $('#uwp-'+uwp_popup_type+'-modal').hide();
                        var mod_shadow = jQuery('#uwp-modal-backdrop');
                        var container = jQuery('#uwp-popup-modal-wrap');
                        container.hide();
                        container.replaceWith('<?php echo $this->modal_loading_html(); ?>');
                        mod_shadow.remove();
                    });
                });
            }( jQuery, window ));
        </script>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return trim($output);
    }

    /**
     * Returns avatar and banner crop submit form.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string          $type           Avatar or Banner
     * @return      string                          Html.
     */
    public function crop_submit_form($type = 'avatar') {
        
        ob_start();

	    // get file sizes
	    $files = new UsersWP_Files();
	    $max_file_size = $files->uwp_get_max_upload_size($type);

	    $design_style = uwp_get_option("design_style",'bootstrap');
	    $template = $design_style ? $design_style."/modal-profile-image" : "modal-profile-image";



	    uwp_locate_template($template);

	    $content_wrap = $design_style == 'bootstrap' ? '.uwp-profile-image-change-modal .modal-content' : '#uwp-popup-modal-wrap';
        ?>

        <script type="text/javascript">
            (function( $, window, undefined ) {

                var uwp_popup_type = '<?php echo $type; ?>';

                $(document).ready(function () {
                    $("#progressbar").progressbar();
                    $('.uwp-modal-close').click(function(e) {
                        e.preventDefault();
                        var uwp_popup_type = $( this ).data( 'type' );
                        // $('#uwp-'+uwp_popup_type+'-modal').hide();
                        var mod_shadow = jQuery('#uwp-modal-backdrop');
                        var container = jQuery('#uwp-popup-modal-wrap');
                        container.hide();
                        container.replaceWith('<?php echo $this->modal_loading_html(); ?>');
                        mod_shadow.remove();
                    });

                    $('#uwp_upload_<?php echo $type; ?>').on('change', function(e) {
                        e.preventDefault();

                        var container = jQuery('<?php echo esc_attr($content_wrap);?>');
                        var err_container = jQuery('#uwp-bs-modal-notice');
	                    err_container.html('');// clear errors on retry

                        var fd = new FormData();
                        var files_data = $(this); // The <input type="file" /> field
	                    var file = files_data[0].files[0];
	                    var file_size = file.size;

	                    // file size check
	                    if(file_size > <?php echo absint($max_file_size);?>){
		                    err_container.html('<div class="uwp-alert-error text-center alert alert-danger"><?php _e( 'File too big.', 'userswp' );?></div>');
		                    return;
	                    }

                        fd.append('<?php echo $type; ?>', file);
                        // our AJAX identifier
                        fd.append('action', 'uwp_avatar_banner_upload');
                        fd.append('uwp_popup_type', '<?php echo $type; ?>');

                        $("#progressBar").show().removeClass('d-none');

                        $.ajax({
                            // Your server script to process the upload
                            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                            type: 'POST',

                            // Form data
                            data: fd,

                            // Tell jQuery not to process data or worry about content-type
                            // You *must* include these options!
                            cache: false,
                            contentType: false,
                            processData: false,

                            // Custom XMLHttpRequest
                            xhr: function() {
                                myXhr = $.ajaxSettings.xhr();
                                if(myXhr.upload){
                                    myXhr.upload.addEventListener('progress',showProgress, false);
                                } else {
                                    console.log("Upload progress is not supported.");
                                }
                                return myXhr;
                            },

                            success:function(response) {
                                $("#progressBar").hide();
                                resp = JSON.parse(response);
                                if (resp['error'] != "") {
                                    err_container.html(resp['error']);
                                } else {
                                    resp = JSON.parse(response);
                                    uwp_full_width = resp['uwp_full_width'];
                                    uwp_full_height = resp['uwp_full_height'];
                                    uwp_true_width = resp['uwp_true_width'];
                                    uwp_true_height = resp['uwp_true_height'];

	                                container.html(resp['uwp_popup_content']).find('#uwp-'+uwp_popup_type+'-to-crop').Jcrop({
                                        // onChange: showPreview,
                                        onSelect: updateCoords,
                                        allowResize: true,
                                        allowSelect: false,
                                        boxWidth: 650,   //Maximum width you want for your bigger images
                                        boxHeight: 400,  //Maximum Height for your bigger images
                                        setSelect: [ 0, 0, uwp_full_width, uwp_full_height ],
                                        aspectRatio: uwp_full_width/uwp_full_height,
                                        trueSize: [uwp_true_width,uwp_true_height],
                                        minSize: [uwp_full_width,uwp_full_height]
                                    });
                                }
                            }
                        });
                    });

                    function showProgress(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = (evt.loaded / evt.total) * 100;
//                            $('#progressbar').progressbar("option", "value", percentComplete );
                            progress(percentComplete, $('#progressBar'));
                        }
                    }

	                function progress(percent, $element) {
		                percent = Math.round(percent);
		                var progressBarWidth = percent * ( $element.width() / 100 );
		                $element.find('div').width( progressBarWidth ).html(percent + "%");
	                }

                    function updateCoords(c) {
                        jQuery('#'+uwp_popup_type+'-x').val(c.x);
                        jQuery('#'+uwp_popup_type+'-y').val(c.y);
                        jQuery('#'+uwp_popup_type+'-w').val(c.w);
                        jQuery('#'+uwp_popup_type+'-h').val(c.h);
                    }

                });
            }( jQuery, window ));
        </script>

        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return trim($output);
 }

    
    /**
     * Array search by value
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array       $array      Original array.
     * @param       mixed       $key        Array key
     * @param       mixed       $value      Array value.
     * @return      array                   Result array.
     */
    public function uwp_array_search($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, $this->uwp_array_search($subarray, $key, $value));
            }
        }

        return $results;
    }

    /*
     * Modifies get_avatar_url function to use userswp avatar URL.
     * @param array $avatar_defaults default avatars
     * @return array $avatar_defaults default avatars
     *
    */
    function get_avatar_url($url, $id_or_email, $args){

	    // don't filter on admin side.
	    if(is_admin() && !wp_doing_ajax()){return $url;}

        $user = false;

        if(1 == uwp_get_option('disable_avatar_override')){
            return $url;
        }

        if ( is_numeric( $id_or_email ) ) {

            $id = (int) $id_or_email;
            $user = get_user_by( 'id' , $id );

        } elseif ( is_object( $id_or_email ) ) {

            if ( ! empty( $id_or_email->user_id ) ) {
                $id = (int) $id_or_email->user_id;
                $user = get_user_by( 'id' , $id );
            }

        } else {
            $user = get_user_by( 'email', $id_or_email );
        }

        if ( $user && is_object( $user ) ) {
            $avatar_thumb = uwp_get_usermeta($user->data->ID, 'avatar_thumb', '');
            if ( !empty($avatar_thumb) ) {
                add_filter( 'upload_dir', 'uwp_handle_multisite_profile_image', 10, 1 );
                $uploads = wp_upload_dir();
                remove_filter( 'upload_dir', 'uwp_handle_multisite_profile_image' );
                $upload_url = $uploads['baseurl'];
                if (substr( $avatar_thumb, 0, 4 ) !== "http") {
                    $url = $upload_url.$avatar_thumb;
                }
            } else {
                $default = uwp_get_default_avatar_uri();
	            if(uwp_is_localhost()){ // if localhost then default gravitar won't work.
		            $url = $default;
	            }else{
		            $url = remove_query_arg('d', $url);
		            $url = add_query_arg(array('d' => $default), $url);
	            }

            }
        }

        return $url;
    }

    /**
     * Modified the comment author url to profile page link for loggedin users.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $link       Original author link.
     * @return      string                  Modified author link.    
     */
    public function get_comment_author_link($link) {
        global $comment;
        if ( !empty( $comment->user_id ) && !empty( get_userdata( $comment->user_id )->ID ) ) {
            $user = get_userdata( $comment->user_id );
            $link = sprintf(
                '<a href="%s" rel="external nofollow" class="url">%s</a>',
                uwp_build_profile_tab_url( $comment->user_id ),
                strip_tags( $user->display_name )
            );
        }
        return $link;
    }

    /**
     * Redirects /author page to /profile page.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function redirect_author_page() {
        
        if(uwp_is_page_builder()){
            return;
        }

        if ( is_author() && apply_filters( 'uwp_check_redirect_author_page', true ) ) {
            $id = get_query_var( 'author' );
            $link = uwp_build_profile_tab_url( $id );
            $link = apply_filters( 'uwp_redirect_author_page', $link, $id );
            wp_redirect($link);
            exit;
        }
    }

    /**
     * Modifies "edit my profile" link in admin bar
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $url        The complete URL including scheme and path.
     * @param       int         $user_id    The user ID.
     * @param       string      $scheme     Scheme to give the URL context. Accepts 'http', 'https', 'login',
     *                                      'login_post', 'admin', 'relative' or null.
     * @return      false|string            Filtered url.
     */
    public function modify_admin_bar_edit_profile_url( $url, $user_id, $scheme )
    {
        // Makes the link to http://example.com/account
        if (!is_admin()) {
            $account_page = uwp_get_page_id('account_page', false);
            if ($account_page) {
                $account_page_link = get_permalink($account_page);
                $url = $account_page_link;
            }
        }
        return $url;
    }

    /**
     * Restrict the files display only to current users in media popup.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $wp_query      Unmodified wp_query. 
     * @return      object                     Modified wp_query.
     */
    public function restrict_attachment_display($wp_query) {
        if (!is_admin()) {
            if ( ! current_user_can( 'manage_options' ) ) {
                //$wp_query['author'] = get_current_user_id();
                $wp_query->set( 'author', get_current_user_id() );
            }
        }
        return $wp_query;
    }

    /**
     * Allow users to upload files who has upload capability.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array   $llcaps         An array of all the user's capabilities.
     * @param       array   $caps           Actual capabilities for meta capability.
     * @param       array   $args           Optional parameters passed to has_cap(), typically object ID.
     * @param       object  $user           The user object.
     * @return      array                   User capabilities.
     */
    public function allow_all_users_profile_uploads($llcaps, $caps, $args, $user) {

        $files = new UsersWP_Files();
        
        if(isset($caps[0]) && $caps[0] =='upload_files' && $files->doing_upload() ){
            $llcaps['upload_files'] = true;
        }

        return $llcaps;
    }

    /**
     * Validates file uploads and returns errors if found.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string|bool         $value              Original value.
     * @param       object              $field              Field info.
     * @param       string              $file_key           Field key.
     * @param       array               $file_to_upload     File data to upload.
     * @return      string|WP_Error                         Returns original value if no erros. Else returns errors.
     */
    public function handle_file_upload_error_checks($value, $field, $file_key, $file_to_upload) {
        
        if (in_array($field->htmlvar_name, array('uwp_avatar_file', 'uwp_banner_file'))) {

            if ($field->htmlvar_name == 'uwp_avatar_file') {
                $min_width  = apply_filters('uwp_avatar_image_width', 150);
                $min_height = apply_filters('uwp_avatar_image_height', 150);
            } else {
                $min_width  = apply_filters('uwp_banner_image_width', uwp_get_option('profile_banner_width', 1000));
                $min_height = apply_filters('uwp_banner_image_height', 300);
            }

            $imagedetails = getimagesize( $file_to_upload['tmp_name'] );
            $width = $imagedetails[0];
            $height = $imagedetails[1];

            if ( $width < $min_width) {
                return new WP_Error( 'image-too-small', sprintf( __( 'The uploaded file is too small. Minimum image width should be %s px', 'userswp' ),  $min_width));
            }
            if ( $height < $min_height) {
                return new WP_Error( 'image-too-small', sprintf( __( 'The uploaded file is too small. Minimum image height should be %s px', 'userswp' ), $min_height) );
            }
        }

        return $value;
        
    }

    /**
     * Sets uwp_profile_upload to true on profile page.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       array      $params     Plupload params
     * @return      array                  Plupload params
     */
    public function add_uwp_plupload_param($params) {

        if(!is_admin() && get_the_ID()==uwp_get_page_id('profile_page', false)){
            $params['uwp_profile_upload'] = true;
        }

        return $params;
    }

    /**
     * Handles avatar and banner file upload.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function ajax_avatar_banner_upload() {
        // Image upload handler
        // todo: security checks
        $type = strip_tags(esc_sql($_POST['uwp_popup_type']));

        if (!in_array($type, array('banner', 'avatar'))) {
            $result['error'] = uwp_wrap_notice(__("Invalid request!", "userswp"), 'error');
            $return = json_encode($result);
            echo $return;
            die();
        }

        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type = 'file' AND is_active = '1' ORDER BY sort_order ASC", array($type)));

        $field = false;
        if ($fields) {
            $field = $fields[0];
        }

        $result = array();

        if (!$field) {
            $result['error'] = uwp_wrap_notice(__("No fields available", "userswp"), 'error');
            $return = json_encode($result);
            echo $return;
            die();
        }

        $files = new UsersWP_Files();
        $errors = $files->handle_file_upload($field, $_FILES);

        if (is_wp_error($errors)) {
            $result['error'] = uwp_wrap_notice($errors->get_error_message(), 'error');
            $return = json_encode($result);
            echo $return;
        } else {
            $return = $this->ajax_image_crop_popup($errors['url'], $type);
            echo $return;
        }

        die();
    }

    /**
     * Returns the avatar and banner crop popup html and js.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string          $image_url      Image url to crop.
     * @param       string          $type           Crop type. Avatar or Banner
     * @return      string|null                     Html and js content.
     */
    public function ajax_image_crop_popup($image_url, $type){
        wp_enqueue_style( 'jcrop' );
        wp_enqueue_script( 'jcrop', array( 'jquery' ) );

        $output = null;
        if ($image_url && $type ) {
            $output = $this->image_crop_popup($image_url, $type);
        }
        return $output;
    }

    /**
     * Handles crop popup form ajax request.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function ajax_image_crop_popup_form(){
        $type = isset($_POST['type']) ? strip_tags(esc_sql($_POST['type'])) : '';

        $output = null;


        if ($type && in_array($type, array('banner', 'avatar'))) {
            $output = $this->crop_submit_form($type);
        }
        echo $output;
        exit();
    }

    /**
     * Defines javascript ajaxurl variable.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function define_ajaxurl() {

        echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
    }
	

    /**
     * Add custom fields as an available tabs
     *
     * @since       1.0.20
     * @package     userswp
     * @param       $tabs_arr
     * @return      mixed
     */
    public function extra_fields_available_tab_items($tabs_arr){
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT htmlvar_name, site_title FROM " . $table_name . " WHERE form_type = 'account' AND is_public != '0' AND show_in LIKE '%[own_tab]%' ORDER BY sort_order ASC");

        foreach ($fields as $field) {
            $key = str_replace('uwp_account_', '', $field->htmlvar_name);
            $tabs_arr[$key] = __($field->site_title, 'userswp');
        }

        return $tabs_arr;
    }

    /**
     * Displays custom fields as tabs
     *
     * @since       1.0.0
     * @package     userswp
     * @param       $tabs
     * @param       $user
     * @return      mixed
     */
    public function extra_fields_as_tabs($tabs, $user, $allowed_tabs)
    {
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT site_title,field_icon,htmlvar_name,is_public,field_type,field_type_key FROM " . $table_name . " WHERE form_type = 'account' AND is_public != '0' AND show_in LIKE '%[own_tab]%' ORDER BY sort_order ASC");
        $usermeta = uwp_get_usermeta_row($user->ID);
        $privacy = ! empty( $usermeta ) && $usermeta->user_privacy ? explode(',', $usermeta->user_privacy) : array();

        foreach ($fields as $field) {
            if ($field->field_icon != '') {
                $icon = uwp_get_field_icon($field->field_icon);
            } else {
                $field_icon = uwp_field_type_to_fa_icon($field->field_type);
                if ($field_icon) {
                    $icon = '<i class="'.$field_icon.'"></i>';
                } else {
                    $icon = '<i class="fas fa-user"></i>';
                }
            }
            $key = str_replace('uwp_account_', '', $field->htmlvar_name);
            $value = $this->get_field_value($field, $user);
            if('fieldset' == $field->field_type){
                $value = true;
            }
            if (!in_array($key, $allowed_tabs) || !$value) {
                continue;
            }
            if ($field->is_public == '1') {
                $tabs[$key] = array(
                    'title' => __($field->site_title, 'userswp'),
                    'count' => $icon
                );
            } else {
                if (!in_array($field->htmlvar_name.'_privacy', $privacy)) {
                    $tabs[$key] = array(
                        'title' => __($field->site_title, 'userswp'),
                        'count' => $icon
                    );
                }
            }
        }
        return $tabs;
    }

    /**
     * Prints custom field values as tab content.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $user           The User ID.
     * @param       string      $active_tab     Active tab.
     */
    public function uwp_extra_fields_as_tab_values($user, $active_tab)
    {
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' AND is_public != '0' ORDER BY sort_order ASC");
        $usermeta = uwp_get_usermeta_row($user->ID);
        $privacy = ! empty( $usermeta ) && $usermeta->user_privacy ? explode(',', $usermeta->user_privacy) : array();

        $fieldsets = array();
        $fieldset = false;
        foreach ($fields as $field) {
            $key = str_replace('uwp_account_', '', $field->htmlvar_name);
            if ($field->field_type == 'fieldset') {
                $fieldset = $key;
            }
            $show_in = explode(',',$field->show_in);
            if (in_array("[fieldset]", $show_in)) {
                $fieldsets[$fieldset][] = $field;
            }
            if ($key == $active_tab && $field->field_type != 'fieldset') {
                $value = $this->get_field_value($field, $user);
                echo '<div class="uwp_profile_tab_field_content">';
                echo $value;
                echo '</div>';
            }
        }

        if (isset($fieldsets[$active_tab])) {
            $fieldset_fields = $fieldsets[$active_tab];
            ?>
            <div class="uwp-profile-extra">
                <div class="uwp-profile-extra-div form-table">
                <?php
                foreach ($fieldset_fields as $field) {
                    $display = false;
                    if ($field->is_public == '1') {
                        $display = true;
                    } else {
                        if (!in_array($field->htmlvar_name.'_privacy', $privacy)) {
                            $display = true;
                        }
                    }
                    if (!$display) {
                        continue;
                    }
                    $value = $this->get_field_value($field, $user);
                    // Icon
                    $icon = uwp_get_field_icon( $field->field_icon );
                    if($value) {
                        ?>
                        <div class="uwp-profile-extra-wrap">
                            <div class="uwp-profile-extra-key"><?php echo $icon . $field->site_title; ?><span class="uwp-profile-extra-sep">:</span></div>
                            <div class="uwp-profile-extra-value">
                                <?php echo $value; ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Gets custom field value based on key.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       object      $field      Field info object.
     * @param       object      $user       The User.
     * @return      string                  Custom field value.
     */
    public function get_field_value($field, $user) {

        $user_data = get_userdata($user->ID);
        $file_obj = new UsersWP_Files();

        if ($field->htmlvar_name == 'email') {
            $value = $user_data->user_email;
        } elseif ($field->htmlvar_name == 'password') {
            $value = '';
            $field->is_required = 0;
        } elseif ($field->htmlvar_name == 'confirm_password') {
            $value = '';
            $field->is_required = 0;
        } else {
            $value = uwp_get_usermeta($user->ID, $field->htmlvar_name, "");
        }
        
        // Select and Multiselect needs Value to be converted
        if ($field->field_type == 'select' || $field->field_type == 'multiselect') {
            $option_values_arr = uwp_string_values_to_options($field->option_values, true);

            // Select
            if ($field->field_type == 'select') {

                if($field->field_type_key != 'country'){
                    if (!empty($value)) {
                        $data = $this->uwp_array_search($option_values_arr, 'value', $value);
                        $value = $data[0]['label'];
                    } else {
                        $value = '';
                    }
                }
            }

            //Multiselect
            if ($field->field_type == 'multiselect' && !empty($value)) {
                if (!empty($value) && is_array($value)) {
                    $array_value = array();
                    foreach ($value as $v) {
                        if(!empty($v)){
                            $data = $this->uwp_array_search($option_values_arr, 'value', $v);
                            $array_value[] = $data[0]['label'];
                        }

                    }
                    if(!empty($array_value)){
                        $value = implode(', ', $array_value);
                    }else{
                        $value = '';
                    }

                } else {
                    $value = '';
                }
            }
        }

        // Date
        if ($field->field_type == 'datepicker') {
            $extra_fields = unserialize($field->extra_fields);

            if ($extra_fields['date_format'] == '')
                $extra_fields['date_format'] = 'yy-mm-dd';

            $date_format = $extra_fields['date_format'];

            if (!empty($value)) {
                $value = date('Y-m-d', $value);
            }

            $value = uwp_date($value, $date_format, 'Y-m-d');
        }


        // Time
        if ($field->field_type == 'time') {
            $value = date(get_option('time_format'), strtotime($value));
        }

        // URL
        if ($field->field_type == 'url' && !empty($value)) {
            $link_text = $value;
            // if deafult_value is not url then it will be used as link text. 
            if ($field->default_value && !empty($field->default_value) ) {
                if (substr( $field->default_value, 0, 4 ) === "http") {
                    $link_text = $value;
                } else {
                    $link_text = $field->default_value;
                }
            }
            if (substr( $link_text, 0, 4 ) === "http") {
                $link_text = esc_url($link_text);
            }

            $value = '<a href="'.$value.'">'.$link_text.'</a>';
        }

        // Checkbox
        if ($field->field_type == 'checkbox') {
            if ($value == '1') {
                $value = 'Yes';
            } else {
                $value = 'No';
            }
        }

        // File
        if ($field->field_type == 'file') {
            $value = $file_obj->file_upload_preview($field, $value, false);
        }

        // Sanitize
        switch ($field->field_type) {
            case 'url':
                // already escaped
                break;
            case 'file':
                // already escaped
                break;
            case 'textarea':
                $value = esc_textarea( $value );
                break;
            default:
                $value = esc_html( $value );
        }

        if(isset($field->field_type_key) && $field->field_type_key == 'country'){
            $value = uwp_output_country_html($value);
        }

        return $value;
    }

    public function wpdiscuz_profile_url($profile_url, $user){
        $profile_page_url = uwp_get_page_id('profile_page', true);
        $allowed = apply_filters('uwp_wpdiscuz_profile_url_change', true, $profile_url, $user);
        if (isset($profile_page_url) && !empty($profile_page_url) && $allowed ) {
            return $profile_page_url;
        }

        return $profile_url;
    }

	/**
	 * This is the JS needed for the list view selector for CPTs view.
	 */
	public function list_view_js() {
		?>
		<script type="text/javascript">/* <![CDATA[ */

			function uwp_list_view_select($val, $noStore) {

				var $storage_key = "uwp_list_view";
				var $list = jQuery('.uwp-users-loop > .row');
				if(!$list.length){
					$list = jQuery('.uwp-profile-cpt-loop > .row');
					$storage_key = "uwp_cpt_list_view";
				}

				var $listSelect = jQuery('.uwp-list-view-select');
				if ($val == 0) {
					$list.removeClass('row-cols-sm-2 row-cols-md-2 row-cols-md-3 row-cols-md-4 row-cols-md-5').addClass('row-cols-md-0');
					$listSelect.find('button').removeClass('active');
					$listSelect.find('button.uwp-list-view-select-list').addClass('active');
				} else {
					$listSelect.find('button').removeClass('active');
					$listSelect.find('button.uwp-list-view-select-grid').addClass('active');
					$listSelect.find('button[data-gridview="'+$val+'"]').addClass('active');
					$list.removeClass('row-cols-md-0 row-cols-md-2 row-cols-md-3 row-cols-md-4 row-cols-md-5').addClass('row-cols-sm-2 row-cols-md-'+$val);
				}

				// only store if it was a user action
				if (!$noStore) {
					// store the user selection
					localStorage.setItem($storage_key, $val);
				}
			}

			// set the current user selection if set
			if (typeof(Storage) !== "undefined") {
				var $storage_key = "uwp_list_view";
				var $list = jQuery('.uwp-users-loop > .row');
				if(!$list.length){
					$list = jQuery('.uwp-profile-cpt-loop > .row');
					$storage_key = "uwp_cpt_list_view";
				}
				var $noStore = false;
				var uwp_list_view = localStorage.getItem($storage_key);
				setTimeout(function () {
					if (!uwp_list_view) {
						$noStore = true;
						if ($list.hasClass('row-cols-md-0')) {
							uwp_list_view = 0;
						} else if($list.hasClass('row-cols-md-1')){
							uwp_list_view = 1;
						} else if($list.hasClass('row-cols-md-2')){
							uwp_list_view = 2;
						} else if($list.hasClass('row-cols-md-3')){
							uwp_list_view = 3;
						} else if($list.hasClass('row-cols-md-4')){
							uwp_list_view = 4;
						}else if($list.hasClass('row-cols-md-5')){
							uwp_list_view = 5;
						} else {
							uwp_list_view = 3;
						}
					}
					uwp_list_view_select(uwp_list_view, $noStore);
				}, 10); // we need to give it a very short time so the page loads the actual html
			}
			/* ]]> */</script>
		<?php
	}

	/**
	 * Display lightbox modal
	 */
	public function lightbox_modals(){
		?>
		<div class="modal fade uwp-profile-image-change-modal" tabindex="-1" role="dialog" aria-labelledby="uwp-profile-modal-title" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="uwp-profile-modal-title"></h5>
					</div>
					<div class="modal-body">
						<i class="fas fa-circle-notch fa-spin"></i>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
}