/**
 * Jankx Ecommerce
 *
 * @author: Puleeno Nguyen <puleeno@gmail.com>
 * @license https://github.com/jankx/ecommerce/blob/master/license.txt
 */

(function ($) {
    var woocommerceGalleryImages = document.querySelectorAll('.woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image img');
    if (woocommerceGalleryImages.length > 0) {
        console.log(woocommerceGalleryImages.length);
        var imageSources       = [];
        window.woocommerceGallery = new FsLightbox();
        for (i=0; i<woocommerceGalleryImages.length; i++) {
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
