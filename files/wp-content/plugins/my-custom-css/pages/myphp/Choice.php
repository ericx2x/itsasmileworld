<?php


namespace Adsstudio\MyCustomCSS\pages\myphp;


use Adsstudio\MyCustomCSS\classes\AbstractPage;

class Choice extends AbstractPage
{
    function registerMenu()
    {
        $title = $this->getMenuTitle();
        $this->menuHook = \add_submenu_page(
            MYCC_MENU_BASE,
            $title,
            $title,
            "manage_options",
            MYCC_MENU_PREFIX . 'choice-php',
            array($this, 'render')
        );
        parent::registerMenu();
    }

    function getMenuTitle()
    {
        $title = __('Create PHP', "my-custom-css");

        return $title;
    }

    function render()
    {
        \wp_enqueue_style('mycc_choice-css', MYCC_PLUGIN_URL . 'assets/choice.css', [], MYCC_VERSION);

        ?>
        <div class="wrap mycc-code-choice">
            <div>
                <h2 class="mycc-code-choice-header"><?= __("Choose what you want to create", "my-custom-css"); ?></h2>
            </div>

            <div class="mycc-code-choice-items">
                <div class="mycc-code-choice-item">
                    <div class="mycc-code-choice-title"><?= __("PHP SHORTCODE", "my-custom-css"); ?></div>
                    <p class="mycc-code-choice-desc"><?= __("Easy way to create a shortcode. Just write a handler and the shortcode will be generated automatically", "my-custom-css"); ?></p>
                    <a class="button button-primary button-large"
                       href="/wp-admin/admin.php?page=mycc--edit-php&code_type=php_shortcode"><?= __("Create", "my-custom-css"); ?></a>
                </div>
                <div class="mycc-code-choice-item">
                    <div class="mycc-code-choice-title"><?= __("PHP SNIPPET", "my-custom-css"); ?></div>
                    <p class="mycc-code-choice-desc"><?= __("Custom php code. You may use hooks and filters", "my-custom-css"); ?></p>
                    <a class="button button-primary button-large"
                       href="/wp-admin/admin.php?page=mycc--edit-php&code_type=php_snippet"><?= __("Create", "my-custom-css"); ?></a>
                </div>
            </div>

        </div>
      <?php
    }
}