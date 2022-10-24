<?php


namespace Adsstudio\MyCustomCSS\pages\myad;


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
            MYCC_MENU_PREFIX . 'choice-ad',
            array($this, 'render')
        );
        parent::registerMenu();

    }

    function getMenuTitle()
    {
        $title = __('Create Ad', "my-custom-css");

        return $title;
    }

    function render()
    {
        \wp_enqueue_style('mycc_choice-css', MYCC_PLUGIN_URL . 'assets/choice.css', [], MYCC_VERSION);
        \wp_enqueue_script('mycc_choice-js', MYCC_PLUGIN_URL . 'assets/choice.js', [], MYCC_VERSION);

        $addon_installed = is_plugin_active("my-custom-advertising/my-custom-advertising.php");
        $nonce = wp_create_nonce('mycc-install-addon');

        ?>
        <div class="wrap mycc-code-choice">
            <div>
                <h2 class="mycc-code-choice-header"><?= __("Choose what you want to create", "my-custom-css"); ?></h2>
            </div>

            <div class="mycc-code-choice-items">
                <div class="mycc-code-choice-item">
                    <div class="mycc-code-choice-title"><?= __("AD SHORTCODE", "my-custom-css"); ?></div>
                    <p class="mycc-code-choice-desc"><?= __("Easy way to insert an ad unit anywhere in your site. Just paste the ad code into the handler, the shortcode will be generated automatically.", "my-custom-css"); ?></p>
                    <a class="button button-primary button-large <?=($addon_installed)?'':'js-disabled-link'?>"
                       href="/wp-admin/admin.php?page=mycc--edit-ad&code_type=ad_shortcode"><?= __("Create", "my-custom-css"); ?></a>
                </div>
                <div class="mycc-code-choice-item">
                    <div class="mycc-code-choice-title"><?= __("CUSTOM ADS", "my-custom-css"); ?></div>
                    <p class="mycc-code-choice-desc"><?= __("Paste your ad code and set up automatic placement in posts or in pages of your site.", "my-custom-css"); ?></p>
                    <a class="button button-primary button-large <?=($addon_installed)?'':'js-disabled-link'?>"
                       href="/wp-admin/admin.php?page=mycc--edit-ad&code_type=ad_snippet"><?= __("Create", "my-custom-css"); ?></a>
                </div>
            </div>

        </div>
        <?
        if (!$addon_installed) {
            ?>
            <div class="mycc-requirements js-mycc-requirements">
                <button class="mycc-btn js-mycc-activate-addon" data-nonce="<?= $nonce ?>"
                        type="button" disabled><?= __('Activate', 'my-custom-css'); ?></button>
                <label class="mycc-requirements-agree">
                    <input class="js-mycc-agree-checkbox" type="checkbox">
                    <div class="mycc-requirements-right">
                        <div class="mycc-requirements-agree-text"><?= __('I agree', 'my-custom-css'); ?></div>
                        <a class="mycc-requirements-link" href="http://mycustomcss.com/license-agreement/"><?= __('terms and conditions', 'my-custom-css'); ?></a>
                        </div>
                </label>

            </div>
            <?php
        }
    }
}