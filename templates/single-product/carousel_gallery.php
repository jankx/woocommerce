<?php
if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}
 ?>
<div class="swiper <?php echo $instance_id; ?>">
    <div class="swiper-wrapper">
        <?php foreach ($images as $index => $image) : ?>
        <div <?php echo jankx_generate_html_attributes([
            'class' => $slide_classes
        ]); ?>>
            <img src="<?php echo $image['src']; ?>" alt="<?php echo $image['alt']; ?>" />
        </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>
