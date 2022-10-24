<?php
/**
 * (c) mediagroup.
 */

namespace Adsstudio\MyCustomCSS\classes;


use Adsstudio\MyCustomCSS\models\Snippet;

abstract class AbstractPage
{
    // must be overrided
    protected $grid_link = '';
    protected $edit_link = '';
    protected $menuHook;

    function __construct()
    {

    }

    public function registerMenu(){
        if($this->menuHook){
            add_action('load-' . $this->menuHook, [$this, 'load']);
        }
    }

    function load()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        $this->onSubmit();
    }

    public function enqueue_assets()
    {
        // override
    }

    protected function onSubmit()
    {
        //TODO move this from grid
        /* Check for a valid nonce */
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'save_snippet')) {
            return;
        }

        if (isset($_POST['mycc_save_snippet']) || isset($_POST['mycc_save_snippet_execute']) ||
            isset($_POST['mycc_save_snippet_activate']) || isset($_POST['mycc_save_snippet_deactivate'])) {
            $this->savePostedSnippet();
        }

        if (isset($_POST['mycc_id'])) {

            /* Delete the snippet if the button was clicked */
            if (isset($_POST['mycc_delete_snippet'])) {
                DatabaseManager::getInstance()->deleteSnippet($_POST['mycc_id']);
                wp_redirect(add_query_arg('result', 'delete', $this->grid_link));
                exit;
            }

            /* Export the snippet if the button was clicked */
            if (isset($_POST['mycc_export_snippet'])) {
                Import::export_snippets(array($_POST['mycc_id']));// todo ren
            }

            /* Download the snippet if the button was clicked */
            if (isset($_POST['mycc_download_snippet'])) {
                Import::download_snippets(array($_POST['mycc_id']));
            }
        }
    }

    function savePostedSnippet()
    {
        $snippet = new Snippet();
        $prefix_len = strlen('mycc_');

        $postCopy = [];
        foreach ($_POST as $field => $value) {
            if ('mycc_' === substr($field, 0, $prefix_len)) {

                /* Remove the 'snippet_' prefix from field name and set it on the object */
                $key = substr($field, $prefix_len);
                if(is_string($value)){
                    $postCopy[$key] = stripslashes($value);
                }else{
                    $postCopy[$key] = $value;
                }

            }
        }
        $snippet->setData($postCopy);


        if (isset($_POST['mycc_save_snippet_execute']) && 'run_once' !== $snippet->scope) {
            unset($_POST['mycc_save_snippet_execute']);
            $_POST['mycc_save_snippet'] = 'yes';
        }

        /* Activate or deactivate the snippet before saving if we clicked the button */

        if (isset($_POST['mycc_save_snippet_execute'])) {
            $snippet->active = 1;
        } elseif (isset($_POST['mycc_save_snippet_activate'])) {
            $snippet->active = 1;
        } elseif (isset($_POST['mycc_save_snippet_deactivate'])) {
            $snippet->active = 0;
        }
        if (isset($_POST['mycc_code_type']) and in_array($_POST['mycc_code_type'], array('default', 'shortcode'))) {
            $snippet->code_type = $_POST['mycc_code_type'];
        }

        // todo check html code

        /* Save the snippet to the database */
        $snippet_id = DatabaseManager::getInstance()->saveSnippet($snippet);


        /* If the saved snippet ID is invalid, display an error message */
        if (!$snippet_id || $snippet_id < 1) {
            /* An error occurred */
            wp_redirect(add_query_arg('result', 'save-error'));
            exit;
        }

        /* Display message if a parse error occurred */
        if (isset($code_error) && $code_error) {// TODO ??
            wp_redirect(add_query_arg(
                array('id' => $snippet_id, 'result' => 'code-error')
            ));
            exit;
        }

        /* Set the result depending on if the snippet was just added */
        $result = isset($_POST['mycc_id']) ? 'updated' : 'added';

        /* Append a suffix if the snippet was activated or deactivated */
        if (isset($_POST['mycc_save_snippet_activate'])) {
            $result .= '-and-activated';
        } elseif (isset($_POST['mycc_save_snippet_deactivate'])) {
            $result .= '-and-deactivated';
        } elseif (isset($_POST['mycc_save_snippet_execute'])) {
            $result .= '-and-executed';
        }

        /* Redirect to edit snippet page */
        $redirect_uri = add_query_arg(
            array('id' => $snippet_id, 'result' => $result),
            $this->edit_link
        );

        if (isset($_POST['mycc_cursor_line'], $_POST['mycc_cursor_ch']) &&
            is_numeric($_POST['mycc_cursor_line']) && is_numeric($_POST['mycc_cursor_ch'])) {
            $redirect_uri = add_query_arg('cursor_line', intval($_POST['mycc_cursor_line']), $redirect_uri);
            $redirect_uri = add_query_arg('cursor_ch', intval($_POST['mycc_cursor_ch']), $redirect_uri);
        }

        wp_redirect(esc_url_raw($redirect_uri));
        exit;
    }


    function renderTagsEditor($snippet)
    {
        ?>
        <h2 style="margin: 25px 0 10px;">
            <label for="snippet_tags" style="cursor: auto;">
                <?php esc_html_e('Tags', 'my-custom-php'); ?>
            </label>
        </h2>

        <input type="text" id="snippet_tags" name="mycc_tags" style="width: 100%;"
               placeholder="<?php esc_html_e('Enter a list of tags; separated by commas', 'my-custom-php'); ?>"
               value="<?php echo esc_attr(implode(',', $snippet->tags)); ?>"/>
        <?php
    }
}