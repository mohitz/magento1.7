    <?php
// testMagentoRestApi.php file
error_reporting(E_ALL);
ini_set('display_errors', 'On');

session_start();

/**
 * @author Bogdan Constantinescu <{first3LettersFromFirstname}_{first3LettersFromLastname}{at}yahoo{dot}com>
 * @license The MIT License (http://opensource.org/licenses/MIT)
 */
class MagentoRestApi
{
    /**
     * @var const int       Curl connect timeout.
     */
    const TIMEOUT = 20;
    /**
     * @var string          Application 's key.
     */
    private $appKey;
    /**
     * @var string          Application 's secret.
     */
    private $appSecret;
    /**
     * @var resource        The curl resource.
     */
    protected $curl;
    /**
     * @var string          Base Magento url.
     */
    protected $baseMagentoUrl;
    /**
     * @var string          Consumer 's callback url.
     */
    protected $callbackUrl;


    /**
     * Constructor; initializes stuffs...
     * @param   string     $strBaseMagentoUrl   Magento 's base url.
     * @param   string     $strAppKey           API application key.
     * @param   string     $strAppSecret        API application secret.
     * @param   string     $strCallbackUrl      Consumer's callback url.
     * @throws  Exception  If params are not ok / curl could not be initialized.
     */
    public function __construct($strBaseMagentoUrl, $strAppKey, $strAppSecret, $strCallbackUrl)
    {
        // check params
        if(!filter_var($strBaseMagentoUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid param base magento url.');
        }
        if (!is_string($strAppKey) || !mb_strlen($strAppKey)) {
            throw new Exception('Invalid param app key.');
        }
        if (!is_string($strAppSecret) || !mb_strlen($strAppSecret)) {
            throw new Exception('Invalid param app secret.');
        }
        if(!filter_var($strCallbackUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid param callback url.');
        }
        $this->baseMagentoUrl = trim($strBaseMagentoUrl, '/');
        $this->callbackUrl    = $strCallbackUrl;
        $this->appKey         = $strAppKey;
        $this->appSecret      = $strAppSecret;

        if (!extension_loaded('curl')) {
            throw new Exception('cURL extension is not enabled.');
        }
        $this->curl = curl_init();
        if (false === $this->curl) {
            throw new Exception('cURL could not be initialized.');
        }
    }


    /**
     * Makes api call.
     * @param   string     $strUrl                      Api call url.
     * @param   string     $strMethod                   Request method (POST|GET|DELETE|PUT...)
     * @param   array      $arrHeaders                  Optional request headers.
     * @param   string     $strPostData                 Request body.
     * @param   boolean    $blnSuprimeResponseHeader    Whether to suprime response 's headers or not.
     * @return  string                                  Api call response.
     * @throws  Exception   If smth went wrong.
     */
    protected function makeApiCall(
        $strUrl,
        $strMethod = 'GET',
        array $arrHeaders = array(),
        $strPostData = '',
        $blnSuprimeResponseHeader = false,
        &$intStatus = null
    ) {
        curl_setopt($this->curl, CURLOPT_URL, $strUrl);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $arrHeaders);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);
        curl_setopt($this->curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $strMethod);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $strPostData);
        curl_setopt($this->curl, CURLOPT_HEADER, !$blnSuprimeResponseHeader);
        curl_setopt($this->curl, CURLOPT_USERAGENT, 'Magento REST API Client');
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);

        $mxdResponse = curl_exec($this->curl);
        if (false === $mxdResponse) {
            throw new Exception(curl_error($this->curl), curl_errno($this->curl));
        }
        $intStatus = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        // var_dump($mxdResponse);
        return $mxdResponse;
    }


    /**
     * Retrieve request token.
     * @return array        With keys 'oauth_token' & 'oauth_token_secret'
     * @throws Exception    If request did not succeed/smth went bad
     */
    public function getRequestToken()
    {
        $returnValue = array();

        // define params that will be used either in Authorization header, or as url query params, excluding 'oauth_signature'
        $params = array(
            'oauth_callback' => $this->callbackUrl,
            'oauth_consumer_key' => $this->appKey,
            'oauth_nonce' => uniqid(mt_rand(1, 1000)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0',
        );
        // define HTTP method
        $method = 'POST';
        // this is the url to get Request Token according to Magento doc
        $url = $this->baseMagentoUrl . '/oauth/initiate?oauth_callback=' . urlencode($params['oauth_callback']);

        // start making the signature
        ksort($params); // @see Zend_Oauth_Signature_SignatureAbstract::_toByteValueOrderedQueryString() for more accurate sorting, including array params 
        $sortedParamsByKeyEncodedForm = array();
        foreach ($params as $key => $value) {
            $sortedParamsByKeyEncodedForm[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $strParams = implode('&', $sortedParamsByKeyEncodedForm);
        $signatureData = strtoupper($method) // HTTP method (POST/GET/PUT/...)
                . '&'
                . rawurlencode($this->baseMagentoUrl . '/oauth/initiate') // base resource url - without port & query params & anchors, @see how Zend extracts it in Zend_Oauth_Signature_SignatureAbstract::normaliseBaseSignatureUrl()
                . '&'
                . rawurlencode($strParams);

        $key = rawurlencode($this->appSecret) . '&'; // on later requests, when you also have a oauth_token_secret from this request append it to $key  ( eq: $key = rawurlencode($this->appSecret) . '&' . rawurlencode($someOauthTokenSecret); )
        $signature = base64_encode(hash_hmac('SHA1', $signatureData, $key, 1));
        // end making signature

        $responseStatusCode = 0;
        $response = $this->makeApiCall(
            $url,
            $method,
            array(
                'Authorization: OAuth '
                . 'oauth_callback="' . rawurlencode($params['oauth_callback']) . '",'
                . 'oauth_consumer_key="' . $params['oauth_consumer_key'] . '",'
                . 'oauth_nonce="' . $params['oauth_nonce'] . '",'
                . 'oauth_signature_method="' . $params['oauth_signature_method'] . '",'
                . 'oauth_signature="' . rawurlencode($signature) . '",'
                . 'oauth_timestamp="' . $params['oauth_timestamp'] . '",'
                . 'oauth_version="' . $params['oauth_version'] . '"'
            ),
            '',
            true,
            $responseStatusCode
        );
        if (200 == $responseStatusCode) {
            parse_str($response, $returnValue);
        } else {
            throw new Exception('Response HTTP code != 200, but ' . $responseStatusCode);
        }
        return $returnValue;
    }


    /**
     * Retrieve access token.
     * @param   string     $strOauthToken         token from "Request Token".
     * @param   string     $strOauthTokenSecret   token secret from "Request Token".
     * @param   string     $strOauthVerifier      verifier returened after user authorization.
     * @return array        With keys 'oauth_token' & 'oauth_token_secret'
     * @throws Exception    If request did not succeed/smth went bad
     */
    public function getAccessToken($strOauthToken, $strOauthTokenSecret, $strOauthVerifier)
    {
        $returnValue = array();

        // define params that will be used either in Authorization header, or as url query params, excluding 'oauth_signature'
        $params = array(
            'oauth_consumer_key' => $this->appKey,
            'oauth_nonce' => uniqid(mt_rand(1, 1000)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0',
            'oauth_token' => $strOauthToken,
            'oauth_verifier' => $strOauthVerifier,
        );
        // define HTTP method
        $method = 'POST';
        // this is the url to get Request Token according to Magento doc
        $url = $this->baseMagentoUrl . '/oauth/token';

        // start making the signature
        ksort($params); // @see Zend_Oauth_Signature_SignatureAbstract::_toByteValueOrderedQueryString() for more accurate sorting, including array params 
        $sortedParamsByKeyEncodedForm = array();
        foreach ($params as $key => $value) {
            $sortedParamsByKeyEncodedForm[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $strParams = implode('&', $sortedParamsByKeyEncodedForm);
        $signatureData = strtoupper($method) // HTTP method (POST/GET/PUT/...)
                . '&'
                . rawurlencode($url) // base resource url - without port & query params & anchors, @see how Zend extracts it in Zend_Oauth_Signature_SignatureAbstract::normaliseBaseSignatureUrl()
                . '&'
                . rawurlencode($strParams);

        $key = rawurlencode($this->appSecret) . '&' . rawurlencode($strOauthTokenSecret); 
        $signature = base64_encode(hash_hmac('SHA1', $signatureData, $key, 1));
        // end making signature

        $responseStatusCode = 0;
        $response = $this->makeApiCall(
            $url,
            $method,
            array(
                'Authorization: OAuth '
                . 'oauth_consumer_key="' . $params['oauth_consumer_key'] . '",'
                . 'oauth_nonce="' . $params['oauth_nonce'] . '",'
                . 'oauth_signature_method="' . $params['oauth_signature_method'] . '",'
                . 'oauth_signature="' . rawurlencode($signature) . '",'
                . 'oauth_timestamp="' . $params['oauth_timestamp'] . '",'
                . 'oauth_version="' . $params['oauth_version'] . '",'
                . 'oauth_token="' . rawurlencode($params['oauth_token']) . '",'
                . 'oauth_verifier="' . rawurlencode($params['oauth_verifier']) . '"'
            ),
            '',
            true,
            $responseStatusCode
        );
        if (200 == $responseStatusCode) {
            parse_str($response, $returnValue);
        } else {
            throw new Exception('Response HTTP code != 200, but ' . $responseStatusCode);
        }
        return $returnValue;
    }


    /**
     * Destructor. Free resources.
     */
    public function __destruct() {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }
}

// change the following variables accordingly to your needs
$callbackUrl = 'http://localhost/testMagentoRestApi.php';
$baseMagentoUrl = 'http://52.66.118.198';
$consumerKey = 'pi9em4wno1h5t2ab68ctlngfwqr23yp5';
$consumerSecret = 'j0uf1iiysn3bflw7csqfibz8fkkkly97';

$client = new MagentoRestApi($baseMagentoUrl, $consumerKey, $consumerSecret, $callbackUrl);

if (!isset($_SESSION['request_token']) && !isset($_GET['oauth_token'])) { // retrieve "Request Token" and Authorize user
    $_SESSION['request_token'] = $client->getRequestToken();
    header('Location: ' . $baseMagentoUrl . '/oauth/authorize?oauth_token=' . urlencode($_SESSION['request_token']['oauth_token']));
    exit;
} elseif (isset($_SESSION['request_token']) && isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) { // user authorized and redirected here, now request access token (step E from http://oauth.net/core/diagram.png)
    $_SESSION['access_token'] = $client->getAccessToken($_GET['oauth_token'], $_SESSION['request_token']['oauth_token_secret'], $_GET['oauth_verifier']);
    var_dump($_SESSION['access_token']);
}

