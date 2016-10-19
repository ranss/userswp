<?php do_action('uwp_template_before', 'login'); ?>
<div class="uwp-content-wrap">
    <div class="uwp-login">
        <div class="uwp-lf-icon"><i class="fa fa-user fa-fw"></i></div>
        <?php do_action('uwp_template_form_title_before', 'login'); ?>
        <h2><?php echo __( 'Sign In', 'uwp' ); ?></h2>
        <?php do_action('uwp_template_form_title_after', 'login'); ?>
        <form class="uwp-login-form" method="post">
            <?php do_action('uwp_template_fields', 'login'); ?>
            <input type="hidden" name="uwp_login_nonce" value="<?php echo wp_create_nonce( 'uwp-login-nonce' ); ?>" />
            <input name="uwp_login_submit" value="<?php echo __( 'Login', 'uwp' ); ?>" type="submit">
            <div class="uwp-remember-me">
                <label for="remember_me"><input name="remember_me" id="remember_me" value="forever" type="checkbox"> <?php echo __( 'Remember Me', 'uwp' ); ?></label>
            </div>
        </form>
        <div class="uwp-forgotpsw"><a href="<?php echo uwp_get_page_link('forgot'); ?>"><?php echo __( 'Forgot password?', 'uwp' ); ?></a></div>
        <div class="clfx"></div>
        <div class="uwp-register-now"><?php echo __( 'Not a Member?', 'uwp' ); ?> <a rel="nofollow" href="<?php echo uwp_get_page_link('register'); ?>"><?php echo __( 'Create Account', 'uwp' ); ?></a></div>
    </div>
</div>
<?php do_action('uwp_template_after', 'login'); ?>