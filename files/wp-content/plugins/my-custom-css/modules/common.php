<?php

namespace Adsstudio\MyCustomCSS\common;

use Adsstudio\MyCustomCSS\pages\Import;

function admin_menu()
{
    $page = new Import();
    $page->registerMenu();
}


add_action('admin_menu', '\Adsstudio\MyCustomCSS\common\admin_menu');
