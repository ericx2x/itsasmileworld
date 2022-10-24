<?php
/**
 * (c) mediagroup.
 */

namespace Adsstudio\MyCustomCSS\models;

/**
 * Class Snippet
 * @package Adsstudio\MyCustomCSS\models
 */
class Snippet
{
    public $id = 0;
    public $name = '';
    public $description = '';
    public $code = '';
    public $tags = [];
    /**
     * @var string available values: global | admin | front-end
     */
    public $scope = '';
    public $priority = 10;
    public $active = 0;
    public $code_type = '';

    // auto placement
    public $device_type = [];
    public $mount_point = [];
    public $post_type = [];
    public $mount_point_num = 3;


    function __construct($fields = [])
    {
        if (is_array($fields)) {
            $this->setData($fields);
            /*$names = array_keys(get_object_vars($this));
            foreach ($names as $name) {
                if (isset($fields[$name])) {
                    $this->$name = $fields[$name];
                }
            }*/
        }

    }

    function setData($data)
    {
        $fields = self::fieldList();
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                switch ($field) {
                    case 'device_type':
                    case 'mount_point':
                    case 'post_type':
                    case 'tags':
                        if(is_string($data[$field])){
                            if(empty($data[$field])){
                                $value = [];
                            }else{
                                $value = explode(',', $data[$field]);
                            }
                        }else{
                            $value = (array) $data[$field];
                        }
                        break;
                    case 'code':
                    case 'description':
                        $value = $data[$field]; // raw
                        break;
                    case 'id':
                    case 'priority':
                    case 'active':
                    case 'mount_point_num':
                        $value = (int)$data[$field];
                        break;
                    default:
                        $value = strip_tags($data[$field]);
                }
                $this->$field = $value;
            }
        }
    }

    static function fieldList()
    {
        return ['id', 'name', 'description', 'code', 'scope', 'tags', 'priority', 'code_type', 'active', 'device_type', 'mount_point', 'post_type', 'mount_point_num'];
    }


}