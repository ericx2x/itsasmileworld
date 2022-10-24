<?php
/**
 * (c) Adsstudio.
 */
namespace Adsstudio\MyCustomCSS\myphp;

use Adsstudio\MyCustomCSS\behaviors\PhpShortcodeBehavior;
use Adsstudio\MyCustomCSS\behaviors\PhpSnippetBehavior;
use Adsstudio\MyCustomCSS\pages\myphp\Choice;
use Adsstudio\MyCustomCSS\pages\myphp\Edit;
use Adsstudio\MyCustomCSS\pages\myphp\Grid;

if (!defined('ABSPATH')) {
    return;
}

new PhpSnippetBehavior();
new PhpShortcodeBehavior();

function admin_menu(){
    $page = new Grid(); $page->registerMenu();
    $page = new Choice(); $page->registerMenu();
    $page = new Edit(); $page->registerMenu();

}


\add_action('admin_menu', '\Adsstudio\MyCustomCSS\myphp\admin_menu');

require_once __DIR__."/../ajax/grid_actions.php";