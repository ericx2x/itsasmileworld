<?php
/*
Plugin Name: My Custom CSS
Plugin URI: https://wordpress.org/plugins/my-custom-css/
Description: With this plugin you can put custom css code without edit your theme and/or your plugins (really useful in case of any theme/plugin update).
It contain also <a href="https://ace.c9.io/">Ace (Ajax.org Cloud9 Editor)</a> Code Editor for write a good css code.
You can see in action (source code) here: <a href="http://www.vegamami.it/">VegAmami</a> :)
PS: support file backup and - very important - static css file (fantastic for performance) ;)
Author: Esther Tyler
Version: 3.3
Author URI: http://mycustomcss.com/
Text Domain: my-custom-css
*/

namespace Adsstudio\MyCustomCSS;

use Adsstudio\MyCustomCSS\classes\DatabaseManager;

if (!defined('ABSPATH')) {
    return;
}

require_once "vendor/autoload.php";

/**
 *  db_prefix  mycc_
 *  const prefix MYCC_
 *  hook prefix mycc/
 *  namespace Adsstudio\MyCustomCSS
 *
 *  php version >=5.4
 */

define('MYCC_VERSION', '3.3.1');

define("MYCC_MENU_PREFIX", 'mycc--');
define("MYCC_MENU_BASE",  'my_custom_css');
define("MYCC_PLUGIN_URL", plugin_dir_url(__FILE__));
define("MYCC_PLUGIN_DIR", __FILE__);

define("MYCC_PLUGIN_ACTIVE", true);
define("MYCC_AD_SHORTCODE", 'my_custom_ad');
define("MYCC_PHP_SHORTCODE", 'my_custom_php');


class Plugin
{

    function __construct()
    {

        if(!defined('MYCC_DISABLE_MODE')){
            define("MYCC_DISABLE_MODE", $this->isDisableMode());
        }

        load_plugin_textdomain('my-custom-css', false, dirname(plugin_basename(__FILE__)) . '/languages/');


        if(isset($_REQUEST['page']) and strpos($_REQUEST['page'], MYCC_MENU_PREFIX) !== false){
            if(DatabaseManager::getInstance()->isNeedMigrate()){
                DatabaseManager::getInstance()->migrate();
            }
        }

        $modules = $this->getModules();
        foreach ($modules as $module) {
            $this->loadModule($module);
        }
        apply_filters('mycc/modules/init', []);
    }

    private function getModules()
    {
        return ['my-css', 'my-php', 'my-ad', 'common'];
    }

    private function loadModule($module)
    {
        require_once(__DIR__ . '/modules/' . $module . '.php');
    }



    function isDisableMode(){
        if(isset($_SESSION['mycc_disable_mode']) and $_SESSION['mycc_disable_mode']){
            return true;
        }
        if(isset($_REQUEST['mycc_disable_mode'])){
            $user = get_current_user();
            if($user->has_cap('manage_options')){
                if($_REQUEST['mycc_disable_mode'] == 1){
                    $_SESSION['mycc_disable_mode'] = true;
                    return true;
                }else{
                    unset($_SESSION['mycc_disable_mode']);
                }
            }
        }
        return false;
    }
}

new Plugin();

