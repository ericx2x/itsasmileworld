<?php
/**
 * (c) mediagroup.
 */

namespace Adsstudio\MyCustomCSS\classes;


class PhpListTable extends AbstractListTable
{
    protected $shortcode = MYCC_PHP_SHORTCODE;
    protected $code_types = ['php_snippet', 'php_shortcode'];
    private $page = "mycc--edit-php";


    protected function getItemLinks($item)
    {
        $delete_url = admin_url( 'admin.php?page=mycc--phpgrid&action=delete&id=' . intval( $item['id'] ) );
        $clone_url  = admin_url( 'admin.php?page=mycc--phpgrid&action=clone&id=' . intval( $item['id'] ) );
        $edit_url   = admin_url( 'admin.php?page=mycc--edit-php&id=' . intval( $item['id'] ) );
        $delete_url = wp_nonce_url( $delete_url, 'mycc-item-delete' );
        $clone_url  = wp_nonce_url( $clone_url, 'mycc-item-clone' );
        //Build row actions
        $actions = array(
            'edit'      => sprintf( '<a href="%s">%s</a>', $edit_url, __( 'Edit', 'my-custom-css' ) ),
            'clone'     => sprintf( '<a href="%s">%s</a>', $clone_url, __( 'Clone', 'my-custom-css' ) ),
            'delete'    => sprintf( '<a href="%s">%s</a>', $delete_url, __( 'Delete', 'my-custom-css' ) ),
        );
        return $actions;
    }

    protected function getEditLink($item)
    {
        return Helper::menuUrl('edit-php&id=' . $item['id'] );
    }
}