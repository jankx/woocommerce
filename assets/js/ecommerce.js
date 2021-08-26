/**
 * Jankx Ecommerce
 *
 * @author: Puleeno Nguyen <puleeno@gmail.com>
 * @license https://github.com/jankx/ecommerce/blob/master/license.txt
 */

(function($){
    if ($('.jankx-ecom-product-thumbnails').length > 0) {
        $('.jankx-ecom-product-thumbnails').slick({
            dots: false,
            infinite: true,
            slidesToShow: 4,
            slidesToScroll: 4
        });
    }
})(jQuery);
