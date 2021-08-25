/**
 * Jankx Ecommerce
 *
 * @author: Puleeno Nguyen <puleeno@gmail.com>
 * @license https://github.com/jankx/ecommerce/blob/master/license.txt
 */


 var carousel = new Carousel({
    elem: '.woocommerce-product-gallery__wrapper',    // id of the carousel container
    autoplay: false,     // starts the rotation automatically
    infinite: true,      // enables the infinite mode
    interval: 1500,      // interval between slide changes
    initial: 0,          // slide to start with
    dots: true,          // show navigation dots
    arrows: true,        // show navigation arrows
    buttons: false,      // hide play/stop buttons,
    btnStopText: 'Pause' // STOP button text
});
