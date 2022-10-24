<?php


namespace Adsstudio\MyCustomCSS\pages\myad;


use Adsstudio\MyCustomCSS\classes\AbstractPage;
use Adsstudio\MyCustomCSS\classes\DatabaseManager;
use Adsstudio\MyCustomCSS\classes\EditorHelper;
use Adsstudio\MyCustomCSS\classes\Helper;
use Adsstudio\MyCustomCSS\classes\Import;
use Adsstudio\MyCustomCSS\models\Snippet;

class Edit extends AbstractPage
{
    private $code_types = ['ad_snippet', 'ad_shortcode'];
    protected $grid_link = '';
    protected $edit_link = '';

    function __construct()
    {
        parent::__construct();
        $this->grid_link = Helper::menuUrl('adgrid');
        $this->edit_link = Helper::menuUrl('edit-ad');
    }

    function registerMenu()
    {
        if (isset($_REQUEST['page']) and $_REQUEST['page'] == 'mycc--edit-ad') {
            $title = $this->getMenuTitle();
            $this->menuHook = \add_submenu_page(
                MYCC_MENU_BASE,
                $title,
                $title,
                "manage_options",
                MYCC_MENU_PREFIX . 'edit-ad',
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


        $atts = EditorHelper::editor_atts(array(), true);
        $inline_script = 'var custom_php_editor_atts = ' . $atts . ';';

        if ($tags_enabled) {
            $snippet_tags = wp_json_encode(DatabaseManager::getInstance()->getAllTags());
            $inline_script .= "\n" . 'var custom_php_all_tags = ' . $snippet_tags . ';';
        }

        wp_add_inline_script('mycc-php-edit', $inline_script, 'before');


    }

    function getMenuTitle()
    {
        $title = __('Edit AD', "my-custom-css");
        if (isset($_GET['post']) and !empty($_GET['post'])) {
            $title = __('Edit AD', "my-custom-css");
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
                    esc_html_e('Edit Ad code', 'my-custom-php');
                } else {
                    esc_html_e('Add new Ad code', 'my-custom-php');
                } ?>
            </h1>

            <form method="post" id="snippet-form" action="" style="margin-top: 10px;" class="">
                <?php
                if ($snippet->id != 0) {
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

                <?php if (isset($_GET['code_type']) and $_GET['code_type'] == 'ad_shortcode') {
                    if (!isset($_GET['id'])) {
                        ?>
                        <p class="mycc_shortcode_placeholder">
                            <?= __('Shortcode for your snippet will be displayed here after post saving', 'my-custom-php') ?>
                        </p>
                    <? } ?>
                <?php } ?>
                <? if ($snippet->code_type == 'ad_shortcode' and 0 !== $snippet->id) { ?>
                    <div class="mycc_shortcode_example">
                        <div class="mycc_shortcode_code">[my_custom_ad id="<?= (int)$_GET['id']; ?>"]</div>
                        <div class="mycc_shortcode_text"><?= __("Insert this shortcode to post content.", 'my-custom-php') ?></div>
                    </div>
                <? } ?>

                <h2>
                    <label for="snippet_code">
                        <?php _e('Code', 'my-custom-php'); ?>
                    </label>
                </h2>

                <textarea id="snippet_code" name="mycc_code" rows="20" spellcheck="false"
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


                    }

                    ?>
                </p>

            </form>
        </div>
        <?php
    }

    private function renderFields($snippet)
    {
        if ($snippet->code_type == 'ad_snippet') {
            $this->renderAdSnippetSettings($snippet);
        }
        $this->renderTagsEditor($snippet);


        do_action('mycc/ad/edit/custom_fields', $snippet);
    }


    private function renderAdSnippetSettings($snippet)
    {
        ?>
        <div class="mycc-control-group">
            <div class="mycc-control-row mycc-target-pages">
                <?php
                $target_post_types = ['post' => __("Display on posts", 'my-custom-css'), 'page' => __("Display on pages", 'my-custom-css')];
                $first = true;
                foreach ($target_post_types as $post_type => $post_type_label) {
                    $is_active = (in_array($post_type, $snippet->post_type) or ($first and !count($snippet->post_type)));
                    ?>
                    <label class="mycc-label">
                        <input type="checkbox" name="mycc_post_type[]"
                               value="<?= $post_type ?>" <?= ($is_active) ? 'checked' : '' ?>>
                        <?= $post_type_label ?>
                    </label>
                    <?php
                    $first = false;
                } ?>
            </div>
            <div class="mycc-control-row mycc-target-devices">
                <?php
                $target_devices = ['desktop' => __("Display on desktop", 'my-custom-css'), 'mobile' => __("Display on mobile", 'my-custom-css')];
                $first = true;
                foreach ($target_devices as $device => $device_label) {
                    $is_active = (in_array($device, $snippet->device_type ) or ($first and !count($snippet->device_type)));
                    ?>
                    <label class="mycc-label ">
                        <input type="checkbox" name="mycc_device_type[]"
                               value="<?= $device ?>" <?= ($is_active) ? 'checked' : '' ?>>
                        <?= $device_label ?>
                    </label>
                    <?php
                    $first = false;
                } ?>

            </div>
        </div>
        <p><?= __("Select the device type to show the ad unit. Check both, if you want to display the ad block on desktop and mobile devices.", "my-custom-css") ?></p>
        <div class="mycc-mount-point">
            <?php
            $target_points = ['before_content' => __("Before content", 'my-custom-css'), 'after_content' => __("After content", 'my-custom-css'), 'after_paragraph' => __("After paragraph", 'my-custom-css')];
            $hints = [
                'before_content' => __('The ad block will be placed right after the post title', 'my-custom-css'),
                'after_content' => __('The ad block will be placed right after the last paragraph of the post', 'my-custom-css'),
                'after_paragraph' => __('The ad block will be placed right after the predefined paragraph', 'my-custom-css')
            ];
            $first = true;
            foreach ($target_points as $point => $point_label) {
                $is_active = (in_array($point, $snippet->mount_point) or ($first and !count($snippet->device_type)));
                ?>
                <label class="mycc-label ">
                    <input type="checkbox" name="mycc_mount_point[]"
                           value="<?= $point ?>" <?= ($is_active) ? 'checked' : '' ?>>
                    <?= $point_label ?>
                    <?php if ($point == 'after_paragraph') { ?>
                        <input class="mycc-aside-control" type="number" name="mycc_mount_point_num"
                               value="<?= ($snippet->mount_point_num > 0) ? (int)$snippet->mount_point_num : 3; ?>">
                    <?php } ?>
                </label>
                <?php if (isset($hints[$point])) { ?>
                    <p><?= $hints[$point] ?></p>
                    <?php
                }
                $first = false;
            } ?>
        </div>
        <?php

    }


}