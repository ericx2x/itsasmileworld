<?php
/**
 * (c) mediagroup.
 */

namespace Adsstudio\MyCustomCSS\behaviors;


use Adsstudio\MyCustomCSS\classes\DatabaseManager;
use Adsstudio\MyCustomCSS\classes\PHPRunner;

class PhpSnippetBehavior
{
    private $snippets;

    function __construct()
    {
        $this->snippets = DatabaseManager::getInstance()->getSnippets(true, 'php_snippet');

        $this->globalScope();

        \add_action('plugins_loaded', [$this, 'pluginsLoaded']);

    }

    function pluginsLoaded()
    {
        $this->globalScope();
        if (is_admin()) {
            $this->adminScope();
        } else {
            $this->frontendScope();
        }

    }


    function frontendScope()
    {
        foreach ($this->snippets as $snippet) {
            if ($snippet->scope === 'front-end') {
                $code = new PHPRunner($snippet);
                echo $code->execute();
            }
        }
    }

    function adminScope()
    {
        foreach ($this->snippets as $snippet) {
            if ($snippet->scope === 'admin') {
                $code = new PHPRunner($snippet);
                echo $code->execute();
            }
        }
    }

    function globalScope()
    {
        foreach ($this->snippets as $snippet) {
            if ($snippet->scope === 'global' or $snippet->scope === 'run_once') {
                if($snippet->scope === 'run_once'){
                    DatabaseManager::getInstance()->deactivateSnippet($snippet->id);
                }
                $code = new PHPRunner($snippet);
                echo $code->execute();
            }
        }
    }

}