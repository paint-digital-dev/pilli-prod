<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php get_bloginfo('name'); ?>
    <?php wp_head(); ?>
</head>

<body>

<div class="wrapper">
    <header class="header" id="header">
        <div class="menu-layout"></div>
        <div class="container">
            <div class="header__inner">
                <a href="/" class="header__logo logo">
                    <img src="https://pilli.paintdigital.agency/wp-content/uploads/2025/05/pilli-logo.svg"
                         alt="Pilli logo" class="logo__image">
                </a>
                <?php echo custom_wpc_fly_cart_button(); ?>
                <nav class="header__menu menu">
                    <button class="menu-close">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                             fill="rgba(255,255,255,1)">
                            <path d="M10.5859 12L2.79297 4.20706L4.20718 2.79285L12.0001 10.5857L19.793 2.79285L21.2072 4.20706L13.4143 12L21.2072 19.7928L19.793 21.2071L12.0001 13.4142L4.20718 21.2071L2.79297 19.7928L10.5859 12Z"></path>
                        </svg>
                    </button>
                    <h3 class="menu__top">Меню</h3>
                    <ul class="menu__list">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'main-menu',
                            'container' => false,
                            'items_wrap' => '%3$s',
                            'menu_class' => 'menu__list',
                            'walker' => new Custom_Category_Menu_Walker()
                        ));
                        ?>
                        <li class="menu__item menu__catalog-mobile">
                            <?php
                            $args = array(
                                'taxonomy' => 'product_cat',
                                'orderby' => 'name',
                                'order' => 'ASC',
                                'hide_empty' => true,
                            );
                            
                            $categories = get_terms($args);
                            
                            $parent_categories = [];
                            $subcategories = [];
                            $excluded_categories = ['Кава', 'Без категорії'];
                            
                            foreach ($categories as $category) {
                                if ($category->parent == 0) {
                                    if (!in_array($category->name, $excluded_categories)) {
                                        $parent_categories[] = $category;
                                    } else if ($category->name == 'Кава') {
                                        $kava_category = $category;
                                    }
                                } else {
                                    $parent = get_term($category->parent, 'product_cat');
                                    if ($parent && $parent->name == 'Кава') {
                                        $subcategories[] = $category;
                                    }
                                }
                            }
                            
                            if (!empty($subcategories)) {
                                echo '<h3>Кава</h3>';
                                echo '<ul>';
                                foreach ($subcategories as $category) {
                                    echo '<li><a href="' . get_term_link($category->term_id, 'product_cat') . '">' . $category->name . '</a></li>';
                                }
                                echo '</ul>';
                            }
                            
                            if (!empty($parent_categories)) {
                                echo '<h3>Каталог</h3>';
                                echo '<ul>';
                                foreach ($parent_categories as $category) {
                                    echo '<li><a href="' . get_term_link($category->term_id, 'product_cat') . '">' . $category->name . '</a></li>';
                                }
                                echo '</ul>';
                            }
                            ?>
                        </li>
                    </ul>
                </nav>
                <button class="menu-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"
                         fill="rgba(255,255,255,1)" class="menu__button-icon">
                        <path d="M3 4H21V6H3V4ZM3 11H21V13H3V11ZM3 18H21V20H3V18Z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main class="<?php echo is_404() ? 'main main_404' : 'main'; ?>">