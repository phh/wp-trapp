<?php

namespace Bonnier\WP\Trapp\Admin;

use Bonnier\WP\Trapp;
use Bonnier\WP\Trapp\Plugin;
use PLL_Walker_Dropdown;

class Main
{
    public function bootstrap()
    {
        add_action('do_meta_boxes', [__CLASS__, 'polylang_meta_box'], 10, 3);
    }

    public static function polylang_meta_box($post_type, $context, $post)
    {
        if ($post_type != 'review' || $context != 'side') {
            return;
        }

        remove_meta_box('ml_box', $post_type, $context);

        $args = [
            'post_type' => $post_type
        ];
        add_meta_box('ml_box', __('Languages','polylang'), [__CLASS__, 'polylang_meta_box_cb'], $post_type, $context, 'high', $args);
    }

    public static function polylang_meta_box_cb($post, $metabox) {
        global $polylang;

        $post_id = $post->ID;
        $post_type = $metabox['args']['post_type'];

        if ($lg = $polylang->model->get_post_language($post_id)) {
            $lang = $lg;
        } elseif (!empty($_GET['new_lang'])) {
            $lang = $polylang->model->get_language($_GET['new_lang']);
        } else {
            $lang = $polylang->pref_lang;
        }

        $text_domain = Plugin::TEXT_DOMAIN;
        $languages = $polylang->model->get_languages_list();
        $pll_dropdown = new PLL_Walker_Dropdown();
        $dropdown = $pll_dropdown->walk($languages, array(
            'name'     => 'post_lang_choice',
            'class'    => 'tags-input',
            'selected' => $lang ? $lang->slug : '',
            'flag'     => true
        ));

        foreach ($languages as $key_language => $language ) {
            if ($language->term_id == $lang->term_id) {
                unset($languages[ $key_language ]);
                $languages = array_values($languages);
                break;
            }
        }

        wp_nonce_field('pll_language', '_pll_nonce');

        $is_autopost = (get_post_status( $post ) == 'auto-draft');

        include( Trapp\instance()->plugin_dir . 'views/admin/metabox-translations-post/language.php');

        if (!$is_autopost) {
            include( Trapp\instance()->plugin_dir . 'views/admin/metabox-translations-post/translations.php');
            include( Trapp\instance()->plugin_dir . 'views/admin/metabox-translations-post/trapp.php');
        }
    }
}
