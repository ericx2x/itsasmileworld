<?php
/**
 * (c) mediagroup.
 */

namespace Adsstudio\MyCustomCSS\classes;


class AdListTable extends AbstractListTable
{
    protected $shortcode = MYCC_AD_SHORTCODE;
    protected $code_types = ['ad_snippet', 'ad_shortcode'];


    protected function getItemLinks($item)
    {
        $delete_url = admin_url( 'admin.php?page=mycc--adgrid&action=delete&id=' . intval( $item['id'] ) );
        $clone_url  = admin_url( 'admin.php?page=mycc--adgrid&action=clone&id=' . intval( $item['id'] ) );
        $edit_url   = admin_url( 'admin.php?page=mycc--edit-ad&id=' . intval( $item['id'] ) );
        $delete_url = wp_nonce_url( $delete_url, 'adp-shortcode-delete' );
        $clone_url  = wp_nonce_url( $clone_url, 'adp-shortcode-clone' );
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
        return Helper::menuUrl('edit-ad&id=' . $item['id'] );
    }
}