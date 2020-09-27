/**
 * Jankx Ecommerce
 *
 * @author: Puleeno Nguyen <puleeno@gmail.com>
 * @license https://github.com/jankx/ecommerce/blob/master/license.txt
 */

function render_product_template(products, target) {
  var productHTML = '';
  products.forEach(function(product){
    productHTML += tmpl('jankx-ecommerce-product-tpl', product);
  }); 
  target.innerHTML = productHTML;
}

document.querySelectorAll('.category-tabs-products .tab a').forEach(function(tab) {
  tab.addEventListener('click', function(e){
    e.preventDefault();

    var wrap = e.target.parentElement.parentElement.parentElement;
    var tab = e.target.parentElement;

    var contentwrap = wrap.querySelector('.jankx-tab-content .products');

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
    xmlhttp.open('POST', jankx_ecommerce.get_product_url, false);

    var body = new FormData();

    tab_type = tab.attributes['data-tab-type'];
    if (tab_type) {
      body.append('tab_type', tab_type.nodeValue);
    }
    tab = tab.attributes['data-tab'];
    if (tab) {
      body.append('tab', tab.nodeValue);
    }

    // Request to WordPress
    xmlhttp.send(body);

    // Success case
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      xmlhttp.responseJSON = JSON.parse(xmlhttp.responseText);

      if (!xmlhttp.responseJSON.success) {
        contentwrap.parentElement.innerHTML = jankx_ecommerce.errors.parse_data_error;
        return;
      }
      render_product_template(xmlhttp.responseJSON.products, contentwrap);
    } else {
        contentwrap.parentElement.innerHTML = jankx_ecommerce.errors.get_data_error;
    }
  });
})