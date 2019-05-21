<?php

namespace Torann\LaravelAsana\Events;

use Illuminate\Queue\SerializesModels;

class AsanaResponse
{
    use SerializesModels;

    /**
     * The API method that was called.
     *
     * @var string
     */
    public $method;

    /**
     * Payload sent to Asana
     *
     * @var array
     */
    public $payload;

    /**
     * Response JSON from Asana
     *
     * @var string
     */
    public $response;

    /**
     * Create a new event instance.
     *
     * @param Request $request
     * @return void
     */
    public function __construct($method, $payload, $response)
    {
        $this->method = $method;
        $this->payload = $payload;
        $this->response = $response;
    }
}