<?php
/**
 * (c) mediagroup.
 */

namespace Adsstudio\MyCustomCSS\classes;


class PHPRunner
{
    private $snippet;

    /**
     * PHPRunner constructor.
     * @param \Adsstudio\MyCustomCSS\models\Snippet $snippet
     */
    function __construct($snippet)
    {
        $this->snippet = $snippet;
    }

    /**
     * is ob_start callback<br>
     * Выводит сообщение об ошибке
     * @param $out - буферизованный вывод пхп сниппета
     * @return string html для экрана ошибки или вывод пхп сниппета
     */
    function errorHandler($out)
    {
        $error = error_get_last();

        if (is_null($error)) {
            return $out;
        }
        $html = sprintf("<h2>My Custom CSS: error</h2> 
    <p>Error occured while run your code</p>
    <p>
       %s
        <div>%s on line %s</div>
    </p>", $error['message'], $error['file'], $error['line']);

        if (strpos($error['file'], __FILE__) !== false and is_object($this->snippet)) {
            $html .= sprintf("<div><b>Check snippet #%d %s on line %s</b></div>", (int)$this->snippet->id, $this->snippet->title,  $error['file'], $error['line']);
        }

        return $html;
    }

    /**
     * В случае ошибки будет отображено сообщение<br>
     * MYCC_DISABLE_MODE - если константа установлена, то код не выполняется
     * @return mixed|false Возвращает результат выполнения php кода, например вывод
     */
    function execute()
    {
        if(defined('MYCC_DISABLE_MODE') and MYCC_DISABLE_MODE){
            return false;
        }
        ob_start([__CLASS__, 'errorHandler']);
        eval($this->snippet->code);
        $output = ob_get_clean();
        return $output;
    }
}