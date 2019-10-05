<?php

namespace Torann\LaravelAsana;

use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Torann\LaravelAsana\Events\AsanaResponse;

class AsanaCurl
{
    /**
     * Define method constants
     */
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_GET    = 'GET';
    const METHOD_DELETE = 'DELETE';

    private $endpoint = "https://app.asana.com/api/1.0/";

    private $syncKey;
    private $curl;

    /**
     * Last request http status.
     *
     * @var int
     **/
    protected $http_code = 200;

    /**
     * Last request error string.
     *
     * @var string
     **/
    protected $errors = null;

    /**
     * Array containing headers from last performed request.
     *
     * @var array
     */
    private $headers = [
        'Accept' => 'Accept: application/json',
        'Accept' => 'Asana-Enable: new_rich_text',
    ];

    /**
     * Constructor
     *
     * @param  string $token
     *
     * @throws Exception
     */
    public function __construct($token = null)
    {
        if (empty($token)) {
            throw new Exception('You need to specify an access token.');
        }

        $this->setHeader('Authorization', "Bearer {$token}");
    }

    /**
     * Add multiple headers to request.
     *
     * @param array $values
     */
    public function setHeaders(array $values)
    {
        foreach ($values as $key => $value) {
            $this->setHeader($key, $value);
        }
    }

    /**
     * Add header to request.
     *
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value ? "{$key}: {$value}" : $value;
    }

    /**
     * Get request
     *
     * @param string $url
     *
     * @return string|null
     */
    public function get($url)
    {
        return $this->request(self::METHOD_GET, $url);
    }

    /**
     * Post request
     *
     * @param string $url
     * @param array  $data
     *
     * @return string|null
     */
    public function post($url, array $data = [])
    {
        return $this->request(self::METHOD_POST, $url, $data);
    }

    /**
     * Put request
     *
     * @param string $url
     * @param array  $data
     *
     * @return string|null
     */
    public function put($url, array $data = [])
    {
        return $this->request(self::METHOD_PUT, $url, $data);
    }

    /**
     * Delete request
     *
     * @param string $url
     * @param array  $data
     *
     * @return string|null
     */
    public function delete($url, array $data = [])
    {
        return $this->request(self::METHOD_DELETE, $url, $data);
    }

    /**
     * Return error
     *
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Return sync key
     *
     * @return mixed
     */
    public function getSyncKey()
    {
        return $this->syncKey;
    }

    /**
     * This function communicates with Asana REST API.
     * You don't need to call this function directly. It's only for inner class working.
     *
     * @param  int    $method
     * @param  string $url
     * @param  array  $parameters
     * @param  array  $headers
     *
     * @return string|null
     * @throws Exception
     */
    private function request($method, $url, array $parameters = [], array $headers = [])
    {
        $this->errors = null;

        // Set default content type
        $this->setHeader('Content-Type', 'application/json');

        $curl = curl_init();

        // Set options
        curl_setopt_array($curl, [
          CURLOPT_URL            => "{$this->endpoint}{$url}",
          CURLOPT_CONNECTTIMEOUT => 10,
          CURLOPT_TIMEOUT        => 90,
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_SSL_VERIFYPEER => 0,
          CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_HEADER         => 1,
          CURLINFO_HEADER_OUT    => 1,
          CURLOPT_VERBOSE        => 1,
        ]);

        // Setup method specific options
        switch ($method) {
            case 'PUT':
            case 'PATCH':
            case 'POST':
                curl_setopt_array($curl, [
                  CURLOPT_CUSTOMREQUEST => $method,
                  CURLOPT_POST          => true,
                  CURLOPT_POSTFIELDS    => $this->buildArrayForCurl($parameters),
                ]);
                break;

            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;

            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                break;
        }

        // Set request headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, array_filter(array_values($this->headers)));

        // Make request
        $response = curl_exec($curl);

        // Set HTTP response code
        $this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Set errors if there are any
        if (curl_errno($curl)) {
            $this->errors = curl_error($curl);
        }

        // Parse body
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header      = substr($response, 0, $header_size);
        $json        = json_decode(substr($response, $header_size), false, 512, JSON_BIGINT_AS_STRING);

        // Check for errors
        $this->checkForCurlErrors($json);

        curl_close($curl);

        event(new AsanaResponse($url, $parameters, $json));

        return $json;
    }

    /**
     * Build http query that will be cUrl compliant.
     *
     * @param array $params
     *
     * @return array
     */
    protected function buildArrayForCurl($params)
    {
        if (isset($params['file'])) {

            // Have cUrl set the correct content type for upload
            $this->setHeader('Content-Type', null);

            // Convert array to a simple cUrl usable array
            return $this->http_build_query_for_curl($params);
        }

        return json_encode($params);
    }

    /**
     * Handle nested arrays when posting
     *
     * @param mixed  $var
     * @param string $prefix
     *
     * @return array
     */
    protected function http_build_query_for_curl($var, $prefix = null)
    {
        $return = [];

        foreach ($var as $key => $value) {
            $name = $prefix ? $prefix . '[' . $key . ']' : $key;

            if (is_array($value)) {
                $return = array_merge($return, $this->http_build_query_for_curl($value, $name));
            } else {
                // Convert file to something usable
                if ($key === 'file') {
                    $value = $this->addPostFile($value);
                }

                $return[$name] = $value;
            }
        }

        return $return;
    }

    /**
     * POST file upload
     *
     * @param  string $filename File to be uploaded
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function addPostFile($filename)
    {
        if ($filename instanceof UploadedFile) {

            // Get original filename
            $name = $filename->getClientOriginalName();

            // Move the file
            $file = $filename->move(sys_get_temp_dir() . '/' . uniqid(), $name);

            // Get the new file path
            $filename = $file->getRealPath();
        }

        if ( !is_readable($filename)) {
            throw new InvalidArgumentException("Unable to open {$filename} for reading");
        }

        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (function_exists('curl_file_create')) {
            return curl_file_create($filename);
        }

        // Use the old style if using an older version of PHP
        return "@{$filename}";
    }

    /*
     * Check for server errors
     *
     * @param  string $json
     *
     * @throws Exception
     */
    private function checkForCurlErrors($json)
    {
        if ($json && isset($json->errors)) {
            // Get errors
            $errors = implode(', ', array_map(function ($error) {
                return $error->message;
            }, $json->errors));

            // fetch sync key for event handling
            if (isset($json->sync)) {
                $this->syncKey = $json->sync;
            }

            throw new Exception($errors, $this->http_code);
        }
    }
}
