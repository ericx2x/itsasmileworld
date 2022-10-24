<?php
function mycc_ajax_active_toggle(){
    check_ajax_referer('mycc-active-toggle');
    if(!isset($_POST['id']) or 0 === ($id =(int)$_POST['id'])){
        return false;
    }
    $snippet = \Adsstudio\MyCustomCSS\classes\DatabaseManager::getInstance()->getSnippetByID($id);
    if(!$snippet){
        return false;
    }
    $snippet->active = ! $snippet->active;
    \Adsstudio\MyCustomCSS\classes\DatabaseManager::getInstance()->saveSnippet($snippet);
    return true;

}
add_action('wp_ajax_mycc_active_toggle', 'mycc_ajax_active_toggle');
