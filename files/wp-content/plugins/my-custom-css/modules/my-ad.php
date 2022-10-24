<?php
/**
 * (c) Adsstudio.
 */

namespace Adsstudio\MyCustomCSS\myadd;
use Adsstudio\MyCustomCSS\behaviors\AdShortcodeBehavior,
    Adsstudio\MyCustomCSS\behaviors\AdSnippetBehavior,
    Adsstudio\MyCustomCSS\pages\myad\Choice,
    Adsstudio\MyCustomCSS\pages\myad\Edit,
    Adsstudio\MyCustomCSS\classes\Installer;
use Adsstudio\MyCustomCSS\pages\myad\Grid;


if (!defined('ABSPATH')) {
    return;
}

// load ad-snippets classes


new AdShortcodeBehavior();
new AdSnippetBehavior();

function admin_menu(){
    $page = new Grid(); $page->registerMenu();
    $page = new Choice(); $page->registerMenu();
    $page = new Edit(); $page->registerMenu();

}


\add_action('admin_menu', '\Adsstudio\MyCustomCSS\myadd\admin_menu');


// AJAX actions

\add_action( 'wp_ajax_mycc_install_addon', array( 'Adsstudio\MyCustomCSS\classes\Installer', 'installAddon' ) );

require_once __DIR__."/../ajax/grid_actions.php";
