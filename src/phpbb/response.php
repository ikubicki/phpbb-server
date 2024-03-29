<?php

namespace phpbb;

use phpbb\apps\router\error;
use phpbb\apps\router\route;
use Throwable;

class response
{

    const OK = 200;
    const NO_CONTENT = 204;
    const BAD_REQUEST = 400;
    const NOT_AUTHORIZED = 401;
    const NOT_FOUND = 404;
    const SERVER_ERROR = 500;

    /**
     * @var request $request
     */
    public request $request;

    /**
     * @var route $route
     */
    public route $route;

    /**
     * @var ?response $previous
     */
    public ?response $previous;

    /**
     * @var int $status
     */
    public int $status = self::OK;

    /**
     * @var array $headers
     */
    public array $headers = [];

    /**
     * @var ?string $type
     */
    public ?string $type;

    /**
     * @var mixed $body
     */
    public mixed $body;

    /**
     * @var bool $sent
     */
    private bool $sent = false;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param request $request
     * @param route $route
     * @param ?response $previous
     */
    public function __construct(request $request, route $route, ?response $previous = null)
    {
        $this->request = $request;
        $this->route = $route;
        $this->previous = $previous;
        $type = $this->extractType($request);
        if (!$type) {
            $type = 'application/json';
        }
        $this->type($type);
    }

    /**
     * Executes application handler for route
     * Executes error route handler on error
     * 
     * @author ikubicki
     * @param app $app
     * @return response
     */
    public function execute(app $app): response
    {
        try {
            $this->preExecution($app);
            $previous = $this->previous ? $this->previous->execute($app) : null;
            if (!$this->sent) {
                call_user_func_array($this->route->callback, [
                    $this->request, $this, $app, $previous
                ]);
            }
            $this->postExecution($app);
        }
        catch(Throwable $throwable) {
            call_user_func_array((new error($throwable))->callback, [
                $this->request, $this, $app, null
            ]);
        }
        return $this;
    }

    /**
     * Calls pre execution middleware
     * 
     * @author ikubicki
     * @param app $app
     * @return void
     */
    private function preExecution(app $app): void
    {
        $middlewares = (array) ($this->route->options['preExecution'] ?? []);
        $this->executeMiddleware($app, $middlewares);
    }

    /**
     * Calls post execution middlewares
     * 
     * @author ikubicki
     * @param app $app
     * @return void
     */
    private function postExecution(app $app): void
    {
        $middlewares = (array) ($this->route->options['postExecution'] ?? []);
        $this->executeMiddleware($app, $middlewares);
    }

    /**
     * Calls middleware
     * 
     * @author ikubicki
     * @param app $app
     * @param array $middlewares
     * @return void
     */
    private function executeMiddleware(app $app, array $middlewares): void
    {
        foreach($middlewares as $middleware) {
            if (!$this->sent) {
                call_user_func_array([$middleware, 'execute'], [$this->request, $this, $app]);
            }
        }
    }

    /**
     * Sets response status
     * 
     * @author ikubicki
     * @param int $status
     * @return response
     */
    public function status(int $status): response
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Sets response header
     * 
     * @author ikubicki
     * @param string $name
     * @param string $value
     * @return response
     */
    public function header(string $name, string $value): response
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Sets response body and flags as sent
     * Optionally sets the type
     * 
     * @author ikubicki
     * @param mixed $body
     * @param ?string $type
     * @return response
     */
    public function send(mixed $body, ?string $type = null): response
    {
        $this->sent = true;
        $this->body = $body;
        if ($type) {
            $this->type($type);
        }
        return $this;
    }

    /**
     * Sets the type of response
     * 
     * @author ikubicki
     * @param ?string $type
     * @return response
     */
    public function type(?string $type): response
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Extracts the type from request accept header
     * 
     * @author ikubicki
     * @param request $request
     * @return ?string
     */
    private function extractType(request $request): ?string
    {
        if (!$request->accept) {
            return null;
        }
        if(stripos($request->accept, 'application/json') !== false) {
            return 'application/json';
        }
        if(stripos($request->accept, 'application/xml') !== false) {
            return 'application/xml';
        }
        return null;
    }
}