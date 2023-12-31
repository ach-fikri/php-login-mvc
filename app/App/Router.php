<?php

namespace AchFikri\Belajar\PHP\MVC\App;

class Router
{
	private static array $router = [];

    public static function add(string $method,
                               string $path,
                               string $controller,
                               string $function,
                               array $middleware = []) : void
    {
        self::$router[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'function' => $function,
            'middleware' =>$middleware
        ];

    }

    public static function run() : void
    {
        $path = '/';
        if (isset($_SERVER['PATH_INFO']))
        {
           $path = $_SERVER['PATH_INFO'];
        }

        $method = $_SERVER['REQUEST_METHOD'];

        foreach (self::$router as $route)
        {
            $pattern = "#^" . $route['path'] . "$#";
            if (preg_match($pattern, $path, $variables) && $method == $route['method']) {

                //call middleware
                foreach ($route['middleware'] as $middleware){
                    $instance = new $middleware;
                    $instance->before();
                }
                    $function = $route['function'];
                    $controller = new $route['controller'];

             array_shift($variables);
             call_user_func_array([$controller, $function], $variables);

             return;
           }

        }

        http_response_code(404);
        echo "CONTROLLER NOT FOUND";

    }
}