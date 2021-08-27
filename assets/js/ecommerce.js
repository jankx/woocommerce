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
            arrows: false,
            infinite: true,
            slidesToShow: 4,
            slidesToScroll: 4
        });
    }
})(jQuery);

/**
 *
 * @param {MouseEvent} e
 */
function jankxEcommerceGalleryItemClickEvent(e) {
    e.preventDefault();
    if (!window.woocommerceGallery) {
        return;
    }
    var galleryItem = e.target;
    var index = galleryItem.dataset.lightboxIndex ? galleryItem.dataset.lightboxIndex : 0;
    return window.woocommerceGallery.open(index);
}

var woocommerceGalleryImages = document.querySelectorAll('.woocommerce-product-gallery__image img');
if (woocommerceGalleryImages.length > 0) {
    var imageSources       = [];
    window.woocommerceGallery = new FsLightbox();
    for(i=0; i<woocommerceGalleryImages.length; i++) {
        var image = woocommerceGalleryImages[i];
        var galleryItem = (image.parentElement && image.parentElement.tagName === 'A') ? image.parentElement : image;
        var src = galleryItem.getAttribute('href')
            ? galleryItem.getAttribute('href')
            : (galleryItem.dataset.src ? galleryItem.dataset.src : galleryItem.getAttribute('src'));
        imageSources.push(src);

        galleryItem.setAttribute('data-lightbox-index', i);
        galleryItem.addEventListener('click', jankxEcommerceGalleryItemClickEvent);
    }
    woocommerceGallery.props.sources = imageSources;
}
