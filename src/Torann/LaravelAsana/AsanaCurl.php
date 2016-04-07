<?php

namespace Torann\LaravelAsana;

use Exception;
use InvalidArgumentException;

class AsanaCurl
{
    /**
     * Define method constants
     */
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_GET = 'GET';
    const METHOD_DELETE = 'DELETE';

    private $timeout = 10;

    private $endpoint = "https://app.asana.com/api/1.0/";

    private $errors = [];

    private $apiKey;
    private $accessToken;
    private $syncKey;
    private $curl;

    /**
     * Constructor
     *
     * @param  string $key
     * @param  string $token
     *
     * @throws Exception
     */
    public function __construct($key = null, $token = null)
    {
        if (!empty($key)) {
            $this->apiKey = $key;
        }
        else if (!empty($token)) {
            $this->accessToken = $token;
        }
        else {
            throw new Exception("You need to specify an API key or token.");
        }
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
     * @param  string $data Must be a json string
     *
     * @return string|null
     */
    private function request($method, $url, $data = null)
    {
        $this->curl = curl_init();
        $this->setCurlOptions($url);

        if (!empty($this->apiKey)) {
            $this->sendWithAPIKey();

            // Send as JSON unless attaching file to task or null data
            if (is_null($data) || empty($data['file'])) {
                curl_setopt($this->curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }
        }
        else if (!empty($this->accessToken)) {
            $this->sendWithAccessToken();
        }

        switch ($method) {
            case self::METHOD_POST:
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
                break;
            case self::METHOD_PUT:
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case self::METHOD_DELETE:
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        if (!is_null($data) && ($method == self::METHOD_POST || $method == self::METHOD_PUT)) {
            curl_setopt($this->curl, CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Make request
        try {
            $response = curl_exec($this->curl);
            $response = json_decode($response);
        } catch (Exception $e) {
            $this->errors = [$e->getMessage()];
            $response = null;
        }

        // Check for errors
        $this->checkForCurlErrors($response);

        curl_close($this->curl);
        unset($this->curl);

        return $response;
    }

    /**
     * POST file upload
     *
     * @param  string $filename File to be uploaded
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function addPostFile($filename)
    {
        // Remove leading @ symbol
        if (strpos($filename, '@') === 0) {
            $filename = substr($filename, 1);
        }

        if (!is_readable($filename)) {
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

    /**
     * Set Curl options
     *
     * @param  string $url API endpoint URL
     */
    private function setCurlOptions($url)
    {
        $url = "{$this->endpoint}{$url}";

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1); // Don't print the result
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0); // Don't verify SSL connection
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0); //         ""           ""
    }

    private function sendWithAPIKey()
    {
        // Send with API key.
        curl_setopt($this->curl, CURLOPT_USERPWD, "{$this->apiKey}:");
        curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }

    private function sendWithAccessToken()
    {
        // Send with auth token.
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ]);
    }

    /*
     * Check for errors
     *
     * @param  string $response
     *
     * @throws Exceptions\AsanaCurlException
     */
    private function checkForCurlErrors($response)
    {
        // Error from server
        if ($response && isset($response->errors)) {
            $resultStatus = curl_getinfo($this->curl);

            // Get errors
            $errors = implode(', ', array_map(function ($error) {
                return $error->message;
            }, $response->errors));

            // fetch sync key for event handling
            if (isset($response->sync)) {
                $this->syncKey = $response->sync;
            }

            throw new Exception($errors, $resultStatus['http_code']);
        }

        // General cURL error
        else if (!$response) {
            throw new Exception(curl_error($this->curl), curl_errno($this->curl));
        }
    }
}