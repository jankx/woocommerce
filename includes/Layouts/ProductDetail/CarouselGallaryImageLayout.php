<?php

namespace Jankx\WooCommerce\Layouts\ProductDetail;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

use Jankx\WooCommerce\Abstracts\ProductDetail\ProductGalleryLayoutAbstract;
use Jankx\WooCommerce\WooCommerceTemplate;
use WC_Product;

class CarouselGallaryImageLayout extends ProductGalleryLayoutAbstract
{
    const NAME = 'carousel';

    const INSTANCE_ID = 'jankx-woocommerce-gallery';

    public function init()
    {
        add_filter('woocommerce_single_product_image_thumbnail_html', [$this, 'changeProductImageToGalleryImages'], 10, 2);
        add_action('woocommerce_before_template_part', [$this, 'appendMainImageToListThumbnail']);
        add_action('woocommerce_after_template_part', [$this, 'removeMainImageToListThumbnail']);
        add_action('wp_enqueue_scripts', [$this, 'registerCarouselScripts'], 999);
        add_filter('woocommerce_post_class', [$this, 'appendLayoutToPostClass']);
        add_filter('jankx/woocommerce/single/gallery/thumbnail/wrap', function ($classes) {
            $classes[] = 'swiper-wrapper';
            return $classes;
        });
    }

    public function changeProductImageToGalleryImages($html, $thumbnail_id)
    {
        global $product;

        if ($thumbnail_id !== $product->get_image_id()) {
            if (apply_filters('jankx/woocommerce/product/gallery/carousel/enable_thumbnail', true)) {
                $html = str_replace('woocommerce-product-gallery__image', 'swiper-slide', $html);
                $html = strip_tags($html, ['img', 'div']);
            }
            return $html;
        }

        $image_ids = array_merge([intval($product->get_image_id())], $product->get_gallery_image_ids());
        $image_ids = array_unique($image_ids);
        if (count($image_ids) <= 1) {
            return $html;
        }
        $thumbnailSize = apply_filters('jankx/woocommerce/product/gallery/image_size', 'full', $product);

        $images = [];
        foreach ($image_ids as $image_id) {
            $images[] = [
                'id' => $image_id,
                'src' => wp_get_attachment_image_url($image_id, $thumbnailSize),
                'alt' => $product->get_title()
            ];
        }

        $slide_classes = ['swiper-slide'];
        if (apply_filters('jankx/woocommerce/product/gallery/lightbox/enabled', true)) {
            $slide_classes[] = 'woocommerce-product-gallery__image';
        }
        return WooCommerceTemplate::render('single-product/carousel_gallery', [
            'images' => $images,
            'instance_id' => static::INSTANCE_ID,
            'slide_classes' => $slide_classes
        ], false);
    }


    public function appendImageToThumbnail($ids)
    {
        global $product;

        if ($product instanceof WC_Product && $product->get_image_id() > 0) {
            return array_merge([intval($product->get_image_id())], $ids);
        }

        return $ids;
    }


    public function appendMainImageToListThumbnail($templateName)
    {
        if ($templateName !== 'single-product/product-thumbnails.php') {
            return;
        }
        add_filter('woocommerce_product_get_gallery_image_ids', [$this, 'appendImageToThumbnail']);
    }

    public function removeMainImageToListThumbnail($templateName)
    {
        if ($templateName !== 'single-product/product-thumbnails.php') {
            return;
        }
        remove_filter('woocommerce_product_get_gallery_image_ids', [$this, 'appendImageToThumbnail']);
    }
    public function registerCarouselScripts()
    {
        $thumbnailOptions = apply_filters('jankx/woocommerce/product/gallery/thumbnails/options', [
            'spaceBetween' => 10,
            'slidesPerView' => 5,
            'freeMode' => true,
            'watchSlidesProgress' => true,
            'breakpoints' => [
                '640' => [
                  'slidesPerView' => 3,
                  'spaceBetween' => 20,
                ],
                '768' => [
                  'slidesPerView' => 4,
                  'spaceBetween' => 40,
                ],
                '1024' => [
                  'slidesPerView' => 5,
                  'spaceBetween' => 50,
                ],
            ]
        ]);
        $galleryOptions = apply_filters('jankx/woocommerce/product/gallery/carousel/options', [
            'spaceBetween' => 10,
            'navigation' => [
                'nextEl' => ".swiper-button-next",
                'prevEl' => ".swiper-button-prev",
            ],
            'autoHeight' => false,
            'observer' => true,
            'observeParents' => true,
        ]);
        ob_start();
        ?>
        <script>
            const thumbnailOptions = <?php echo json_encode($thumbnailOptions); ?>;
            const thumbnails = new Swiper(".jankx-ecom-product-thumbnails", thumbnailOptions);

            const galleryOptions = <?php echo json_encode($galleryOptions); ?>;
            if (typeof galleryOptions.thumbs ==='undefined') {
                galleryOptions.thumbs= {};
            }
            galleryOptions.thumbs.swiper = thumbnails;
            const productGallery = new Swiper(".jankx-woocommerce-gallery", galleryOptions);
        </script>
        <?php
        execute_script(ob_get_clean());
    }

    public function appendLayoutToPostClass($classes)
    {
        $classes[] = 'jankx-carousel-product-images';


        return $classes;
    }
}
