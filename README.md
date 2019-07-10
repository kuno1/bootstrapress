# bootstrapress

UI wrapper of Bootstrap for WordPress.

## Installation

```
composer require kunoichi/bootstrapress
```

And in your `functions.php`, require `vendor/autoload.php`.

```php
// functions.php
require __DIR__ . '/vendor/autoload.php';
```

Now you are ready!

## How To Use

It simply wraps Bootstrap UI to WordPress one.

### Navbar

Convert WordPress menu's HTML to suit with Bootstrap [navbar](https://getbootstrap.com/docs/4.1/components/navbar/).

```php
// Pass $theme_location of your menu.
// It will be output as navbar.
new \Kunoichi\BootstraPress\NavbarMenu( 'header-menu' );
```

### Pagination

Output [Bootstrap style pagination](https://getbootstrap.com/docs/4.1/components/pagination/) for archive page. Wrapping it in your function because of future updates.

```php
/**
 * Output pagination for archive page.
 */
function your_theme_pagination() {
    \Kunoichi\BootstraPress\PageNavi::pagination();
}
```


### Navpages

Output links for paginated posts in [Bootstrap style](https://getbootstrap.com/docs/4.1/components/pagination/). Uses `wp_link_pages` internally.
Wrapping it in your function because of future updates.

```php
/**
 * Output pagination for archive page.
 */
function your_theme_link_pages() {
    \Kunoichi\BootstraPress\PageNavi::pagination();
}
```


## License

GPL 3.0 and later. Compatible with WordPress.