<?php

require_once $MODEL_ROOT . '/model.class.php';

class Facility extends Model {
  public $name = '';
  public $phone = '';
  public $fax = '';
  public $street = '';
  public $city = '';
  public $state = '';
  public $postal_code = '';
  public $country_code = '';
  public $federal_ein = '';
  public $website = '';
  public $email = '';
  public $color = '';
  public $service_location = 1;
  public $billing_location = 0;
  public $accepts_assignment = 0;
  public $pos_code = NULL;
  public $x12_sender_id = '';
  public $domain_identifier = '';
  public $attn = '';
  public $tax_id_type = '';
  public $primary_business_entity = 0;
  public $facility_npi = '';
  public $facility_code = '';


  public function Facility($id = NULL) { 
    parent::Model('facility', $id); 
  }

  public static function getAllByName() {
    $qry = "SELECT * FROM facility ORDER BY name";
    $results = sqlStatement($qry);

    $facilities = array();
    while($row = sqlFetchArray($results)) {
      $facility = new Facility();
      $facility->id = $row['id'];
      $facility->loadFromArray($row);
      $facilities[] = $facility;
    }

    return $facilities;
  }

  public function create() {
    $qry = "INSERT INTO facility SET " . $this->buildInsertUpdateQuery() . ";";

    sqlInsert($qry);
  }
  public function update() {
    $qry = "UPDATE facility SET " . $this->buildInsertUpdateQuery() . " WHERE id = " . $this->getId() . ";";

    sqlStatement($qry);
  }

  private function buildInsertUpdateQuery() {
    $qryProperties = "name = '" . $this->name . "',
      phone='" . $this->phone . "',
      fax='" . $this->fax . "',
      street='" . $this->street . "',
      city='" . $this->city . "',
      state='" . $this->state . "',
      postal_code='" . $this->postal_code . "',
      country_code='" . $this->country_code . "',
      federal_ein='" . $this->federal_ein . "',
      website='" . $this->website . "',
      email='" . $this->email . "',
      color='" . $this->color . "',
      domain_identifier='" . $this->domain_identifier . "',
      facility_npi='" . $this->facility_npi . "',
      attn='" . $this->attn . "' ,
      tax_id_type='" . $this->tax_id_type . "'";

      if (!empty($this->service_location)) $qryProperties .= ", service_location=" . $this->service_location;
      if (!empty($this->billing_location)) $qryProperties .= ", billing_location=" . $this->billing_location;
      if (!empty($this->accepts_assignment)) $qryProperties .= ", accepts_assignment=" . $this->accepts_assignment;
      if (!empty($this->pos_code)) $qryProperties .= ", pos_code=" . $this->pos_code;
      if (!empty($this->primary_business_entity)) $qryProperties .= ", primary_business_entity=" . $this->primary_business_entity;

    return $qryProperties;
  }
}

?>