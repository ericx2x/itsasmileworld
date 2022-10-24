<?php
/**
 * (c) mediagroup.
 */

namespace Adsstudio\MyCustomCSS\behaviors;
use Adsstudio\MyCustomCSS\classes\DatabaseManager;
use Adsstudio\MyCustomCSS\classes\PHPRunner;

if (!defined('ABSPATH')) {
    return;
}

class PhpShortcodeBehavior
{
    function __construct()
    {
        // register ad shortcodes
        add_shortcode('my_custom_php', array($this, 'render'));
    }

    function render($atts, $content, $tag)
    {
        $atts = shortcode_atts(
            [
                'id' => 0,
            ],
            $atts, 'my_custom_php'
        );

        if (!$id = (int)$atts['id']) {
            return '';
        }
        $snippet = DatabaseManager::getInstance()->getSnippetByID($id);
        if($snippet->code_type !== 'php_shortcode'){
            return '';
        }
        if (!trim($snippet->code) or !$snippet->active) {
            return ''; // TODO message id not found
        }
        $code = new PHPRunner($snippet);
        $output = $code->execute();
        return $output;
    }
}