<aside id="jankx-shop-sidebar" class="sidebar shop">
    <?php
    if (is_active_sidebar('shop')) {
        dynamic_sidebar('shop');
    } elseif(current_user_can('edit_theme_options')) {
        printf(
            __('Please add the widgets to this sidebar at <a href="%s">Widget Dashboard</a>', 'jankx'),
            admin_url('widgets.php')
        );
    }
    ?>
</aside>