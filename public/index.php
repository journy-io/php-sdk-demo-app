<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ShopManager\Authentication\Authentication;
use ShopManager\HandlerHomePage;
use ShopManager\HandlerLogin;
use ShopManager\HandlerLoginForm;
use ShopManager\HandlerLogout;
use ShopManager\HandlerProductsAdd;
use ShopManager\HandlerProductsAddForm;
use ShopManager\HandlerProductsDelete;
use ShopManager\HandlerProductsList;
use ShopManager\Products\Products;
use ShopManager\Products\ProductsJSONFile;
use ShopManager\Authentication\RedirectIfAuthenticated;
use ShopManager\Authentication\RedirectIfNotAuthenticated;
use ShopManager\ShopAdmins\ShopAdmin;
use ShopManager\ShopAdmins\ShopAdmins;
use ShopManager\ShopAdmins\ShopAdminsInMemory;
use ShopManager\Shops\Shop;
use ShopManager\Shops\ShopId;
use ShopManager\Shops\Shops;
use ShopManager\Shops\ShopsInMemory;
use ShopManager\Users\Email;
use ShopManager\Users\PhoneNumber;
use ShopManager\Users\User;
use ShopManager\Users\UserId;
use ShopManager\Users\Users;
use ShopManager\Users\UsersInMemory;
use Buzz\Client\Curl;
use Dflydev\FigCookies\SetCookie;
use JournyIO\SDK\Client;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use League\Route\RouteGroup;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use Twig\Environment;
use League\Route\Router;
use Twig\Loader\FilesystemLoader;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$factory = new Psr17Factory();
$creator = new ServerRequestCreator($factory, $factory, $factory, $factory);

$router = new Router();
$router->middleware(
    new SessionMiddleware(
        Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($_ENV["SESSION_SECRET"])),
        SetCookie::create("session")
            ->withSecure(false) // false on purpose, unless you have https locally
            ->withHttpOnly(true)
            ->withPath("/"),
        1200,
        new SystemClock(
            new DateTimeZone(date_default_timezone_get())
        )
    )
);

$loader = new FilesystemLoader(__DIR__ . '/../views');
$twig = new Environment($loader);
$http = new Curl($factory, ["timeout" => 5]);
$client = new Client($http, $factory, $factory, ["apiKey" => $_ENV["API_KEY"], "rootUrl" => $_ENV["API_URL"]]);

/** @var Users $users */
$users = new UsersInMemory();
$john = new User(
    new UserId("1"),
    new Email("john@acme.com"),
    "John",
    "Doe",
    new PhoneNumber("+32 495 555 730"),
    new DateTimeImmutable("2021-05-03")
);
$users->persist($john);
$jane = new User(
    new UserId("2"),
    new Email("jane@acme.com"),
    "Jane",
    "Doe",
    new PhoneNumber("0456555338"),
    new DateTimeImmutable("2021-04-26")
);
$users->persist($jane);

/** @var Shops $shops */
$shops = new ShopsInMemory();
$shop1 = new Shop(new ShopId("1"), "John's Shop", new DateTimeImmutable("2021-09-10"));
$shops->persist($shop1);
$shop2 = new Shop(new ShopId("2"), "Jane's Shop", new DateTimeImmutable("2021-12-13"));
$shops->persist($shop2);

/** @var ShopAdmins $shopAdmins */
$shopAdmins = new ShopAdminsInMemory();
$shopAdmins->persist(new ShopAdmin($john->getId(), $shop1->getId()));
$shopAdmins->persist(new ShopAdmin($john->getId(), $shop2->getId()));
$shopAdmins->persist(new ShopAdmin($jane->getId(), $shop1->getId()));
$shopAdmins->persist(new ShopAdmin($jane->getId(), $shop2->getId()));

/** @var Products $products */
$products = new ProductsJSONFile(__DIR__ . '/../data');

$authentication = new Authentication($users);
$redirectIfNotAuthenticated = new RedirectIfNotAuthenticated($factory, $authentication);
$redirectIfAuthenticated = new RedirectIfAuthenticated($factory, $authentication, $shopAdmins);

$router->get('/', new HandlerHomePage($factory, $twig));
$router->get('/login', new HandlerLoginForm($users, $shopAdmins, $factory, $twig))->middleware($redirectIfAuthenticated);
$router->post('/login', new HandlerLogin($client, $users, $shopAdmins, $shops, $products, $factory, $authentication))->middleware($redirectIfAuthenticated);
$router->post("/logout", new HandlerLogout($client, $factory, $authentication))->middleware($redirectIfNotAuthenticated);

$router
    ->group("/shops", function (RouteGroup $route) use ($shopAdmins, $twig, $factory, $products, $shops, $client, $authentication) {
        $route->get('/{shopId}/products', new HandlerProductsList($shopAdmins, $twig, $factory, $products, $shops, $authentication));
        $route->get('/{shopId}/products/add', new HandlerProductsAddForm($twig, $factory, $authentication));
        $route->post('/{shopId}/products/add', new HandlerProductsAdd($authentication, $products, $client, $factory));
        $route->post('/{shopId}/products/{productId}/delete', new HandlerProductsDelete($authentication, $products, $client, $factory));
    })
    ->middleware($redirectIfNotAuthenticated);

$request = $creator->fromGlobals();
$response = $router->dispatch($request);
(new SapiEmitter())->emit($response);
