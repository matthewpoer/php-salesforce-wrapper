<?php
class php_sfdc_wrapper {

  private $pest = NULL;
  private $access_token = NULL;
  const API_VERSION = '43.0';

  // invocation is authentication
  public function __construct(
    $SFDC_BASE_URL,
    $SFDC_CLIENT_ID,
    $SFDC_CLIENT_SECRET,
    $SFDC_USERNAME,
    $SFDC_PASSWORD,
    $SFDC_SECURITY_TOKEN
  ) {

    try {
      $this->pest = new Pest($SFDC_BASE_URL);

      $login_result = json_decode($this->pest->post(
        '/services/oauth2/token',
        array(
          'grant_type' => 'password',
          'client_id' => $SFDC_CLIENT_ID,
          'client_secret' => $SFDC_CLIENT_SECRET,
          'username' => $SFDC_USERNAME,
          'password' => $SFDC_PASSWORD . $SFDC_SECURITY_TOKEN,
        )
      ), TRUE);
      $this->pest = new Pest($login_result['instance_url']);
      $this->access_token = $login_result['access_token'];
    } catch (\Exception $e) {
      $message = 'Error authenticating with Salesforce. Exception occurred.' . PHP_EOL;
      $message .= $e->getMessage() . PHP_EOL;
      $message .= print_r($this->pest->last_request, TRUE);
      throw new Exception($message);
    }

    if(empty($this->access_token)) {
      throw new Exception('Error authenticating with Salesforce. No result.');
    }

  }

  public function query($sObject = '', $fields = array('Id'), $where = '', $require_result = TRUE) {
    $query = 'select ' . implode(',',$fields) . ' from ' . $sObject . ' where ' . $where;
    try {
      $results = json_decode($this->pest->get(
        '/services/data/v' . self::API_VERSION . '/query/',
        array(
          'q' => $query
        ),
        array(
          'Authorization: Bearer ' . $this->access_token
        )
      ), TRUE);
      if($require_result && empty($results['records'])) {
        $message = 'Unable to find SFDC ' . $sObject . ' using query: ' . $query . PHP_EOL;
        $message .= print_r($this->pest->last_request, TRUE);
        throw new Exception($message);
      }
      elseif(empty($results['records'])) {
        return array();
      }
      else {
        return $results['records'];
      }
    } catch (\Exception $e) {
      $message = 'Error querying Salesforce ' . $sObject . '. Exception occurred. Using query: ' . $query . PHP_EOL;
      $message .= $e->getMessage();
      $message .= print_r($this->pest->last_request, TRUE);
      throw new Exception($message);
    }
    $message = 'Error querying Salesforce ' . $sObject . '. No result. Using query: ' . $query . PHP_EOL;
    throw new Exception($message);
  }

  public function create($sObject = '', $fields = array()) {
    try {
      $results = json_decode($this->pest->post(
        '/services/data/v' . self::API_VERSION . '/sobjects/' . $sObject . '/',
        json_encode($fields),
        array(
          'Authorization: Bearer ' . $this->access_token,
          'Content-Type: application/json'
        )
      ), TRUE);
      return $results['id'];
      if(empty($results) || empty($results['success']) || empty($results['id'])) {
        $message = "Error creating new {$sObject} in Salesforce. Invalid Result." . PHP_EOL;
        $message .= print_r($this->pest->last_request, TRUE);
        throw new Exception($message);
      }
    } catch (\Exception $e) {
      $message = 'Error working with Salesforce ' . $sObject . '. Exception occurred.' . PHP_EOL;
      $message .= $e->getMessage();
      $message .= print_r($this->pest->last_request, TRUE);
      $message .= print_r($fields, TRUE);
      throw new Exception($message);
    }
    throw new Exception('Error creating a Salesforce ' . $sObject . '. No result.');
  }

  public function update($sObject = '', $id = '', $fields = array()) {
    try {
      return json_decode($this->pest->patch(
        '/services/data/v' . self::API_VERSION . '/sobjects/' . $sObject . '/' . $id,
        json_encode($fields),
        array(
          'Authorization: Bearer ' . $this->access_token,
          'Content-Type: application/json'
        )
      ), TRUE);
    } catch (\Exception $e) {
      $message = 'Error updating Salesforce ' . $sObject . '. Exception occurred.' . PHP_EOL;
      $message .= $e->getMessage();
      $message .= print_r($this->pest->last_request, TRUE);
      $message .= print_r($fields, TRUE);
      throw new Exception($message);
    }
    throw new Exception('Error updating Salesforce ' . $sObject . '. No result.');
  }

  public function getFieldsFor($sObject) {
    $return = array();
    try {
      $all_results = json_decode($this->pest->get(
        '/services/data/v' . self::API_VERSION . '/sobjects/' . $sObject . '/describe',
        array(),
        array(
          'Authorization: Bearer ' . $this->access_token,
          'Content-Type: application/json'
        )
      ), TRUE);
      foreach($all_results['fields'] as $field) {
        $return[] = $field['name'];
      }
      return $return;
    } catch (\Exception $e) {
      $message = 'Error working with Salesforce ' . $sObject . ' while attempting to find fields list. Exception occurred.' . PHP_EOL;
      $message .= $e->getMessage();
      $message .= print_r($this->pest->last_request, TRUE);
      throw new Exception($message);
    }
    if(empty($return)) {
      throw new Exception('Error working with Salesforce ' . $sObject . ' while attempting to find fields list. Field list is empty.');
    }
  }

}
