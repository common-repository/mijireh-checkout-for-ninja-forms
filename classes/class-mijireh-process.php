<?php

/*
 *
 * This class does all of the heavy-lifting and interacting with Mijireh Checkout via cURL.
 *
 * This file is a modified version of a Mijireh Checkout tutorial that can be found here:
 * http://coding.smashingmagazine.com/2011/09/05/getting-started-with-the-mijireh-api/
 *
 * Unlike the rest of this program, this file is licensed under the MIT license.
 *
 * @since 1.0
 */

class NF_Process_Mijireh 
{
   /**
    * Last error message(s)
    * @var array
    */
   protected $_errors = array();

   /**
    * API Credentials
    * Use the correct credentials for the environment in use (Live / Sandbox)
    * @var array
    */
  public function get_credentials() {
    global $ninja_forms_processing;

    // Get Mijireh Checkout settings and return our API credentials.
    $plugin_settings = get_option( 'ninja_forms_mijireh' );

	if ( isset ( $plugin_settings['mijireh_access_key'] ) ) {
    	$api_user = $plugin_settings['mijireh_access_key'];
	} else {
    	$api_user = '';
	}
    

    $credentials = array(
        'access_key' => $api_user,
    );

    return $credentials;
  }
    

   /**
    * API endpoint
    * Live - https://api-3t.mijireh.com/nvp
    * Sandbox - https://api-3t.sandbox.mijireh.com/nvp
    * @var string
    */
  public function get_endpoint() {
    global $ninja_forms_processing;

    // Get Mijireh Checkout settings to determine if we are in "test" mode.
    if ( $ninja_forms_processing->get_form_setting( 'mijireh_test_mode' ) == 1 ) {
      $end_point = 'https://api-3t.sandbox.mijireh.com/nvp';
    } else {
      $end_point = 'https://api-3t.mijireh.com/nvp';
    }
    return $end_point;
  }

   /**
    * API Version
    * @var string
    */
  protected $_version = '106.0';

   /**
    * Make API request
    *
    * @param string $method string API method to request
    * @param array $params Additional request parameters
    * @return array / boolean Response array / boolean false on failure
    */
  public function request($method,$params = array()) {
      $this -> _errors = array();
      if( empty($method) ) { //Check if API method is not empty
         $this -> _errors = array('API method is missing');
         return false;
      }

      //Our request parameters
      $requestParams = array(
         'METHOD' => $method,
         'VERSION' => $this -> _version
      ) + $this->get_credentials();

      //Building our NVP string
      $request = http_build_query($requestParams + $params);

      //cURL settings
      $curlOptions = array (
         CURLOPT_URL => $this->get_endpoint(),
         CURLOPT_VERBOSE => 1,
         CURLOPT_SSL_VERIFYPEER => true,
         CURLOPT_SSL_VERIFYHOST => 2,
         CURLOPT_CAINFO => NINJA_FORMS_MIJIREH_DIR.'/includes/cacert.pem', //CA cert file
         CURLOPT_RETURNTRANSFER => 1,
         CURLOPT_POST => 1,
         CURLOPT_POSTFIELDS => $request
      );

      $ch = curl_init();
      curl_setopt_array($ch,$curlOptions);

      //Sending our request - $response will hold the API response
      $response = curl_exec($ch);

      //Checking for cURL errors
      if (curl_errno($ch)) {
         $this -> _errors = curl_error($ch);
         curl_close($ch);
         return false;
         //Handle errors
      } else  {
         curl_close($ch);
         $responseArray = array();
         parse_str($response,$responseArray); // Break the NVP string to an array
         return $responseArray;
      }
  }
}