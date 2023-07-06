<?php

namespace App\Routing;

use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionMethod;

class Router
{
  public function __construct(
    private ContainerInterface $container
  ) {
  }

  private array $routes = [];

  public function addRoute(
    string $name,
    string $url,
    array $httpMethod,
    string $controllerClass,
    string $controllerMethod
  ) {
    $this->routes[] = [
      'name' => $name,
      'url' => $url,
      'http_method' => $httpMethod,
      'controller' => $controllerClass,
      'method' => $controllerMethod
    ];
  }

  public function getRoute(string $uri, string $httpMethod): ?array
  {
    foreach ($this->routes as $route) {
      if ($route['url'] === $uri && in_array($httpMethod, $route['http_method'])) {
        return $route;
      }
    }

    return null;
  }

  /**
   * @param string $requestUri
   * @param string $httpMethod
   * @return void
   * @throws RouteNotFoundException
   */
  public function execute(string $requestUri, string $httpMethod)
  {
    $route = $this->getRoute($requestUri, $httpMethod);

    if ($route === null) {
      throw new RouteNotFoundException($requestUri, $httpMethod);
    }

    $controllerClass = $route['controller'];
    $method = $route['method'];

    $constructorParams = $this->getMethodParams($controllerClass . '::__construct');
    $controllerInstance = new $controllerClass(...$constructorParams);

    $controllerParams = $this->getMethodParams($controllerClass . '::' . $method);
    echo $controllerInstance->$method(...$controllerParams);
  }

  /**
   * Get an array containing services instances guessed from method signature
   *
   * @param string $method Format : FQCN::method
   * @return array The services to inject
   */
  private function getMethodParams(string $method): array
  {
    $params = [];

    try {
      $methodInfos = new ReflectionMethod($method);
    } catch (ReflectionException $e) {
      return [];
    }
    $methodParams = $methodInfos->getParameters();

    foreach ($methodParams as $methodParam) {
      $paramType = $methodParam->getType();
      $paramTypeName = $paramType->getName();
      $params[] = $this->container->get($paramTypeName);
    }

    return $params;
  }
}
