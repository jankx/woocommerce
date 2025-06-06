<aside id="jankx-shop-sidebar" class="sidebar shop">
    <?php do_action('jankx_woocommerce_before_shop_sidebar'); ?>
    <?php
    if (is_active_sidebar('shop')) {
        dynamic_sidebar('shop');
    } elseif (current_user_can('edit_theme_options')) {
        printf(
            __('Please add the widgets to this sidebar at <a href="%s">Widget Dashboard</a>. Only you see this message because you are the moderator.', 'jankx'),
            admin_url('widgets.php')
        );
    }
    ?>
</aside>
