<?php
/**
 * (c) mediagroup.
 */

namespace Adsstudio\MyCustomCSS\pages\myad;

use Adsstudio\MyCustomCSS\classes\AbstractPage;
use Adsstudio\MyCustomCSS\classes\AdListTable;


class Grid extends AbstractPage
{
    private $listTable;
    protected $menuHook;

    public function registerMenu()
    {
        $title = $this->getMenuTitle();
        $this->menuHook = \add_submenu_page(
            MYCC_MENU_BASE,
            $title,
            $title,
            "manage_options",
            MYCC_MENU_PREFIX . 'adgrid',
            array($this, 'render')
        );
        add_action('load-' . $this->menuHook, [$this, 'createListTable']);

        parent::registerMenu();

    }

    public function createListTable()
    {
        $this->listTable = new AdListTable;
        $this->listTable->prepare_items();
    }


    function getMenuTitle()
    {
        $title = __('My custom ADs', "my-custom-css");
        return $title;
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('mycc-grid', MYCC_PLUGIN_URL . "/assets/css/min/grid.css", array(), MYCC_VERSION);
        wp_enqueue_script('mycc-grid', MYCC_PLUGIN_URL . "/assets/js/grid.js", array('jquery'), MYCC_VERSION);
    }

    function render()
    {
        $nonce = wp_create_nonce('mycc-item-activation');
        $list_table = $this->listTable;
        ?>

        <div class="wrap mycc-ad-page">
            <hr class="wp-header-end">
            <h1><?php
                esc_html_e('My Custom Ads', 'my-custom-php');
                printf('<a href="%2$s" class="page-title-action add-new-h2">%1$s</a>',
                    esc_html_x('Add New', 'snippet', 'my-custom-php'),
                    'admin.php?page=' . MYCC_MENU_PREFIX . 'choice-ad'
                );

                printf('<a href="%2$s" class="page-title-action">%1$s</a>',
                    esc_html_x('Import', 'snippets', 'my-custom-php'),
                    'admin.php?page=' . MYCC_MENU_PREFIX . 'import'
                );


                $list_table->search_notice();

                ?></h1>


            <?php $list_table->views(); ?>


            <form method="post" action="">
                <?php
                $list_table->display();
                ?>
            </form>
            <input type="hidden" id="custom_php_ajax_nonce" value="">
        </div>
        <?php
    }
}