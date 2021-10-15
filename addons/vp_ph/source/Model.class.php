<?php
//decode by Www.Yxymk.Com
//模板著承接PHP开发定制,软件开发定制，PHP解密
class Model
{
    const MODEL_INSERT = 1;
    const MODEL_UPDATE = 2;
    const MODEL_BOTH = 3;
    const MUST_VALIDATE = 1;
    const EXISTS_VALIDATE = 0;
    const VALUE_VALIDATE = 2;
    private $_extModel = null;
    protected $db = null;
    protected $pk = "id";
    protected $tablePrefix = '';
    protected $name = '';
    protected $dbName = '';
    protected $connection = '';
    protected $tableName = '';
    protected $trueTableName = '';
    protected $error = '';
    protected $fields = array();
    protected $data = array();
    protected $options = array();
    protected $_validate = array();
    protected $_auto = array();
    protected $_map = array();
    protected $_scope = array();
    protected $autoCheckFields = true;
    protected $patchValidate = false;
    protected $methods = array("table", "order", "alias", "having", "group", "lock", "distinct", "auto", "filter", "validate", "result", "bind", "token");
    public function __construct($name = '', $tablePrefix = '', $connection = '')
    {
        $this->_initialize();
        $this->name = $this->getModelName();
        $this->tableName = strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $this->name), "_"));
    }
    protected function _initialize()
    {
    }
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
    public function __unset($name)
    {
        unset($this->data[$name]);
    }
    public function create($data = '', $type = '')
    {
        if (empty($data)) {
            $data = $_POST;
        } else {
            if (is_object($data)) {
                $data = get_object_vars($data);
            } else {
            }
        }
        if (empty($data) || !is_array($data)) {
            $this->error = "非法数据对象！";
            return false;
        }
        if (!$this->autoValidation($data, $type)) {
            return false;
        }
        $this->autoOperation($data, $type);
        $this->data = $data;
        return $data;
    }
    public function regex($value, $rule)
    {
        $validate = array("require" => "/.+/", "email" => "/^\\w+([-+.]\\w+)*@\\w+([-.]\\w+)*\\.\\w+([-.]\\w+)*\$/", "url" => "/^http(s?):\\/\\/(?:[A-za-z0-9-]+\\.)+[A-za-z]{2,4}(?:[\\/\\?#][\\/=\\?%\\-&~`@[\\]':+!\\.#\\w]*)?\$/", "currency" => "/^\\d+(\\.\\d+)?\$/", "number" => "/^\\d+\$/", "zip" => "/^\\d{6}\$/", "integer" => "/^[-\\+]?\\d+\$/", "double" => "/^[-\\+]?\\d+(\\.\\d+)?\$/", "english" => "/^[A-Za-z]+\$/");
        if (isset($validate[strtolower($rule)])) {
            $rule = $validate[strtolower($rule)];
        }
        return preg_match($rule, $value) === 1;
    }
    private function autoOperation(&$data, $type)
    {
        if (!empty($this->options["auto"])) {
            $_auto = $this->options["auto"];
            unset($this->options["auto"]);
        } else {
            if (!empty($this->_auto)) {
                $_auto = $this->_auto;
            } else {
            }
        }
        if (isset($_auto)) {
            foreach ($_auto as $auto) {
                if (empty($auto[2])) {
                    $auto[2] = self::MODEL_INSERT;
                }
                if ($type == $auto[2] || $auto[2] == self::MODEL_BOTH) {
                    switch (trim($auto[3])) {
                        case "function":
                        case "callback":
                            $args = isset($auto[4]) ? (array) $auto[4] : array();
                            if (isset($data[$auto[0]])) {
                                array_unshift($args, $data[$auto[0]]);
                            }
                            if ("function" == $auto[3]) {
                                $data[$auto[0]] = call_user_func_array($auto[1], $args);
                            } else {
                                $data[$auto[0]] = call_user_func_array(array(&$this, $auto[1]), $args);
                            }
                            break;
                        case "field":
                            $data[$auto[0]] = $data[$auto[1]];
                            break;
                        case "ignore":
                            if (!('' === $data[$auto[0]])) {
                            } else {
                                unset($data[$auto[0]]);
                            }
                            break;
                        case "string":
                        default:
                            $data[$auto[0]] = $auto[1];
                    }
                    if (false === $data[$auto[0]]) {
                        unset($data[$auto[0]]);
                    }
                }
            }
        }
        return $data;
    }
    protected function autoValidation($data, $type)
    {
        if (!empty($this->options["validate"])) {
            $_validate = $this->options["validate"];
            unset($this->options["validate"]);
        } else {
            if (!empty($this->_validate)) {
                $_validate = $this->_validate;
            } else {
            }
        }
        if (isset($_validate)) {
            if ($this->patchValidate) {
                $this->error = array();
            }
            foreach ($_validate as $key => $val) {
                if (empty($val[5]) || $val[5] == self::MODEL_BOTH || $val[5] == $type) {
                    if (0 == strpos($val[2], "{%") && strpos($val[2], "}")) {
                        $val[2] = L(substr($val[2], 2, -1));
                    }
                    $val[3] = isset($val[3]) ? $val[3] : self::EXISTS_VALIDATE;
                    $val[4] = isset($val[4]) ? $val[4] : "regex";
                    switch ($val[3]) {
                        case self::MUST_VALIDATE:
                            if (!(false === $this->_validationField($data, $val))) {
                            } else {
                                return false;
                            }
                            break;
                        case self::VALUE_VALIDATE:
                            if (!('' != trim($data[$val[0]]))) {
                            } else {
                                if (!(false === $this->_validationField($data, $val))) {
                                } else {
                                    return false;
                                }
                            }
                            break;
                        default:
                            if (isset($data[$val[0]])) {
                                if (false === $this->_validationField($data, $val)) {
                                    return false;
                                }
                            }
                    }
                }
            }
            if (!empty($this->error)) {
                return false;
            }
        }
        return true;
    }
    protected function _validationField($data, $val)
    {
        if (false === $this->_validationFieldItem($data, $val)) {
            if ($this->patchValidate) {
                $this->error[$val[0]] = $val[2];
            } else {
                $this->error = $val[2];
                return false;
            }
        }
        return;
    }
    protected function _validationFieldItem($data, $val)
    {
        switch (strtolower(trim($val[4]))) {
            case "function":
            case "callback":
                $args = isset($val[6]) ? (array) $val[6] : array();
                if (is_string($val[0]) && strpos($val[0], ",")) {
                    $val[0] = explode(",", $val[0]);
                }
                if (is_array($val[0])) {
                    foreach ($val[0] as $field) {
                        $_data[$field] = $data[$field];
                    }
                    array_unshift($args, $_data);
                } else {
                    array_unshift($args, $data[$val[0]]);
                }
                if ("function" == $val[4]) {
                    return call_user_func_array($val[1], $args);
                } else {
                    return call_user_func_array(array(&$this, $val[1]), $args);
                }
            case "confirm":
                return $data[$val[0]] == $data[$val[1]];
            default:
                return $this->check($data[$val[0]], $val[1], $val[4]);
        }
    }
    public function check($value, $rule, $type = "regex")
    {
        $type = strtolower(trim($type));
        switch ($type) {
            case "in":
            case "notin":
                $range = is_array($rule) ? $rule : explode(",", $rule);
                return $type == "in" ? in_array($value, $range) : !in_array($value, $range);
            case "between":
            case "notbetween":
                if (is_array($rule)) {
                    $min = $rule[0];
                    $max = $rule[1];
                } else {
                    list($min, $max) = explode(",", $rule);
                }
                return $type == "between" ? $value >= $min && $value <= $max : $value < $min || $value > $max;
            case "equal":
            case "notequal":
                return $type == "equal" ? $value == $rule : $value != $rule;
            case "length":
                $length = mb_strlen($value, "utf-8");
                if (strpos($rule, ",")) {
                    list($min, $max) = explode(",", $rule);
                    return $length >= $min && $length <= $max;
                } else {
                    return $length == $rule;
                }
            case "expire":
                list($start, $end) = explode(",", $rule);
                if (!is_numeric($start)) {
                    $start = strtotime($start);
                }
                if (!is_numeric($end)) {
                    $end = strtotime($end);
                }
                return TIMESTAMP >= $start && TIMESTAMP <= $end;
            case "ip_allow":
                return in_array(getip(), explode(",", $rule));
            case "ip_deny":
                return !in_array(getip(), explode(",", $rule));
            case "regex":
            default:
                return $this->regex($value, $rule);
        }
    }
    public function getModelName()
    {
        if (empty($this->name)) {
            $this->name = substr(get_class($this), 0, -5);
        }
        return $this->name;
    }
    public function data($data = '')
    {
        if ('' === $data && !empty($this->data)) {
            return $this->data;
        }
        if (is_object($data)) {
            $data = get_object_vars($data);
        } else {
            if (is_string($data)) {
                parse_str($data, $data);
            } else {
                if (!is_array($data)) {
                    return $this->data;
                } else {
                }
            }
        }
        $this->data = $data;
        return $this;
    }
    public function setProperty($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
        return $this;
    }
    public function getError()
    {
        return $this->error;
    }
}