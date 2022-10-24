<?php


class MyCustomPhp_Choice_Menu  extends MyCustomPhp_Admin_Menu
{
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct( 'choice',
            __( 'Add PHP code', 'my-custom-php' ),
            __( 'Choice', 'my-custom-php' )
        );
    }

    /**
     * Register action and filter hooks
     */
    public function run() {
        parent::run();
    }

    /**
     * Register the admin menu
     */
    public function register() {
            parent::register();


    }

    /**
     * Enqueue assets for the edit menu
     */
    public function enqueue_assets() {
        $plugin = custom_php();
        $rtl = /*is_rtl() ? '-rtl' : */ ''; // not implemented


        wp_enqueue_style(
            'my-custom-php-choice',
            plugins_url( "css/min/choice{$rtl}.css", $plugin->file ),
            array(), $plugin->version
        );

        /* the tag-it library has a number of jQuery dependencies */
        $deps = array(
            'jquery'
        );

        wp_enqueue_script(
            'my-custom-php-choice',
            plugins_url( 'js/min/choice.js', $plugin->file ),
            $deps,
            $plugin->version, true
        );

    }
}