<?php
/**
 * (c) Adsstudio.
 */

namespace Adsstudio\MyCustomCSS\behaviors;
use Adsstudio\MyCustomCSS\classes\DatabaseManager;

if (!defined('ABSPATH')) {
    return;
}

class AdShortcodeBehavior
{
    function __construct()
    {
        // register ad shortcodes
        add_shortcode('my_custom_ad', array($this, 'render'));
    }

    function render($atts){
        $atts = shortcode_atts(
            array(
                'id'      => 0,
            ),
            $atts, 'my_custom_ad'
        );

        if ( ! $id = intval( $atts['id'] ) ) {
            return '';
        }

        $snippet = DatabaseManager::getInstance()->getSnippetByID( $id );
        $code = $snippet->code;
        $blockIndex = 0;
        $totalBlocks = 1;
        if($snippet->code_type !== 'ad_shortcode'){
            return ''; // todo messages
        }
        if ( ! trim( $snippet->code ) or !$snippet->active) {
            return '';
        }
        $code = apply_filters('myccad/frontend/ad_code', $code, $snippet->id, $blockIndex, $totalBlocks);

        return $code;
    }
}