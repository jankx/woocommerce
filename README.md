Jankx Ecommerce
=

Integrate ecommerce plugins with Jankx theme framework

# Supported Plugins:

- [x] WooCommerce
- [ ] BigCommerce For WordPress
- [ ] Ecwid Ecommerce Shopping Cart


# Customize

Rating icons:
```
.star-rating, p.stars a:before {
  font-family: "font-name";
}

.star-rating:before, span::before {
  content: "icon content 5 times";
}

p.stars a:before,
p.stars a:hover ~ a::before,
p.stars:hover a:before,
p.stars.selected a.active:before
p.stars.selected a.active ~ a::before,
p.stars.selected a:not(.active):before {
  content: "icon content";
}

.star-rating:before,
p.stars a:before
p.stars a:hover ~ a:before,
p.stars.selected ~ a::before {
  color: 'empty color';
}
.star-rating  span::before,
p.stars:hover a::before,
p.stars.selected a.active:before,
p.stars.selected a:not(.active):before{
  color: 'has value color';
}
```
