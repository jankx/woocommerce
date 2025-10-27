# WooCommerce Content Generator

WooCommerce Content Generator tự động áp dụng cho Post Type Layout block khi post type là `product`.

## Tính năng

- **Tự động detect**: Khi post type là `product`, WooCommerce generator tự động được áp dụng
- **Sử dụng WC template**: Sử dụng WooCommerce templates (`content-product.php`) để đảm bảo tương thích 100%
- **WC hooks và filters**: Tất cả WC hooks và filters hoạt động bình thường
- **Responsive columns**: Hỗ trợ responsive columns thông qua `loop_shop_columns` filter

## Sử dụng

### Tự động (mặc định)

Khi tạo Post Type Layout block với `postType = 'product'`, WooCommerce generator tự động được áp dụng:

```php
// Trong PostTypeLayoutBlock
$attributes['postType'] = 'product';
$decorator = $layoutManager->createLayout('grid', $attributes);
// WooCommerce generator tự động được áp dụng
```

### Thủ công

```php
use Jankx\Layouts\PostLayout\Generators\WooCommerceContentGenerator;
use Jankx\Layouts\PostLayout\Supports\GridLayout;

$layout = new GridLayout();
$layout->setContentGenerator(new WooCommerceContentGenerator());

// Set options
$layout->setOptions([
    'columns' => 4,
    'showPrice' => true,
    'showRating' => true,
    'showAddToCart' => true,
    'showSaleBadge' => true,
]);

// Build query và render
$query = new WP_Query(['post_type' => 'product', 'posts_per_page' => 12]);
$layout->setQuery($query);
echo $layout->render();
```

## Options hỗ trợ

| Option | Type | Default | Mô tả |
|--------|------|---------|-------|
| `columns` | int | 3 | Số cột |
| `showPrice` | bool | true | Hiển thị giá |
| `showRating` | bool | true | Hiển thị rating |
| `showAddToCart` | bool | true | Hiển thị nút add to cart |
| `showSaleBadge` | bool | true | Hiển thị badge giảm giá |
| `postsPerPage` | int | 10 | Số sản phẩm mỗi page |
| `imageSize` | string | 'woocommerce_thumbnail' | Kích thước hình ảnh |

## Tương thích WooCommerce

Generator sử dụng:
- `wc_get_template_part('content', 'product')` để load WC template
- `wc_setup_product_data()` để setup product data
- `loop_shop_columns` filter để control columns
- Tất cả WC hooks và filters

## Customize

Để customize template, tạo file trong theme:
- `woocommerce/content-product.php` - Product item template

## Lưu ý

1. **WooCommerce phải active**: Generator chỉ hoạt động khi WooCommerce đang active
2. **Post type = 'product'**: Chỉ áp dụng cho product post type
3. **WC Templates**: Sử dụng WC template system để đảm bảo tương thích
4. **Global variables**: Tự động restore `$wp_query` và `$product` sau render

## Ví dụ trong Gutenberg Block

```jsx
// Block attributes
const attributes = {
    postType: 'product',
    columns: 4,
    showPrice: true,
    showRating: true,
    showAddToCart: true,
    showSaleBadge: true,
    postsPerPage: 12,
    // ... other attributes
};
```

Generator tự động áp dụng khi `postType = 'product'`.
