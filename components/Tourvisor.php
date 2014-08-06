<?php
/**
 * Created by Treschelet.
 * Date: 05.08.14
 */

namespace treschelet\tourvisor\components;

use Yii;
use yii\base\Object;
use yii\base\Exception;
use yii\web\HttpException;
use yii\helpers\Json;

class Tourvisor extends Object
{

    const CONTENT_TYPE_JSON = 'json'; // JSON format
    const CONTENT_TYPE_URLENCODED = 'urlencoded'; // urlencoded query string, like name1=value1&name2=value2
    const CONTENT_TYPE_XML = 'xml'; // XML format
    const CONTENT_TYPE_AUTO = 'auto'; // attempts to determine format automatically

    const API_LIST_URL = 'http://tourvisor.ru/xml/list.php';
    const API_SEARCH_URL = 'http://tourvisor.ru/xml/search.php';
    const API_RESULT_URL = 'http://tourvisor.ru/xml/result.php';

    public $login;
    public $password;
    public $version = '1.0';

    public function getList($types, $params = [], $format = 'json')
    {
        if (is_string($types)) $types = [$types];
        $params['type'] = implode(',', $types);
        $params['format'] = $format;

        return $this->makeRequet(self::API_LIST_URL, $params);
    }

    public function getResult($id, $type, $params, $format = 'json')
    {
        $params['requestid'] = $id;
        $params['type'] = $type;
        $params['format'] = $format;

        return $this->makeRequet(self::API_RESULT_URL, $params);
    }

    public function search($params)
    {
        return $this->makeRequet(self::API_SEARCH_URL, $params);
    }

    protected function makeRequet($url, $params = [])
    {
        $params['authlogin'] = $this->login;
        $params['authpass'] = $this->password;

        $curlOptions = [
            CURLOPT_URL => $this->composeUrl($url, $params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => Yii::$app->name . ' TourVisor ' . $this->version . ' Client',
        ];
        $curlResource = curl_init();
        foreach($curlOptions as $option => $value)
            curl_setopt($curlResource, $option, $value);
        $response = curl_exec($curlResource);
        $responseHeaders = curl_getinfo($curlResource);
        // check cURL error
        $errorNumber = curl_errno($curlResource);
        $errorMessage = curl_error($curlResource);
        curl_close($curlResource);

        if ($errorNumber > 0) {
            throw new Exception('Curl error requesting "' .  $url . '": #' . $errorNumber . ' - ' . $errorMessage);
        }
        if ($responseHeaders['http_code'] != 200) {
            throw new HttpException($responseHeaders['http_code'], 'Request failed with code: ' . $responseHeaders['http_code'] . ', message: ' . $response);
        }

        return $this->processResponse($response, $this->determineContentTypeByHeaders($responseHeaders));
    }

    protected function composeUrl($url, array $params = [])
    {
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }
        $url .= http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return $url;
    }

    protected function processResponse($rawResponse, $contentType = self::CONTENT_TYPE_AUTO)
    {
        if (empty($rawResponse)) {
            return [];
        }
        switch ($contentType) {
            case self::CONTENT_TYPE_AUTO: {
                $contentType = $this->determineContentTypeByRaw($rawResponse);
                if ($contentType == self::CONTENT_TYPE_AUTO) {
                    throw new Exception('Unable to determine response content type automatically.');
                }
                $response = $this->processResponse($rawResponse, $contentType);
                break;
            }
            case self::CONTENT_TYPE_JSON: {
                $response = Json::decode($rawResponse, true);
                if (isset($response['error'])) {
                    throw new Exception('Response error: ' . $response['error']);
                }
                break;
            }
            case self::CONTENT_TYPE_URLENCODED: {
                $response = [];
                parse_str($rawResponse, $response);
                break;
            }
            case self::CONTENT_TYPE_XML: {
                $response = $this->convertXmlToArray($rawResponse);
                break;
            }
            default: {
            throw new Exception('Unknown response type "' . $contentType . '".');
            }
        }

        return $response;
    }

    /**
     * Converts XML document to array.
     * @param string|\SimpleXMLElement $xml xml to process.
     * @return array XML array representation.
     */
    protected function convertXmlToArray($xml)
    {
        if (!is_object($xml)) {
            $xml = simplexml_load_string($xml);
        }
        $result = (array) $xml;
        foreach ($result as $key => $value) {
            if (is_object($value)) {
                $result[$key] = $this->convertXmlToArray($value);
            }
        }

        return $result;
    }

    /**
     * Attempts to determine HTTP request content type by headers.
     * @param array $headers request headers.
     * @return string content type.
     */
    protected function determineContentTypeByHeaders(array $headers)
    {
        if (isset($headers['content_type'])) {
            if (stripos($headers['content_type'], 'json') !== false) {
                return self::CONTENT_TYPE_JSON;
            }
            if (stripos($headers['content_type'], 'urlencoded') !== false) {
                return self::CONTENT_TYPE_URLENCODED;
            }
            if (stripos($headers['content_type'], 'xml') !== false) {
                return self::CONTENT_TYPE_XML;
            }
        }

        return self::CONTENT_TYPE_AUTO;
    }

    /**
     * Attempts to determine the content type from raw content.
     * @param string $rawContent raw response content.
     * @return string response type.
     */
    protected function determineContentTypeByRaw($rawContent)
    {
        if (preg_match('/^\\{.*\\}$/is', $rawContent)) {
            return self::CONTENT_TYPE_JSON;
        }
        if (preg_match('/^[^=|^&]+=[^=|^&]+(&[^=|^&]+=[^=|^&]+)*$/is', $rawContent)) {
            return self::CONTENT_TYPE_URLENCODED;
        }
        if (preg_match('/^<.*>$/is', $rawContent)) {
            return self::CONTENT_TYPE_XML;
        }

        return self::CONTENT_TYPE_AUTO;
    }
} 