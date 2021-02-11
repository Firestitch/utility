<?php

namespace Utility\Model;

class CMODEL_MODEL_INSERT extends CMODEL {

  protected $_content = "";
  protected $_file  = "";

  public function __construct($file) {
    $this->_file = $file;
    $this->_content = FILE_UTIL::get($file);
  }

  function prepend_save($exists, $value) {

    if (stripos($this->_content, $exists) !== false)
      throw new Exception('The Cmodel already has a the image functions');

    if (!preg_match("/^(.*?)(\s+(?:public|private)?\s+function\s+save\(.*)/ism", $this->_content, $matches))
      throw new Exception("Cannot find save function");

    $this->_content = value($matches, 1) . $value . value($matches, 2);

    $this->save();
  }



  //if(preg_match("/^(.*?[^}]+})(.*)$/ism",$this->_content,$matches)) {

  //}

  function save() {
    FILE_UTIL::put($this->_file, $this->_content);
  }
}
