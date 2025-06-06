<?php
// Disable Bitrix24 default hooks before anything else
remove_action('woocommerce_checkout_order_processed', 'flamix_b24_woo_send_order', 10);
remove_action('woocommerce_payment_complete', 'flamix_b24_woo_send_order', 10);
add_action('init', function() {
    remove_action('woocommerce_checkout_order_processed', 'flamix_b24_woo_send_order', 10);
    remove_action('woocommerce_payment_complete', 'flamix_b24_woo_send_order', 10);
});

add_action('muplugins_loaded', function() {
    remove_action('woocommerce_checkout_order_processed', 'flamix_b24_woo_send_order', 10);
    remove_action('woocommerce_payment_complete', 'flamix_b24_woo_send_order', 10);
}, 1);

/**
 * Theme Core Functions
 *
 * @package Pilli
 * @version 1.0.0
 *
 * Table of Contents:
 * 1. Core Setup
 *    - Theme Support
 *    - Assets Loading
 *    - Navigation Setup  
 *    - Widget Areas
 *
 * 2. WooCommerce Base
 *    - Basic Configuration
 *    - Layout Containers
 *    - Currency Settings
 *    - Shop Page Redirect
 *    - Ajax Handlers
 *    - Cart Configuration
 *    - Turn off emails from wooCommerce
 *
 * 3. Product Features  
 *    - Display & Layout
 *    - Price & SKU Management
 *    - Grind Options System
 *    - Product Page Structure
 *    - Product Attributes
 *    - Custom Product Fields
 *
 * 4. Cart System
 *    - Cart Actions
 *    - Mini Cart
 *    - Checkout Fields
 *    - Cart Fragments
 *    - Ajax Cart Updates
 *
 * 5. Shop & Catalog
 *    - Filter System
 *    - Category Display
 *    - Sorting Options  
 *    - Thank You Page
 *    - Product List Layout
 *
 * 6. Menu & Navigation
 *    - Category Menu Walker
 *    - Custom Menu Types
 *    - Mobile Menu Logic
 *    - Menu Icons System
 *
 * 7. Interface Elements
 *    - Admin Bar 
 *    - Media Library
 *    - Popup System
 *    - Loading States
 *    - UI Components
 *
 * 8. Grind Popup
 *    - Popup Template
 *    - Ajax Processing
 *    - Cart Integration
 *    - Label Management
 *
 * 9. BITRIX24 Integration
 *    - Order Sync
 *    - Nova Poshta Fields
 *    - Custom Fields
 *    - Callback System
 */

/*==============================================
=            1. Core Setup            =
==============================================*/

// Add theme support features
function mytheme_setup()
{
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('woocommerce');
    
    // Register navigation menus
    register_nav_menus(array(
        'main-menu' => __('Main Menu'),
        'menu-footer' => ('Footer Menu'),
    ));
}

add_action('after_setup_theme', 'mytheme_setup');

// Disables the block editor from managing widgets in the Gutenberg plugin.
add_filter('gutenberg_use_widgets_block_editor', '__return_false');
// Disables the block editor from managing widgets.
add_filter('use_widgets_block_editor', '__return_false');

function mytheme_enqueue_assets()
{
    // Styles
    wp_enqueue_style('style', get_template_directory_uri() . '/dist/css/style.min.css');
    wp_enqueue_style('custom-fonts', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap');
    
    // Scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'app',
        get_template_directory_uri() . '/dist/js/main.min.js',
        ['jquery'],
        '1.0.0',
        true
    );
    wp_localize_script('app', 'wpc_fly_cart_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}

add_action('wp_enqueue_scripts', 'mytheme_enqueue_assets');

/*==============================================
=            2. WooCommerce Base            =
==============================================*/

// Add WooCommerce container
function custom_add_main_container_start()
{
    echo '<div class="container">';
}

add_action('woocommerce_before_main_content', 'custom_add_main_container_start', 5);

function custom_add_main_container_end()
{
    echo '</div>';
}

add_action('woocommerce_after_main_content', 'custom_add_main_container_end', 5);

// Remove default WooCommerce wrappers and elements
function mytheme_remove_woocommerce_defaults()
{
    // Remove woocommerce <main>
    remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
    
    // Remove woocommerce header
    remove_action('woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10);
    
    // Remove woocommerce sidebar
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    
    // Remove standart tabs in single-product card
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
}

add_action('init', 'mytheme_remove_woocommerce_defaults');

// Remove zeros in prices
add_filter('woocommerce_price_trim_zeros', '__return_true');

// Customize currency symbol
function custom_uah_currency_symbol($currency_symbol, $currency)
{
    if ('UAH' === $currency) {
        $currency_symbol = 'грн';
    }
    return $currency_symbol;
}

add_filter('woocommerce_currency_symbol', 'custom_uah_currency_symbol', 10, 2);

// Redirect from shop to /
add_action('template_redirect', 'my_shop_redirect');
function my_shop_redirect()
{
    if (is_shop()) {
        wp_redirect(home_url('/new-page/'), 301);
        exit;
    }
}

// Turn off WooCommerce emails
add_filter('woocommerce_email_recipient_new_order', '__return_empty_string');
add_filter('woocommerce_email_recipient_customer_processing_order', '__return_empty_string');
add_filter('woocommerce_email_recipient_customer_completed_order', '__return_empty_string');

/*==============================================
=            3. Product Features            =
==============================================*/

/**
 * Move SKU to the top of the form (including variable products),
 * and for simple products, display price there as well.
 */
function custom_move_sku_and_price_into_form_top()
{
    
    // 1) Remove standard price output only for simple products (from summary block)
    add_action('woocommerce_before_single_product_summary', 'remove_price_for_simple_products', 1);
    
    // 2) Remove custom SKU which we previously added to summary (if it exists)
    remove_action('woocommerce_single_product_summary', 'display_weight_and_sku_after_price', 11);
    
    // 3) Remove Add to Cart form from summary block
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    
    // 4) Add form after summary
    add_action('woocommerce_after_single_product_summary', 'woocommerce_template_single_add_to_cart', 5);
    
    // 5) For variable products: display SKU (inside form) above variations table
    add_action('woocommerce_before_variations_form', 'display_sku_in_form_top', 5);
    
    // 6) For simple products: display SKU (and price) (inside form) above quantity/button
    add_action('woocommerce_before_add_to_cart_button', 'display_sku_in_form_top', 5);
    
}

add_action('wp', 'custom_move_sku_and_price_into_form_top');

/**
 * Remove standard price output (woocommerce_template_single_price)
 * only for simple products. For variable products, price remains in summary.
 */
function remove_price_for_simple_products()
{
    global $product;
    if ($product && $product->is_type('simple')) {
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
    }
}

/**
 * Display SKU at the top of the form.
 * For simple products, also display price here.
 */
function display_sku_in_form_top()
{
    global $product;
    if (!$product) return;
    
    echo '<div class="product-info-wrapper top-of-form">';
    
    if ($product->get_sku()) {
        echo '<p class="product-sku"><strong>' . __('Артикул:', 'woocommerce') . '</strong> ' . esc_html($product->get_sku()) . '</p>';
    }
    
    echo '<p class="product-price">' . $product->get_price_html() . '</p>';
    
    echo '</div>';
}

// Display custom product attributes
function display_custom_product_attributes()
{
    if (is_product()) {
        global $product;
        
        $attributes = $product->get_attributes();
        
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                if (!$attribute->get_visible()) {
                    continue;
                }
                
                if ($attribute->is_taxonomy()) {
                    $values = wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'names'));
                    
                    $value = implode(', ', $values);
                    $label = wc_attribute_label($attribute->get_name());
                    echo '<p><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</p>';
                } else {
                    $value = $attribute->get_options();
                    $value = implode(', ', $value);
                    $label = wc_attribute_label($attribute->get_name());
                    echo '<p><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</p>';
                }
            }
        }
    }
}

add_action('woocommerce_single_product_summary', 'display_custom_product_attributes', 25);

function remove_default_short_description()
{
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
}

add_action('init', 'remove_default_short_description');

function mytheme_display_product_descriptions()
{
    global $product;
    if (!$product) return;
    
    // Full Description
    if ($full = $product->get_description()) {
        echo '<div class="product-full-description">';
        echo wpautop(wp_kses_post($full));
        echo '</div>';
    }
    
    // Short Description
    if ($short = $product->get_short_description()) {
        echo '<div class="product-short-description">';
        echo wpautop(wp_kses_post($short));
        echo '</div>';
    }
}

add_action('woocommerce_single_product_summary', 'mytheme_display_product_descriptions', 20);

function move_product_title_before_summary()
{
    if (is_product()) {
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
        add_action('woocommerce_before_single_product_summary', function () {
            echo '<h1 class="product_title entry-title">' . get_the_title() . '</h1>';
        }, 5);
    }
}

add_action('wp', 'move_product_title_before_summary');

add_action('wp_ajax_get_cart_items', 'get_cart_items');
add_action('wp_ajax_nopriv_get_cart_items', 'get_cart_items');
function get_cart_items()
{
    $items = [];
    foreach (WC()->cart->get_cart() as $cart_item) {
        $items[] = $cart_item['product_id'];
    }
    wp_send_json($items);
}

/*==============================================
=            4. Cart System            =
==============================================*/

// Custom WPC Fly Cart Button
function custom_wpc_fly_cart_button()
{
    if (class_exists('WPCleverWoofc')) {
        $cart_count = WC()->cart->get_cart_contents_count();
        $button_html = '<a href="#" class="custom-fly-cart-button woofc-cart-trigger" data-effect="hover_shake">';
        $button_html .= '<span class="wpc-fly-cart-icon"><i class="woofc-icon-cart12"></i></span>';
        $button_html .= '<span class="wpc-fly-cart-count">' . esc_html($cart_count) . '</span>';
        $button_html .= '</a>';
        
        return $button_html;
    }
    return '';
}

add_shortcode('custom_wpc_fly_cart', 'custom_wpc_fly_cart_button');

// Cart AJAX Handlers
function update_wpc_cart_count()
{
    if (class_exists('WPCleverWoofc')) {
        echo WC()->cart->get_cart_contents_count();
    }
    wp_die();
}

add_action('wp_ajax_update_wpc_cart_count', 'update_wpc_cart_count');
add_action('wp_ajax_nopriv_update_wpc_cart_count', 'update_wpc_cart_count');

function custom_wpc_cart_fragments($fragments)
{
    $fragments['.wpc-fly-cart-count'] = '<span class="wpc-fly-cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
    return $fragments;
}

add_filter('woocommerce_add_to_cart_fragments', 'custom_wpc_cart_fragments');

add_filter('woocommerce_checkout_fields', 'remove_unwanted_woocommerce_checkout_fields');

function remove_unwanted_woocommerce_checkout_fields($fields)
{
    // Billing fields
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    
    // Shipping fields (на випадок, якщо активована доставка)
    unset($fields['shipping']['shipping_company']);
    unset($fields['shipping']['shipping_address_1']);
    unset($fields['shipping']['shipping_address_2']);
    unset($fields['shipping']['shipping_city']);
    unset($fields['shipping']['shipping_postcode']);
    unset($fields['shipping']['shipping_country']);
    unset($fields['shipping']['shipping_state']);
    
    return $fields;
}

// Don't require shipping address
add_filter('woocommerce_cart_needs_shipping_address', '__return_false');

// Remove payment description in checkout
function remove_default_gateway_description($description, $payment_id)
{
    if ($payment_id === 'bacs' || $payment_id === 'cod') {
        return '';
    }
    return $description;
}

add_filter('woocommerce_gateway_description', 'remove_default_gateway_description', 20, 2);

/*==============================================
=            5. Shop & Catalog            =
==============================================*/

// Filter widget
add_action('widgets_init', 'mytheme_widgets_init');
function mytheme_widgets_init()
{
    register_sidebar(array(
        'name' => __('Фільтр товарів', 'pilli-theme'),
        'id' => 'shop-filters',
        'description' => __('Панель з фільтрами для сторінки магазину', 'pilli-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}

remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

// Filters only in Kava category
add_action('woocommerce_before_shop_loop', 'custom_shop_filters_inner_wrap_open', 4);
add_action('woocommerce_before_shop_loop', 'custom_shop_filters_top', 5);
add_action('woocommerce_before_shop_loop', 'custom_shop_sorting', 6);
add_action('woocommerce_before_shop_loop', 'custom_shop_filters_inner_wrap_close', 7);

function custom_shop_filters_inner_wrap_open()
{
    // Check if we in Kava
    if (is_tax('product_cat', 'vsya-kava')) {
        echo '<div class="product__filters-inner">';
    }
}

function custom_shop_filters_top()
{
    // Check if we in Kava
    if (is_tax('product_cat', 'vsya-kava') && is_active_sidebar('shop-filters')) {
        dynamic_sidebar('shop-filters');
    }
}

function custom_shop_sorting()
{
    // Check if we in Kava
    if (is_tax('product_cat', 'vsya-kava')) {
        woocommerce_catalog_ordering();
    }
}

function custom_shop_filters_inner_wrap_close()
{
    /// Check if we in Kava
    if (is_tax('product_cat', 'vsya-kava')) {
        echo '</div>';
    }
}

// reset filters button
add_action('woocommerce_before_shop_loop', 'custom_reset_filters_button_premmerce', 6);
function custom_reset_filters_button_premmerce()
{
    if (is_tax('product_cat', 'vsya-kava')) {
        $current_url = get_term_link(get_queried_object());
        if (!is_wp_error($current_url)) {
            echo '<a href="' . esc_url($current_url) . '" class="reset-filters-button">Скинути фільтри</a>';
        }
    }
}

// Select
add_filter('gettext', 'custom_replace_any_to_all', 20, 3);
function custom_replace_any_to_all($translated_text, $text, $domain)
{
    if (strpos($translated_text, 'Будь-який') !== false) {
        $translated_text = str_replace('Будь-який', '', $translated_text);
    }
    return $translated_text;
}

// thank you page custom
add_action( 'template_redirect', 'custom_redirect_after_checkout' );
function custom_redirect_after_checkout() {
    if ( is_order_received_page() ) {
        wp_safe_redirect( site_url('/success') );
        exit;
    }
}

// Edit the address output on the “order-received” page
add_action('wp_footer', 'modify_shipping_address_with_js');
function modify_shipping_address_with_js()
{
    if (!is_wc_endpoint_url('order-received')) {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function ($) {
            $('.xlwcty_Dview p, .xlwcty_Mview p').each(function () {
                var lines = $(this).html().split('<br>');
                var name = lines[0] || '',
                    addr = lines[1] || '',
                    city = lines[2] || '';
                $(this).html(
                    '<span><b>Ім\'я:</b> ' + name + '<br></span>' +
                    '<span><b>Адреса:</b> ' + addr + '<br></span>' +
                    '<span><b>Місто:</b> ' + city + '</span>'
                );
            });
        });
    </script>
    <?php
}

/*==============================================
=            6. Menu & Navigation            =
==============================================*/

class Custom_Category_Menu_Walker extends Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        $classes = empty($item->classes) ? array() : (array)$item->classes;
        
        // Add BEM classes
        if ($depth === 0) {
            $classes[] = 'menu__item';
            
            // Add special class for Catalog item
            if ($item->title === 'Каталог') {
                $classes[] = 'menu__item-catalog';
            }
        } else {
            $classes[] = 'menu__submenu-item';
        }
        
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
        
        $output .= '<li' . $class_names . '>';
        
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        
        if ($item->title === 'Каталог') {
            $attributes .= ' class="menu__link menu__link--toggle" href="javascript:void(0);"';
            $item_output = $args->before;
            $item_output .= '<a' . $attributes . '>';
            $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
            $item_output .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="rgba(255,255,255,1)" class="menu__arrow"><path d="M12 16L6 10H18L12 16Z"></path></svg></a>';
            
            $item_output .= '<ul class="menu__submenu category__list">';
            
            // Get all product categories
            $categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'parent' => 0,
            ));
            
            if (!empty($categories) && !is_wp_error($categories)) {
                foreach ($categories as $category) {
                    if ($category->name === 'Кава') {
                        $item_output .= '<li class="category__item category__item--has-children">';
                        $item_output .= '<a href="javascript:void(0);" class="category__link category__link--toggle">' .
                            esc_html($category->name) .
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="rgba(65,61,62,1)" class="category__arrow"><path d="M16 12L10 18V6L16 12Z"></path></svg></a>';
                        $item_output .= $this->get_subcategories($category->term_id);
                    } else {
                        $item_output .= '<li class="category__item">';
                        $item_output .= '<a href="' . esc_url(get_term_link($category)) . '" class="category__link">' . esc_html($category->name) . '</a>';
                    }
                    
                    $item_output .= '</li>';
                }
            }
            
            $item_output .= '</ul>';
            
        } else {
            $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
            
            if ($depth === 0) {
                $attributes .= ' class="menu__link"';
            } else {
                $attributes .= ' class="menu__submenu-link"';
            }
            
            $item_output = $args->before;
            $item_output .= '<a' . $attributes . '>';
            $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
            $item_output .= '</a>';
        }
        
        $item_output .= $args->after;
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
    
    // Function to get subcategories
    private function get_subcategories($parent_id)
    {
        $subcategories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'parent' => $parent_id,
        ));
        
        if (empty($subcategories) || is_wp_error($subcategories)) {
            return '';
        }
        
        $output = '<ul class="category__submenu">';
        
        foreach ($subcategories as $subcategory) {
            $output .= '<li class="category__item">';
            $output .= '<a href="' . esc_url(get_term_link($subcategory)) . '" class="category__link">' . esc_html($subcategory->name) . '</a>';
            $output .= '</li>';
        }
        
        $output .= '</ul>';
        
        return $output;
    }
}

/*==============================================
=            7. Interface Elements            =
==============================================*/

// Collapsible Admin Bar
final class Custom_Collapse_Admin_Bar
{
    public static function init()
    {
        add_action('admin_bar_init', [__CLASS__, 'hooks']);
    }
    
    public static function hooks()
    {
        remove_action('wp_head', '_admin_bar_bump_cb');
        add_action('wp_enqueue_scripts', [__CLASS__, 'collapse_styles']);
        add_action('admin_bar_menu', [__CLASS__, 'remove_nodes'], 999);
    }
    
    public static function collapse_styles()
    {
        $styles = "
            #wpadminbar
            {
                transition: clip-path .3s ease 1s, background-color .2s ease 1s;
                clip-path: polygon( 0 0, 32px 0, 32px 100%, 0 100% );
            }

            #wpadminbar:not( :hover )
            {
                background-color: rgba( 29, 35, 39, 0 );
            }

            #wpadminbar:not( :hover ) .ab-item::before
            {
                color: #1d2327;
                transition-delay: 1s;
            }

            #wpadminbar .ab-item
            {
                position: relative;
            }

            #wpadminbar #wp-admin-bar-site-name > .ab-item::after
            {
                content: '';
                position: absolute;
                top: 7px;
                left: 7px;
                z-index: -1;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                background-color: #fff;
                opacity: .8;
                transition: opacity .2s ease 1s;
            }

            #wpadminbar:hover #wp-admin-bar-site-name > .ab-item::after
            {
                opacity: 0;
                transition-delay: 0s;
            }

            #wpadminbar:not( :hover ) > *
            {
                pointer-events: none;
            }

            #wpadminbar:hover
            {
                transition-delay: 0s;
                clip-path: polygon( 0 0, 100% 0, 100% 100vh, 0 100vh );
            }

            @media screen and ( max-width: 782px )
            {
                #wpadminbar
                {
                    clip-path: polygon( 0 0, 50px 0, 50px 100%, 0 100% );
                }
            }
        ";
        
        wp_register_style('collapse-admin-bar', false);
        wp_add_inline_style('collapse-admin-bar', $styles);
        wp_enqueue_style('collapse-admin-bar');
    }
    
    public static function remove_nodes($wp_admin_bar)
    {
        $wp_admin_bar->remove_node('wp-logo');
        $wp_admin_bar->remove_node('search');
    }
}

add_action('init', [Custom_Collapse_Admin_Bar::class, 'init']);

// Allow SVG uploads
function svg_upload_allow($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}

add_filter('upload_mimes', 'svg_upload_allow');

function fix_svg_mime_type($data, $file, $filename, $mimes, $real_mime = '')
{
    if (version_compare($GLOBALS['wp_version'], '5.1.0', '>=')) {
        $dosvg = in_array($real_mime, ['image/svg', 'image/svg+xml']);
    } else {
        $dosvg = ('.svg' === strtolower(substr($filename, -4)));
    }
    
    if ($dosvg) {
        if (current_user_can('manage_options')) {
            $data['ext'] = 'svg';
            $data['type'] = 'image/svg+xml';
        } else {
            $data['ext'] = false;
            $data['type'] = false;
        }
    }
    
    return $data;
}

add_filter('wp_check_filetype_and_ext', 'fix_svg_mime_type', 10, 5);


add_action('woocommerce_before_add_to_cart_button', 'insert_custom_grind_from_product');

function insert_custom_grind_from_product()
{
    global $product;
    
    if (!$product || !$product->is_type('simple')) return;
    
    // check 'pa_pomel'
    $attributes = $product->get_attributes();
    if (!isset($attributes['pa_pomel'])) return;
    
    $attribute = $attributes['pa_pomel'];
    
    if (!$attribute->is_taxonomy()) return;
    
    $terms = wc_get_product_terms($product->get_id(), 'pa_pomel', ['fields' => 'all']);
    if (empty($terms)) return;
    
    echo '<div class="custom-select-wrap">
            <label for="custom_grind">Помел</label>
            <div class="custom-select-inner">
                <select id="custom_grind" name="custom_grind">';
    
    foreach ($terms as $term) {
        $selected = ($term->slug === 'u-zerni') ? ' selected' : '';
        echo '<option value="' . esc_attr($term->slug) . '"' . $selected . '>' . esc_html($term->name) . '</option>';
    }
    
    echo '</div></select></div>';
}


//1. Save custom_grind in mini cart
add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id) {
    if (!empty($_POST['custom_grind'])) {
        $cart_item_data['custom_grind'] = sanitize_text_field($_POST['custom_grind']);
    }
    return $cart_item_data;
}, 10, 2);

add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    if (!empty($cart_item['custom_grind'])) {
        $item_data[] = [
            'name' => __('Помел', 'woocommerce'),
            'value' => wc_clean($cart_item['custom_grind']),
        ];
    }
    return $item_data;
}, 10, 2);

// Grind transfer in mini-cart
add_filter('woocommerce_cart_item_name', function ($name, $cart_item, $cart_item_key) {
    if (!empty($cart_item['custom_grind'])) {
        $term = get_term_by('slug', $cart_item['custom_grind'], 'pa_pomel');
        if ($term && !is_wp_error($term)) {
            $label = esc_html($term->name);
            $name .= '<br><span class="grind-label">' . $label . '</span>';
        }
    }
    return $name;
}, 10, 3);

// Прибрати пусті опції по типу Віберіть варіант в селектах
add_filter('woocommerce_dropdown_variation_attribute_options_args', 'wc_remove_options_text');
function wc_remove_options_text($args)
{
    $args['show_option_none'] = '';
    return $args;
}

/*==============================================
=            8. Grind popup            =
==============================================*/

// ================================ GRIND POPUP ================================
add_action('wp_ajax_my_add_grind_to_cart', 'my_add_grind_to_cart');
add_action('wp_ajax_nopriv_my_add_grind_to_cart', 'my_add_grind_to_cart');

function my_add_grind_to_cart()
{
    $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
    $custom_grind = isset($_POST['custom_grind']) ? sanitize_text_field(wp_unslash($_POST['custom_grind'])) : '';
    
    if ($product_id <= 0) {
        wp_send_json_error(['message' => 'Неправильний ID товару']);
        return;
    }
    
    $cart_item_key = WC()->cart->add_to_cart(
        $product_id,
        1,
        0,
        [],
        ['custom_grind' => $custom_grind]
    );
    
    if (!$cart_item_key) {
        wp_send_json_error(['message' => 'Не вдалося додати товар до кошика']);
        return;
    }
    
    wp_send_json_success([
        'message' => 'Товар успішно додано!',
        'cart_hash' => WC()->cart->get_cart_hash(),
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'cart_item_key' => $cart_item_key
    ]);
}

add_filter('woocommerce_add_cart_item_data', 'my_add_grind_to_cart_item_data', 10, 3);
function my_add_grind_to_cart_item_data($cart_item_data, $product_id, $variation_id)
{
    if (isset($_POST['custom_grind']) && !empty($_POST['custom_grind'])) {
        $cart_item_data['custom_grind'] = sanitize_text_field(wp_unslash($_POST['custom_grind']));
    }
    return $cart_item_data;
}

add_action('woocommerce_add_order_item_meta', 'my_save_grind_to_order_item_meta', 10, 3);
function my_save_grind_to_order_item_meta($item_id, $cart_item, $cart_item_key)
{
    if (isset($cart_item['custom_grind']) && !empty($cart_item['custom_grind'])) {
        $term = get_term_by('slug', $cart_item['custom_grind'], 'pa_pomel');
        $grind_name = ($term && !is_wp_error($term)) ? $term->name : $cart_item['custom_grind'];
        
        wc_add_order_item_meta($item_id, 'Помел', $grind_name);
    }
}

add_filter('woocommerce_loop_add_to_cart_link', 'my_custom_loop_add_to_cart_button', 10, 2);
function my_custom_loop_add_to_cart_button($button_html, $product)
{
    if (!$product->is_type('simple')) {
        return $button_html;
    }
    
    $attributes = $product->get_attributes();
    if (!isset($attributes['pa_pomel'])) {
        return $button_html;
    }
    
    return sprintf(
        '<a href="#" class="button open-grind-popup" data-product_id="%d" data-product_name="%s">Купити</a>',
        $product->get_id(),
        esc_attr($product->get_name())
    );
}

add_action('wp_footer', 'my_render_grind_popup');
function my_render_grind_popup() {
    $all_pomel_terms = get_terms([
        'taxonomy' => 'pa_pomel',
        'hide_empty' => false,
    ]);
    
    if (is_wp_error($all_pomel_terms) || empty($all_pomel_terms)) {
        return;
    }
    ?>
    <div class="product-card-popup__layout product-card-popup__layout_inactive" id="grind-popup">
        <div class="product-card-popup__content">
            <div class="product-card-popup__top">
                <h3>Оберіть помел</h3>
                <button class="product-card-popup__close" type="button" id="grind-popup-close">
                    <svg style="pointer-events: none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24"
                         height="24"
                         fill="333333" class="menu__close-icon">
                        <path d="M10.5859 12L2.79297 4.20706L4.20718 2.79285L12.0001 10.5857L19.793 2.79285L21.2072 4.20706L13.4143 12L21.2072 19.7928L19.793 21.2071L12.0001 13.4142L4.20718 21.2071L2.79297 19.7928L10.5859 12Z"></path>
                    </svg>
                </button>
            </div>

            <div class="product-title-placeholder"></div>

            <div class="pomel-blocks">
                <?php foreach ($all_pomel_terms as $term): ?>
                    <div class="pomel-block" data-value="<?php echo esc_attr($term->slug); ?>">
                        <?php echo esc_html($term->name); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="spinner" style="display: none;"></div>
        </div>
    </div>
    <script>
        // body scroll width
        let div = document.createElement('div');

        div.style.overflowY = 'scroll';
        div.style.width = '50px';
        div.style.height = '50px';

        document.body.append(div);
        let scrollWidth = div.offsetWidth - div.clientWidth;
        div.remove();

        function bodyRemoveScroll() {
            document.body.style.overflow = 'hidden';
            document.body.style.paddingRight = scrollWidth + 'px';
            document.querySelector('.header').style.padding = `12px ${scrollWidth + 12}px 12px 12px`;
        }

        function bodyAddScroll() {
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            document.querySelector('.header').style.padding = '';
        }
        
        document.addEventListener('DOMContentLoaded', function () {
            const popup = {
                layout: document.querySelector('.product-card-popup__layout'),
                titlePlaceholder: document.querySelector('.product-title-placeholder'),
                triggerBtn: null,
                open: function (productId, productName, btn) {
                    this.triggerBtn = btn;
                    // remove loading
                    this.triggerBtn.classList.remove('loading');
                    this.layout.setAttribute('data-product_id', productId);
                    if (this.titlePlaceholder) {
                        this.titlePlaceholder.textContent = productName;
                    }
                    this.layout.classList.replace(
                        'product-card-popup__layout_inactive',
                        'product-card-popup__layout_active'
                    );
                    this.resetBlocks();
                    document.body.style.overflow = 'hidden';
                },
                close: function () {
                    // remove loading
                    if (this.triggerBtn) {
                        this.triggerBtn.classList.remove('loading');
                    } else {
                        const pid = this.layout.getAttribute('data-product_id');
                        const btn = document.querySelector(`.open-grind-popup[data-product_id="${pid}"]`);
                        if (btn) btn.classList.remove('loading');
                    }
                    this.layout.classList.replace(
                        'product-card-popup__layout_active',
                        'product-card-popup__layout_inactive'
                    );
                    this.resetBlocks();
                    document.body.style.overflow = '';
                },
                showSpinner: function () {
                    if (this.spinner) this.spinner.style.display = 'block';
                },
                resetBlocks: function () {
                    const pomelBlocks = document.querySelectorAll('.pomel-block');
                    pomelBlocks.forEach(block => {
                        block.classList.remove('button_loading', 'selected', 'loading');
                        const spin = block.querySelector('.spinner');
                        if (spin) spin.remove();
                        block.style.pointerEvents = 'auto';
                    });
                }
            };

            // Open popup
            document.addEventListener('click', function (event) {
                const btn = event.target.closest('.open-grind-popup');
                if (btn) {
                    event.preventDefault();
                    bodyRemoveScroll();
                    popup.open(
                        btn.getAttribute('data-product_id'),
                        btn.getAttribute('data-product_name'),
                        btn
                    );
                }
            });

            // Choose grind method
            document.addEventListener('click', function (event) {
                const block = event.target.closest('.pomel-block');
                if (
                    block &&
                    popup.layout.classList.contains('product-card-popup__layout_active')
                ) {
                    if (block.classList.contains('loading')) return;

                    // block clicks
                    document.querySelectorAll('.pomel-block').forEach(b => b.style.pointerEvents = 'none');
                    block.classList.add('loading');
                    const spinner = document.createElement('div');
                    spinner.classList.add('spinner');
                    block.appendChild(spinner);

                    const customGrind = block.getAttribute('data-value') || '';
                    const productId = popup.layout.getAttribute('data-product_id') || 0;

                    const form = new FormData();
                    form.append('action', 'my_add_grind_to_cart');
                    form.append('product_id', productId);
                    form.append('custom_grind', customGrind);

                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 10000);

                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: form,
                        signal: controller.signal
                    })
                        .then(response => response.json())
                        .then(data => {
                            clearTimeout(timeoutId);
                            popup.resetBlocks();
                            if (data.success) {
                                popup.close();
                                // update mini-cart
                                jQuery(document.body).trigger('wc_fragment_refresh');
                            } else {
                                alert(data.data.message || 'Помилка при додаванні в кошик');
                            }
                        })
                        .catch(error => {
                            clearTimeout(timeoutId);
                            popup.resetBlocks();
                            console.error('AJAX Error:', error);
                            alert('Помилка при додаванні товару. Спробуйте ще раз.');
                            popup.close();
                        });
                }
            });

            // close popup
            document.addEventListener('click', function (event) {
                if (
                    event.target.id === 'grind-popup-close' ||
                    (event.target.closest('.product-card-popup__layout') &&
                        !event.target.closest('.product-card-popup__content'))
                ) {
                    popup.close();
                    bodyAddScroll();
                }
            });
        });
    </script>
    <?php
}

/*==============================================
=            9. BITRIX24 Integration           =
==============================================*/

// Disabling standard Bitrix24 hooks
function disable_bitrix24_checkout_hooks() {
    remove_all_actions('woocommerce_checkout_order_processed');
    remove_all_actions('woocommerce_payment_complete');
    remove_all_actions('woocommerce_order_status_changed');
}
add_action('init', 'disable_bitrix24_checkout_hooks', 5);

// Check order shipment status
function check_order_sent_to_bitrix24($order_id) {
    try {
        $queue = new FlamixLocal\WooOrders\Jobs\Order();
        return $queue->isSentSuccessfully($order_id);
    } catch (Exception $e) {
        return false;
    }
}

// Disable all automatic order submissions in Bitrix24 from Flamix plugin
add_action('init', function() {
    remove_action('woocommerce_new_order', [\Flamix\Bitrix24\WooCommerce\Handlers::class, 'new_order']);
    remove_action('woocommerce_payment_complete', [\Flamix\Bitrix24\WooCommerce\Handlers::class, 'payment_complete']);
    remove_action('woocommerce_order_status_changed', [\Flamix\Bitrix24\WooCommerce\Handlers::class, 'sendStatusToBitrix24']);
    remove_action('woocommerce_checkout_order_processed', [\Flamix\Bitrix24\WooCommerce\Handlers::class, 'dispatchNotSentOrders']);
}, 1);

// Leave only your order shipment on the required statuses
add_action('woocommerce_order_status_processing', 'send_order_to_bitrix24_on_status', 20, 2);
add_action('woocommerce_order_status_completed', 'send_order_to_bitrix24_on_status', 20, 2);
function send_order_to_bitrix24_on_status($order_id, $order) {
    if (!$order_id || !$order) return;
    // Проверяем, не отправлен ли уже заказ
    if (check_order_sent_to_bitrix24($order_id)) return;

    // Отправка заказа в Bitrix24
    try {
        $queue = new FlamixLocal\WooOrders\Jobs\Order();
        $queue->dispatch($order_id);
    } catch (Exception $e) {
        error_log('Ошибка отправки в Bitrix24: ' . $e->getMessage());
    }
}

// AJAX send handler
add_action('wp_ajax_custom_send_to_bitrix24', 'handle_custom_send_to_bitrix24');
add_action('wp_ajax_nopriv_custom_send_to_bitrix24', 'handle_custom_send_to_bitrix24');

function handle_custom_send_to_bitrix24() {
    check_ajax_referer('send-to-bitrix24', 'security');
    
    $order_id = absint($_POST['order_id']);
    if (!$order_id) {
        wp_send_json_error(['message' => 'Invalid order ID']);
    }
    
    // Check for duplicates
    if (check_order_sent_to_bitrix24($order_id)) {
        wp_send_json_error([
            'message' => 'Order already sent to Bitrix24',
            'already_sent' => true
        ]);
        return;
    }

    try {
        $queue = new FlamixLocal\WooOrders\Jobs\Order();
        $job_id = $queue->dispatch($order_id);
        
        wp_send_json_success([
            'job_id' => $job_id,
            'message' => 'Order queued successfully'
        ]);
    } catch (Exception $e) {
        wp_send_json_error([
            'message' => $e->getMessage()
        ]);
    }
}

// Checkout callback functions
function add_call_me_back_checkbox($fields) {
    $fields['billing']['billing_call_me_back'] = array(
        'type' => 'checkbox',
        'label' => 'Передзвоніть мені',
        'required' => false,
        'priority' => 120,
        'default' => 0,
    );
    return $fields;
}

// Save value to order meta
function save_call_me_back_field($order_id) {
    $value = isset($_POST['billing_call_me_back']) ? 'yes' : 'no';
    update_post_meta($order_id, '_billing_call_me_back', $value);
}

// Remove "(optional)" text for both callback checkbox and other fields
function remove_optional_text_for_call_me_back($field, $key, $args, $value) {
    // Remove optional text for callback checkbox
    if ('billing_call_me_back' === $key) {
        $field = preg_replace('/<span class="optional">.*?<\/span>/i', '', $field);
    }
    return $field;
}

add_filter('woocommerce_checkout_fields', 'add_call_me_back_checkbox');
add_action('woocommerce_checkout_update_order_meta', 'save_call_me_back_field');
add_filter('woocommerce_form_field', 'remove_optional_text_for_call_me_back', 10, 4);

// Add callback checkbox field to order for Bitrix24
add_filter('flamix_bitrix24_integrations_fields_filter', function (array $fields) {
    $order_id = $fields['ORDER_ID'] ?? 0;
    if ($order_id) {
        $call_me_back = get_post_meta($order_id, '_billing_call_me_back', true);
        if ($call_me_back) {
            $fields['BILLING_CALL_ME_BACK'] = ($call_me_back === 'yes') ? 'Так' : 'Ні';
        }
    }
    return $fields;
});

// (optional) Add to JSON REST API
add_filter('woocommerce_rest_prepare_shop_order_object', 'add_call_me_back_to_rest', 10, 3);
function add_call_me_back_to_rest($response, $object, $request)
{
    $call = get_post_meta($object->get_id(), '_billing_call_me_back', true);
    $response->data['billing_call_me_back'] = $call === 'yes' ? 'Так' : 'Ні';
    return $response;
}

// Save grind options in bitrix24

// On order creation: save human-readable grind label to meta
add_action('woocommerce_checkout_create_order_line_item', 'save_grind_label_to_order_item', 20, 4);
function save_grind_label_to_order_item($item, $cart_item_key, $values, $order)
{
    if (!empty($values['custom_grind'])) {
        $slug = sanitize_text_field($values['custom_grind']);
        $term = get_term_by('slug', $slug, 'pa_pomel');
        if ($term && !is_wp_error($term)) {
            $item->add_meta_data('custom_grind_label', $term->name, true);
        }
    }
}

// Before sending to Bitrix: remove SKU and add grind info
add_filter('flamix_bitrix24_integrations_product_filter', function (array $products, int $order_id) {
    $order = wc_get_order($order_id);
    foreach ($products as $item_key => &$p) {
        $item = $order->get_item($item_key);
        if (!$item) {
            continue;
        }
        
        // Remove article (00000XXXX) from product name
        $p['NAME'] = preg_replace('/\s*\(\d+\)$/', '', $p['NAME']);
        
        // Add grind in product name
        $label = $item->get_meta('custom_grind_label', true);
        if ($label) {
            $p['NAME'] .= " (Мелена під: {$label})";
        }
    }
    return $products;
}, 10, 2);

// Function to get shipping meta data
function custom_wc_ukr_shipping_get_shipping_meta(\WC_Order $order, string $key): string 
{ 
    if (!$order->has_shipping_method('nova_poshta_shipping')) {
        return '';
    }
    
    $shippingMethods = $order->get_shipping_methods();
    if (count($shippingMethods) === 0) {
        return '';
    }
    
    $shippingMethod = reset($shippingMethods);
    return $shippingMethod->get_meta($key);
}

// Add Nova Poshta warehouse data to order fields for Bitrix24
add_filter('flamix_bitrix24_integrations_fields_filter', function (array $fields) {
    $order_id = $fields['ORDER_ID'] ?? 0;
    if ($order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            // Get Nova Poshta warehouse ref
            $warehouseRef = custom_wc_ukr_shipping_get_shipping_meta($order, 'wcus_warehouse_ref');
            
            // Add to Bitrix24 fields array
            if ($warehouseRef) {
                $fields['NOVA_POSHTA_WAREHOUSE_REF'] = $warehouseRef;
            }
        }
    }
    return $fields;
});

// woocommerce emails enable
add_filter('woocommerce_email_enabled', '__return_false');