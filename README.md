Jankx WooCommerce
=

Integrate ecommerce plugins with Jankx theme framework

# Supported Plugins:

- [x] WooCommerce
- [ ] Ecwid WooCommerce Shopping Cart
- [ ] WP EasyCart
- [ ] Studiocart


# Customize

Rating icons:
```
.star-rating {
  width: 82px;
  font-size: 16px;
}

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


# Theme.json settings

```
{
    ...
    "store": {
        "detail": {
            "layout": "default";
        }
    }
}
```
