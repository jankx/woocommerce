/**
 * Jankx Ecommerce
 *
 * @author: Puleeno Nguyen <puleeno@gmail.com>
 * @license https://github.com/jankx/ecommerce/blob/master/license.txt
 */

document.querySelectorAll('.category-tabs-products .tab a').forEach(function(tab) {
  tab.addEventListener('click', function(e){
    e.preventDefault();

    var wrap = e.target.parentElement.parentElement.parentElement;
    var contentwrap = wrap.querySelector('.jankx-tab-content');

    if (contentwrap === undefined) {
      return;
    }

    // Create XML HTTP to request Ajax
    var xmlhttp;

    if (window.XMLHttpRequest) {
       xmlhttp = new XMLHttpRequest();
     } else {
       xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    } 
    xmlhttp.open('POST', jankx_ecommerce.get_product_url, true);

    // Request to WordPress
    xmlhttp.send();

    // Success case
    if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {

    } else {
        contentwrap.innerHTML = jankx_ecommerce.errors.get_data_error;
    }
  });
})