<?php
class Func extends Object {
  public $name = "";
  public $type = "function";
  public $className = "[object Function]";
  public $bound = null;

  static $protoObject = null;
  static $callStack = array();

  function __construct() {
    parent::__construct();
    $args = func_get_args();
    if (gettype($args[0]) === 'string') {
      $this->name = array_shift($args);
    }
    $this->fn = array_shift($args);
    $this->meta = (count($args) === 1) ? $args[0] : array();
    $prototype = new Object('constructor', $this);
    $this->set('prototype', $prototype);
  }

  function construct() {
    if (property_exists($this, 'instantiate')) {
      $instantiate = $this->instantiate;
      $obj = $instantiate();
    } else {
      $obj = new Object();
      $obj->setProto($this->get('prototype'));
    }
    $result = $this->apply($obj, func_get_args());
    return is_primitive($result) ? $obj : $result;
  }

  function call($context = null) {
    if ($this->bound !== null) {
      $context = $this->bound;
    } else
    if (func_num_args() === 0) {
      $context = Object::$global;
    }
    $args = array_slice(func_get_args(), 1);
    $arguments = self::makeArgs($args, $this);
    array_unshift($args, $arguments);
    array_unshift($args, $context);
    $result = call_user_func_array($this->fn, $args);
    array_pop(self::$callStack);
    return $result;
  }

  function apply($context, $args) {
    if ($this->bound !== null) {
      $context = $this->bound;
    }
    $arguments = self::makeArgs($args, $this);
    array_unshift($args, $arguments);
    array_unshift($args, $context);
    $result = call_user_func_array($this->fn, $args);
    array_pop(self::$callStack);
    return $result;
  }

  function get_name() {
    return $this->name;
  }

  function set_name($value) {
    return $value;
  }

  static function initProtoObject() {
    $methods = array(
      'bind' => function($this_, $context) {
        $fn = new Func($this_->name, $this_->fn, $this_->meta);
        $fn->bound = $context;
        return $fn;
      },
      'call' => function($this_, $arguments) {
        $args = $arguments->args;
        $context = array_shift($args);
        $this_->apply($context, $args);
      },
      'apply' => function($this_, $arguments, $context, $args) {
        $this_->apply($context, $args);
      },
      'toString' => function($this_) {
        if ($GLOBALS['source_'] && $this_->source_id) {
          $meta = $this_->meta;
          $source = $GLOBALS['source_'][$meta->id];
          return substr($source, $meta->start, $meta->end - $meta->start + 1);
        }
        return 'function ' . $this_->name . '() { [native code] }';
      }
    );
    self::$protoObject = new Object();
    self::$protoObject->setMethods($methods, true, false, true);
  }

  static function makeArgs($args, $callee) {
    $obj = new Object();
    $obj->args = $args;
    $obj->callee = $callee;
    self::$callStack[] = $callee;
    $len = count($args);
    for ($i = 0; $i < $len; $i++) {
      $obj->set($i, $args[$i]);
    }
    $obj->set('length', (float)$len);
    $obj->data->callee = new Property($callee, true, false, true);
    return $obj;
  }
}

Object::initProtoMethods();
Func::initProtoObject();
