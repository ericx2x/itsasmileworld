<?php

namespace Adsstudio\MyCustomCSS\classes;

/* The WP_List_Table base class is not included by default, so we need to load it */

use Adsstudio\MyCustomCSS\models\Snippet;

if (!class_exists('\WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// TODO

abstract class AbstractListTable extends \WP_List_Table
{
    public $statuses = array('all', 'active', 'inactive', 'recently_activated');
    public $is_network;
    private $current_status = 'all';
    private $page = '';

    protected $shortcode = '';
    protected $code_types = [];

    public function __construct()
    {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'snippet',
            'plural' => 'snippets',
            'ajax' => true
        ));


        if (isset($_REQUEST['status']) && in_array($_REQUEST['status'], $this->statuses)) {
            $this->current_status = $_REQUEST['status'];
        }
    }

    private function get_statuses()
    {
        $data = DatabaseManager::getInstance()->countSnippets($this->code_types);
        return $data;
    }

    function get_views()
    {
        $status_links = array();

        $statuses = $this->get_statuses();
        /* Loop through the view counts */
        foreach ($statuses as $type => $count) {

            /* Don't show the view if there is no count */
            if (!$count) {
                continue;
            }

            /* Define the labels for each view */
            $labels = array(

                /* translators: %s: total number of snippets */
                'all' => _n('All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'my-custom-php'),

                /* translators: %s: total number of active snippets */
                'active' => _n('Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', $count, 'my-custom-php'),

                /* translators: %s: total number of inactive snippets */
                'inactive' => _n('Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', $count, 'my-custom-php'),

                /* translators: %s: total number of recently activated snippets */
//                'recently_activated' => _n( 'Recently Active <span class="count">(%s)</span>', 'Recently Active <span class="count">(%s)</span>', $count, 'my-custom-php' ),
            );

            /* The page URL with the status parameter */
            $url = esc_url(add_query_arg('status', $type));

            /* Add a class if this view is currently being viewed */
            $class = $type === $this->current_status ? ' class="current"' : '';

            /* Add the view count to the label */
            $text = sprintf($labels[$type], number_format_i18n($count));

            /* Construct the link */
            $status_links[$type] = sprintf('<a href="%s"%s>%s</a>', $url, $class, $text);
        }
        return $status_links;

    }


    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
            case 'shortcode':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes TODO del
        }
    }

    protected function getItemLinks($item)
    {
        return [];
    }


    public function column_name($item)
    {
        $actions = $this->getItemLinks($item);

        // TODO COLOR ACTIVE
        return sprintf('<a class="adp-shortcode-name" href="%2$s">%1$s</a>%3$s',
            $item['name'],
            $this->getEditLink($item),
            $this->row_actions($actions)
        );
    }

    public function column_active($item)
    {
        $active_class = '';
        $active = $item['active'];
        if ($active) {
            $active_class = 'active';
        }
        return sprintf('<a class="mycc-active-toggle %1$s" data-id="%2$s" data-nonce="%3$s" href="#"></a>',
            $active_class,
            $item['id'],
            wp_create_nonce('mycc-active-toggle'));


    }

    /**
     * Выводит колонку shortcode
     *
     * @param  mixed $item
     *
     * @return string
     */
    public function column_shortcode($item)
    {
        // TODO check
        return ($item['shortcode']) ? '<input type="text" class="adp-shortcode-input" value="' . esc_attr($item['shortcode']) . '" readonly>' : '&nbsp;';
    }


    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item['id']
        );
    }


    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'name' => __('Name', 'my-custom-css'),
            'shortcode' => __('Shortcode', 'my-custom-css'),
            'active' => __('Active', 'my-custom-css'),
            'tags' => __('Tags', 'my-custom-php'),
        );
        return $columns;
    }


    public function get_bulk_actions()
    {
        // TODO check
        $actions = array(
            'active' => __('Active', 'my-custom-css'),
            'deactive' => __('Deactive', 'my-custom-css'),
            'clone' => __('Clone', 'my-custom-css'),
            'delete' => __('Delete', 'my-custom-css'),
        );
        return $actions;
    }


    public function process_bulk_action()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        // TODO check
        if ('active' === $this->current_action()) {
            $snippets = isset($_POST['snippet']) ? $_POST['snippet'] : false;
            if (!$snippets) {
                return false;
            }
            foreach ($snippets as $snippet_id) {
                $snippet = DatabaseManager::getInstance()->getSnippetByID($snippet_id);
                if (!$snippet) {
                    return false;
                }
                $snippet->active = 1;
                DatabaseManager::getInstance()->saveSnippet($snippet);
            }
            return true;
        }
        if ('deactive' === $this->current_action()) {
            $snippets = isset($_POST['snippet']) ? $_POST['snippet'] : false;
            if (!$snippets) {
                return false;
            }
            foreach ($snippets as $snippet_id) {
                $snippet = DatabaseManager::getInstance()->getSnippetByID($snippet_id);
                if (!$snippet) {
                    return false;
                }
                $snippet->active = 0;
                DatabaseManager::getInstance()->saveSnippet($snippet);
            }
            return true;
        }
        if ('delete' === $this->current_action()) {
            $snippets = isset($_POST['snippet']) ? $_POST['snippet'] : false;
            if (!$snippets) {
                return false;
            }
            foreach ($snippets as $snippet_id) {
                DatabaseManager::getInstance()->deleteSnippet($snippet_id);
            }
            return true;
        }
        if ('clone' === $this->current_action()) {
            $snippets = isset($_POST['snippet']) ? $_POST['snippet'] : false;
            if (!$snippets) {
                return false;
            }
            foreach ($snippets as $snippet_id) {
                $snippet = DatabaseManager::getInstance()->getSnippetByID($snippet_id);
                if (!$snippet) {
                    continue;
                }
                $snippet->id = 0;
                $snippet->name .= ' ' . __('(copy)', 'my-custom-css');
                $new_id = DatabaseManager::getInstance()->saveSnippet($snippet);

                if (!$new_id) {
                    return false;
                }
            }
            return true;
        }
    }


    public function prepare_items()
    {
        $this->process_bulk_action();
        $this->process_item_action();

        $per_page = 999;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);


        $active = null; // all values
        if ($this->current_status == 'active') {
            $active = 1;
        }
        if ($this->current_status == 'inactive') {
            $active = 0;
        }
        $posts = DatabaseManager::getInstance()->getSnippets($active, $this->code_types);
        if(isset($_REQUEST['tag']) and !empty($_REQUEST['tag'])){
            $posts = array_filter($posts, function($snippet){
                return in_array($_REQUEST['tag'], $snippet->tags);
            });
        }
        $data = array();
        foreach ($posts as $post) {
            $data[] = array(
                'id' => $post->id,
                'name' => $post->name ? esc_attr($post->name) : __('(untitled)', 'my-custom-css'),
                'shortcode' => ($post->code_type == 'ad_shortcode' or $post->code_type == 'php_shortcode') ? '[' . $this->shortcode . ' id="' . $post->id . '"]' : '',
                'tags' => $post->tags,
                'active' => $post->active
            );
        }

        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $this->items = $data;
        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }


    public function get_current_tags()
    {
        global $snippets, $status;

        /* If we're not viewing a snippets table, get all used tags instead */
        if (!isset($snippets, $status)) {
            $tags = DatabaseManager::getInstance()->getAllTags();
        } else {
            $tags = array();

            /* Merge all tags into a single array */
            foreach ($snippets[$status] as $snippet) {
                $tags = array_merge($snippet->tags, $tags);
            }

            /* Remove duplicate tags */
            $tags = array_unique($tags);
        }

        sort($tags);

        return $tags;
    }

    public function extra_tablenav($which)
    {
        global $status, $wpdb;

        if ('top' === $which) {

            /* Tags dropdown filter */
            $tags = $this->get_current_tags();

            if (count($tags)) {
                $query = isset($_REQUEST['tag']) ? $_REQUEST['tag'] : '';

                echo '<div class="alignleft actions">';
                echo '<select name="tag">';

                printf("<option %s value=''>%s</option>\n",
                    selected($query, '', false),
                    __('Show all tags', 'my-custom-php')
                );

                foreach ($tags as $tag) {

                    printf("<option %s value='%s'>%s</option>\n",
                        selected($query, $tag, false),
                        esc_attr($tag),
                        $tag
                    );
                }

                echo '</select>';

                submit_button(__('Filter', 'my-custom-php'), 'button', 'filter_action', false);
                echo '</div>';
            }
        }

        echo '<div class="alignleft actions">';


        do_action('mycc/list_table/actions', $which);

        echo '</div>';
    }

    protected function column_tags($snippet)
    {

        /* Return a placeholder if there are no tags */
        if (!count($snippet['tags'])) {
            return '&#8212;';
        }

        $out = array();

        /* Loop through the tags and create a link for each one */
        foreach ($snippet['tags'] as $tag) {
            $out[] = sprintf('<a href="%s">%s</a>',
                esc_url(add_query_arg('tag', esc_attr($tag))),
                esc_html($tag)
            );
        }

        return join(', ', $out);
    }

    protected function getEditLink($item)
    {
        return '';
    }

    protected function process_item_action()
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET" and isset($_GET['action'])) {
            $error = false;
            // todo check nonce
            if (isset($_GET['id'])) {
                $snippet_id = (int)$_GET['id'];
                $snippet = DatabaseManager::getInstance()->getSnippetByID($snippet_id);
                if ($snippet) {
                    switch ($_GET['action']) {
                        case "clone":
                            $snippet->id = 0;
                            $snippet->name .= ' ' . __('(copy)', 'my-custom-css');
                            $new_id = DatabaseManager::getInstance()->saveSnippet($snippet);
                            if (!$new_id) {
                                $error = true;
                            }
                            break;

                        case "delete":
                            DatabaseManager::getInstance()->deleteSnippet($snippet_id);
                            break;
                    }
                }

            }
            $_SERVER['REQUEST_URI'] = remove_query_arg(array('action', 'id', '_wpnonce'));
            wp_redirect($_SERVER['REQUEST_URI']);

            exit;
        }
    }
}
