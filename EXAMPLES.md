# Jankx WooCommerce Layout System - Examples

## Example 1: Tạo Custom Product Detail Layout

### Step 1: Tạo Layout Class

```php
<?php
// file: themes/my-theme/includes/woocommerce/MyProductDetailLayout.php

namespace MyTheme\WooCommerce;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductDetailLayout;
use WC_Product;

class MyProductDetailLayout extends AbstractProductDetailLayout
{
    public function __construct()
    {
        parent::__construct(
            'my-product-detail',
            __('My Custom Product Detail', 'my-theme'),
            'product-detail'
        );
        
        // Set layout structure
        $this->layoutStructure = 'sidebar-left';
        $this->supportsStickyAddToCart = true;
        
        // Supported settings
        $this->supportedSettings = [
            'gallery_width',
            'primary_color',
            'show_related',
            'related_columns',
        ];
    }
    
    // Custom gallery với vertical thumbnails
    public function renderGallery(WC_Product $product): string
    {
        ob_start();
        ?>
        <div class="custom-gallery vertical-thumbnails">
            <div class="main-image">
                <?php echo $product->get_image('large'); ?>
            </div>
            <div class="thumbnails-sidebar">
                <?php
                $attachment_ids = $product->get_gallery_image_ids();
                foreach ($attachment_ids as $attachment_id) {
                    echo wp_get_attachment_image($attachment_id, 'thumbnail');
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // Override dynamic CSS
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);
        
        // Custom CSS based on settings
        if (isset($settings['related_columns'])) {
            $columns = intval($settings['related_columns']);
            $css .= "
                .related.products ul.products {
                    grid-template-columns: repeat({$columns}, 1fr);
                }
            ";
        }
        
        return $css;
    }
}
```

### Step 2: Tạo SCSS File

```scss
// file: themes/my-theme/jankx-woocommerce-scss/my-product-detail.scss

@import '../node_modules/jankx-woocommerce/assets/scss/layouts/common';

.product-detail-my-product-detail {
    display: flex;
    gap: $spacing-xl;
    
    .custom-gallery {
        flex: 1;
        display: flex;
        gap: $spacing-md;
        
        .thumbnails-sidebar {
            display: flex;
            flex-direction: column;
            gap: $spacing-sm;
            max-width: 100px;
            
            img {
                cursor: pointer;
                border: 2px solid transparent;
                transition: border-color 0.3s;
                
                &:hover {
                    border-color: $primary-color;
                }
            }
        }
    }
    
    .product-summary {
        flex: 1;
    }
    
    @include tablet {
        flex-direction: column;
        
        .custom-gallery {
            flex-direction: column-reverse;
            
            .thumbnails-sidebar {
                flex-direction: row;
                max-width: 100%;
            }
        }
    }
}
```

### Step 3: Đăng ký Layout

```php
// file: themes/my-theme/functions.php

add_action('jankx_woocommerce_register_layouts', function($manager) {
    require_once get_template_directory() . '/includes/woocommerce/MyProductDetailLayout.php';
    $manager->register(new MyTheme\WooCommerce\MyProductDetailLayout());
});

// Set làm default cho tất cả products
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if ($type === 'product-detail') {
        $manager = \Jankx\WooCommerce\LayoutSystem\LayoutManager::getInstance();
        return $manager->get('my-product-detail');
    }
    return $layout;
}, 10, 2);
```

### Step 4: Add Settings

```php
// file: themes/my-theme/includes/customizer.php

add_action('customize_register', function($wp_customize) {
    $wp_customize->add_section('my_product_layout', [
        'title' => __('Product Layout', 'my-theme'),
        'priority' => 30,
    ]);
    
    $wp_customize->add_setting('my_product_detail_related_columns', [
        'default' => 4,
        'sanitize_callback' => 'absint',
    ]);
    
    $wp_customize->add_control('my_product_detail_related_columns', [
        'label' => __('Related Products Columns', 'my-theme'),
        'section' => 'my_product_layout',
        'type' => 'number',
    ]);
});

// Save to SettingsManager
add_action('customize_save_after', function() {
    $settings = \Jankx\WooCommerce\LayoutSystem\SettingsManager::getInstance();
    
    $settings->setLayoutSettings('my-product-detail', [
        'related_columns' => get_theme_mod('my_product_detail_related_columns', 4),
    ]);
});
```

---

## Example 2: Product Loop với Multiple Display Modes

```php
<?php
// file: themes/my-theme/includes/woocommerce/FlexibleProductLoop.php

class FlexibleProductLoop extends \Jankx\WooCommerce\Abstracts\Layouts\AbstractProductLoopLayout
{
    private $displayMode = 'grid'; // grid, list, masonry
    
    public function __construct()
    {
        parent::__construct(
            'flexible-product-loop',
            __('Flexible Product Loop', 'my-theme'),
            'product-loop'
        );
        
        $this->supportedSettings = [
            'display_mode',
            'columns',
            'show_excerpt',
            'hover_effect',
        ];
    }
    
    public function render(array $data = []): string
    {
        $this->displayMode = $data['options']['display_mode'] ?? 'grid';
        return parent::render($data);
    }
    
    public function renderProduct(WC_Product $product, array $options = []): string
    {
        $template = $this->getTemplateForMode($this->displayMode);
        
        return $this->loadTemplate($template, [
            'product' => $product,
            'layout' => $this,
            'mode' => $this->displayMode,
            'thumbnail' => $this->renderThumbnail($product),
            'title' => $this->renderTitle($product),
            'price' => $this->renderPrice($product),
            'excerpt' => $this->renderExcerpt($product),
        ]);
    }
    
    private function getTemplateForMode($mode)
    {
        $templates = [
            'grid' => 'product-grid.php',
            'list' => 'product-list.php',
            'masonry' => 'product-masonry.php',
        ];
        
        return get_template_directory() . '/woocommerce/loop/' . $templates[$mode];
    }
    
    private function renderExcerpt(WC_Product $product): string
    {
        if ($this->displayMode !== 'list') {
            return '';
        }
        
        $excerpt = $product->get_short_description();
        return $excerpt ? '<p class="product-excerpt">' . wp_trim_words($excerpt, 20) . '</p>' : '';
    }
    
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);
        
        $mode = $settings['display_mode'] ?? 'grid';
        
        if ($mode === 'masonry') {
            $css .= "
                .jankx-products-loop.flexible-product-loop {
                    column-count: {$settings['columns']};
                    column-gap: 20px;
                }
                .product-item {
                    break-inside: avoid;
                    margin-bottom: 20px;
                }
            ";
        }
        
        return $css;
    }
}
```

---

## Example 3: Quick Checkout với One-Click Buy

```php
<?php
// file: plugins/my-plugin/includes/OneClickCheckout.php

class OneClickCheckoutLayout extends \Jankx\WooCommerce\Abstracts\Layouts\AbstractQuickCheckoutLayout
{
    public function __construct()
    {
        parent::__construct(
            'one-click-checkout',
            __('One-Click Checkout', 'my-plugin'),
            'quick-checkout'
        );
        
        $this->supportsOneClick = true;
        $this->supportsSavedAddresses = true;
        $this->requiredFields = ['billing_phone', 'billing_email'];
    }
    
    public function renderQuickCheckout(array $options = []): string
    {
        // Check if user logged in và có saved address
        if (is_user_logged_in() && $this->hasSavedAddress()) {
            return $this->renderOneClickButton();
        }
        
        return parent::renderQuickCheckout($options);
    }
    
    private function renderOneClickButton(): string
    {
        ob_start();
        ?>
        <button class="one-click-buy" data-product-id="<?php echo get_the_ID(); ?>">
            <?php _e('Buy Now - One Click', 'my-plugin'); ?>
        </button>
        <script>
        jQuery('.one-click-buy').on('click', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'one_click_checkout',
                    product_id: productId,
                    nonce: '<?php echo wp_create_nonce('one-click'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect;
                    }
                }
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    private function hasSavedAddress(): bool
    {
        $user_id = get_current_user_id();
        return !empty(get_user_meta($user_id, 'billing_address_1', true));
    }
}

// AJAX handler
add_action('wp_ajax_one_click_checkout', function() {
    check_ajax_referer('one-click', 'nonce');
    
    $product_id = intval($_POST['product_id']);
    
    // Add to cart
    WC()->cart->empty_cart();
    WC()->cart->add_to_cart($product_id);
    
    // Create order with saved address
    $order = wc_create_order();
    $order->set_customer_id(get_current_user_id());
    
    // Use saved billing address
    $user_id = get_current_user_id();
    $order->set_billing_address([
        'first_name' => get_user_meta($user_id, 'billing_first_name', true),
        'last_name' => get_user_meta($user_id, 'billing_last_name', true),
        'address_1' => get_user_meta($user_id, 'billing_address_1', true),
        'city' => get_user_meta($user_id, 'billing_city', true),
        'phone' => get_user_meta($user_id, 'billing_phone', true),
        'email' => get_user_meta($user_id, 'billing_email', true),
    ]);
    
    // Add items from cart
    foreach (WC()->cart->get_cart() as $cart_item) {
        $order->add_product($cart_item['data'], $cart_item['quantity']);
    }
    
    $order->calculate_totals();
    $order->save();
    
    wp_send_json_success([
        'redirect' => $order->get_checkout_payment_url()
    ]);
});
```

---

## Example 4: A/B Testing Layouts

```php
<?php
// file: plugins/ab-testing/includes/layout-ab-test.php

class LayoutABTest
{
    private $variants = [
        'control' => 'grid-product-loop',
        'variant_a' => 'list-product-loop',
        'variant_b' => 'custom-grid-loop',
    ];
    
    public function __construct()
    {
        add_filter('jankx_woocommerce_current_layout', [$this, 'selectVariant'], 10, 2);
        add_action('wp_footer', [$this, 'trackConversion']);
    }
    
    public function selectVariant($layout, $type)
    {
        if ($type !== 'product-loop') {
            return $layout;
        }
        
        // Get or assign variant
        $variant = $this->getUserVariant();
        $layoutId = $this->variants[$variant];
        
        $manager = \Jankx\WooCommerce\LayoutSystem\LayoutManager::getInstance();
        $selectedLayout = $manager->get($layoutId);
        
        // Track impression
        $this->trackImpression($variant);
        
        return $selectedLayout ?: $layout;
    }
    
    private function getUserVariant(): string
    {
        if (isset($_COOKIE['layout_variant'])) {
            return $_COOKIE['layout_variant'];
        }
        
        // Random assignment
        $variants = array_keys($this->variants);
        $variant = $variants[array_rand($variants)];
        
        // Store in cookie
        setcookie('layout_variant', $variant, time() + 30 * DAY_IN_SECONDS, '/');
        
        return $variant;
    }
    
    private function trackImpression($variant): void
    {
        // Track to analytics
        $impressions = get_option('layout_ab_impressions', []);
        $impressions[$variant] = ($impressions[$variant] ?? 0) + 1;
        update_option('layout_ab_impressions', $impressions);
    }
    
    public function trackConversion(): void
    {
        if (!is_order_received_page()) {
            return;
        }
        
        if (!isset($_COOKIE['layout_variant'])) {
            return;
        }
        
        $variant = $_COOKIE['layout_variant'];
        
        // Track conversion
        $conversions = get_option('layout_ab_conversions', []);
        $conversions[$variant] = ($conversions[$variant] ?? 0) + 1;
        update_option('layout_ab_conversions', $conversions);
    }
}

new LayoutABTest();
```

---

## Example 5: Dynamic Layout Selection per Product

```php
<?php
// file: themes/my-theme/functions.php

// Add meta box để chọn layout cho từng product
add_action('add_meta_boxes', function() {
    add_meta_box(
        'product_layout_selector',
        __('Product Layout', 'my-theme'),
        'render_product_layout_metabox',
        'product',
        'side'
    );
});

function render_product_layout_metabox($post)
{
    $manager = \Jankx\WooCommerce\LayoutSystem\LayoutManager::getInstance();
    $layouts = $manager->getByType('product-detail');
    $selected = get_post_meta($post->ID, '_product_layout', true);
    
    wp_nonce_field('product_layout_nonce', 'product_layout_nonce');
    ?>
    <select name="product_layout" style="width:100%;">
        <option value=""><?php _e('Default', 'my-theme'); ?></option>
        <?php foreach ($layouts as $layout): ?>
            <option value="<?php echo esc_attr($layout->getId()); ?>" 
                    <?php selected($selected, $layout->getId()); ?>>
                <?php echo esc_html($layout->getName()); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

// Save meta
add_action('save_post_product', function($post_id) {
    if (!isset($_POST['product_layout_nonce'])) {
        return;
    }
    
    if (!wp_verify_nonce($_POST['product_layout_nonce'], 'product_layout_nonce')) {
        return;
    }
    
    if (isset($_POST['product_layout'])) {
        update_post_meta($post_id, '_product_layout', sanitize_text_field($_POST['product_layout']));
    }
});

// Apply selected layout
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if ($type === 'product-detail' && is_product()) {
        $custom_layout = get_post_meta(get_the_ID(), '_product_layout', true);
        
        if ($custom_layout) {
            $manager = \Jankx\WooCommerce\LayoutSystem\LayoutManager::getInstance();
            $selected = $manager->get($custom_layout);
            
            if ($selected) {
                return $selected;
            }
        }
    }
    
    return $layout;
}, 10, 2);
```

Các examples này minh họa cách sử dụng Layout System trong các scenarios thực tế khác nhau.

