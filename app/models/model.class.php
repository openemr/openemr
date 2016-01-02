<?php

require_once $LIB_DIR . '/sql.inc';

class Model {
  private $db;
  private $table = '';
  private $fields = array();
  private $existing = false;

  protected $id = NULL;
  public function getId() {
    return $this->id;
  }

  public function Model($table = '', $id = NULL) {
    $this->db = get_db();
    $this->table = $table;
    $this->fields = sqlListFields($this->table);

    if(!is_null($id)) {
      $this->id = $id;
      $this->getById();
    }
  }

  public function getById() {
    $sql = "SELECT * from " . $this->table . " WHERE id = " . add_escape_custom(strval($this->id));
    $results = sqlQuery($sql);
    $this->loadFromArray($results);
    $this->existing = true;
  }
  
  public function loadFromArray($array) {
    $refl = new ReflectionClass($this);

    foreach ($this->fields as $field) {
      $property = $refl->getProperty($field);

      if ($property instanceof ReflectionProperty && !$property->isProtected() && array_key_exists($field, $array)) {
        $property->setValue($this, $array[$field]);
      }
    }
  }

  public function save() {
    if( $this->existing ) {
      $this->update();
    } else {
      $this->create();
    }
  }

  public function create() {

  }

  public function update() {

  }
}

?>