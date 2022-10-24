<?php


namespace Adsstudio\MyCustomCSS\pages\myphp;


use Adsstudio\MyCustomCSS\classes\AbstractPage;
use Adsstudio\MyCustomCSS\classes\DatabaseManager;
use Adsstudio\MyCustomCSS\classes\EditorHelper;
use Adsstudio\MyCustomCSS\classes\Helper;
use Adsstudio\MyCustomCSS\classes\Import;
use Adsstudio\MyCustomCSS\models\Snippet;

class Edit extends AbstractPage
{
    private $code_types = ['php_snippet', 'php_shortcode'];
    protected $grid_link = '';
    protected $edit_link = '';

    function __construct()
    {
        parent::__construct();
        $this->grid_link = Helper::menuUrl('phpgrid');
        $this->edit_link = Helper::menuUrl('edit-php');
    }

    function registerMenu()
    {
        if (isset($_REQUEST['page']) and $_REQUEST['page'] == 'mycc--edit-php') {
            $title = $this->getMenuTitle();
            $this->menuHook = \add_submenu_page(
                MYCC_MENU_BASE,
                $title,
                $title,
                "manage_options",
                MYCC_MENU_PREFIX . 'edit-php',
                array($this, 'render')
            );

            parent::registerMenu();
        }

    }


    public function enqueue_assets()
    {
        EditorHelper::enqueue_editor();
        wp_enqueue_style('mycc-php-edit', MYCC_PLUGIN_URL . "/assets/css/min/edit.css", array(), MYCC_VERSION);

        // -----------
        $tags_enabled = DatabaseManager::getInstance()->getSettings('general', 'enable_tags'); // TODO remove

        /* the tag-it library has a number of jQuery dependencies */
        $tagit_deps = array(
            'jquery', 'jquery-ui-core',
            'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-autocomplete',
            'jquery-effects-blind', 'jquery-effects-highlight',
        );
        wp_enqueue_script('mycc-php-edit', MYCC_PLUGIN_URL . '/assets/js/min/edit.js', $tagit_deps, MYCC_VERSION, true);


        $atts = EditorHelper::editor_atts(array('mode' => 'text/x-php'), true);
        $inline_script = 'var custom_php_editor_atts = ' . $atts . ';';

        if ($tags_enabled) {
            $snippet_tags = wp_json_encode(DatabaseManager::getInstance()->getAllTags());
            $inline_script .= "\n" . 'var custom_php_all_tags = ' . $snippet_tags . ';';
        }

        wp_add_inline_script('mycc-php-edit', $inline_script, 'before');


    }

    function getMenuTitle()
    {
        $title = __('Edit PHP', "my-custom-css");
        if (isset($_GET['post']) and !empty($_GET['post'])) {
            $title = __('Edit PHP', "my-custom-css");
        }
        return $title;
    }


    function savePostedSnippet()
    {

        parent::savePostedSnippet();
    }

    function render()
    {
        $edit_id = isset($_REQUEST['id']) && intval($_REQUEST['id']) ? absint($_REQUEST['id']) : 0;
        if ($edit_id) {
            $snippet = DatabaseManager::getInstance()->getSnippetByID($edit_id);
        } else {
            $snippet = new Snippet();
            $code_type = $this->code_types[0];
            if (!empty($_REQUEST['code_type']) and in_array($_REQUEST['code_type'], $this->code_types)) {
                $code_type = $_REQUEST['code_type'];
            }
            $snippet->code_type = $code_type;
        }


        ?>
        <div class="wrap">
            <h1><?php

                if ($edit_id) {
                    esc_html_e('Edit PHP code', 'my-custom-php');
                } else {
                    esc_html_e('Add new PHP code', 'my-custom-php');
                } ?>
            </h1>

            <form method="post" id="snippet-form" action="" style="margin-top: 10px;" class="">
                <?php
                if ($snippet->id !== 0) {
                    printf('<input type="hidden" name="mycc_id" value="%d" />', $snippet->id);
                }

                printf('<input type="hidden" name="mycc_active" value="%d" />', $snippet->active);
                if (isset($_GET['code_type']) and in_array($_GET['code_type'], $this->code_types)) {
                    ?>
                    <input type="hidden" name="mycc_code_type" value="<?= $_GET['code_type'] ?>">
                    <?
                } else {
                    ?>
                    <input type="hidden" name="mycc_code_type" value="<?= $snippet->code_type ?>">
                    <?
                }
                ?>

                <div id="titlediv">
                    <div id="titlewrap">
                        <label for="title" style="display: none;"><?php _e('Name', 'my-custom-php'); ?></label>
                        <input id="title" type="text" autocomplete="off" name="mycc_name"
                               value="<?php echo esc_attr($snippet->name); ?>"
                               placeholder="<?php _e('Enter title here', 'my-custom-php'); ?>"/>
                    </div>
                </div>

                <?php if (isset($_GET['code_type']) and $_GET['code_type'] == 'php_shortcode') {
                    if (!isset($_GET['id'])) {
                        ?>
                        <p class="mycc_shortcode_placeholder">
                            <?= __('Shortcode for your snippet will be displayed here after post saving', 'my-custom-php') ?>
                        </p>
                    <? } ?>
                <?php } ?>
                <? if ($snippet->code_type == 'php_shortcode' and 0 !== $snippet->id) { ?>
                    <div class="mycc_shortcode_example">
                        <div class="mycc_shortcode_code">[my_custom_php id="<?= (int)$_GET['id']; ?>"]</div>
                        <div class="mycc_shortcode_text"><?= __("Insert this shortcode to post content.", 'my-custom-php') ?></div>
                    </div>
                <? } ?>

                <h2>
                    <label for="snippet_code">
                        <?php _e('Code', 'my-custom-php'); ?>
                    </label>
                </h2>

                <textarea id="snippet_code" class="mycc-php-code" name="mycc_code" rows="20" spellcheck="false"
                          style="font-family: monospace; width: 100%;"><?php
                    echo esc_textarea($snippet->code);
                    ?></textarea>

                <?php

                $this->renderFields($snippet);

                /* Add a nonce for security */
                wp_nonce_field('save_snippet');

                ?>

                <p class="submit">
                    <?php

                    /* Make the 'Save and Activate' button the default if the setting is enabled */

                    if ('run_once' === $snippet->scope) {

                        submit_button(null, 'primary', 'mycc_save_snippet', false);

                        submit_button(__('Save Changes and Execute Once', 'my-custom-php'), 'secondary', 'mycc_save_snippet_execute', false);

                    } elseif (!$snippet->active && DatabaseManager::getInstance()->getSettings('general', 'activate_by_default')) {

                        submit_button(
                            __('Save Changes and Activate', 'my-custom-php'),
                            'primary', 'mycc_save_snippet_activate', false
                        );

                        submit_button(null, 'secondary', 'mycc_save_snippet', false);

                    } else {

                        /* Save Snippet button */
                        submit_button(null, 'primary', 'mycc_save_snippet', false);

                        /* Save Snippet and Activate/Deactivate button */
                        if (!$snippet->active) {
                            submit_button(
                                __('Save Changes and Activate', 'my-custom-php'),
                                'secondary', 'mycc_save_snippet_activate', false
                            );

                        } else {
                            submit_button(
                                __('Save Changes and Deactivate', 'my-custom-php'),
                                'secondary', 'mycc_save_snippet_deactivate', false
                            );
                        }
                    }

                    if (0 !== $snippet->id) {

                        /* Download button */

                        if (apply_filters('mycc/enable_downloads', true)) {
                            submit_button(__('Download', 'my-custom-php'), 'secondary', 'mycc_download_snippet', false);
                        }

                        /* Export button */

                        submit_button(__('Export', 'my-custom-php'), 'secondary', 'mycc_export_snippet', false);

                        /* Delete button */

                        $confirm_delete_js = esc_js(
                            sprintf(
                                'return confirm("%s");',
                                __('You are about to permanently delete this snippet.', 'my-custom-php') . "\n" .
                                __("'Cancel' to stop, 'OK' to delete.", 'my-custom-php')
                            )
                        );

                        submit_button(
                            __('Delete', 'my-custom-php'),
                            'secondary', 'mycc_delete_snippet', false,
                            sprintf('onclick="%s"', $confirm_delete_js)
                        );
                    }

                    ?>
                </p>

            </form>
        </div>
        <?php
    }

    private function renderFields($snippet)
    {
        if (in_array($snippet->code_type, ['php_snippet'])) {
            $this->renderScopeControls($snippet);
        }
        $this->renderTagsEditor($snippet);


        do_action('mycc/php/edit/custom_fields', $snippet);
    }

    function renderScopeControls($snippet)
    {
        $scopes = [
            'global' => __("Global", 'my-custom-css'),
            'admin' => __("Admin", 'my-custom-css'),
            'front-end' => __('Frontend', 'my-custom-css'),
            'run_once' => __('Run once', 'my-custom-css'),
        ];

        $first = true;
        foreach ($scopes as $scope => $scope_label) {
            $is_active = ($scope == $snippet->scope or ($snippet->scope == '' and $first));

            ?>
            <div class="mycc-scope-control">
                <label>
                    <input type="radio" class="mycc-scope-radio" name="mycc_scope"
                           value="<?= $scope ?>" <?= ($is_active) ? 'checked' : '' ?>>
                    <?= $scope_label ?>
                </label>
            </div>
            <?php
            $first = false;
        }

    }


}