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
.star-rating,
p.stars a::before {
  font-family: "font name";
}

.star-rating::before, .star-rating span::before {
  content: "ooooo";
}

p.stars a::before,
p.stars a:hover ~ a::before,
p.stars:hover a::before,
p.stars.selected a.active::before,
p.stars.selected a.active ~ a::before,
p.stars.selected a:not(.active)::before {
  content: "o";
}

.star-rating::before,
p.stars a::before,
p.stars a:hover ~ a::before,
p.stars.selected a.active ~ a::before {
  color: #dfdfdf;
}

.star-rating span::before,
p.stars:hover a::before,
p.stars.selected a.active::before,
p.stars.selected a:not(.active)::before {
  color: #debe60;
}
```
