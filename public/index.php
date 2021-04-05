<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\{ShopAdmin,
    ShopAdmins,
    ShopAdminsInMemory,
    Product,
    ProductsJSONFile,
    Shop,
    Shops,
    ShopsInMemory,
    User,
    Users,
    UsersInMemory};
use Buzz\Client\Curl;
use Dflydev\FigCookies\SetCookie;
use JournyIO\SDK\AccountIdentified;
use JournyIO\SDK\Client;
use JournyIO\SDK\Event;
use JournyIO\SDK\UserIdentified;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7Server\ServerRequestCreator;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use Twig\Environment;
use League\Route\Router;
use Twig\Loader\FilesystemLoader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$factory = new Psr17Factory();
$creator = new ServerRequestCreator(
    $factory,
    $factory,
    $factory,
    $factory
);

$request = $creator->fromGlobals();

$router = new Router();
$router->middleware(
    new SessionMiddleware(
        Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($_ENV["SESSION_SECRET"])),
        SetCookie::create("session")
            ->withSecure(false) // false on purpose, unless you have https locally
            ->withHttpOnly(true)
            ->withPath("/"),
        1200,
        new SystemClock(new DateTimeZone(date_default_timezone_get()))
    )
);
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader);
/** @var Users $users */
$users = new UsersInMemory();
$hans = new User("1", "hans@journy.io", "Hans", "Ott");
$users->persist($hans);
/** @var Shops $shops */
$shops = new ShopsInMemory();
$clothing = new Shop("1", "journy.io's clothing");
$shops->persist($clothing);
$electronics = new Shop("2", "journy.io's electronics");
$shops->persist($electronics);
/** @var ShopAdmins $admins */
$admins = new ShopAdminsInMemory();
$admins->persist(new ShopAdmin($hans->getId(), $clothing->getId()));
$admins->persist(new ShopAdmin($hans->getId(), $electronics->getId()));
$products = new ProductsJSONFile(__DIR__ . '/../data');
$http = new Curl($factory, ["timeout" => 5]);
$client = new Client($http, $factory, $factory, ["apiKey" => $_ENV["API_KEY"], "rootUrl" => $_ENV["API_URL"]]);

$router->get('/', function (): ResponseInterface {
    $response = new Response();
    $response = $response
        ->withStatus(302)
        ->withAddedHeader("Location", "/login")
    ;

    return $response;
});

$router->get('/login', function (ServerRequestInterface  $request) use ($twig, $users, $admins): ResponseInterface {
    /** @var PSR7Sessions\Storageless\Session\SessionInterface $session */
    $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    $userId = $session->get("userId");

    if ($userId) {
        $user = $users->getById($userId);

        if ($user) {
            $shopAdmins = $admins->getByUserId($user->getId());

            return (new Response())
                ->withStatus(302)
                ->withAddedHeader("Location", "/shops/{$shopAdmins[0]->getShopId()}/products")
            ;
        }
    }

    $response = new Response();
    $response->getBody()->write($twig->render("login.twig"));

    return $response;
});

$router->post('/login', function (ServerRequestInterface $request) use ($users, $admins, $shops, $client, $products): ResponseInterface {
    $form = $request->getParsedBody();

    if (!$form) {
        return (new Response())
            ->withStatus(302)
            ->withAddedHeader("Location", "/login")
        ;
    }

    $user = $users->getByEmail($form["email"]);

    if (!$user) {
        return (new Response())
            ->withStatus(302)
            ->withAddedHeader("Location", "/login")
        ;
    }

    $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    $session->set("userId", $user->getId());

    $client->upsertUser([
        "userId" => $user->getId(),
        "email" => $user->getEmail(),
        "properties" => [
            "first_name" => $user->getFirstName(),
            "last_name" => $user->getLastName(),
        ],
    ]);

    $client->addEvent(
        Event::forUser(
            "logged_in",
            UserIdentified::byUserId($user->getId())
        )
    );

    $shopAdmins = $admins->getByUserId($user->getId());
    foreach ($shopAdmins as $admin) {
        $shop = $shops->getById($admin->getShopId());

        if ($shop) {
            $client->upsertAccount([
                "accountId" => $shop->getId(),
                "properties" => [
                    "name" => $shop->getName(),
                    "products" => count($products->getByShopId($admin->getShopId())),
                ],
                "members" => array_map(
                    fn ($shopAdmin) => ["userId" => $shopAdmin->getUserId()],
                    $admins->getByShopId($shop->getId())
                )
            ]);
        }
    }

    return (new Response())
        ->withStatus(302)
        ->withAddedHeader("Location", "/shops/{$shopAdmins[0]->getShopId()}/products")
    ;
});

$router->get('/shops/{shopId}/products', function (ServerRequestInterface $request, array $args) use (
    $twig,
    $users,
    $products,
    $admins,
    $shops
): ResponseInterface {
    /** @var PSR7Sessions\Storageless\Session\SessionInterface $session */
    $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    $userId = $session->get("userId");
    $user = $users->getById($userId);

    if (!$user) {
        return (new Response())
            ->withStatus(302)
            ->withAddedHeader("Location", "/login")
        ;
    }

    $admins = $admins->getByUserId($user->getId());
    $numberFormatter = new NumberFormatter("en_UK", NumberFormatter::DECIMAL);
    $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());
    $response = new Response();
    $response->getBody()->write(
      $twig->render(
          "products.twig",
          [
              'shopId' => $args["shopId"],
              'shops' => array_map(
                  function (ShopAdmin $admin) use ($shops) {
                      $shop = $shops->getById($admin->getShopId());

                      return [
                          "id" => $shop->getId(),
                          "name" => $shop->getName(),
                      ];
                  },
                  $admins
              ),
              'user' => [
                  "id" => $user->getId(),
                  "first_name" => $user->getFirstName(),
                  "last_name" => $user->getLastName(),
              ],
              'products' => array_map(
                  fn ($product) => ([
                      "id" => $product->getId(),
                      "name" => $product->getName(),
                      "price" => $moneyFormatter->format($product->getPrice())
                  ]),
                  $products->getByShopId($args["shopId"])
              )
          ]
      )
    );

    return $response;
});

$router->get('/shops/{shopId}/products/add', function (ServerRequestInterface $request, array $args) use (
    $twig,
    $users
): ResponseInterface {
    /** @var PSR7Sessions\Storageless\Session\SessionInterface $session */
    $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    $userId = $session->get("userId");
    $user = $users->getById($userId);

    if (!$user) {
        return (new Response())
            ->withStatus(302)
            ->withAddedHeader("Location", '/login')
        ;
    }

    $response = new Response();
    $response->getBody()->write(
        $twig->render("add-product.twig", [
            'shopId' => $args['shopId'],
            'user' => [
                "id" => $user->getId(),
                "first_name" => $user->getFirstName(),
                "last_name" => $user->getLastName(),
            ],
        ])
    );

    return $response;
});

$router->post('/shops/{shopId}/products/add', function (ServerRequestInterface $request, array $args) use (
    $users,
    $products,
    $client
): ResponseInterface {
    /** @var PSR7Sessions\Storageless\Session\SessionInterface $session */
    $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    $userId = $session->get("userId");
    $user = $users->getById($userId);

    if (!$user) {
        return (new Response())
            ->withStatus(302)
            ->withAddedHeader("Location", "/login")
        ;
    }

    $shopId = $args["shopId"];
    $body = $request->getParsedBody();

    $products->persist(
        new Product(
            uniqid("product_", true),
            $shopId,
            $body["name"],
            Money::EUR($body["price"] * 100)
        )
    );

    $client->addEvent(
      Event::forUserInAccount(
          "added_product",
          UserIdentified::byUserId($user->getId()),
          AccountIdentified::byAccountId($shopId)
      )->withMetadata(["name" => $body["name"], "price" => (int) $body["price"]])
    );

    return (new Response())
        ->withStatus(302)
        ->withAddedHeader("Location", "/shops/{$shopId}/products")
    ;
});

$router->post("/logout", function (ServerRequestInterface $request) {
    /** @var PSR7Sessions\Storageless\Session\SessionInterface $session */
    $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    $session->clear();

    return (new Response())
        ->withStatus(302)
        ->withAddedHeader("Location", '/login')
    ;
});

$response = $router->dispatch($request);
(new SapiEmitter())->emit($response);
