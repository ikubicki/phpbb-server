<?php

namespace apps\auth\modules;

use apps\auth\middleware\jwtAuthMiddleware;
use apps\auth\middleware\permissionsMiddleware;
use phpbb\app;
use phpbb\core\accessRules;
use phpbb\core\accessRules\policies;
use phpbb\request;
use phpbb\response;

class permissions
{

    /**
     * @var app $app
     */
    private app $app;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param app $app
     */
    public function __construct(app $app)
    {
        $this->app = $app;
    }

    /**
     * Setups application routes
     * 
     * @author ikubicki
     * @return void
     */
    public function setup()
    {
        $this->app->get('/permissions', [$this, 'getPermissions'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([
                    policies::VIEW,
                ])
            ]
        ]);
        $this->app->post('/permissions', [$this, 'postPermissions'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([
                    policies::VIEW,
                ])
            ]
        ]);
    }

    /**
     * Handles GET /permissions request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     */
    public function getPermissions(request $request, response $response, app $app): response
    {
        return $response->send($request->context('access'));
    }

    /**
     * Handles POST /permissions request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     */
    public function postPermissions(request $request, response $response, app $app): response
    {
        $accessRules = $request->context('access');
        $results = $accessRules->getAccessRules($request->body->toArray());
        return $response->send($results);
    }
}