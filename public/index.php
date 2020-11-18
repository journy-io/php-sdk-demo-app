<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use League\Route\Router;
use Zend\Diactoros\Response;
use Twig\Loader\FilesystemLoader;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

$request = ServerRequestFactory::fromGlobals(
  $_SERVER,
  $_GET,
  $_POST,
  $_COOKIE,
  $_FILES
);

$router = new Router();
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader);

$router->get('/', function () use ($twig): ResponseInterface {
  $response = new Response();
  $response->getBody()->write($twig->render("home.twig"));

  return $response;
});

$router->get('/pricing', function () use ($twig): ResponseInterface {
  $response = new Response();
  $response->getBody()->write($twig->render("pricing.twig"));

  return $response;
});

$router->get('/sign-up', function (ServerRequestInterface $serverRequest) use (
  $twig
): ResponseInterface {
  $response = new Response();
  $response->getBody()->write($twig->render("sign-up.twig"));

  return $response;
});

$router->post('/sign-up', function () use ($twig): ResponseInterface {
  $response = new Response();
  $response = $response
    ->withStatus(302)
    ->withAddedHeader("Location", "/sign-up");

  return $response;
});

$router->get('/ebooks', function (ServerRequestInterface $serverRequest) use (
  $twig
): ResponseInterface {
  $response = new Response();
  $response->getBody()->write($twig->render("ebooks.twig"));

  return $response;
});

$response = $router->dispatch($request);
(new SapiEmitter())->emit($response);
