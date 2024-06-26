<?php

namespace phpbb\request;

use phpbb\core\keyvalue;
use phpbb\request;

/**
 * Request body class
 */
class body
{
    
    /**
     * @var mixed $data
     */
    private mixed $data;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param request $request
     */
    public function __construct(request $request)
    {
        $contents = file_get_contents('php://input');
        if ($request->isJson()) {
            $this->data = new keyvalue(json_decode($contents));
        }
        else if ($request->isXml()) {
            $this->data = simplexml_load_string($contents);
        }
        else {
            $this->data = $contents;
        }
    }

    /**
     * Returns body parameters as string
     * 
     * @author ikubicki
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->data;
    }

    /**
     * Returns a keyvalue property value
     * 
     * @author ikubicki
     * @param string $property
     * @return mixed
     */
    public function get(string $property): mixed
    {
        return $this->data->get($property);
    }

    /**
     * Returns a raw keyvalue property value
     * 
     * @author ikubicki
     * @return mixed
     */
    public function raw(string $property): mixed
    {
        return $this->data->raw($property);
    }

    /**
     * Checks if body is an array
     * 
     * @author ikubicki
     * @return bool
     */
    public function isArray(): bool
    {
        if ($this->data instanceof keyvalue) {
            return $this->data->isArray();
        }
        return is_array($this->data);
    }

    /**
     * Returns body parameters as array
     * 
     * @author ikubicki
     * @return array
     */
    public function toArray(): array
    {
        if ($this->data instanceof keyvalue) {
            return $this->data->jsonSerialize();
        }
        return (array) $this->data;
    }

    /**
     * Returns body contents
     * 
     * @author ikubicki
     * @return mixed
     */
    public function export(): mixed
    {
        if ($this->data instanceof keyvalue) {
            return $this->data->export();
        }
        return $this->data;
    }
}