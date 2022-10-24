<?php
/**
 * (c) Adsstudio.
 */

namespace Adsstudio\MyCustomCSS\behaviors;
use Adsstudio\MyCustomCSS\classes\DatabaseManager;
use Adsstudio\MyCustomCSS\classes\Helper;

if (!defined('ABSPATH')) {
    return;
}

class AdSnippetBehavior
{
    function __construct()
    {
        \add_action('init', [$this, 'runActiveSnippets']);
    }

    public function runActiveSnippets()
    {
        $snippets = DatabaseManager::getInstance()->getSnippets(true, 'ad_snippet');// filter desktop, mobile,

        //'before_post'
        \add_filter('the_content', function ($content) use ($snippets) {
            foreach ($snippets as $blockIndex => $snippet){

                $detect = new \Mobile_Detect;
                $isMobile = $detect->isMobile();

                // device condition
                $device = $isMobile ? 'mobile' : 'desktop';
                if(!in_array($device, $snippet->device_type)){
                    continue;
                }

                // content_type condition
                if(count($snippet->post_type)){
                    if(!is_singular($snippet->post_type)){
                        continue;
                    }
                }

                // placement

                $ad_code = $snippet->code; // todo check.
                $totalBlocks = count($snippets);
                $ad_code = apply_filters('myccad/frontend/ad_code', $ad_code, $snippet->id, $blockIndex, $totalBlocks);

                if(in_array('before_content', $snippet->mount_point)){
                    $content = $ad_code . $content;
                }
                if(in_array('after_content', $snippet->mount_point)){
                    $content .= $ad_code;
                }
                if (in_array('after_paragraph', $snippet->mount_point)){
                    $content = $this->insertAfterParagraph($ad_code, $snippet->mount_point_num, $content);
                }
            }
            return $content;
        }, 150);

    }

    private function insertAfterParagraph( $insertion, $paragraph_id, $content)
    {
        $closing_p  = '</p>';
        $paragraphs = explode( $closing_p, $content );
        $settings   = get_option( 'adp-settings' );
        foreach ( $paragraphs as $index => $paragraph ) {
            // Only add closing tag to non-empty paragraphs
            if ( trim( $paragraph ) ) {
                // Adding closing markup now, rather than at implode, means insertion
                // is outside of the paragraph markup, and not just inside of it.
                $paragraphs[ $index ] .= $closing_p;
            }

            // + 1 allows for considering the first paragraph as #1, not #0.
            if ( $paragraph_id == $index + 1 ) {
                $ads                  = '<div ' . ( isset( $settings['css'] ) ? '' : ' style="clear:both;float:left;width:100%;margin:0 0 20px 0;"' ) . '>' . $insertion . '</div>';
                $paragraphs[ $index ] .= $ads;
            }
        }

        return implode( '', $paragraphs );
    }


}