<?php
/**
 * (c) mediagroup.
 */

namespace Adsstudio\MyCustomCSS\classes;


use Adsstudio\MyCustomCSS\models\Snippet;

class DatabaseManager
{
    private $snippet_table = 'my_custom_php';
    static private $instance = null;
    public $data_version = "1.0";

    /**
     * @return \Adsstudio\MyCustomCSS\classes\DatabaseManager
     */
    static function getInstance()
    {
        if (self::$instance === null) {
            $class = __class__;
            self::$instance = new $class();
        }
        return self::$instance;
    }

    function migrate()
    {
        global $wpdb;
        $table = $this->getSnippetTable();

        self::create_table($table);
        // migrate to remaster
        // TODO add support network table
        $sql = sprintf("UPDATE %s SET code_type = 'php_snippet' WHERE code_type = 'default'", $table);
        $sql2 = sprintf("UPDATE %s SET code_type = 'php_shortcode' WHERE code_type = 'shortcode'", $table);
        $wpdb->query($sql);
        $wpdb->query($sql2);

        \update_option('mycc_data_version', $this->data_version);

        // single-use -> run_once
    }

    public static function create_table($table_name)
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        /* Create the database table */
        $sql = "CREATE TABLE $table_name (
				id          BIGINT(20)    NOT NULL AUTO_INCREMENT,
				name        TINYTEXT      NOT NULL DEFAULT '',
				description TEXT          NOT NULL DEFAULT '',
				code        LONGTEXT      NOT NULL DEFAULT '',
				tags        LONGTEXT      NOT NULL DEFAULT '',
				scope       VARCHAR(15)   NOT NULL DEFAULT 'global',
				priority    SMALLINT      NOT NULL DEFAULT 10,
				active      TINYINT(1)    NOT NULL DEFAULT 0,
				code_type   VARCHAR (50)  NOT NULL DEFAULT 'php_snippet',
				device_type VARCHAR (255)  DEFAULT '',
				mount_point   VARCHAR (255)  DEFAULT '',
				mount_point_num   INTEGER (11)  NOT NULL DEFAULT 0,
				post_type   VARCHAR (255)  DEFAULT '',
				PRIMARY KEY  (id)
			) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        $success = empty($wpdb->last_error);

        if ($success) {
            do_action('mycc/create_table', $table_name);
        }

        return $success;
    }

    function isNeedMigrate()
    {
        $db_versiorn = \get_option('mycc_data_version', 0);
        return version_compare($db_versiorn, $this->data_version, '<');
    }


    function check()
    {

    }

    public function getSnippetTable()
    {
        global $wpdb;
        return $wpdb->prefix . $this->snippet_table;
    }

    /**
     * @param null $active
     * @param null $code_type php_snippet | php_shortcode | ad_snippet | ad_shortcode
     * @param null $scope
     * @return array return all if filters is defaults
     */
    function getSnippets($active = null, $code_type = null, $scope = null)
    {
        global $wpdb;
        $table = $this->getSnippetTable();
        $where = "true";
        if ($active !== null) {
            if ($active) {
                $where .= " AND active = 1";
            } else {
                $where .= " AND active = 0";
            }
        }

        if ($scope !== null) {
            $where .= sprintf(" AND scope = '%s'", esc_sql($scope));
        }
        if ($code_type !== null) {
            if (is_array($code_type)) {
                if (count($code_type)) {
                    // make %s placeholders
                    $placeholders = array_fill(0, count($code_type), '%s');
                    $placeholders = implode(',', $placeholders);
                    $where .= $wpdb->prepare(" AND code_type in ( $placeholders)", $code_type);
                }

            } else {
                $where .= sprintf(" AND code_type = '%s'", esc_sql($code_type));
            }

        }

        $sql = sprintf("SELECT * FROM %s WHERE $where", $table);
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        $snippets = array();
        if (count($result)) {
            foreach ($result as $row) {
                $snippets[] = new Snippet($row);
            }
        }
        return $snippets;

    }

    /**
     * @param int|array $ids snippet id
     * @param bool $raw if true associative array will be returned
     * @return Snippet|array
     */
    function getSnippetByID($ids, $raw = false)
    {
        global $wpdb;
        $table = $this->getSnippetTable();
        $where = "";
        if (is_array($ids)) {
            $condition = "";
            foreach ($ids as $id) {
                if ($condition) $condition .= ",";
                $condition .= (int)$id;
            }
            $where .= "id IN ( $condition )";
        } else {
            $where = " id = " . (int)$ids;
        }
        $sql = sprintf("SELECT * from %s WHERE $where", $table);
        if (is_array($ids)) {
            $snippets = [];
            $results = $wpdb->get_results($sql, 'ARRAY_A');
            if ($raw) return $results;

            foreach ($results as $result) {
                $snippets[] = new Snippet($result);
            }

            return $snippets;
        } else {
            $result = $wpdb->get_row($sql, 'ARRAY_A');
            if ($raw) return $result;
            $snippet = new Snippet($result);

            return $snippet;
        }


    }

    /**
     *
     */
    function countSnippets($code_types = [])
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->snippet_table;
        $where = "";
        if (count($code_types)) {
            $types_list = array_map(function ($item) {
                return "'" . addslashes($item) . "'";
            }, $code_types);
            $types_list = implode(',', $types_list);
            $where = sprintf(" AND code_type in (%s)", $types_list);
        }

        $sql = "SELECT (SELECT COUNT(*) FROM $table WHERE true $where) as `all`, (SELECT COUNT(*) FROM $table WHERE active = 1 $where) as `active`, (SELECT COUNT(*) FROM $table WHERE active = 0 $where) as `inactive` ";
        $results = $wpdb->get_row($sql, 'ARRAY_A');
        return $results;
    }

    function getSettings($group = null, $option = null)
    {

        /* Check if the settings have been cached */
        if ($settings = wp_cache_get('mycc_settings')) {
            return $settings;
        }

        /* Begin with the default settings */
        $settings = Helper::default_settings();
        /* Retrieve saved settings from the database */
        $saved = get_option('mycc_settings', array());

        /* Replace the default field values with the ones saved in the database */
        if (function_exists('array_replace_recursive')) {

            /* Use the much more efficient array_replace_recursive() function in PHP 5.3 and later */
            $settings = array_replace_recursive($settings, $saved);
        } else {

            /* Otherwise, do it manually */
            foreach ($settings as $section => $fields) {
                foreach ($fields as $field => $value) {

                    if (isset($saved[$section][$field])) {
                        $settings[$section][$field] = $saved[$section][$field];
                    }
                }
            }
        }

        wp_cache_set('mycc_settings', $settings);

        if ($group === null and $option === null) {
            return $settings;
        } elseif (!empty($group) and !empty($option) and isset($settings[$group]) and isset($settings[$group][$option])) {
            return $settings[$group][$option];
        } else {
            return false;
        }

    }

    function getAllTags()
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->snippet_table;
        $sql = sprintf("SELECT `tags` FROM %s", $table);
        $results = $wpdb->get_results($sql, 'ARRAY_A');
        $tags = [];
        if (count($results)) {
            foreach ($results as $result) {
                if(!empty($result['tags'])){
                    $tags = array_merge($tags, explode(',', htmlspecialchars(strip_tags($result['tags']))));
                }

            }
        }
        $tags = array_unique($tags);
        return $tags;
    }

    function deleteSnippet($id, $multisite = null)
    {
        global $wpdb;

        $wpdb->delete(
            $wpdb->prefix . $this->snippet_table,
            array('id' => $id),
            array('%d')
        );
    }

    function saveSnippet($snippet)
    {
        global $wpdb;

        $table = $wpdb->prefix . $this->snippet_table;

        /* Build array of data to insert */
        $data = array(
            'name' => $snippet->name,
            'description' => $snippet->description,
            'code' => $snippet->code,
            'tags' => is_array($snippet->tags) ? implode(',', $snippet->tags) : $snippet->tags,
            'scope' => $snippet->scope,
            'priority' => $snippet->priority,
            'active' => intval($snippet->active),
            'code_type' => $snippet->code_type,
            'device_type' => implode(',', (array)$snippet->device_type),
            'mount_point' => implode(',', (array)$snippet->mount_point),
            'post_type' => implode(',', (array)$snippet->post_type),
            'mount_point_num' => intval($snippet->mount_point_num),
        );

        /* Create a new snippet if the ID is not set */
        if (0 == $snippet->id) {

            $wpdb->insert($table, $data, '%s');
            $snippet->id = $wpdb->insert_id;

        } else {

            /* Otherwise update the snippet data */
            $wpdb->update($table, $data, array('id' => $snippet->id), null, array('%d'));

        }
        $err = $wpdb->last_error;

        return $snippet->id;
    }

    function deactivateSnippet($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->snippet_table;
        $wpdb->update($table, array('active' => '0'), array('id' => $id), array('%d'), array('%d'));
    }


}