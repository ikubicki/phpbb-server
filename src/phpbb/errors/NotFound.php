<?php

namespace phpbb\errors;

use phpbb\request;
use phpbb\response;

class NotFound extends \LogicException
{
    public function __construct(request $request)
    {
        parent::__construct(
            sprintf('%s %s not found', $request->method, $request->http->path), 
            response::NOT_FOUND
        );
    }
}