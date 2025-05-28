<div class="swiper <?php echo $instance_id; ?>">
    <div class="swiper-wrapper woocommerce-product-gallery__wrapper">
        <?php foreach($images as $index => $image): ?>
        <div class="swiper-slide woocommerce-product-gallery__image">
            <a href="<?php echo $image['src']; ?>" >
                <img src="<?php echo $image['src']; ?>" alt="<?php echo $image['alt']; ?>" />
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>
