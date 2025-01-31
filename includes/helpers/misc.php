<?php
/**
 * Converts string value to options array.
 * Used in select, multiselect and radio fields.
 * Wraps inside optgroup if available.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string          $option_values          String option values.
 * @param       bool            $translated             Do you want to translate the output?
 *
 * @return      array|null                              Options array.
 */
function uwp_string_values_to_options($option_values = '', $translated = false)
{
    $options = array();
    if ($option_values == '') {
        return NULL;
    }

    if (strpos($option_values, "{/optgroup}") !== false) {
        $option_values_arr = explode("{/optgroup}", $option_values);

        foreach ($option_values_arr as $optgroup) {
            if (strpos($optgroup, "{optgroup}") !== false) {
                $optgroup_arr = explode("{optgroup}", $optgroup);

                $count = 0;
                foreach ($optgroup_arr as $optgroup_str) {
                    $count++;
                    $optgroup_str = trim($optgroup_str);

                    $optgroup_label = '';
                    if (strpos($optgroup_str, "|") !== false) {
                        $optgroup_str_arr = explode("|", $optgroup_str, 2);
                        $optgroup_label = trim($optgroup_str_arr[0]);
                        if ($translated && $optgroup_label != '') {
                            $optgroup_label = __($optgroup_label, 'userswp');
                        }
                        $optgroup_label = ucfirst($optgroup_label);
                        $optgroup_str = $optgroup_str_arr[1];
                    }

                    $optgroup3 = uwp_string_to_options($optgroup_str, $translated);

                    if ($count > 1 && $optgroup_label != '' && !empty($optgroup3)) {
                        $optgroup_start = array(array('label' => $optgroup_label, 'value' => NULL, 'optgroup' => 'start'));
                        $optgroup_end = array(array('label' => $optgroup_label, 'value' => NULL, 'optgroup' => 'end'));
                        $optgroup3 = array_merge($optgroup_start, $optgroup3, $optgroup_end);
                    }
                    $options = array_merge($options, $optgroup3);
                }
            } else {
                $optgroup1 = uwp_string_to_options($optgroup, $translated);
                $options = array_merge($options, $optgroup1);
            }
        }
    } else {
        $options = uwp_string_to_options($option_values, $translated);
    }

    return $options;
}

/**
 * Converts string value to options array.
 * Used in select, multiselect and radio fields.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $input          Input String
 * @param       bool        $translated     Do you want to translate the output?
 *
 * @return      array                       Options array.
 */
function uwp_string_to_options($input = '', $translated = false)
{
    $return = array();
    if ($input != '') {
        $input = trim($input);
        $input = rtrim($input, ",");
        $input = ltrim($input, ",");
        $input = trim($input);
    }

    $input_arr = explode(',', $input);

    if (!empty($input_arr)) {
        foreach ($input_arr as $input_str) {
            $input_str = trim($input_str);

            if (strpos($input_str, "/") !== false) {
                $input_str = explode("/", $input_str, 2);
                $label = trim($input_str[0]);
                if ($translated && $label != '') {
                    $label = __($label, 'userswp');
                }
                $label = ucfirst($label);
                $value = trim($input_str[1]);
            } else {
                if ($translated && $input_str != '') {
                    $input_str = __($input_str, 'userswp');
                }
                $label = ucfirst($input_str);
                $value = $input_str;
            }

            if ($label != '') {
                $return[] = array('label' => $label, 'value' => $value, 'optgroup' => NULL);
            }
        }
    }

    return $return;
}


/**
 * Resizes the image.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $image      Reference a local file
 * @param       int         $width      Image width      
 * @param       int         $height     Image height    
 * @param       float       $scale      Image scale ratio.
 *
 * @return      mixed                   Resized image.
 */
function uwp_resizeImage($image,$width,$height,$scale) {
    /** @noinspection PhpUnusedLocalVariableInspection */
    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
    $imageType = image_type_to_mime_type($imageType);
    $newImageWidth = ceil($width * $scale);
    $newImageHeight = ceil($height * $scale);
    $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
    $source = false;
    switch($imageType) {
        case "image/gif":
            $source=imagecreatefromgif($image);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            $source=imagecreatefromjpeg($image);
            break;
        case "image/png":
        case "image/x-png":
            $source=imagecreatefrompng($image);
            break;
    }
    imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);

    switch($imageType) {
        case "image/gif":
            imagegif($newImage,$image);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            imagejpeg($newImage,$image,90);
            break;
        case "image/png":
        case "image/x-png":
            imagepng($newImage,$image);
            break;
    }

    chmod($image, 0777);
    return $image;
}

/**
 * Resizes thumbnail image.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $thumb_image_name
 * @param       string      $image
 * @param       int         $x                      x-coordinate of source point.
 * @param       int         $y                      y-coordinate of source point.
 * @param       int         $src_w                  Source width.
 * @param       int         $src_h                  Source height.
 * @param       float       $scale                  Image scale ratio.
 *
 * @return      mixed                               Resized image.
 */
function uwp_resizeThumbnailImage($thumb_image_name, $image, $x, $y, $src_w, $src_h, $scale){
    uwp_set_php_limits();
    // ignore image createion warnings
    @ini_set('gd.jpeg_ignore_warning', 1);
    /** @noinspection PhpUnusedLocalVariableInspection */
    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
    $imageType = image_type_to_mime_type($imageType);

    $newImageWidth = ceil($src_w * $scale);
    $newImageHeight = ceil($src_h * $scale);
    $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
    $source = false;
    switch($imageType) {
        case "image/gif":
            $source=imagecreatefromgif($image);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            $source=imagecreatefromjpeg($image);
            break;
        case "image/png":
        case "image/x-png":
            $source=imagecreatefrompng($image);
            break;
    }
    imagecopyresampled($newImage,$source,0,0,$x,$y,$newImageWidth, $newImageHeight, $src_w, $src_h);
    switch($imageType) {
        case "image/gif":
            imagegif($newImage, $thumb_image_name);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            imagejpeg($newImage, $thumb_image_name, 100);
            break;
        case "image/png":
        case "image/x-png":
            imagepng($newImage, $thumb_image_name);
            break;
    }

    chmod($thumb_image_name, 0777);
    return $thumb_image_name;
}

/**
 * Try to set higher limits on the fly
 */
function uwp_set_php_limits() {
    error_reporting( 0 );

    // try to set higher limits for import
    $max_input_time     = ini_get( 'max_input_time' );
    $max_execution_time = ini_get( 'max_execution_time' );
    $memory_limit       = ini_get( 'memory_limit' );

    if ( $max_input_time !== 0 && $max_input_time != -1 && ( ! $max_input_time || $max_input_time < 3000 ) ) {
        ini_set( 'max_input_time', 3000 );
    }

    if ( $max_execution_time !== 0 && ( ! $max_execution_time || $max_execution_time < 3000 ) ) {
        ini_set( 'max_execution_time', 3000 );
    }

    if ( $memory_limit && str_replace( 'M', '', $memory_limit ) ) {
        if ( str_replace( 'M', '', $memory_limit ) < 256 ) {
            ini_set( 'memory_limit', '256M' );
        }
    }

    ini_set( 'auto_detect_line_endings', true );
}

/**
 * Logs the error message.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       array|object|string     $log        Error message.
 *
 * @return      void
 */
function uwp_error_log($log){
    /*
     * A filter to override the WP_DEBUG setting for function uwp_error_log().
     */
    $should_log = apply_filters( 'uwp_log_errors', WP_DEBUG);
    if ( true === $should_log ) {
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}


/**
 * Prints the users page main content.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return   array $users array of users
 */
function get_uwp_users_list() {

    global $wpdb;

    $keyword = false;
    if (isset($_GET['uwps']) && $_GET['uwps'] != '') {
        $keyword = stripslashes(strip_tags($_GET['uwps']));
    }

    $sort_by = false;
    if (isset($_GET['uwp_sort_by']) && $_GET['uwp_sort_by'] != '') {
        $sort_by = strip_tags(esc_sql($_GET['uwp_sort_by']));
    }

    $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

    $number = uwp_get_option('profile_no_of_items', 10);

    $where = '';
    $where = apply_filters('uwp_users_search_where', $where, $keyword);

    $arg = array(
        'fields' => 'ID',
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key'     => 'uwp_mod',
                'value'   => 'email_unconfirmed',
                'compare' => '=='
            ),
            array(
                'key'     => 'uwp_hide_from_listing',
                'value'   => 1,
                'compare' => '=='
            )
        )
    );

    $inactive_users = new WP_User_Query($arg);
    $exclude_users = $inactive_users->get_results();

    $excluded_globally = uwp_get_option('users_excluded_from_list');
    if ( $excluded_globally ) {
        $excluded_users = str_replace(' ', '', $excluded_globally );
        $users_array = explode(',', $excluded_users );
        $exclude_users = array_merge($exclude_users, $users_array);
    }

    $exclude_users = apply_filters('uwp_excluded_users_from_list', $exclude_users, $where, $keyword);

    if($exclude_users){
        $exclude_users_list = implode(',', array_unique($exclude_users));
        $exclude_query = 'AND '. $wpdb->users.'.ID NOT IN ('.$exclude_users_list.')';
    } else {
        $exclude_query = ' ';
    }

    if ($keyword || $where) {
        if (empty($where)) {
            $user_query = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.*
            FROM $wpdb->users
            INNER JOIN $wpdb->usermeta
            ON ( $wpdb->users.ID = $wpdb->usermeta.user_id )
            WHERE 1=1
            $exclude_query
            AND ( 
            ( $wpdb->usermeta.meta_key = 'first_name' AND $wpdb->usermeta.meta_value LIKE %s ) 
            OR 
            ( $wpdb->usermeta.meta_key = 'last_name' AND $wpdb->usermeta.meta_value LIKE %s ) 
            OR 
            user_login LIKE %s 
            OR 
            user_nicename LIKE %s 
            OR 
            display_name LIKE %s 
            OR 
            user_email LIKE %s
            )
            ORDER BY display_name ASC
            LIMIT 0, 20",
                array(
                    '%' . $keyword . '%',
                    '%' . $keyword . '%',
                    '%' . $keyword . '%',
                    '%' . $keyword . '%',
                    '%' . $keyword . '%',
                    '%' . $keyword . '%'
                )
            ));

        } else {
            $usermeta_table = get_usermeta_table_prefix() . 'uwp_usermeta';

            $user_query = $wpdb->get_results(
                "SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.*
            FROM $wpdb->users
            INNER JOIN $usermeta_table
            ON ( $wpdb->users.ID = $usermeta_table.user_id )
            WHERE 1=1
            $exclude_query
            $where
            ORDER BY display_name ASC
            LIMIT 0, 20");
        }

        $get_users = wp_list_pluck($user_query, 'ID');
        $args = array(
            'include' => $get_users,
        );

        $uwp_users_query = new WP_User_Query($args);
        $users['users'] = $uwp_users_query->get_results();
        $users['total_users'] = $uwp_users_query->get_total();

    } else {

        $args = array(
            'number' => (int) $number,
            'paged' => $paged,
            'exclude' => $exclude_users,
        );


        if ($sort_by) {
            switch ($sort_by) {
                case "newer":
                    $args['orderby'] = 'registered';
                    $args['order'] = 'desc';
                    break;
                case "older":
                    $args['orderby'] = 'registered';
                    $args['order'] = 'asc';
                    break;
                case "alpha_asc":
                    $args['orderby'] = 'display_name';
                    $args['order'] = 'asc';
                    break;
                case "alpha_desc":
                    $args['orderby'] = 'display_name';
                    $args['order'] = 'desc';
                    break;

            }
        }

        $uwp_users_query = new WP_User_Query($args);
        $users['users'] = $uwp_users_query->get_results();
        $users['total_users'] = $uwp_users_query->get_total();

    }

    return $users;

}

/**
 * Gets the custom field info for given key.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $htmlvar_name       Custom field key.
 *
 * @return      object                          Field info.
 */
function uwp_get_custom_field_info($htmlvar_name,$form_type = 'account') {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    if($form_type){
        $field = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE htmlvar_name = %s AND form_type = %s", array($htmlvar_name,$form_type)));
    }else{
        $field = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE htmlvar_name = %s", array($htmlvar_name)));
    }
    return $field;
}



/**
 * Returns the Users page layout class based on the setting.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      string      Layout class.
 */
function uwp_get_layout_class($layout) {
    if(!$layout){
        $layout = uwp_get_option('users_default_layout', 'list');
    }

    switch ($layout) {
        case "list":
            $class = "uwp_listview";
            break;
        case "2col":
            $class = "uwp_gridview uwp_gridview_2col";
            break;
        case "3col":
            $class = "uwp_gridview uwp_gridview_3col";
            break;
        case "4col":
            $class = "uwp_gridview uwp_gridview_4col";
            break;
        case "5col":
            $class = "uwp_gridview uwp_gridview_5col";
            break;
        default:
            $class = "uwp_listview";
    }

    return $class;
}

add_filter( 'uwp_users_list_ul_extra_class', 'uwp_get_layout_class', 10, 1 );

add_filter( 'get_user_option_metaboxhidden_nav-menus', 'uwp_always_nav_menu_visibility', 10, 3 );

/**
 * Filters nav menu visibility option value.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       mixed       $result     Value for the user's option.
 * @param       string      $option     Name of the option being retrieved.
 * @param       WP_User     $user       WP_User object of the user whose option is being retrieved.
 *
 * @return      array                   Filtered value.
 */
function uwp_always_nav_menu_visibility( $result, $option, $user )
{
    if( is_array($result) && in_array( 'add-users-wp-nav-menu', $result ) ) {
        $result = array_diff( $result, array( 'add-users-wp-nav-menu' ) );
    }

    return $result;
}

add_filter('user_profile_picture_description', 'uwp_admin_user_profile_picture_description');

/**
 * Filters the user profile picture description displayed under the Gravatar.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $description    Profile picture description.
 *
 * @return      string                      Modified description.
 */
function uwp_admin_user_profile_picture_description($description) {
    if (is_admin() && IS_PROFILE_PAGE) {
        $user_id = get_current_user_id();
        $avatar = uwp_get_usermeta($user_id, 'avatar_thumb', '');

        if (!empty($avatar)) {
            $description = sprintf( __( 'You can change your profile picture on your <a href="%s">Profile Page</a>.', 'userswp' ),
                uwp_build_profile_tab_url( $user_id ));
        }

    }
    return $description;
}

/**
 * Adds avatar and banner fields in admin side.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       object      $user       User object.
 *
 * @return      void
 */
function uwp_admin_edit_banner_fields($user) {
    global $wpdb;

    $file_obj = new UsersWP_Files();

    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE (form_type = 'avatar' OR form_type = 'banner') ORDER BY sort_order ASC");
    if ($fields) {
        ?>
        <div class="uwp-profile-extra uwp_page">
            <?php do_action('uwp_admin_profile_edit', $user ); ?>
            <table class="uwp-profile-extra-table form-table">
                <?php
                foreach ($fields as $field) {

                    // Icon
                    $icon = uwp_get_field_icon( $field->field_icon );

                    if ($field->field_type == 'fieldset') {
                        ?>
                        <tr style="margin: 0; padding: 0">
                            <th class="uwp-profile-extra-key" style="margin: 0; padding: 0"><h3 style="margin: 10px 0;">
                                    <?php echo $icon.$field->site_title; ?></h3></th>
                            <td></td>
                        </tr>
                        <?php
                    } else { ?>
                        <tr>
                            <th class="uwp-profile-extra-key"><?php echo $icon.$field->site_title; ?></th>
                            <td class="uwp-profile-extra-value">
                                <?php
                                if ($field->htmlvar_name == "uwp_avatar_file") {
                                    $value = uwp_get_usermeta($user->ID, "avatar_thumb", "");
                                } elseif ($field->htmlvar_name == "uwp_banner_file") {
                                    $value = uwp_get_usermeta($user->ID, "banner_thumb", "");
                                } else {
                                    $value = "";
                                }
                                ?>
                                <?php echo $file_obj->file_upload_preview($field, $value); ?>
                                <?php
                                if ($field->htmlvar_name == "uwp_avatar_file") {
                                    if (!empty($value)) {
                                        ?>
                                        <a class="uwp-profile-modal-form-trigger" data-type="avatar" href="#">
                                            <?php echo __("Change Avatar", "userswp"); ?>
                                        </a>
                                        <?php
                                    } else {
                                        ?>
                                        <a class="uwp-profile-modal-form-trigger" data-type="avatar" href="#">
                                            <?php echo __("Upload Avatar", "userswp"); ?>
                                        </a>
                                        <?php
                                    }
                                } elseif ($field->htmlvar_name == "uwp_banner_file") {
                                    if (!empty($value)) {
                                        ?>
                                        <a class="uwp-profile-modal-form-trigger" data-type="banner" href="#">
                                            <?php echo __("Change Banner", "userswp"); ?>
                                        </a>
                                        <?php
                                    } else {
                                        ?>
                                        <a class="uwp-profile-modal-form-trigger" data-type="banner" href="#">
                                            <?php echo __("Upload Banner", "userswp"); ?>
                                        </a>
                                        <?php
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>
        <?php
    }
}
add_action('show_user_profile', 'uwp_admin_edit_banner_fields');
add_action('edit_user_profile', 'uwp_admin_edit_banner_fields');

add_filter('admin_body_class', 'uwp_add_admin_body_class');
function uwp_add_admin_body_class($classes) {
    $screen = get_current_screen();
    if ( 'profile' == $screen->base || 'user-edit' == $screen->base )
    $classes .= 'uwp_page';
    return $classes;
}

// Privacy
add_filter('uwp_account_page_title', 'uwp_account_privacy_page_title', 10, 2);

/**
 * Adds Privacy tab title in Account page.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $title      Privacy title.
 * @param       string      $type       Tab type.
 *
 * @return      string|void             Title.
 */
function uwp_account_privacy_page_title($title, $type) {
    if ($type == 'privacy') {
        $title = __( 'Privacy', 'userswp' );
    }

    return $title;
}

add_action('uwp_account_form_display', 'uwp_account_privacy_edit_form_display');

/**
 * Adds form html for privacy fields in account page.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $type       Form type.
 *
 * @return      void
 */
function uwp_account_privacy_edit_form_display($type) {
    if ($type == 'privacy') {
        $make_profile_private = uwp_can_make_profile_private();
        echo '<div class="uwp-account-form uwp_wc_form">';
        $extra_where = "AND is_public='2'";
        $fields = get_account_form_fields($extra_where);
        $fields = apply_filters('uwp_account_privacy_fields', $fields);
        $user_id = get_current_user_id();
        $design_style = uwp_get_option("design_style","bootstrap");
        $bs_form_group = $design_style ? "form-group row" : "";
        $bs_form_control = $design_style ? "form-control" : "";
        $bs_btn_class = $design_style ? "btn btn-primary btn-block text-uppercase" : "";
        ?>
        <div class="uwp-profile-extra">
            <div class="uwp-profile-extra-div form-table">
                <form class="uwp-account-form uwp_form" method="post">
                    <?php if ($fields) { ?>
                        <div class="uwp-profile-extra-wrap row">
                            <div class="uwp-profile-extra-key col" style="font-weight: bold;">
                                <?php echo __("Field", "userswp") ?>
                            </div>
                            <div class="uwp-profile-extra-value col" style="font-weight: bold;">
                                <?php echo __("Is Public?", "userswp") ?>
                            </div>
                        </div>
                        <?php foreach ($fields as $field) { ?>
                            <div class="uwp-profile-extra-wrap <?php echo $bs_form_group; ?>">
                                <div class="uwp-profile-extra-key col"><?php echo $field->site_title; ?>
                                    <span class="uwp-profile-extra-sep">:</span></div>
                                <div class="uwp-profile-extra-value col">
                                    <?php
                                    $field_name = $field->htmlvar_name . '_privacy';
                                    $value = uwp_get_usermeta($user_id, $field_name, false);
                                    if ($value === false) {
                                        $value = 'yes';
                                    }
                                    ?>
                                    <select name="<?php echo $field_name; ?>" class="uwp_privacy_field uwp_select2 <?php echo $bs_form_control; ?>"
                                            style="margin: 0;">
                                        <option value="no" <?php selected($value, "no"); ?>><?php echo __("No", "userswp") ?></option>
                                        <option value="yes" <?php selected($value, "yes"); ?>><?php echo __("Yes", "userswp") ?></option>
                                    </select>
                                </div>
                            </div>
                        <?php }
                    }
                    $value = get_user_meta($user_id, 'uwp_hide_from_listing', true); ?>
                    <div class="uwp-profile-extra-wrap">
                        <div id="uwp_hide_from_listing" class="uwp_hide_from_listing">
                            <input name="uwp_hide_from_listing" class="" <?php checked($value, "1", true); ?> type="checkbox" value="1"><?php _e('Hide profile from the users listing page.', 'userswp'); ?>
                        </div>
                    </div>
                    <?php
                    do_action('uwp_after_privacy_form_fields', $fields);
                    if ($make_profile_private) {
                        $field_name = 'uwp_make_profile_private';
                        $value = get_user_meta($user_id, $field_name, true);
                        if ($value === false) {
                            $value = '0';
                        }
                        ?>
                        <div id="uwp_make_profile_private" class=" uwp_make_profile_private_row">
                            <input type="hidden" name="uwp_make_profile_private" value="0">
                            <input name="uwp_make_profile_private" class="" <?php checked( $value, "1", true ); ?> type="checkbox" value="1">
                            <?php _e( 'Make the whole profile private', 'userswp' ); ?>
                        </div>
                        <?php
                    }
                    ?>
                    <input type="hidden" name="uwp_privacy_nonce" value="<?php echo wp_create_nonce( 'uwp-privacy-nonce' ); ?>" />
                    <input name="uwp_privacy_submit" class="<?php echo $bs_btn_class; ?>"  value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit">
                </form>
            </div>
        </div>
        <?php
        echo '</div>';
    }
}

add_action('uwp_account_menu_display', 'uwp_add_account_menu_links');

/**
 * Prints "Edit account" page subtab / submenu links. Ex: Privacy
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      void
 */
function uwp_add_account_menu_links() {

    if (isset($_GET['type'])) {
        $type = strip_tags(esc_sql($_GET['type']));
    } else {
        $type = 'account';
    }

    $account_page = uwp_get_page_id('account_page', false);
    $account_page_link = get_permalink($account_page);

    $account_available_tabs = uwp_account_get_available_tabs();

    if (!is_array($account_available_tabs) && count($account_available_tabs) > 0) {
        return;
    }

	$legacy = '<ul class="uwp_account_menu">';
    ob_start();
    ?>
    <div class="dropdown text-center">
        <button class="btn btn-primary dropdown-toggle" type="button" id="account_settings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php _e('Account Settings', 'userswp'); ?>
        </button>
        <ul class="dropdown-menu m-0 p-0 mt-3 list-unstyled" aria-labelledby="account_settings">
            <?php
            foreach( $account_available_tabs as $tab_id => $tab ) {

	            if ($tab_id == 'account') {
		            $tab_url = $account_page_link;
	            } else {
		            $tab_url = add_query_arg(array(
			            'type' => $tab_id,
		            ), $account_page_link);
	            }

	            if (isset($tab['link'])) {
		            $tab_url = $tab['link'];
	            }

	            $active = $type == $tab_id ? ' active' : '';

	            ?>
                <li class="m-0 p-0 list-unstyled"><a class="dropdown-item uwp-account-<?php echo $tab_id.' '.$active; ?>" href="<?php echo esc_url( $tab_url ); ?>"><?php echo $tab['title']; ?></a></li>
	            <?php

	            $legacy .= '<li id="uwp-account-'.$tab_id.'">';
	            $legacy .= '<a class="'.$active.'" href="'.esc_url( $tab_url ).'">';
	            $legacy .= '<i class="'.$tab["icon"].'"></i>'.$tab["title"];
	            $legacy .= '</a></li>';
            }
            ?>
        </ul>
    </div>
    <?php
	$legacy .=  '</ul>';
	$bs_output = ob_get_clean();
	$style = uwp_get_option('design_style', 'bootstrap');
	if(!empty($style)){
		echo $bs_output;
    } else {
		echo $legacy;
    }
}




/**
 * Updates extras fields sort order.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       array       $field_ids      Form extras field ids.
 * @param       string      $form_type      Form type.
 *
 * @return      array|bool                  Sorted field ids.
 */
function uwp_form_extras_field_order($field_ids = array(), $form_type = 'register')
{
    global $wpdb;
    $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

    $count = 0;
    if (!empty($field_ids)):
        foreach ($field_ids as $id) {

            $cf = trim($id, '_');

            $wpdb->query(
                $wpdb->prepare(
                    "update " . $extras_table_name . " set
															sort_order=%d
															where id= %d",
                    array($count, $cf)
                )
            );
            $count++;
        }

        return $field_ids;
    else:
        return false;
    endif;
}

/**
 * Uppercase the first character of each word in a string.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $string     String to convert.
 * @param       string      $charset    Charset.
 *
 * @return      string                  Converted string.
 */
function uwp_ucwords($string, $charset='UTF-8') {
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($string, MB_CASE_TITLE, $charset);
    } else {
        return ucwords($string);
    }
}

/**
 * Checks whether the column exists in the table.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $db             Table name.
 * @param       string      $column         Column name.
 *
 * @return      bool
 */
function uwp_column_exist($db, $column)
{
    $table = new UsersWP_Tables();
    return $table->column_exists($db, $column);
}

/**
 * Adds column if not exist in the table.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $db             Table name.
 * @param       string      $column         Column name.
 * @param       string      $column_attr    Column attributes.
 *
 * @return      bool|int                    True when success.
 */
function uwp_add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NOT NULL")
{
    $table = new UsersWP_Tables();
    return $table->add_column_if_not_exist($db, $column, $column_attr);

}

/**
 * Returns excluded custom fields.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array   Excluded custom fields.
 */
function uwp_get_excluded_fields() {
    $excluded = array(
        'password',
        'confirm_password',
        'user_privacy',
    );
    return apply_filters('uwp_excluded_fields',$excluded);
}

/**
 * Formats the currency using currency separator.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string              $number     Currency number.
 * @param       array|string        $cf         Custom field info.
 *
 * @return      string                          Formatted currency.
 */
function uwp_currency_format_number($number='',$cf=''){

    $cs = isset($cf['extra_fields']) ? maybe_unserialize($cf['extra_fields']) : '';

    $symbol = isset($cs['currency_symbol']) ? $cs['currency_symbol'] : '$';
    $decimals = isset($cf['decimal_point']) && $cf['decimal_point'] ? $cf['decimal_point'] : 2;
    $decimal_display = isset($cf['decimal_display']) && $cf['decimal_display'] ? $cf['decimal_display'] : 'if';
    $decimalpoint = '.';

    if(isset($cs['decimal_separator']) && $cs['decimal_separator']=='comma'){
        $decimalpoint = ',';
    }

    $separator = ',';

    if(isset($cs['thousand_separator'])){
        if($cs['thousand_separator']=='comma'){$separator = ',';}
        if($cs['thousand_separator']=='slash'){$separator = '\\';}
        if($cs['thousand_separator']=='period'){$separator = '.';}
        if($cs['thousand_separator']=='space'){$separator = ' ';}
        if($cs['thousand_separator']=='none'){$separator = '';}
    }

    $currency_symbol_placement = isset($cs['currency_symbol_placement']) ? $cs['currency_symbol_placement'] : 'left';

    if($decimals>0 && $decimal_display=='if'){
        if(is_int($number) || floor( $number ) == $number)
            $decimals = 0;
    }

    $number = number_format($number,$decimals,$decimalpoint,$separator);



    if($currency_symbol_placement=='left'){
        $number = $symbol . $number;
    }else{
        $number = $number . $symbol;
    }


    return $number;
}


/**
 * Checks whether the user can make his/her own profile private or not.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      bool
 */
function uwp_can_make_profile_private() {
    $make_profile_private = apply_filters('uwp_user_can_make_profile_private', false);
    return $make_profile_private;
}

/**
 * Returns available registration status options.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Registration status options.
 */
function uwp_registration_status_options() {
    $registration_options = array(
        'auto_approve' =>  __('Auto approve', 'userswp'),
        'auto_approve_login' =>  __('Auto approve + Auto Login', 'userswp'),
        'require_email_activation' =>  __('Require Email Activation', 'userswp'),
    );

    $registration_options = apply_filters('uwp_registration_status_options', $registration_options);

    return $registration_options;
}

/**
 * Retrieves a user row based on password reset key and login
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string         $key       Hash to validate sending user's password.
 * @param       string         $login     The user login.
 *
 * @return      WP_Error|WP_User          User object.
 */
function uwp_check_activation_key( $key, $login ) {
    $user_data = check_password_reset_key( $key, $login );

    return $user_data;
}


add_action('uwp_template_fields', 'uwp_template_fields_terms_check', 100, 1);

/**
 * Adds "Accept terms and conditions" checkbox in register form.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $form_type      Form type.
 *
 * @return      void
 */
function uwp_template_fields_terms_check($form_type) {
    if ($form_type == 'register') {
        $terms_page = false;
        $reg_terms_page_id = uwp_get_page_id('register_terms_page', false);
        $reg_terms_page_id = apply_filters('uwp_reg_terms_page_id', $reg_terms_page_id);
        if (!empty($reg_terms_page_id)) {
            $terms_page = get_permalink($reg_terms_page_id);
        }
        if ($terms_page) {
            ?>
            <div class="uwp-remember-me">
                <label style="display: inline-block;font-weight: normal" for="agree_terms">
                    <input name="agree_terms" id="agree_terms" value="yes" type="checkbox">
                    <?php echo sprintf( __( 'I Accept <a href="%s" target="_blank">Terms and Conditions</a>.', 'userswp' ), $terms_page); ?>
                </label>
            </div>
            <?php
        }
    }
}



/**
 * Redirects the user to login page when email not confirmed.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $username       Username.
 * @param       object      $user           User object.
 *
 * @return      void
 */
function uwp_unconfirmed_login_redirect( $username, $user ) {
    if (!is_wp_error($user)) {
        $mod_value = get_user_meta( $user->ID, 'uwp_mod', true );
        if ($mod_value == 'email_unconfirmed') {
            if ( !in_array( 'administrator', $user->roles ) ) {
                $login_page = uwp_get_page_id('login_page', false);
                if ($login_page) {
                    $redirect_to = add_query_arg(array('uwp_err' => 'act_pending'), get_permalink($login_page));
                    wp_destroy_current_session();
                    wp_clear_auth_cookie();
                    if(wp_doing_ajax()){
                        global $userswp;
                        $message = $userswp->notices->form_notice_by_key('act_pending',false);
                        wp_send_json_error($message);
                    }else{
                        wp_redirect($redirect_to);
                    }
                    exit();
                }
            }
        }
    }
}
add_filter( 'wp_login', 'uwp_unconfirmed_login_redirect', 10, 2 );

/**
 * Adds notification menu in admin toolbar.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      void
 */
function uwp_notifications_toolbar_menu() {
    global $wp_admin_bar;

    if ( ! is_user_logged_in() ) {
        return;
    }

    $available_counts = apply_filters('uwp_notifications_available_counts', array());

    if (count($available_counts) == 0) {
        return;
    }

    $total_count = 0;
    foreach ($available_counts as $key => $value) {
        $total_count = $total_count + $value;
    }


    $alert_class   = (int) $total_count > 0 ? 'pending-count' : 'count';
    $menu_title    = '<span id="uwp-notification-count" class="' . $alert_class . '">'
        . number_format_i18n( $total_count ) . '</span>';
    $menu_link     = '';

    // Add the top-level Notifications button.
    $wp_admin_bar->add_menu( array(
        'parent'    => 'top-secondary',
        'id'        => 'uwp-notifications',
        'title'     => $menu_title,
        'href'      => $menu_link,
    ) );

    do_action('uwp_notifications_items', $wp_admin_bar);

    return;
}
add_action( 'admin_bar_menu', 'uwp_notifications_toolbar_menu', 90 );

/**
 * Returns the installation type.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      string      Installation type.
 */
function uwp_get_installation_type() {
    // *. Single Site
    if (!is_multisite()) {
        return "single";
    } else {
        // Multisite
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }

        // Network active.
        if ( is_plugin_active_for_network( 'userswp/userswp.php' ) ) {
            if (defined('UWP_ROOT_PAGES')) {
                if (UWP_ROOT_PAGES == 'all') {
                    // *. Multisite - Network Active - Pages on all sites
                    return "multi_na_all";
                } else {
                    // *. Multisite - Network Active - Pages on specific site
                    return "multi_na_site_id";
                }
            } else {
                // Multi - network active - default
                // *. Multisite - Network Active - Pages on main site
                return "multi_na_default";
            }
        } else {
            // * Multisite - Not network active
            return "multi_not_na";
        }
    }
}

/**
 * Returns the table prefix based on the installation type.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      string      Table prefix
 */
function uwp_get_table_prefix() {
    $tables = new UsersWP_Tables();
    return $tables->get_table_prefix();
}

/**
 * Returns the table prefix based on the installation type.
 *
 * @since       1.0.16
 * @package     userswp
 *
 * @return      string      Table prefix
 */
function get_usermeta_table_prefix() {
    $tables = new UsersWP_Tables();
    return $tables->get_usermeta_table_prefix();
}



/**
 * Converts array to comma separated string.
 *
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $key        Custom field key.
 * @param       string      $value      Custom field value.
 *
 * @return      string                  Converted custom field value string.
 */
function uwp_maybe_serialize($key, $value) {
    $field = uwp_get_custom_field_info($key);
    if (isset($field->field_type) && $field->field_type == 'multiselect' && is_array($value)) {
        $value = implode(",", $value);
    }
    return $value;
}

/**
 * Converts comma separated string to array.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $key        Custom field key.
 * @param       string      $value      Custom field value.
 *
 * @return      array                   Converted custom field value array.
 */
function uwp_maybe_unserialize($key, $value) {
    $field = uwp_get_custom_field_info($key);
    if (isset($field->field_type) && $field->field_type == 'multiselect') {
        $value = explode(",", $value);
    }
    return $value;
}

/**
 * Creates UsersWP related tables.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      void
 */
function uwp_create_tables()
{
    $tables = new UsersWP_Tables();
    $tables->create_tables();
}

/**
 * Returns tye client IP.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      string      IP address.
 */
function uwp_get_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return apply_filters('uwp_get_ip', $ip);
}

/**
 * Checks whether the string starts with the given string.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $haystack       String to compare with.
 * @param       string      $needle         String to search for.
 *
 * @return      bool                        True when success. False when failure.
 */
function uwp_str_starts_with($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

/**
 * Checks whether the string ends with the given string.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $haystack       String to compare with.
 * @param       string      $needle         String to search for.
 *
 * @return      bool                        True when success. False when failure.
 */
function uwp_str_ends_with($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

/**
 * Returns the font awesome icon value for field type. 
 * Displayed in profile tabs.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $type       Field type.
 *
 * @return      string                  Font awesome icon value.
 */
function uwp_field_type_to_fa_icon($type) {
    $field_types = array(
        'text' => 'fas fa-minus',
        'datepicker' => 'fas fa-calendar-alt',
        'textarea' => 'fas fa-bars',
        'time' =>'far fa-clock',
        'checkbox' =>'far fa-check-square',
        'phone' =>'far fa-phone',
        'radio' =>'far fa-dot-circle',
        'email' =>'far fa-envelope',
        'select' =>'far fa-caret-square-down',
        'multiselect' =>'far fa-caret-square-down',
        'url' =>'fas fa-link',
        'file' =>'fas fa-file'
    );

    if (isset($field_types[$type])) {
        return $field_types[$type];
    } else {
        return "";
    }
    
}

/**
 * Check wpml active or not.
 *
 * @since 1.0.7
 *
 * @return True if WPML is active else False.
 */
function uwp_is_wpml() {
    if (function_exists('icl_object_id')) {
        return true;
    }

    return false;
}

/**
 * Get the element in the WPML current language.
 *
 * @since 1.0.7
 *
 * @param int         $element_id                 Use term_id for taxonomies, post_id for posts
 * @param string      $element_type               Use post, page, {custom post type name}, nav_menu, nav_menu_item, category, tag, etc.
 *                                                You can also pass 'any', to let WPML guess the type, but this will only work for posts.
 * @param bool        $return_original_if_missing Optional, default is FALSE. If set to true it will always return a value (the original value, if translation is missing).
 * @param string|NULL $ulanguage_code              Optional, default is NULL. If missing, it will use the current language.
 *                                                If set to a language code, it will return a translation for that language code or
 *                                                the original if the translation is missing and $return_original_if_missing is set to TRUE.
 *
 * @return int|NULL
 */
function uwp_wpml_object_id( $element_id, $element_type = 'post', $return_original_if_missing = false, $ulanguage_code = null ) {
    if ( uwp_is_wpml() ) {
        if ( function_exists( 'wpml_object_id_filter' ) ) {
            return apply_filters( 'wpml_object_id', $element_id, $element_type, $return_original_if_missing, $ulanguage_code );
        } else {
            return icl_object_id( $element_id, $element_type, $return_original_if_missing, $ulanguage_code );
        }
    }

    return $element_id;
}

/**
 * Check if we might be on localhost.
 *
 * @return bool
 */
function uwp_is_localhost(){
    $localhost = false;

    if( isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME']=='localhost' ){
        $localhost = true;
    }elseif(isset($_SERVER['SERVER_ADDR']) && ( $_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1' )  ){
        $localhost = true;
    }

    return $localhost;
}

function uwp_get_default_avatar_uri(){
    $default = uwp_get_option('profile_default_profile', '');
    if(empty($default)){
        $default = USERSWP_PLUGIN_URL."assets/images/no_profile.png";
    } else {
        $default = wp_get_attachment_url($default);
    }

    $default = apply_filters('uwp_default_avatar_uri', $default);

    return $default;
}

function uwp_get_default_thumb_uri(){
    $thumb_url = USERSWP_PLUGIN_URL."/assets/images/no_thumb.png";
    $thumb_url = apply_filters('uwp_default_thumb_uri', $thumb_url);
    return $thumb_url;
}

function uwp_get_default_banner_uri(){
    $banner_url = USERSWP_PLUGIN_URL."/assets/images/banner.png";
    $banner_url = apply_filters('uwp_default_banner_uri', $banner_url);
    return $banner_url;
}

function uwp_refresh_permalinks_on_bad_404() {

    global $wp;

    if( ! is_404() ) {
        return;
    }

    if( isset( $_GET['uwp-flush'] ) ) {
        return;
    }

    if( false === get_transient( 'uwp_refresh_404_permalinks' ) ) {

        flush_rewrite_rules( false );

        set_transient( 'uwp_refresh_404_permalinks', 1, HOUR_IN_SECONDS * 12 );

        wp_redirect( home_url( add_query_arg( array( 'uwp-flush' => 1 ), $wp->request ) ) ); exit;

    }
}
add_action( 'template_redirect', 'uwp_refresh_permalinks_on_bad_404' );

/**
 * Handles multisite upload dir path
 *
 * @param $uploads array upload variable array
 *
 * @return array updated upload variable array.
 */
function uwp_handle_multisite_profile_image($uploads){
    if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    }

    // Network active.
    if ( is_plugin_active_for_network( 'userswp/userswp.php' ) ) {
        $main_site = get_network()->site_id;
        switch_to_blog( $main_site );
        remove_filter( 'upload_dir', 'uwp_handle_multisite_profile_image');
        $uploads = wp_upload_dir();
        restore_current_blog();
    }

    return $uploads;
}

/**
 * let_to_num function.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @since 2.0.0
 * @param $size
 * @return int
 */
function uwp_let_to_num( $size ) {
    $l   = substr( $size, -1 );
    $ret = substr( $size, 0, -1 );
    switch ( strtoupper( $l ) ) {
        case 'P':
            $ret *= 1024;
        case 'T':
            $ret *= 1024;
        case 'G':
            $ret *= 1024;
        case 'M':
            $ret *= 1024;
        case 'K':
            $ret *= 1024;
    }
    return $ret;
}

function uwp_format_decimal($number, $dp = false, $trim_zeros = false){
    $locale   = localeconv();
    $decimals = array( uwp_get_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

    // Remove locale from string.
    if ( ! is_float( $number ) ) {
        $number = str_replace( $decimals, '.', $number );
        $number = preg_replace( '/[^0-9\.,-]/', '', uwp_clean( $number ) );
    }

    if ( false !== $dp ) {
        $dp     = intval( '' == $dp ? uwp_get_decimal_separator() : $dp );
        $number = number_format( floatval( $number ), $dp, '.', '' );
        // DP is false - don't use number format, just return a string in our format
    } elseif ( is_float( $number ) ) {
        // DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
        $number     = str_replace( $decimals, '.', sprintf( '%.' . uwp_get_rounding_precision() . 'f', $number ) );
        // We already had a float, so trailing zeros are not needed.
        $trim_zeros = true;
    }

    if ( $trim_zeros && strstr( $number, '.' ) ) {
        $number = rtrim( rtrim( $number, '0' ), '.' );
    }

    return $number;
}

/**
 * Return the decimal separator.
 * @since  1.0.20
 * @return string
 */
function uwp_get_decimal_separator() {
    $separator = apply_filters( 'uwp_decimal_separator', '.' );
    return $separator ? stripslashes( $separator ) : '.';
}

/**
 * Get rounding precision for internal UWP calculations.
 * Will increase the precision of uwp_get_decimal_separator by 2 decimals, unless UWP_ROUNDING_PRECISION is set to a higher number.
 *
 * @since 1.0.20
 * @return int
 */
function uwp_get_rounding_precision() {
    $precision = uwp_get_decimal_separator() + 2;
    if ( absint( UWP_ROUNDING_PRECISION ) > $precision ) {
        $precision = absint( UWP_ROUNDING_PRECISION );
    }
    return $precision;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 *
 * @return string|array
 */
function uwp_clean( $var ) {

    if ( is_array( $var ) ) {
        return array_map( 'uwp_clean', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }

}

/**
 * Define a constant if it is not already defined.
 *
 * @since 1.0.21
 *
 * @param string $name Constant name.
 * @param string $value Value.
 */
function uwp_maybe_define( $name, $value ) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}

function uwp_insert_usermeta(){
    global $wpdb;
    $sort= "user_registered";

    $all_users_id = $wpdb->get_col( $wpdb->prepare(
        "SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC"
        , $sort ));

    //we got all the IDs, now loop through them to get individual IDs
    foreach ( $all_users_id as $user_id ) {
        $user_data = get_userdata($user_id);

        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';
        $user_meta = array(
            'username' => $user_data->user_login,
            'email' => $user_data->user_email,
            'first_name' => $user_data->first_name,
            'last_name' => $user_data->last_name,
            'display_name' => $user_data->display_name,
        );

        $users = $wpdb->get_var($wpdb->prepare("SELECT COUNT(user_id) FROM {$meta_table} WHERE user_id = %d", $user_id));

        if(!empty($users)) {
            $wpdb->update(
                $meta_table,
                $user_meta,
                array('user_id' => $user_id)
            );
        }  else {
            $user_meta['user_id'] = $user_id;
            $wpdb->insert(
                $meta_table,
                $user_meta
            );
        }
    }
}

function uwp_get_localize_data(){
    $uwp_localize_data = array(
        'uwp_more_char_limit' => '100',
        'uwp_more_text' => __('more','userswp'),
        'uwp_less_text' => __('less','userswp'),
        'uwp_more_ellipses_text' => '...',
        'ajaxurl' => admin_url('admin-ajax.php'),
        'login_modal' => uwp_get_option("design_style",'bootstrap')=='bootstrap' && uwp_get_option("login_modal",1) ? 1 : '',
        'register_modal' => uwp_get_option("design_style",'bootstrap')=='bootstrap' && uwp_get_option("register_modal",1) ? 1 : '',
        'forgot_modal' => uwp_get_option("design_style",'bootstrap')=='bootstrap' && uwp_get_option("forgot_modal",1) ? 1 : '',
    );

    return apply_filters('uwp_localize_data', $uwp_localize_data);
}

function uwp_is_page_builder(){
    if( isset($_GET['elementor-preview']) && $_GET['elementor-preview'] > 0){
        return true; // Elementor builder.
    }

    return false;
}

/**
 * Display a help tip for settings.
 *
 * @param  string $tip Help tip text
 * @param  bool $allow_html Allow sanitized HTML if true or escape
 *
 * @return string
 */
function uwp_help_tip( $tip, $allow_html = false ) {
    if ( $allow_html ) {
        $tip = uwp_sanitize_tooltip( $tip );
    } else {
        $tip = esc_attr( $tip );
    }

    return '<span class="uwp-help-tip dashicons dashicons-editor-help" title="' . $tip . '"></span>';
}

/**
 * Sanitize a string destined to be a tooltip.
 *
 * Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
 *
 * @param string $var
 * @return string
 */
function uwp_sanitize_tooltip( $var ) {
    return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
        'br'     => array(),
        'em'     => array(),
        'strong' => array(),
        'small'  => array(),
        'span'   => array(),
        'ul'     => array(),
        'li'     => array(),
        'ol'     => array(),
        'p'      => array(),
    ) ) );
}

function uwp_all_email_tags( $inline = true ){
    $tags = array( '[#site_name#]', '[#site_name_url#]', '[#current_date#]', '[#to_name#]', '[#from_name#]', '[#from_email#]', '[#user_name#]', '[#username#]', '[#login_details#]', '[#date_time#]', '[#current_date#]', '[#login_url#]', '[#user_login#]', '[#profile_link#]' );

    $tags = apply_filters( 'uwp_all_email_tags', $tags );

    if ( $inline ) {
        $tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
    }

    return $tags;
}

function uwp_authbox_tags( $inline = true ){
    global $wpdb;

    $tags = array( '[#post_id#]', '[#author_id#]', '[#author_name#]', '[#author_link#]', '[#author_bio#]', '[#author_image#]', '[#author_image_url#]', '[#post_modified#]', '[#post_date#]', '[#author_nicename#]', '[#author_registered#]', '[#author_website#]' );

    $tags = apply_filters('uwp_author_box_default_tags', $tags, $inline);

    $table_name = uwp_get_table_prefix() . 'uwp_usermeta';

    $excluded = uwp_get_excluded_fields();

    $columns = $wpdb->get_col("show columns from $table_name");

    $extra_tags = array_diff($columns,$excluded);

    if( !empty( $extra_tags ) && '' != $extra_tags ) {

        foreach ( $extra_tags as $tag_val ) {
            $tags[] = '[#'.$tag_val.'#]';
        }

    }

    $tags = apply_filters( 'uwp_all_author_box_tags', $tags );

    if ( $inline ) {
        $tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
    }

    return $tags;
}

function uwp_get_posttypes() {

    $exclude_posts = array('attachment','revision','nav_menu_item','custom_css');
    $exclude_posttype = apply_filters('uwp_exclude_register_posttype', $exclude_posts);

    $all_posttyps = get_post_types(array('public'   => true,),'objects');

    $display_posttypes = array();

    if( !empty( $all_posttyps ) && '' != $all_posttyps ) {
        foreach ( $all_posttyps as $pt_keys => $pt_values ) {

            if( !in_array($pt_values->name,$exclude_posttype) ) {
                $display_posttypes[$pt_values->name] = $pt_values->label;
            }

        }
    }

    return $display_posttypes;
}

/**
 * Get the field icon.
 *
 * @since       1.0.12
 * @package     userswp
 *
 * @param       string      Field icon value.
 * @return      string       Field icon element.
 */
function uwp_get_field_icon( $value ) {
    $field_icon = $value;

    if ( ! empty( $value ) ) {
        if (strpos($value, 'http') === 0) {
            $field_icon = '<span class="uwp_field_icon" style="background: url(' . $value . ') no-repeat left center;padding-left:14px;background-size:100% auto;margin-right:5px"></span>';
        } else {
            $field_icon = '<i class="uwp_field_icon ' . $value . '"></i>';
        }
    }

    return apply_filters( 'uwp_get_field_icon', $field_icon, $value );
}

function uwp_get_user_by_author_slug(){
    $url_type = apply_filters('uwp_profile_url_type', 'slug');
    $author_slug = get_query_var('uwp_profile');
    if ($url_type == 'id') {
        $user = get_user_by('id', $author_slug);
    } else {
        $user = get_user_by('slug', $author_slug);
    }

    return $user;
}

function uwp_get_show_in_locations(){
    $show_in_locations = array(
        "[users]" => __("Users Page", 'userswp'),
        "[more_info]" => __("More info tab", 'userswp'),
        "[profile_side]" => __("Profile side (non bootstrap)", 'userswp'),
        "[fieldset]" => __("Fieldset", 'userswp'),
    );

    $show_in_locations = apply_filters('uwp_show_in_locations', $show_in_locations);

    return $show_in_locations;
}

function uwp_get_layout_options(){
    $layouts = array(
        'list' => __( 'List View', 'userswp' ),
        '2col' => __( 'Grid View - 2 Column', 'userswp' ),
        '3col' => __( 'Grid View - 3 Column', 'userswp' ),
        '4col' => __( 'Grid View - 4 Column', 'userswp' ),
        '5col' => __( 'Grid View - 5 Column', 'userswp' ),
    );

    $layouts = apply_filters('uwp_available_users_layout', $layouts);

    return $layouts;
}

function uwp_locate_template($template, $template_path = "" ){

    $temp_obj = new UsersWP_Templates();

    $template_file = $temp_obj->locate_template($template, $template_path );

    if (file_exists($template_file)) {
        include($template_file);
    }

}

function uwp_no_users_found(){
    uwp_locate_template( 'no-users-found' );
}

function uwp_get_displayed_user(){
    global $uwp_user;
    $user = uwp_get_user_by_author_slug(); // for user displayed in profile

    if(!$user && is_user_logged_in()){
        $user = get_userdata(get_current_user_id()); // for user currently logged in
    }

    if(isset($uwp_user) && !empty($uwp_user) && $uwp_user instanceof WP_User){ // for user displaying in loop
        $user = $uwp_user;
    }

    return apply_filters('uwp_get_displayed_user', $user);
}

/**
 * Check font awesome icon or not.
 *
 * @param string $icon Font awesome icon.
 * @return bool True if font awesome icon.
 */
function uwp_is_fa_icon( $icon ) {
	$return = false;
	if ( $icon != '' ) {
		$fa_icon = trim( $icon );
		if ( strpos( $fa_icon, 'fa fa-' ) === 0 || strpos( $fa_icon, 'fas fa-' ) === 0 || strpos( $fa_icon, 'far fa-' ) === 0 || strpos( $fa_icon, 'fab fa-' ) === 0 || strpos( $fa_icon, 'fa-' ) === 0 ) {
			$return = true;
		}
	}
	return apply_filters( 'uwp_is_fa_icon', $return, $icon  );
}

/**
 * Check icon url.
 *
 * @param string $icon Icon url.
 * @return bool True if icon url.
 */
function uwp_is_icon_url( $icon ) {
	$return = false;
	if ( $icon != '' ) {
		$icon = trim( $icon );
		if ( strpos( $icon, 'http://' ) === 0 || strpos( $icon, 'https://' ) === 0 ) {
			$return = true;
		}
	}
	return apply_filters( 'uwp_is_icon_url', $return, $icon  );
}

function uwp_is_gdv2(){

	if(defined('GEODIRECTORY_VERSION') && version_compare(GEODIRECTORY_VERSION,'2.0.0.0', '>=') ) {
		return true;
	}

	return false;
}