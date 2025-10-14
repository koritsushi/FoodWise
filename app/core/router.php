<?php
class Router {
    private $routes = [];

    public function add($pattern, $callback) {
        $this->routes[$pattern] = $callback;
    }

    public function dispatch($uri) {
        foreach ($this->routes as $pattern => $callback) {
            if (preg_match($pattern, $uri, $params)) {
                array_shift($params);
                return call_user_func_array($callback, $params);
            }
        }
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
    }
}
?>