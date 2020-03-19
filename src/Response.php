<?php

namespace Gouguoyin\EasyHttp;

use ArrayAccess;
use LogicException;

class Response implements ArrayAccess
{
    protected $response;

    /**
     * The decoded JSON response.
     *
     * @var array
     */
    protected $decoded;

    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Get the body of the response.
     * @return string
     */
    public function body()
    {
        return (string) $this->response->getBody();
    }

    /**
     * Get the JSON decoded body of the response.
     * @return array|mixed
     */
    public function json()
    {
        if (!$this->decoded) {
            $this->decoded = json_decode((string) $this->response->getBody(), true);
        }

        return $this->decoded;
    }

    /**
     * Get a header from the response.
     * @param string $header
     * @return mixed
     */
    public function header(string $header)
    {
        return $this->response->getHeaderLine($header);
    }

    /**
     * Get the headers from the response.
     * @return mixed
     */
    public function headers()
    {
        return $this->mapWithKeys($this->response->getHeaders(), function ($v, $k) {
            return [$k => $v];
        })->response;
    }

    /**
     * Get the status code of the response.
     * @return int
     */
    public function status()
    {
        return (int) $this->response->getStatusCode();
    }

    /**
     * Determine if the request was successful.
     * @return bool
     */
    public function successful()
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * Determine if the response code was "OK".
     * @return bool
     */
    public function ok()
    {
        return $this->status() === 200;
    }

    /**
     * Determine if the response was a redirect.
     * @return bool
     */
    public function redirect()
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    /**
     * Determine if the response indicates a client error occurred.
     * @return bool
     */
    public function clientError()
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    /**
     * Determine if the response indicates a server error occurred.
     * @return bool
     */
    public function serverError()
    {
        return $this->status() >= 500;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $this->response->then($onFulfilled, $onRejected);

        return $this;
    }

    public function wait($unwrap = true)
    {
        return $this->response->wait($unwrap);
    }

    public function cancel()
    {
        return $this->response->cancel();
    }

    public function resolve($value)
    {
        return $this->response->resolve($value);
    }

    public function reject($reason)
    {
        return $this->response->reject($reason);
    }

    /**
     * Get the underlying PSR response for the response.
     * @return mixed
     */
    public function toPsrResponse()
    {
        return $this->response;
    }

    /**
     * Throw an exception if a server or client error occurred.
     * @return $this
     * @throws RequestException
     */
    public function throw()
    {
        if ($this->serverError() || $this->clientError()) {
            throw new LogicException("888 HTTP request returned status code {$this->status()}.");
        }

        return $this;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->json()[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->json()[$offset];
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetSet($offset, $value)
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  string  $offset
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetUnset($offset)
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->body();
    }

    protected function mapWithKeys($items, callable $callback)
    {
        $result = [];

        foreach ($items as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return new static($result);
    }

}