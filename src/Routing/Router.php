<?php

namespace App\Routing;

use App\Routing\Attribute\Route;
use App\Utils\Filesystem;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Router
{
  private const CONTROLLERS_GLOB_PATH = __DIR__ . "/../Controller/*Controller.php";

  public function __construct(
    private ContainerInterface $container
  ) {
  }

  private array $routes = [];

  public function addRoute(
    string $name,
    string $url,
    string $httpMethod,
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
      if ($route['url'] === $uri && $route['http_method'] === $httpMethod) {
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

  public function registerRoutes(): void
  {
    // Explorer le dossier des classes de contrôleurs
    // Pour chacun d'eux, enregistrer les méthodes
    // donc les contrôleurs portant un attribut Route
    $classNames = Filesystem::getClassNames(self::CONTROLLERS_GLOB_PATH);

    foreach ($classNames as $class) {
      $fqcn = "App\\Controller\\" . $class;
      $classInfos = new ReflectionClass($fqcn);

      if ($classInfos->isAbstract()) {
        continue;
      }

      $methods = $classInfos->getMethods(ReflectionMethod::IS_PUBLIC);

      foreach ($methods as $method) {
        if ($method->isConstructor()) {
          continue;
        }

        $attributes = $method->getAttributes(Route::class);

        if (!empty($attributes)) {
          $routeAttribute = $attributes[0];
          /** @var Route */
          $routeInstance = $routeAttribute->newInstance();
          $this->addRoute(
            $routeInstance->getName(),
            $routeInstance->getPath(),
            $routeInstance->getHttpMethod(),
            $fqcn,
            $method->getName()
          );
        }
      }
    }
  }
}
