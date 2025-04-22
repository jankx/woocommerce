/**
 * Jankx WooCommerce
 *
 * @author: Puleeno Nguyen <puleeno@gmail.com>
 * @license https://github.com/jankx/ecommerce/blob/master/license.txt
 */

(function ($) {
    var woocommerceGalleryImages = document.querySelectorAll('.woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img');
    if (woocommerceGalleryImages.length > 0) {
        var imageSources       = [];
        window.woocommerceGallery = new FsLightbox();
        for (i = 0; i < woocommerceGalleryImages.length; i++) {
            var image = woocommerceGalleryImages[i];
            var galleryItem = (image.parentElement && image.parentElement.tagName === 'A') ? image.parentElement : image;
            var src = galleryItem.getAttribute('href')
                ? galleryItem.getAttribute('href')
                : (galleryItem.dataset.src ? galleryItem.dataset.src : galleryItem.getAttribute('src'));
            imageSources.push(src);

            galleryItem.setAttribute('data-lightbox-index', i);
            galleryItem.addEventListener('click', function (e) {
                e.preventDefault();

                var clickedItem = e.target;
                var target = galleryItem.tagName === 'A' ? clickedItem.findParent('a') : clickedItem;
                var index = target.dataset.lightboxIndex ? parseInt(target.dataset.lightboxIndex) : 0;

                return window.woocommerceGallery.open(index);
            });
        }
        woocommerceGallery.props.sources = imageSources;
    }
})(jQuery);


/**
 *
 * @param {MouseEvent} e
 */
function jankx_woocommerce_quantity_control_event(e) {
    /**
     * @var {HtmlElement} elemt
     */
    const elemt = e.target;
    const qty = elemt.parentElement.querySelector('.qty');
    let qtyValue = parseInt(qty.value);

    if (elemt.classList.contains('decrease')) {
        qtyValue -= 1;
    }
    if (elemt.classList.contains('increase')) {
        qtyValue += 1;
    }
    if (qtyValue <= 0) {
        qtyValue = 1;
    }
    qty.value = qtyValue;
    qty.focus();
}

document.addEventListener("DOMContentLoaded", function() {
    const controls = document.querySelectorAll('.quantity-control');
    if (controls.length > 0) {
        controls.forEach(function(element){
            element.addEventListener('click', jankx_woocommerce_quantity_control_event);
        });
    }
});
