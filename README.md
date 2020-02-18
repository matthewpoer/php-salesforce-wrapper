# php-salesforce-wrapper

## Using Pest for REST
[Pest](https://github.com/educoder/pest) is a PHP client library for RESTful web services. I found it to be a minimal and effective back-bone for this project. The Pest dependency is handled by composer.

Thanks @Educoder!

## Composer Install

```
composer require matthewpoer/php-salesforce-wrapper:dev-master
```

## Get Started

### Authentication
Authentication with Salesforce occurs upon instantiation of the wrapper.
~~~
try {
  $sfdc = new php_sfdc_wrapper(
    SFDC_BASE_URL,
    SFDC_CLIENT_ID,
    SFDC_CLIENT_SECRET,
    SFDC_USERNAME,
    SFDC_PASSWORD,
    SFDC_SECURITY_TOKEN
  );
} catch (\Exception $e) {
  $log->critical('Error authenticating with Salesforce. Exception occurred.', array(
    'Exception' => $e->getMessage()
  ));
  die('Error authenticating with Salesforce. Exception occurred.' . PHP_EOL);
}
~~~

### Create an Account
~~~
try {
  $account_id = $sfdc->create('Account', array(
    'BillingCity' => 'San Francisco',
    'BillingCountry' => 'United States',
    'BillingPostalCode' => '94105',
    'BillingState' => 'California',
    'BillingStreet' => 'The Landmark @ One Market Suite 300',
    'Name' => 'Salesforce.com',
  ));
} catch (\Exception $e) {
  $message = $e->getMessage();
  $log->critical($message);
  die('Error creating Salesforce Account. Exception occurred.' . PHP_EOL);
}
~~~

### Delete an Account
~~~
try {
  $account_id = $sfdc->delete('Account', $account_id);
} catch (\Exception $e) {
  $message = $e->getMessage();
  $log->critical($message);
  die('Error deleting Salesforce Account. Exception occurred.' . PHP_EOL);
}
~~~

### Find an Account by Name
The `query` method accepts as parameters,
1. an s0bject name
2. an array of fields desired in the result, defaulting to just the `Id` and `Name` fields
3. an string to use as the SOQL query's `WHERE` clause
4. a boolean on whether or not a result should be required (default TRUE). If this is TRUE then the wrapper will throw an exception when no records are found matching the conditions in the `WHERE` clause.

~~~
try {
  $accounts = $sfdc->query(
    'Account',
    array('Id','Name'),
    "name='Salesforce.com'",
    FALSE
  );
  foreach($accounts as $account) {
    echo "Found account with ID of " . $account['Id'] . PHP_EOL;
  }
} catch (\Exception $e) {
  $message = $e->getMessage();
  $log->critical($message);
  die('Error querying for the Salesforce Account. Exception occurred.' . PHP_EOL);
}
~~~
