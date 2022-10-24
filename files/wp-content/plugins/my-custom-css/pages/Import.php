<?php

namespace Adsstudio\MyCustomCSS\pages;

use Adsstudio\MyCustomCSS\classes\AbstractPage;
use Adsstudio\MyCustomCSS\classes\DatabaseManager;
use Adsstudio\MyCustomCSS\models\Snippet;
use DOMDocument;

class Import extends AbstractPage
{
    function registerMenu()
    {
        $title = $this->getMenuTitle();
        $this->menuHook = \add_submenu_page(
            MYCC_MENU_BASE,
            $title,
            $title,
            "manage_options",
            MYCC_MENU_PREFIX . 'import',
            array($this, 'render')
        );

        add_action('admin_init', [$this, 'registerImporter']);
        parent::registerMenu();
    }

    function load()
    {
        parent::load();

        $this->registerImporter();
        $this->processImportFiles();
    }

    public function registerImporter()
    {
        $user = wp_get_current_user();
        if (!defined('WP_LOAD_IMPORTERS') || !$user->has_cap('manage_options')) {
            return;
        }

        /* Register the My Custom PHP importer with WordPress */
        \register_importer(
            'my-custom-css',
            __('My Custom CSS', 'my-custom-php'),
            __('Import php/ad snippets from a export file', 'my-custom-php'),
            array($this, 'render')
        );
    }


    function processImportFiles()
    {
        /* Ensure the import file exists */
        if (!isset($_FILES['custom_php_import_files']) || !count($_FILES['custom_php_import_files'])) {
            return;
        }

        $count = 0;
        $network = is_network_admin();
        $uploads = $_FILES['custom_php_import_files'];
        $dup_action = isset($_POST['duplicate_action']) ? $_POST['duplicate_action'] : 'ignore';
        $error = false;

        /* Loop through the uploaded files and import the snippets */

        foreach ($uploads['tmp_name'] as $i => $import_file) {
            $ext = pathinfo($uploads['name'][$i]);
            $ext = $ext['extension'];
            $mime_type = $uploads['type'][$i];

            if ('json' === $ext || 'application/json' === $mime_type) {
                $result = self::importJson($import_file, $network, $dup_action);
            } elseif ('xml' === $ext || 'text/xml' === $mime_type) {
                $result = self::importXml($import_file, $network, $dup_action);
            } else {
                $result = false;
            }

            if (false === $result || -1 === $result) {
                $error = true;
            } else {
                $count += count($result);
            }
        }

        /* Send the amount of imported snippets to the page */
        $url = add_query_arg($error ? array('error' => true) : array('imported' => $count));
        wp_redirect(esc_url_raw($url));
        exit;
    }

    function getMenuTitle()
    {
        $title = __('Import snippets (php, ad)', "my-custom-css");

        return $title;
    }

    protected function print_messages()
    {

        if (isset($_REQUEST['error']) && $_REQUEST['error']) {
            echo '<div id="message" class="error fade"><p>';
            _e('An error occurred when processing the import files.', 'my-custom-php');
            echo '</p></div>';
        }

        if (isset($_REQUEST['imported']) && intval($_REQUEST['imported']) >= 0) {
            echo '<div id="message" class="updated fade"><p>';

            $imported = intval($_REQUEST['imported']);

            if (0 === $imported) {
                esc_html_e('No snippets were imported.', 'my-custom-php');

            } else {

                printf(
                /* translators: 1: amount of snippets imported, 2: link to Snippets menu */
                    _n(
                        'Successfully imported <strong>%1$d</strong> snippet.',
                        'Successfully imported <strong>%1$d</strong> snippets.',
                        $imported, 'my-custom-php'
                    ),
                    $imported
                );
            }

            echo '</p></div>';
        }
    }

    public function render()
    {

        /**
         * HTML code for the Import Snippets page
         *
         * @package MyCustomPhp
         * @subpackage Views
         */

        /* Bail if accessed directly */
        if (!defined('ABSPATH')) {
            return;
        }

        $max_size_bytes = apply_filters('import_upload_size_limit', wp_max_upload_size());

        ?>
        <div class="wrap">
            <h1><?php _e('Import PHP code', 'my-custom-php');


                ?></h1>

            <div class="narrow">

                <p><?php _e('Upload one or more PHP Code export files and the snippets will be imported.', 'my-custom-php'); ?></p>

                <p><?php
                    __('Afterwards, you will need to visit the "My custom php" or "My custom ads" page to activate the imported snippets.', 'my-custom-php');
                    ?></p>


                <form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form"
                      name="custom_php_import">

                    <h2><?php _e('Duplicate Snippets', 'my-custom-php'); ?></h2>

                    <p class="description">
                        <?php esc_html_e('What should happen if an existing snippet is found with an identical name to an imported snippet?', 'my-custom-php'); ?>
                    </p>

                    <fieldset>
                        <p>
                            <label>
                                <input type="radio" name="duplicate_action" value="ignore" checked="checked">
                                <?php esc_html_e('Ignore any duplicate snippets: import all snippets from the file regardless and leave all existing snippets unchanged.', 'my-custom-php'); ?>
                            </label>
                        </p>

                        <p>
                            <label>
                                <input type="radio" name="duplicate_action" value="replace">
                                <?php esc_html_e('Replace any existing snippets with a newly imported snippet of the same name.', 'my-custom-php'); ?>
                            </label>
                        </p>

                        <p>
                            <label>
                                <input type="radio" name="duplicate_action" value="skip">
                                <?php esc_html_e('Do not import any duplicate snippets; leave all existing snippets unchanged.', 'my-custom-php'); ?>
                            </label>
                        </p>
                    </fieldset>

                    <h2><?php _e('Upload Files', 'my-custom-php'); ?></h2>

                    <p class="description">
                        <?php _e('Choose one or more PHP Code (.xml or .json) files to upload, then click "Upload files and import".', 'my-custom-php'); ?>
                    </p>

                    <fieldset>
                        <p>
                            <label for="upload"><?php esc_html_e('Choose files from your computer:', 'my-custom-php'); ?></label>
                            <?php printf(
                            /* translators: %s: size in bytes */
                                esc_html__('(Maximum size: %s)', 'my-custom-php'),
                                size_format($max_size_bytes)
                            ); ?>
                            <input type="file" id="upload" name="custom_php_import_files[]" size="25"
                                   accept="application/json,.json,text/xml" multiple="multiple">
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="max_file_size" value="<?php echo esc_attr($max_size_bytes); ?>">
                        </p>
                    </fieldset>

                    <?php
                    do_action('mycc/admin/import_form');
                    submit_button(__('Upload files and import', 'my-custom-php'));
                    ?>
                </form>
            </div>
        </div>

        <?php
    }

    static function importJson($file, $multisite = null, $dup_action = 'ignore')
    {

        if (!file_exists($file) || !is_file($file)) {
            return false;
        }

        $raw_data = file_get_contents($file);
        $data = json_decode($raw_data, true);
        $snippets = array();

        /* Reformat the data into snippet objects */
        foreach ($data['snippets'] as $snippet) {
            $snippet = new Snippet($snippet);
            $snippets[] = $snippet;
        }

        $imported = self::saveImported($snippets, $multisite, $dup_action);
        do_action('mycc/import/json', $file, $multisite);

        return $imported;
    }

    static function importXml( $file, $multisite = null, $dup_action = 'ignore' ) {

        if ( ! file_exists( $file ) || ! is_file( $file ) ) {
            return false;
        }

        $dom = new DOMDocument( '1.0', get_bloginfo( 'charset' ) );
        $dom->load( $file );

        $snippets_xml = $dom->getElementsByTagName( 'snippet' );
        $fields = array( 'name', 'description', 'desc', 'code', 'tags', 'scope', 'code_type' );

        $snippets = array();

        /* Loop through all snippets */

        /** @var \DOMElement $snippet_xml */
        $allowed = Snippet::fieldList();
        foreach ( $snippets_xml as $snippet_xml ) {
            $snippet = new Snippet();

            /* Build a snippet object by looping through the field names */
            foreach ( $fields as $field_name ) {

                /* Fetch the field element from the document */
                $field = $snippet_xml->getElementsByTagName( $field_name )->item( 0 );

                /* If the field element exists, add it to the snippet object */
                if ( isset( $field->nodeValue )  and in_array($field_name, $allowed)) {
                    $snippet->$field_name = $field->nodeValue;
                }
            }

            /* Get scope from attribute */
            $scope = $snippet_xml->getAttribute( 'scope' );
            if ( ! empty( $scope ) ) {
                $snippet->scope = $scope;
            }

            $snippets[] = $snippet;
        }

        $imported = self::saveImported( $snippets, $dup_action, $multisite );
        do_action( 'mycc/import/xml', $file, $multisite );

        return $imported;
    }

    static private function saveImported( $snippets, $multisite = null, $dup_action = 'ignore' ) {

        /* Get a list of existing snippet names keyed to their IDs */
        $existing_snippets = array();
        if ( 'replace' == $dup_action || 'skip' === $dup_action ) {
            $all_snippets = DatabaseManager::getInstance()->getSnippets();

            foreach ( $all_snippets as $snippet ) {
                if ( $snippet->name ) {
                    $existing_snippets[ $snippet->name ] = $snippet->id;
                }
            }
        }

        /* Save a record of the snippets which were imported */
        $imported = array();

        /* Loop through the provided snippets */
        foreach ( $snippets as $snippet ) {
            $snippet->id = 0; // clear old id
//            $snippet->active = 0; // prevent auto run

            /* Check if the snippet already exists */
            if ( 'ignore' !== $dup_action && isset( $existing_snippets[ $snippet->name ] ) ) {

                /* If so, either overwrite the existing ID, or skip this import */
                if ( 'replace' === $dup_action ) {
                    $snippet->id = $existing_snippets[ $snippet->name ];
                } elseif ( 'skip' === $dup_action ) {
                    continue;
                }
            }

            /* Save the snippet and increase the counter if successful */
            if ( $snippet_id = DatabaseManager::getInstance()->saveSnippet( $snippet ) ) {
                $imported[] = $snippet_id;
            }
        }

        return $imported;
    }
}