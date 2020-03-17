<?php

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {

    // Routes
    $r->addGroup('/api/v1', function ($r){
        $r->get('/', 'App\\Controllers\\Api\\V1\\IndexController@index');
    });

});

$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getRequestUri());
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        call_user_func([new \App\Base\Controller, 'notFound']);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        call_user_func([new \App\Base\Controller, 'notAllowedMethod']);;
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $expHandler = explode('@', $handler);
        call_user_func([new $expHandler[0], $expHandler[1]]);
        break;
}