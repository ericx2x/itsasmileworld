<?php
/**
 * (c) mediagroup.
 */

namespace Adsstudio\MyCustomCSS\classes;

use Plugin_Upgrader;
use Adsstudio\MyCustomCSS\classes\UpgraderSkin;

class Installer
{

    static public function installAddon()
    {
        check_ajax_referer('mycc-install-addon', 'wpnonce');

        $module = self::getModule();

        $all_plugins = get_plugins();

        // check installed plugins
        if (isset($all_plugins[$module['basename']])) {
            if (!is_plugin_active($module['basename'])) {
                $result = activate_plugin($module['basename']);
                if (is_wp_error($result)) {
                    wp_send_json_error(array('msg' => __('Error with activating the plugin', 'my-custom-css')));
                } else {
                    wp_send_json_success(array('msg' => __('Module successfully activated', 'my-custom-css'), 'active' => 1));
                }
            }
        } else {
            // download and install
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/misc.php');
            require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

            add_filter('async_update_translation', '__return_false', 1);

            ob_start();

            $upgrader = new Plugin_Upgrader(new UpgraderSkin());

            $download_url = $module['download_url'];

            $res = $upgrader->install($download_url);
            ob_end_clean();

            if (is_wp_error($res)) {
                wp_send_json_error(array('msg' => __('Ошибка при установке модуля', 'my-custom-css')));
            }

            $result = activate_plugin($module['basename']);

            if (is_wp_error($result)) {
                wp_send_json_error(array('msg' => __('Ошибка при активации плагина', 'my-custom-css')));
            } else {
                wp_send_json_success(array('msg' => __('Модуль успешно активирован', 'my-custom-css'), 'active' => 1));
            }

        }

    }

    static public function getModule()
    {
        return [
            'basename' => 'my-custom-advertising/my-custom-advertising.php',
            'download_url' => "http://mycustomcss.com/modules/my-custom-advertising.zip"
        ];
    }
}