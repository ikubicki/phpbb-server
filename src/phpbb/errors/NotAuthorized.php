<?php

namespace phpbb\errors;

use phpbb\request;
use phpbb\response;

class NotAuthorized extends \LogicException
{
    /**
     * The constructor
     * 
     * @author ikubicki
     * @param request $request
     */
    public function __construct(request $request)
    {
        parent::__construct(
            sprintf('You\'re not authorized to access %s', $request->http->path), 
            response::NOT_AUTHORIZED
        );
    }
}