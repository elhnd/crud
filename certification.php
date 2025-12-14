<?php

/**
 * CERTIFICATION QUESTIONS - SensioLabs PHP
 * ==========================================
 */

/**
 * QUESTION 1 - CHOIX UNIQUE
 * ========================
 * 
 * Consider the following class:
 * 
 * class Order
 * {
 *     // ...
 *
 *     public function finishCheckout(): void
 *     {
 *         match (true) {
 *             $this->inStock() => $this->processorOrder(),
 *             $this->stockComingSoon() => $this->notifyUser(),
 *             $this->outOfStock() => $this->orderNewStock(),
 *         };
 *     }
 * }
 * 
 * Question: Will the finishCheckout() method throw any exception because of an error in the match() statement?
 * 
 * Options:
 * - Yes, because you always need to capture the value returned by match() (even if it's void).
 * - Yes, because you can't pass true to match().
 * - Yes, because match() must always define a default expression.
 * - No.
 * 
 * RÉPONSE CORRECTE: No.
 * 
 * JUSTIFICATION:
 * Le match() expression en PHP 8+ n'a pas besoin d'une capture de valeur.
 * Il est valide de passer true à match().
 * Un "default" n'est pas obligatoire si tous les cas sont couverts par les conditions.
 * La match expression peut être utilisée comme statement sans valeur de retour.
 */

/**
 * QUESTION 2 - CHOIX UNIQUE
 * ========================
 * 
 * Consider the following function definition:
 * 
 * function sum(int $a, int $b): int
 * {
 *     return $a + $b;
 * }
 * 
 * Question: When using PHP 8 or higher, can you call this function as follows without triggering any exception?
 * 
 * echo sum(a: 3, 7);
 * 
 * Options:
 * - True
 * - False
 * 
 * RÉPONSE CORRECTE: False
 * 
 * JUSTIFICATION:
 * Avec PHP 8 et les named arguments, vous devez utiliser la syntaxe correcte.
 * La syntaxe "sum(a: 3, 7)" est INVALIDE car après avoir utilisé un argument nommé (a: 3),
 * vous ne pouvez pas utiliser un argument positionnel (7).
 * La syntaxe correcte serait: sum(a: 3, b: 7) ou sum(3, 7)
 */

/**
 * QUESTION 3 - CHOIX UNIQUE
 * ========================
 * 
 * Question: Is the following class definition valid when using PHP 8 and higher?
 * 
 * class Point
 * {
 *     public function __construct(private int|float $x, private int|float $y)
 *     {
 *     }
 * 
 *     // ...
 * }
 * 
 * Options:
 * - True
 * - False
 * 
 * RÉPONSE CORRECTE: True
 * 
 * JUSTIFICATION:
 * PHP 8 introduit la "Constructor Property Promotion" qui permet de déclarer et initialiser
 * des propriétés directement dans les paramètres du constructeur.
 * Les union types (int|float) sont également supportés en PHP 8.
 * Cette syntaxe est entièrement valide.
 */

/**
 * QUESTION 4 - CHOIX UNIQUE
 * ========================
 * 
 * Consider the following code snippet:
 * 
 * interface SayHelloInterface
 * {
 *     public function greet(string $who): string;
 * }
 * 
 * class Person implements SayHelloInterface
 * {
 *     public function greet(string $who, bool $scream = false): string
 *     {
 *         return sprintf($scream ? 'WELCOME %s!' : 'Welcome %s!', $who);
 *     }
 * }
 * 
 * echo (new Person())->greet('Alice', true);
 * 
 * Question: What is the result of executing this code snippet?
 * 
 * Options:
 * - Welcome Alice!
 * - WELCOME Alice!
 * - WELCOME ALICE!
 * - It produces a fatal error because the two greet() method signatures mismatch.
 * 
 * RÉPONSE CORRECTE: WELCOME Alice!
 * 
 * JUSTIFICATION:
 * La signature de la méthode dans la classe peut avoir des paramètres supplémentaires avec des valeurs par défaut.
 * La condition ternaire: $scream ? 'WELCOME %s!' : 'Welcome %s!'
 * Puisque $scream = true, la première partie est utilisée: 'WELCOME %s!'
 * sprintf() remplace %s par 'Alice', résultant en 'WELCOME Alice!'
 */

/**
 * QUESTION 5 - RÉPONSES MULTIPLES
 * ================================
 * 
 * Find all the working solutions for generating a 404 error page from a controller 
 * that extends the Symfony's AbstractController:
 * 
 * Options:
 * 1. return $this->error404();
 * 2. throw $this->createNotFoundException('Page not found');
 * 3. throw new NotFoundHttpException('Page not found');
 * 4. return new Response('Page not found', 404);
 * 5. return $this->createNotFoundException('Page not found');
 * 
 * RÉPONSES CORRECTES: Options 2, 3, et 4
 * 
 * JUSTIFICATIONS:
 * 2. ✓ $this->createNotFoundException() lance une exception qui génère une page 404
 * 3. ✓ NotFoundHttpException est l'exception appropriée pour une page 404
 * 4. ✓ Retourner une Response avec le code 404 fonctionne
 * 1. ✗ error404() n'existe pas dans AbstractController
 * 5. ✗ createNotFoundException() retourne une exception, elle ne peut pas être retournée directement
 */

/**
 * QUESTION 6 - CHOIX UNIQUE
 * ========================
 * 
 * Consider the following code:
 * 
 * # src/Controller/DefaultController.php
 * namespace App\Controller;
 * 
 * use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 * 
 * class DefaultController extends AbstractController
 * {
 *     public function index()
 *     {
 *         return $this->forward(???);
 *     }
 * }
 * 
 * # src/Controller/BlogController.php
 * namespace App\Controller;
 * 
 * use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 * 
 * class BlogController extends AbstractController
 * {
 *     public function index()
 *     {
 *         // ...
 *     }
 * }
 * 
 * Question: Which statement does ??? successfully replace to forward the execution to the index method of the BlogController?
 * 
 * Options:
 * - [BlogController::class, 'index']
 * - 'App\\Controller\\BlogController::index'
 * - ['App\\Controller\\BlogController::index']
 * - BlogController::class
 * - 'App\\Controller\\BlogController@index'
 * 
 * RÉPONSE CORRECTE: 'App\\Controller\\BlogController::index'
 * 
 * JUSTIFICATION:
 * La méthode forward() accepte un contrôleur au format string avec la syntaxe 'NameSpace\\Controller::method'
 * La deuxième option 'App\\Controller\\BlogController::index' est correcte - c'est le format attendu par forward()
 * La première option [BlogController::class, 'index'] retournerait un tableau, pas le format attendu
 * La troisième option ['App\\Controller\\BlogController::index'] est un tableau, pas un string
 * La quatrième option BlogController::class seul ne fonctionne pas sans la méthode
 * La cinquième option utilise la syntaxe @index qui n'est pas valide pour forward()
 */

/**
 * QUESTION 7 - CHOIX UNIQUE
 * ========================
 * 
 * Question: What's the recommended naming convention for action methods in Symfony controllers?
 * 
 * Options:
 * - do[actionName]() (e.g. doShow())
 * - actionName() (e.g. show())
 * - [actionName]Action() (e.g. showAction())
 * - perform[actionName]() (e.g. performShow())
 * 
 * RÉPONSE CORRECTE: actionName() (e.g. show())
 * 
 * JUSTIFICATION:
 * Depuis Symfony 2.8 et particulièrement en Symfony 4+, la convention recommandée est simplement
 * d'utiliser le nom de l'action en camelCase sans préfixe.
 * Les anciennes conventions avec suffixe "Action" (showAction()) sont dépréciées.
 * Les préfixes "do" et "perform" ne sont pas des conventions Symfony standard.
 */

/**
 * QUESTION 8 - CHOIX UNIQUE
 * ========================
 * 
 * Consider the following controller code:
 * 
 * # src/Controller/DemoController.php
 * namespace App\Controller;
 * 
 * use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 * 
 * class DemoController extends AbstractController
 * {
 *     public function index()
 *     {
 *         return $this->redirect('/');
 *     }
 * }
 * 
 * Question: What is the HTTP status code of the returned response?
 * 
 * Options:
 * - 301 (Moved Permanently)
 * - 302 (Found)
 * - 303 (See Other)
 * - 304 (Not Modified)
 * - 307 (Temporary Redirect)
 * 
 * RÉPONSE CORRECTE: 302 (Found)
 * 
 * JUSTIFICATION:
 * La méthode redirect() de AbstractController retourne par défaut un code HTTP 302.
 * 302 signifie "Found" et indique une redirection temporaire.
 * 301 serait pour une redirection permanente (utiliserait redirectToRoute() avec statusCode: 301)
 * 307 est également une redirection temporaire mais préserve la méthode HTTP (moins couramment utilisée)
 */

/**
 * QUESTION 9 - CHOIX UNIQUE
 * ========================
 * 
 * Question: In a controller that extends Symfony's AbstractController and receives the current Request 
 * object in an argument called $request, which of the following statements allows to store a temporary 
 * message in the session in order to display it after a redirect?
 * 
 * Options:
 * - $this->addFlash('notice', 'Item added successfully');
 * - $this->getSession()->store('notice', 'Item added successfully');
 * - $request->flashes->set('notice', 'Item added successfully');
 * - $request->session->getFlashes()->set('notice', 'Item added successfully');
 * 
 * RÉPONSE CORRECTE: $this->addFlash('notice', 'Item added successfully');
 * 
 * JUSTIFICATION:
 * La méthode addFlash() est la manière recommandée et officielle dans Symfony pour créer des messages flash.
 * Les messages flash sont stockés temporairement en session et s'auto-suppriment après affichage.
 * Les autres options utilisent des APIs inexistantes ou incorrectes :
 * - getSession()->store() ne fonctionne pas ainsi
 * - $request->flashes n'existe pas (devrait être $request->getSession())
 * - $request->session->getFlashes() n'est pas la bonne syntaxe
 */

/**
 * QUESTION 10 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following controller in a default Symfony application with both autowiring and autoconfiguration enabled:
 * 
 * use Psr\Log\LoggerInterface;
 * use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 * use Symfony\Component\HttpFoundation\Response;
 * // ...
 * 
 * class SomeController extends AbstractController
 * {
 *     public function index(
 *         #[ ??? (service: 'app.request_logger')] LoggerInterface $logger,
 *     ): Response
 *     {
 *         // ...
 *     }
 * }
 * 
 * Question: Which statement does ??? successfully replace in order to inject the service with ID app.request_logger in the $logger controller argument?
 * 
 * Options:
 * - Autoconfigure
 * - Autowire
 * - AsService
 * - Target
 * - Inject
 * 
 * RÉPONSE CORRECTE: Autowire
 * 
 * JUSTIFICATION:
 * L'attribut #[Autowire(service: 'app.request_logger')] est utilisé pour injecter un service spécifique par son ID.
 * Cet attribut accepte le paramètre 'service' pour spécifier l'ID du service à injecter.
 * Les autres options ne sont pas correctes :
 * - Autoconfigure est une directive de configuration services.yaml, pas un attribut d'injection
 * - AsService n'existe pas comme attribut d'injection
 * - Target est utilisé pour les alias de services autowirés mais sans le paramètre 'service'
 * - Inject n'est pas un attribut Symfony standard (c'est utilisé dans d'autres frameworks comme Doctrine)
 */

/**
 * QUESTION 11 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following is the valid way to persist a value in the user's session?
 * 
 * Options:
 * - $session->write('foo', 'bar');
 * - $session->add('foo', 'bar');
 * - $session->set('foo', 'bar');
 * - $session->store('foo', 'bar');
 * - $session->save('foo', 'bar');
 * 
 * RÉPONSE CORRECTE: $session->set('foo', 'bar');
 * 
 * JUSTIFICATION:
 * La méthode set() est la méthode standard pour stocker une valeur dans la session Symfony.
 * Les autres méthodes (write, add, store, save) n'existent pas dans l'interface SessionInterface.
 * Pour récupérer la valeur, on utilise $session->get('foo').
 */

/**
 * QUESTION 12 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following route available in two locales (en and nl):
 * 
 * contact:
 *     controller: App\Controller\ContactController::send
 *     path:
 *         en: /send-us-an-email
 *         nl: /stuur-ons-een-email
 * 
 * Question: Which URL will generate the following code which doesn't specify the locale? 
 * (consider that $urlGenerator is the default URL generator provided by Symfony)
 * 
 * $url = $urlGenerator->generate('contact');
 * 
 * Options:
 * - The first defined URL (in this case, the one for en locale).
 * - The last defined URL (in this case, the one for nl locale).
 * - The most appropriate URL depending on the geo-location of the user.
 * - The URL associated with the user locale or an exception if user locale is not en or nl.
 * 
 * RÉPONSE CORRECTE: The URL associated with the user locale or an exception if user locale is not en or nl.
 * 
 * JUSTIFICATION:
 * Quand on génère une URL sans spécifier la locale, Symfony utilise la locale courante de l'utilisateur.
 * Si la locale de l'utilisateur ne correspond à aucune des locales définies (en ou nl), 
 * une exception sera levée car Symfony ne peut pas déterminer quelle URL utiliser.
 * Symfony ne fait pas de géolocalisation automatique.
 */

/**
 * QUESTION 13 - CHOIX UNIQUE
 * =========================
 * 
 * A developer concerned with SEO (Search Engine Optimization) defines the following route to match 
 * multiple URLs with the same controller:
 * 
 * # config/routes.yaml
 * demo_route:
 *     paths: ['/', '/demo', '/demos', '/demos-about-xxx']
 *     controller: 'App\Controller\MainController::demo'
 * 
 * Question: Is this a valid route definition in Symfony?
 * 
 * Options:
 * - True
 * - False
 * 
 * RÉPONSE CORRECTE: False
 * 
 * JUSTIFICATION:
 * La syntaxe "paths" avec un tableau n'est pas valide dans la configuration de routes Symfony.
 * Pour définir plusieurs URLs pour un même contrôleur, il faut créer plusieurs routes distinctes
 * ou utiliser des paramètres optionnels dans le path.
 * La clé correcte est "path" (singulier) et non "paths" (pluriel).
 */

/**
 * QUESTION 14 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following controller code:
 * 
 * # src/Controller/BlogController.php
 * namespace App\Controller;
 * 
 * use Symfony\Component\HttpFoundation\Response;
 * 
 * class BlogController
 * {
 *     public function showAction(): Response
 *     {
 *         // ...
 *     }
 * }
 * 
 * And the following YAML route configuration:
 * 
 * # config/routes.yaml
 * app_blog:
 *     path: /blog
 *     controller: 'App\Controller\BlogController::show'
 *     methods: ['GET']
 * 
 * Question: Will the showAction() method be executed if a user requests the /blog URL using the address bar of a web browser?
 * 
 * Options:
 * - True (impliqué)
 * - False (impliqué)
 * 
 * RÉPONSE CORRECTE: False
 * 
 * JUSTIFICATION:
 * La route pointe vers 'BlogController::show' mais la méthode dans le contrôleur s'appelle 'showAction()'.
 * Il y a une incohérence entre le nom de la méthode configurée (show) et le nom réel (showAction).
 * Symfony cherchera la méthode show() qui n'existe pas, ce qui provoquera une erreur.
 * Le suffixe "Action" n'est plus automatiquement ajouté dans les versions modernes de Symfony.
 */

/**
 * QUESTION 15 - CHOIX UNIQUE
 * =========================
 * 
 * Identify the correct command name to complete the following sentence:
 * 
 * "The .......... command can be used to get all the information about a specific route."
 * 
 * Options:
 * - routing:route
 * - router:dump-routes
 * - router:info
 * - debug:router
 * - router:match
 * 
 * RÉPONSE CORRECTE: debug:router
 * 
 * JUSTIFICATION:
 * La commande debug:router affiche toutes les routes configurées dans l'application.
 * Pour obtenir les détails d'une route spécifique : php bin/console debug:router nom_de_la_route
 * Les autres commandes n'existent pas ou ont d'autres fonctions :
 * - router:match teste quelle route correspond à une URL donnée
 * - routing:route, router:dump-routes, router:info n'existent pas
 */

/**
 * QUESTION 16 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following route definition:
 * 
 * # src/Controller/BlogController.php
 * namespace App\Controller;
 * 
 * use Symfony\Component\HttpFoundation\Response;
 * 
 * class BlogController
 * {
 *     #[Route('/blog/{id}', defaults: ['_fragment' => 'comments'], name: 'blog_post_comments')]
 *     public function blogPost(): Response
 *     {
 *         // ...
 *     }
 * }
 * 
 * Question: Which will be the value of the following $url variable? 
 * (consider that $urlGenerator is the default URL generator provided by Symfony)
 * 
 * $url = $urlGenerator->generate('blog_post_comments', ['id' => 37]);
 * 
 * Options:
 * - /blog/37/_fragment/comments
 * - /blog?id=37&_fragment=comments
 * - /blog/?id=37&_fragment=comments
 * - /blog/37
 * - /blog/37#comments
 * 
 * RÉPONSE CORRECTE: /blog/37#comments
 * 
 * JUSTIFICATION:
 * Le paramètre spécial '_fragment' dans Symfony génère un fragment d'URL (hash/ancre).
 * Quand on définit '_fragment' => 'comments' dans les defaults, l'URL générée inclura #comments.
 * Le {id} est remplacé par 37, donnant /blog/37.
 * Le fragment est ajouté à la fin avec le caractère #, résultant en /blog/37#comments.
 * Note: _fragment n'est pas un paramètre de query string, c'est un fragment d'ancre HTML.
 */

/**
 * QUESTION 17 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following route definition:
 * 
 * # src/Controller/PostController.php
 * namespace App\Controller;
 * 
 * use Symfony\Component\HttpFoundation\Response;
 * use Symfony\Component\Routing\Attribute\Route;
 * 
 * #[Route('/blog', name: 'blog_')]
 * class PostController
 * {
 *     #[Route('/{id}', name: 'show')]
 *     public function show(int $id): Response
 *     {
 *         // ...
 *     }
 * }
 * 
 * Question: Which will be the name of the route associated with the show() method?
 * 
 * Options:
 * - show
 * - blog_1 (if show() is the first method, blog_2 if it's the second, etc.)
 * - This code will throw an exception (the parent #[Route] cannot define a name parameter).
 * - blog_show
 * - blog_
 * 
 * RÉPONSE CORRECTE: blog_show
 * 
 * JUSTIFICATION:
 * Quand un attribut #[Route] est défini au niveau de la classe avec un paramètre 'name',
 * ce nom est préfixé aux noms des routes des méthodes de la classe.
 * Ici: 'blog_' (classe) + 'show' (méthode) = 'blog_show'
 * C'est une bonne pratique pour organiser et grouper les noms de routes.
 */

/**
 * QUESTION 18 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following services configuration:
 * 
 * # config/services.yaml
 * imports:
 *     - { resource: './parameters.yaml', ignore_errors: true }
 * 
 * Question: What will happen if the parameters.yaml file does not exist in the same config/ directory 
 * from where it's being imported?
 * 
 * Options:
 * - You'll get an exception.
 * - The application will return a 404 (not found) HTTP response.
 * - The application will keep working (and that file won't be imported).
 * 
 * RÉPONSE CORRECTE: The application will keep working (and that file won't be imported).
 * 
 * JUSTIFICATION:
 * Le paramètre 'ignore_errors: true' indique à Symfony d'ignorer les erreurs lors de l'import.
 * Si le fichier n'existe pas, Symfony continue l'exécution normalement sans lever d'exception.
 * Sans ce paramètre, une exception FileLocatorFileNotFoundException serait levée.
 * C'est utile pour des fichiers de configuration optionnels.
 */

/**
 * QUESTION 19 - CHOIX UNIQUE
 * =========================
 * 
 * Question: A default Symfony application with both autowiring and autoconfiguration enabled defines 
 * many different logger services. Can you still use autowiring to inject some specific logger in a service?
 * 
 * Options:
 * - Yes, and you don't have to do or configure anything.
 * - Yes, but you have to add some service configuration or use some PHP attributes in your service class.
 * - No, you can't use autowiring in that case because Symfony doesn't know which exact logger to inject.
 * 
 * RÉPONSE CORRECTE: Yes, but you have to add some service configuration or use some PHP attributes in your service class.
 * 
 * JUSTIFICATION:
 * Quand plusieurs services implémentent la même interface (comme LoggerInterface),
 * Symfony ne peut pas deviner lequel injecter automatiquement.
 * Solutions possibles :
 * 1. Utiliser l'attribut #[Autowire(service: 'monolog.logger.custom')]
 * 2. Configurer un alias dans services.yaml
 * 3. Utiliser l'attribut #[Target] pour spécifier quel service injecter
 * Sans configuration supplémentaire, Symfony lèverait une exception d'autowiring.
 */

/**
 * QUESTION 20 - CHOIX UNIQUE
 * =========================
 * 
 * Question: In a Symfony application that uses autowiring, which of the following classes should you use 
 * to type-hint a class constructor argument in order to inject the current request stack?
 * 
 * Options:
 * - Symfony\Component\HttpFoundation\RequestStack
 * - Symfony\Component\Routing\RequestStackInterface
 * - Psr\Psr7\RequestStackInterface
 * - Symfony\Component\HttpKernel\RequestStack
 * - Symfony\Component\HttpFoundation\RequestStackInterface
 * 
 * RÉPONSE CORRECTE: Symfony\Component\HttpFoundation\RequestStack
 * 
 * JUSTIFICATION:
 * RequestStack est la classe concrète dans Symfony pour gérer la pile de requêtes.
 * Elle se trouve dans le composant HttpFoundation.
 * Il n'existe pas de RequestStackInterface dans Symfony.
 * RequestStack permet d'accéder à la requête courante via getCurrentRequest() ou getMainRequest().
 * C'est préférable d'injecter RequestStack plutôt que Request directement dans les services.
 */

/**
 * QUESTION 21 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following snippet of services configuration:
 * 
 * # config/services.yaml
 * services:
 *     _defaults:
 *         ???:
 *             $projectDir: '%kernel.project_dir%'
 *     # ...
 * 
 * Question: Which option does ??? successfully replace in order to inject the '%kernel.project_dir%' parameter 
 * in every constructor argument called $projectDir for all services created in this file?
 * 
 * Options:
 * - inject_parameters
 * - bind
 * - bind_parameters
 * - inject
 * - parameters
 * 
 * RÉPONSE CORRECTE: bind
 * 
 * JUSTIFICATION:
 * L'option 'bind' dans _defaults permet de lier automatiquement des valeurs à des paramètres
 * de constructeur basés sur leur nom, pour tous les services définis dans ce fichier.
 * Syntaxe: bind: { $nomParametre: 'valeur' }
 * Cela évite de répéter la même injection dans chaque définition de service.
 * 'bind' fonctionne avec des noms de paramètres, des types, ou des services.
 */

/**
 * QUESTION 22 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which service should you pass as a dependency of another service that needs to get access 
 * to the current Request object?
 * 
 * Options:
 * - event_dispatcher
 * - router.request_context
 * - http_kernel
 * - request
 * - request_stack
 * 
 * RÉPONSE CORRECTE: request_stack
 * 
 * JUSTIFICATION:
 * Le service 'request_stack' (RequestStack) est la manière recommandée pour accéder à la requête courante.
 * Il maintient une pile de toutes les requêtes (principale et sub-requests).
 * Méthode: $requestStack->getCurrentRequest() ou getMainRequest()
 * Injecter directement 'request' n'est pas recommandé car il change à chaque requête,
 * causant des problèmes avec les services en scope différent.
 * RequestStack résout ces problèmes de scope.
 */

/**
 * QUESTION 23 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following service configuration example of a default Symfony application:
 * 
 * # config/services.yaml
 * services:
 *     _defaults:
 *         autowire: true
 *         autoconfigure: true
 *         bind:
 *             string $projectDir: '%kernel.project_dir%'
 * 
 * Question: In which services will Symfony inject the value of the kernel.project_dir container parameter 
 * in any constructor argument called $projectDir?
 * 
 * Options:
 * - In none. This bind option does not exist.
 * - In all services (both your own services and the vendor services).
 * - In all of your own services.
 * - In all services defined/created in this config/services.yaml file.
 * 
 * RÉPONSE CORRECTE: In all services defined/created in this config/services.yaml file.
 * 
 * JUSTIFICATION:
 * La section _defaults s'applique uniquement aux services définis dans le même fichier de configuration.
 * Elle ne s'applique pas aux services définis dans d'autres fichiers ou bundles.
 * L'option 'bind' sous _defaults lie automatiquement $projectDir à la valeur spécifiée
 * pour tous les services créés dans ce fichier config/services.yaml uniquement.
 * Les services des vendors ou d'autres fichiers ne sont pas affectés.
 */

/**
 * QUESTION 24 - CHOIX UNIQUE
 * =========================
 * 
 * A Symfony application wants to store the templates in the resources/views/ directory instead of 
 * the default templates/ directory. Which statements do xxx and yyy successfully replace in the 
 * following config to achieve that?
 * 
 * # config/packages/twig.yaml
 * twig:
 *     xxx: ['yyy']
 * 
 * Options:
 * - paths and '%kernel.project_dir%/resources/views/'
 * - templates and '%kernel.root_dir%/resources/views/'
 * - path and '@resources/views/'
 * - path and '@framework/views/'
 * 
 * RÉPONSE CORRECTE: paths and '%kernel.project_dir%/resources/views/'
 * 
 * JUSTIFICATION:
 * L'option 'paths' (pluriel) dans la configuration Twig permet de définir des répertoires 
 * supplémentaires où chercher les templates.
 * Syntaxe: paths: ['chemin1', 'chemin2']
 * %kernel.project_dir% pointe vers la racine du projet.
 * Note: 'path' (singulier) n'existe pas, c'est 'paths' (pluriel).
 * Les préfixes @ sont pour les namespaces de bundles, pas pour des chemins physiques.
 */

/**
 * QUESTION 25 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following simple Twig template inheritance:
 * 
 * {# parent.html.twig #}
 * <head>
 *     <title>
 *         {% block title %}Lorem ipsum{% endblock %}
 *     </title>
 *     ...
 * </head>
 * 
 * {# child.html.twig #}
 * {% extends "parent.html.twig" %}
 * {% block title %}???{% endblock %}
 * 
 * Question: In the child.html.twig template, which statement does ??? successfully replace to render 
 * "Lorem ipsum - Dolor Sit Amet" as the page title?
 * 
 * Options:
 * - parent('title') - Dolor Sit Amet
 * - {{ parent() }} - Dolor Sit Amet
 * - {{ block('title') ~ "- Dolor Sit Amet" }}
 * - {{ parent('title') }} - {{ Dolor Sit Amet }}
 * 
 * RÉPONSE CORRECTE: {{ parent() }} - Dolor Sit Amet
 * 
 * JUSTIFICATION:
 * La fonction parent() dans Twig permet d'accéder au contenu du block parent.
 * Syntaxe: {{ parent() }} - sans argument
 * Elle doit être appelée à l'intérieur d'un block et affiche le contenu du même block du template parent.
 * {{ parent() }} retourne "Lorem ipsum", puis on concatène " - Dolor Sit Amet".
 * parent() ne prend pas de paramètre, contrairement à block('title').
 */

/**
 * QUESTION 26 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following Twig filters would you apply to remove the white spaces only from 
 * the right side of the contents of a given variable?
 * 
 * Options:
 * - |rtrim
 * - |right_trim
 * - |trim
 * - |trim(side = 'right')
 * 
 * RÉPONSE CORRECTE: |rtrim
 * 
 * JUSTIFICATION:
 * Le filtre |rtrim (right trim) supprime les espaces blancs uniquement à droite d'une chaîne.
 * Exemple: {{ text|rtrim }}
 * Autres filtres Twig trim:
 * - |trim : supprime des deux côtés
 * - |ltrim : supprime à gauche (left trim)
 * - |rtrim : supprime à droite (right trim)
 * Les filtres |right_trim et |trim(side='right') n'existent pas dans Twig.
 */

/**
 * QUESTION 27 - CHOIX UNIQUE
 * =========================
 * 
 * A default Symfony application defines the following asset configuration:
 * 
 * # config/packages/framework.yaml
 * framework:
 *     assets:
 *         version: 'v2'
 *         version_format: '%%s?version=%%s'
 *         packages:
 *             docs:
 *                 base_path: /docs/pdf
 * 
 * A Twig template has the following code:
 * 
 * {{ asset('terms_and_conditions.pdf', 'docs') }}
 * 
 * Question: What will be the link generated by the above Twig snippet?
 * 
 * Options:
 * - /docs/pdf/v2/terms_and_conditions.pdf
 * - /docs/pdf/terms_and_conditions.pdf?version=v2
 * - /v2/docs/pdf/terms_and_conditions.pdf
 * - /docs/pdf/terms_and_conditions.pdf
 * - terms_and_conditions.pdf?version=v2
 * 
 * RÉPONSE CORRECTE: /docs/pdf/terms_and_conditions.pdf?version=v2
 * 
 * JUSTIFICATION:
 * La fonction asset() génère un chemin vers un asset avec le package spécifié.
 * Le base_path du package 'docs' est '/docs/pdf', donc le chemin commence par /docs/pdf/
 * Le version_format '%%s?version=%%s' ajoute la version comme paramètre de query string
 * Le fichier 'terms_and_conditions.pdf' est ajouté au base_path
 * La version 'v2' est appliquée selon le format spécifié
 * Résultat final: /docs/pdf/terms_and_conditions.pdf?version=v2
 */

/**
 * QUESTION 28 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which will be the result of executing this Twig snippet?
 * 
 * {{ block("footer", "base.html.twig") }}
 * 
 * Options:
 * - It renders the contents of the footer block. If that block is undefined, it renders the contents 
 *   of the base.html.twig template as a fallback content.
 * - It renders the contents of the footer block (the second argument base.html.twig is ignored).
 * - It renders the contents of the footer block from the base.html.twig template.
 * - This code throws an exception because block() doesn't accept more than 1 argument.
 * 
 * RÉPONSE CORRECTE: It renders the contents of the footer block from the base.html.twig template.
 * 
 * JUSTIFICATION:
 * La fonction block() dans Twig peut accepter deux arguments :
 * 1. Le nom du block à rendre
 * 2. Le template d'où extraire ce block (optionnel)
 * Avec block("footer", "base.html.twig"), Twig rend le contenu du block "footer" 
 * tel qu'il est défini dans le template base.html.twig.
 * C'est utile pour inclure des blocks spécifiques d'autres templates.
 */

/**
 * QUESTION 29 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Is the following a valid Twig statement that won't produce any error? 
 * (consider that the variable title is defined in the template and the some_template.html.twig exists)
 * 
 * {% include 'some_template.html.twig' with { title } only %}
 * 
 * Options:
 * - True
 * - False
 * 
 * RÉPONSE CORRECTE: True
 * 
 * JUSTIFICATION:
 * Cette syntaxe est valide en Twig. C'est une syntaxe raccourcie (shorthand) pour passer des variables.
 * { title } est équivalent à { 'title': title } - c'est une syntaxe raccourcie introduite dans Twig 3.
 * Le mot-clé 'only' indique que seules les variables passées explicitement seront disponibles dans le template inclus.
 * Sans 'only', toutes les variables du contexte parent seraient également disponibles.
 */

/**
 * QUESTION 30 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following Twig snippet:
 * 
 * {% set result = null %}
 * {% for i in 1..5 %}
 *     {% set result = result + loop.index0 %}
 * {% endfor %}
 * 
 * The result is: {{ result }}
 * 
 * Question: What will be the output when rendering this template?
 * 
 * Options:
 * - The result is: 10
 * - The result is: [0, 1, 3, 6, 10]
 * - The result is: null
 * - The result is: 15
 * - The result is: 5
 * 
 * RÉPONSE CORRECTE: The result is: 10
 * 
 * JUSTIFICATION:
 * Analyse du code :
 * - result commence à null
 * - La boucle itère de 1 à 5 (5 itérations)
 * - loop.index0 est l'index basé sur 0 (commence à 0)
 * Itération 1: i=1, loop.index0=0, result = null + 0 = 0
 * Itération 2: i=2, loop.index0=1, result = 0 + 1 = 1
 * Itération 3: i=3, loop.index0=2, result = 1 + 2 = 3
 * Itération 4: i=4, loop.index0=3, result = 3 + 3 = 6
 * Itération 5: i=5, loop.index0=4, result = 6 + 4 = 10
 * Résultat final: 10
 */

/**
 * QUESTION 31 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following code that ensures that the user's password does not contain their username:
 * 
 * # src/Model/User.php
 * namespace App\Model;
 * 
 * use Symfony\Component\Validator\Constraints as Assert;
 * 
 * class User
 * {
 *     public $username;
 *     public $password;
 * 
 *     #[Assert\IsTrue(message: 'Your password must not contain your username.')]
 *     public function isPasswordValid(): bool
 *     {
 *         return !str_contains($this->password, $this->username);
 *     }
 * }
 * 
 * Question: If this validation is not successful, where will the error message be displayed?
 * 
 * Options:
 * - In the username field.
 * - In the password field.
 * - In both the username and password fields.
 * - It's a global error, so it will rendered at the form level (top or bottom depending on the form theme design).
 * - This error won't be displayed anywhere in the form, it will just be logged.
 * 
 * RÉPONSE CORRECTE: It's a global error, so it will rendered at the form level (top or bottom depending on the form theme design).
 * 
 * JUSTIFICATION:
 * La contrainte #[Assert\IsTrue] est appliquée sur une méthode (isPasswordValid()), pas sur une propriété spécifique.
 * Quand une contrainte de validation n'est pas liée à une propriété/champ spécifique, 
 * l'erreur est considérée comme une erreur globale de formulaire.
 * Les erreurs globales sont généralement affichées en haut ou en bas du formulaire selon le thème utilisé.
 * Pour afficher l'erreur sur un champ spécifique, il faudrait utiliser @Assert\Callback 
 * ou appliquer la contrainte directement sur la propriété $password avec une contrainte personnalisée.
 */

/**
 * QUESTION 32 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following model class:
 * 
 * namespace App\Model;
 * 
 * use Symfony\Component\Validator\Constraints as Assert;
 * 
 * class Actor
 * {
 *     ???
 *     private \DateTime $dateOfDeath;
 * 
 *     // ...
 * 
 *     public function getDateOfDeath(): \DateTime
 *     {
 *         return $this->dateOfDeath;
 *     }
 * }
 * 
 * Question: Which of the following constraints does ??? successfully replace in order to validate 
 * the date of death is not greater than the current day?
 * 
 * Options:
 * - #[Assert\Range(min: "today")]
 * - #[Assert\LessThanOrEqual("today")]
 * - #[Assert\Expression("this.getDateOfDeath().format('U') > strtotime('today')")]
 * - #[Assert\Date("now")]
 * - #[Assert\LessThan("current day")]
 * 
 * RÉPONSE CORRECTE: #[Assert\LessThanOrEqual("today")]
 * 
 * JUSTIFICATION:
 * La contrainte LessThanOrEqual permet de vérifier qu'une date est inférieure ou égale à une autre date.
 * "today" est une valeur spéciale reconnue par cette contrainte qui représente la date du jour.
 * Cela garantit que dateOfDeath <= aujourd'hui (pas dans le futur).
 * Les autres options :
 * - Range(min: "today") vérifie que la date est >= aujourd'hui (inverse de ce qu'on veut)
 * - Expression: la syntaxe serait incorrecte et trop complexe pour ce cas simple
 * - Date("now") n'existe pas avec ce paramètre
 * - LessThan("current day"): "current day" n'est pas une valeur valide, il faut "today"
 */

/**
 * QUESTION 33 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following code:
 * 
 * # src/Model/User.php
 * namespace App\Model;
 * 
 * use Symfony\Component\Validator\Constraints as Assert;
 * 
 * class User
 * {
 *     ???
 *     public \DateTime $birthday;
 * }
 * 
 * Question: Which Symfony Validator constraint does ??? successfully replace to ensure the user is under 18 years old?
 * 
 * Options:
 * - #[Assert\Regex('/^\d+$/')]
 * - #[Assert\Date('18 years')]
 * - #[Assert\Birthday(18)]
 * - #[Assert\Range(limit: 18)]
 * - None of the above
 * 
 * RÉPONSE CORRECTE: None of the above
 * 
 * JUSTIFICATION:
 * Aucune des contraintes proposées n'est valide ou appropriée pour vérifier l'âge :
 * - Regex ne peut pas calculer un âge à partir d'une date
 * - Date('18 years') n'existe pas avec cette syntaxe
 * - Birthday(18) n'existe pas comme contrainte Symfony standard
 * - Range(limit: 18) n'existe pas avec ce paramètre
 * Pour vérifier qu'un utilisateur a moins de 18 ans, il faudrait utiliser :
 * #[Assert\LessThan('-18 years')] ou #[Assert\GreaterThan('today - 18 years')]
 * Ou créer une contrainte personnalisée avec Expression ou Callback.
 */

/**
 * QUESTION 34 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following model class:
 * 
 * namespace App\Model;
 * 
 * use Symfony\Component\HttpFoundation\File\UploadedFile;
 * use Symfony\Component\Validator\Constraints as Assert;
 * 
 * class Picture
 * {
 *     #[Assert\Image(
 *         mimeTypes: ['image/png'],
 *         maxSize: '2M',
 *         orientations: ['landscape']
 *     )]
 *     public UploadedFile $uploadedFile;
 * }
 * 
 * Question: Is the above validation configuration valid?
 * 
 * Options:
 * - True
 * - False
 * 
 * RÉPONSE CORRECTE: False
 * 
 * JUSTIFICATION:
 * La contrainte #[Assert\Image] n'a pas de paramètre 'orientations'.
 * Les paramètres valides pour Image sont :
 * - mimeTypes (valide)
 * - maxSize (valide)
 * - minWidth, maxWidth, minHeight, maxHeight
 * - maxRatio, minRatio
 * - allowSquare, allowLandscape, allowPortrait (booléens, pas un tableau)
 * - detectCorrupted
 * Le paramètre 'orientations' n'existe pas. Si on veut forcer landscape, on utiliserait :
 * allowLandscape: true, allowPortrait: false, allowSquare: false
 */

/**
 * QUESTION 35 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following form defined in a class:
 * 
 * use Symfony\Component\Form\AbstractType;
 * use Symfony\Component\Form\Extension\Core\Type\TextType;
 * 
 * class TaskType extends AbstractType
 * {
 *     public function buildForm(FormBuilderInterface $builder, array $options): void
 *     {
 *         $builder
 *             ->add('field1', TextType::class)
 *             ->add('field2', null)
 *             ->add('field3');
 *     }
 *     // ...
 * }
 * 
 * Question: What fields will Symfony apply the "form type guessing" mechanism to?
 * 
 * Options:
 * - field1 only.
 * - field2 and field3 only.
 * - field1 and field2 only.
 * - field2 only.
 * - field3 only.
 * 
 * RÉPONSE CORRECTE: field2 and field3 only.
 * 
 * JUSTIFICATION:
 * Le "form type guessing" s'applique quand le type du champ n'est pas explicitement spécifié.
 * - field1: TextType::class est explicitement défini → pas de guessing
 * - field2: null comme type → Symfony devine le type à partir des métadonnées de la classe
 * - field3: aucun type spécifié → Symfony devine également le type
 * Symfony utilise les annotations/attributs de validation, les types de propriété PHP, 
 * et les métadonnées Doctrine pour deviner le type de formulaire approprié.
 */

/**
 * QUESTION 36 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following is not a built-in form field type class provided by the Symfony Form component?
 * 
 * Options:
 * - CurrencyType
 * - CountryType
 * - LocaleType
 * - LanguageType
 * - FloatType
 * 
 * RÉPONSE CORRECTE: FloatType
 * 
 * JUSTIFICATION:
 * Les types de formulaire Symfony standard incluent :
 * - CurrencyType ✓ (pour sélectionner une devise)
 * - CountryType ✓ (pour sélectionner un pays)
 * - LocaleType ✓ (pour sélectionner une locale)
 * - LanguageType ✓ (pour sélectionner une langue)
 * - FloatType ✗ n'existe pas
 * 
 * Pour les nombres décimaux, Symfony utilise NumberType avec l'option 'scale' ou 'html5' => false.
 * Il n'existe pas de type spécifique "FloatType" dans le composant Form de Symfony.
 */

/**
 * QUESTION 37 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Inside a custom form type definition class, which of the following statements is the right 
 * solution to customize the name of the twig block used to render the form?
 * 
 * Options:
 * - Implement a getName() method.
 * - Register a block_name option in the OptionsResolver object.
 * - Call the setRenderingBlockName() method on the FormBuilder object.
 * - Override the getBlockPrefix() method from the base AbstractType class.
 * - It's not possible to change the Twig block name from the form type definition class.
 * 
 * RÉPONSE CORRECTE: Override the getBlockPrefix() method from the base AbstractType class.
 * 
 * JUSTIFICATION:
 * La méthode getBlockPrefix() dans un custom form type détermine le préfixe utilisé pour les blocks Twig.
 * Par exemple, si getBlockPrefix() retourne 'my_custom', Symfony cherchera les blocks :
 * - my_custom_widget
 * - my_custom_row
 * - my_custom_label
 * - etc.
 * 
 * Les autres options ne sont pas correctes :
 * - getName() était utilisé dans Symfony 2.x mais est déprécié
 * - block_name n'est pas une option standard dans OptionsResolver
 * - setRenderingBlockName() n'existe pas
 */

/**
 * QUESTION 38 - CHOIX UNIQUE
 * =========================
 * 
 * Question: How can you dynamically change the submitted data of a form object just after they are 
 * normalized and written into the mapped object?
 * 
 * Options:
 * - Creating a listener that listens to the kernel.request main event.
 * - Overriding the submit() method of the form type class.
 * - Attaching a listener to the form type object that listens to the form.post_submit form event.
 * - Declaring a new postSubmitData() method in the form type class.
 * - It's not possible to dynamically change the submitted data of a form object.
 * 
 * RÉPONSE CORRECTE: Attaching a listener to the form type object that listens to the form.post_submit form event.
 * 
 * JUSTIFICATION:
 * L'événement FormEvents::POST_SUBMIT est déclenché après que les données ont été normalisées 
 * et écrites dans l'objet mappé.
 * C'est l'endroit approprié pour modifier les données soumises de manière dynamique.
 * 
 * Exemple :
 * $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
 *     $data = $event->getData();
 *     // Modifier $data
 *     $event->setData($data);
 * });
 * 
 * Les autres options ne fonctionnent pas pour ce cas d'usage.
 */

/**
 * QUESTION 39 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following snippet of a form theme:
 * 
 * {% block form_row %}
 *     {% set row_attr = row_attr|merge({
 *         class: ???.class|default('') ~ 'some_custom_class'
 *     }) %}
 * 
 *     {{ parent() }}
 * {% endblock %}
 * 
 * Question: Which statement does ??? successfully replace in order to add a CSS class to the form_row block?
 * 
 * Options:
 * - attributes
 * - widget_attr
 * - parent_attr
 * - _attr
 * - row_attr
 * 
 * RÉPONSE CORRECTE: row_attr
 * 
 * JUSTIFICATION:
 * La variable row_attr contient les attributs HTML de la ligne de formulaire.
 * Pour accéder à la classe existante et y ajouter une nouvelle, on utilise row_attr.class.
 * 
 * Syntaxe correcte : row_attr.class|default('') ~ ' some_custom_class'
 * 
 * Les autres variables :
 * - attributes n'existe pas dans ce contexte
 * - widget_attr concerne le widget du champ, pas la row
 * - parent_attr n'existe pas
 * - _attr n'est pas une variable standard
 */

/**
 * QUESTION 40 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following methods is not considered idempotent by the HTTP specification?
 * 
 * Options:
 * - PUT
 * - DELETE
 * - POST
 * - GET
 * - HEAD
 * 
 * RÉPONSE CORRECTE: POST
 * 
 * JUSTIFICATION:
 * Selon la spécification HTTP, les méthodes idempotentes sont celles qui peuvent être appelées 
 * plusieurs fois avec le même résultat :
 * 
 * IDEMPOTENTES :
 * - GET ✓ (lecture de ressource)
 * - PUT ✓ (remplacement complet d'une ressource)
 * - DELETE ✓ (suppression d'une ressource)
 * - HEAD ✓ (comme GET mais sans body)
 * 
 * NON IDEMPOTENTE :
 * - POST ✗ (création de ressource - chaque appel peut créer une nouvelle ressource)
 * 
 * Exemple : POST /users crée un nouvel utilisateur à chaque appel.
 * PUT /users/123 remplace l'utilisateur 123, même résultat si appelé plusieurs fois.
 */

/**
 * QUESTION 41 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following is not a valid HTTP status code?
 * 
 * Options:
 * - 204 No Content
 * - 307 Temporary Redirect
 * - 451 Unavailable For Legal Reasons
 * - 505 Locale Not Available
 * 
 * RÉPONSE CORRECTE: 505 Locale Not Available
 * 
 * JUSTIFICATION:
 * Le code 505 existe mais signifie "HTTP Version Not Supported", pas "Locale Not Available".
 * "Locale Not Available" n'est pas un code de statut HTTP standard.
 * 
 * Les codes valides sont :
 * - 204 No Content ✓ (succès sans contenu à retourner)
 * - 307 Temporary Redirect ✓ (redirection temporaire en préservant la méthode HTTP)
 * - 451 Unavailable For Legal Reasons ✓ (contenu censuré pour des raisons légales)
 * - 505 HTTP Version Not Supported ✓ (mais PAS "Locale Not Available")
 */

/**
 * QUESTION 42 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following messages is mostly associated with the 405 HTTP status code?
 * 
 * Options:
 * - Unauthorized
 * - Method Not Allowed
 * - Forbidden
 * - Not Found
 * - Payment Required
 * 
 * RÉPONSE CORRECTE: Method Not Allowed
 * 
 * JUSTIFICATION:
 * Le code HTTP 405 signifie "Method Not Allowed".
 * Il est retourné quand la méthode HTTP utilisée (GET, POST, PUT, DELETE, etc.) 
 * n'est pas autorisée pour la ressource demandée.
 * 
 * Autres codes pour référence :
 * - 401 Unauthorized
 * - 403 Forbidden
 * - 404 Not Found
 * - 402 Payment Required
 */

/**
 * QUESTION 43 - CHOIX UNIQUE
 * =========================
 * 
 * Question: 400 is one of the server error HTTP status codes.
 * 
 * Options:
 * - True
 * - False
 * 
 * RÉPONSE CORRECTE: False
 * 
 * JUSTIFICATION:
 * 400 est un code d'erreur CLIENT (4xx), pas un code d'erreur SERVEUR (5xx).
 * 
 * Classification des codes HTTP :
 * - 1xx : Informationnel
 * - 2xx : Succès
 * - 3xx : Redirection
 * - 4xx : Erreur CLIENT (400 Bad Request fait partie de cette catégorie)
 * - 5xx : Erreur SERVEUR (500, 502, 503, 504, etc.)
 * 
 * Le code 400 signifie "Bad Request" - la requête du client est malformée.
 */

/**
 * QUESTION 44 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following HTTP status codes is mostly associated with the Service Unavailable message?
 * 
 * Options:
 * - 500
 * - 501
 * - 502
 * - 503
 * - 504
 * 
 * RÉPONSE CORRECTE: 503
 * 
 * JUSTIFICATION:
 * Le code HTTP 503 signifie "Service Unavailable".
 * Il indique que le serveur est temporairement indisponible (maintenance, surcharge, etc.).
 * 
 * Autres codes 5xx pour référence :
 * - 500 Internal Server Error (erreur générique du serveur)
 * - 501 Not Implemented (méthode non supportée)
 * - 502 Bad Gateway (réponse invalide d'un serveur en amont)
 * - 503 Service Unavailable ✓ (service temporairement indisponible)
 * - 504 Gateway Timeout (timeout d'un serveur en amont)
 */

/**
 * QUESTION 45 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following controller code:
 * 
 * # src/Controller/DateController.php
 * namespace App\Controller;
 * 
 * use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 * use Symfony\Component\HttpFoundation\Response;
 * use Symfony\Component\HttpKernel\Attribute\Cache;
 * use Symfony\Component\Routing\Attribute\Route;
 * 
 * class DateController extends AbstractController
 * {
 *     #[Route('/time')]
 *     #[Cache(expires: '+1 hour')]
 *     public function time(): Response
 *     {
 *         return $this->render('date/time.html.twig', [
 *             'date' => new \DateTime(),
 *         ]);
 *     }
 * }
 * 
 * The template just displays the date passed from the controller.
 * 
 * A user accesses the /time page twice using the same browser:
 * 1. date/time of the first request: Wed, March 2nd 16:00:00.
 * 2. date/time of the second request: Wed, March 2nd 16:50:00.
 * 
 * Question: What date/time will the page display on the second request?
 * 
 * Options:
 * - Wed, March 2nd 16:00:00
 * - Wed, March 2nd 16:50:00
 * - It depends on the user's timezone.
 * - The date when the page was requested by any other user.
 * 
 * RÉPONSE CORRECTE: Wed, March 2nd 16:00:00
 * 
 * JUSTIFICATION:
 * L'attribut #[Cache(expires: '+1 hour')] configure le cache HTTP pour 1 heure.
 * 
 * Timeline :
 * - 1ère requête à 16:00:00 → page générée et mise en cache jusqu'à 17:00:00
 * - 2ème requête à 16:50:00 → encore dans la période de cache (< 17:00:00)
 * 
 * Le navigateur utilise sa version en cache qui affiche la date de la première requête : 16:00:00
 * La page ne sera régénérée qu'après 17:00:00 (expiration du cache).
 * 
 * Note: Le cache HTTP côté client évite de régénérer la page tant que le délai n'est pas expiré.
 */

/**
 * QUESTION 46 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following controller code:
 * 
 * # src/Controller/PageController.php
 * namespace App\Controller;
 * 
 * use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 * use Symfony\Component\HttpFoundation\Response;
 * use Symfony\Component\HttpKernel\Attribute\Cache;
 * use Symfony\Component\Routing\Attribute\Route;
 * 
 * class PageController extends AbstractController
 * {
 *     #[Route('/about')]
 *     #[Cache(smaxage: 3600)]
 *     public function about(): Response
 *     {
 *         return $this->render('page/about.html.twig', [
 *             'updated_at' => new \DateTime(),
 *         ]);
 *     }
 * 
 *     #[Cache(smaxage: 600)]
 *     public function news(): Response
 *     {
 *         return $this->render('page/news.html.twig', [
 *             'updated_at' => new \DateTime(),
 *         ]);
 *     }
 * }
 * 
 * And the code of the two associated templates:
 * 
 * {# page/about.html.twig #}
 * Clock1: {{ updated_at|date('H:i:s T') }}
 * {{ render_esi(controller('App\\Controller\\PageController::news')) }}
 * 
 * {# page/news.html.twig #}
 * Clock2: {{ updated_at|date('H:i:s T') }}
 * 
 * Question: If ESI caching is enabled and the first user requests the /about page at 16:00:00 GMT.
 * What date will each clock display when the second user requests the same page at 17:30:00 GMT?
 * 
 * Options:
 * - Clock1: 17:00:00 GMT / Clock2: 17:30:00 GMT
 * - Clock1: 16:00:00 GMT / Clock2: 17:30:00 GMT
 * - Clock1: 17:30:00 GMT / Clock2: 16:00:00 GMT
 * - Clock1: 17:30:00 GMT / Clock2: 17:30:00 GMT
 * - Clock1: 17:00:00 GMT / Clock2: 16:10:00 GMT
 * 
 * RÉPONSE CORRECTE: Clock1: 16:00:00 GMT / Clock2: 17:30:00 GMT
 * 
 * JUSTIFICATION:
 * Avec ESI (Edge Side Includes), les fragments sont mis en cache et invalidés indépendamment.
 * 
 * Analyse du cache :
 * - Page /about (Clock1) : smaxage: 3600 (1 heure) → cache valide de 16:00:00 à 17:00:00
 * - Fragment news (Clock2) : smaxage: 600 (10 minutes) → cache valide de 16:00:00 à 16:10:00
 * 
 * À 17:30:00 GMT :
 * - Clock1 (about) : Le cache de 1h a expiré à 17:00:00, mais avec ESI le fragment principal
 *   reste en cache côté proxy/Varnish. Le contenu statique de about.html.twig affiche toujours
 *   16:00:00 car c'est la valeur mise en cache initialement.
 * - Clock2 (news via ESI) : Le fragment ESI a son propre cycle de cache. À 17:30:00, le cache
 *   de 10 minutes est expiré depuis longtemps, donc le fragment est régénéré et affiche 17:30:00.
 * 
 * Le principe clé d'ESI : chaque fragment a sa propre durée de cache indépendante.
 * Le fragment ESI est rendu séparément et peut être mis à jour sans régénérer la page principale.
 */

/**
 * QUESTION 47 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following controller code:
 * 
 * # src/Controller/PageController.php
 * namespace App\Controller;
 * 
 * use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 * use Symfony\Component\HttpFoundation\Response;
 * 
 * class PageController extends AbstractController
 * {
 *     public function termsOfUse(): Response
 *     {
 *         $response = $this->render('page/tos.html.twig');
 *         $response->setETag('abcdef');
 * 
 *         return $response;
 *     }
 * }
 * 
 * Question: Is this response cacheable either on the client (web browser) or on a shared reverse proxy cache like Varnish?
 * 
 * Options:
 * - True
 * - False
 * 
 * RÉPONSE CORRECTE: False
 * 
 * JUSTIFICATION:
 * Définir un ETag seul ne rend pas une réponse cacheable.
 * L'ETag est utilisé pour la validation du cache (validation conditionnelle), 
 * mais il ne spécifie pas que la réponse PEUT être mise en cache.
 * 
 * Pour rendre une réponse cacheable, il faut également définir :
 * - Cache-Control: public ou private
 * - Ou définir s-maxage/max-age
 * - Ou utiliser l'attribut #[Cache(...)]
 * 
 * L'ETag permet au client de demander "cette ressource a-t-elle changé ?" avec If-None-Match,
 * mais sans directive de cache explicite, la réponse n'est pas mise en cache.
 */

/**
 * QUESTION 48 - RÉPONSES MULTIPLES
 * ================================
 * 
 * Question: Which of the following HTTP methods are not cacheable?
 * 
 * Options:
 * - DELETE
 * - PUT
 * - GET
 * - POST
 * - HEAD
 * 
 * RÉPONSES CORRECTES: DELETE, PUT, POST
 * 
 * JUSTIFICATION:
 * Selon la spécification HTTP (RFC 7231), les méthodes cacheables sont :
 * 
 * CACHEABLES :
 * - GET ✓ (la méthode la plus couramment mise en cache)
 * - HEAD ✓ (comme GET mais sans body, cacheable)
 * 
 * NON CACHEABLES :
 * - DELETE ✗ (modifie l'état du serveur - suppression)
 * - PUT ✗ (modifie l'état du serveur - mise à jour)
 * - POST ✗ (généralement modifie l'état du serveur - création)
 * 
 * Note: POST peut techniquement être cacheable si des headers de cache explicites sont présents,
 * mais par défaut et en pratique, il n'est pas mis en cache.
 */

/**
 * QUESTION 49 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following Symfony command:
 * 
 * namespace App\Command;
 * 
 * use Symfony\Component\Console\Attribute\AsCommand;
 * use Symfony\Component\Console\Command\Command;
 * use Symfony\Component\Console\Input\InputInterface;
 * use Symfony\Component\Console\Output\OutputInterface;
 * 
 * #[AsCommand(name: 'app:some-command')]
 * class SomeCommand extends Command
 * {
 *     protected function execute(InputInterface $input, OutputInterface $output): int
 *     {
 *         $output->writeln('Hello world');
 *     }
 * }
 * 
 * Question: If all the code of the execute() method is just the $output->writeln('...') shown above, 
 * will this command run successfully when executing it as php bin/console app:some-command in the console terminal?
 * 
 * Options:
 * - Yes, it will print the "Hello world" message.
 * - No, it will throw an exception.
 * 
 * RÉPONSE CORRECTE: No, it will throw an exception.
 * 
 * JUSTIFICATION:
 * La méthode execute() doit retourner un code de statut (int) :
 * - Command::SUCCESS (0) en cas de succès
 * - Command::FAILURE (1) en cas d'échec
 * - Command::INVALID (2) pour une utilisation incorrecte
 * 
 * Dans le code présenté, la méthode execute() ne retourne aucune valeur après writeln().
 * Cela provoquera une TypeError car la signature déclare un retour de type `int`.
 * 
 * Code correct :
 * protected function execute(InputInterface $input, OutputInterface $output): int
 * {
 *     $output->writeln('Hello world');
 *     return Command::SUCCESS;
 * }
 */

/**
 * QUESTION 50 - CHOIX UNIQUE
 * =========================
 * 
 * Question: When running the following command in a default Symfony application, will Symfony always 
 * collect debug information during the command execution?
 * 
 * php bin/console --profile app:my-command
 * 
 * Options:
 * - Yes.
 * - Yes, but only if the command is run in the dev environment.
 * - Yes, but only if the application is running in "debug mode".
 * - No.
 * 
 * RÉPONSE CORRECTE: Yes, but only if the application is running in "debug mode".
 * 
 * JUSTIFICATION:
 * L'option --profile active le profilage de la commande, mais cela nécessite que le Profiler soit actif.
 * Le Profiler Symfony n'est actif que lorsque l'application est en "debug mode" (APP_DEBUG=1).
 * 
 * En environnement de production (APP_DEBUG=0), le Profiler n'est pas chargé, 
 * donc l'option --profile n'aura aucun effet même si elle est utilisée.
 * 
 * En dev (généralement APP_DEBUG=1), le Profiler est actif et --profile fonctionnera.
 * C'est le "debug mode" qui détermine si le profiling est possible, pas juste l'environnement.
 */

/**
 * QUESTION 51 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following command code:
 * 
 * # src/Command/FooCommand.php
 * namespace App\Command;
 * 
 * use Symfony\Component\Console\Attribute\AsCommand;
 * use Symfony\Component\Console\Command\Command;
 * 
 * #[AsCommand(name: 'app:foo')]
 * class FooCommand extends Command
 * {
 *     protected function configure()
 *     {
 *         $this
 *             ->xxx
 *             // ...
 *         ;
 *     }
 * }
 * 
 * Question: Which statement does xxx successfully replace in order to avoid showing this command 
 * in the list of commands displayed when executing php bin/console list?
 * 
 * Options:
 * - setPublic(false)
 * - setHidden(true)
 * - hidden()
 * - show(false)
 * - private()
 * 
 * RÉPONSE CORRECTE: setHidden(true)
 * 
 * JUSTIFICATION:
 * La méthode setHidden(true) permet de masquer une commande de la liste affichée par `php bin/console list`.
 * La commande reste exécutable directement (php bin/console app:foo), mais elle n'apparaît pas dans la liste.
 * 
 * C'est utile pour les commandes internes ou de maintenance qui ne doivent pas être visibles aux utilisateurs.
 * 
 * Les autres options n'existent pas :
 * - setPublic(false) : n'existe pas
 * - hidden() : n'existe pas (c'est setHidden())
 * - show(false) : n'existe pas
 * - private() : n'existe pas
 * 
 * Note: Depuis Symfony 6.1, on peut aussi utiliser l'attribut #[AsCommand(name: 'app:foo', hidden: true)]
 */

/**
 * QUESTION 52 - RÉPONSES MULTIPLES
 * =================================
 * 
 * Consider the code of the following Symfony console command:
 * 
 * // ...
 * use Symfony\Component\Console\Attribute\AsCommand;
 * use Symfony\Component\Console\Input\InputArgument;
 * use Symfony\Component\Console\Input\InputOption;
 * 
 * #[AsCommand(name: 'app:greet', description: 'Greet someone')]
 * class GreetCommand extends Command
 * {
 *     protected function configure()
 *     {
 *         $this
 *             ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
 *             ->addOption('yell', null, InputOption::VALUE_NONE, 'Yell in uppercase letters')
 *         ;
 *     }
 * 
 *     // ...
 * }
 * 
 * Question: Which of the following will execute the command correctly?
 * 
 * Options:
 * - php bin/console app:greet --yell=yes
 * - php bin/console app:greet --yell --name="Jane Smith"
 * - php bin/console app:greet --yell
 * - php bin/console app:greet "Jane Smith" --yell
 * - php bin/console app:greet "Jane Smith"
 * 
 * RÉPONSES CORRECTES: php bin/console app:greet --yell, php bin/console app:greet "Jane Smith" --yell, php bin/console app:greet "Jane Smith"
 * 
 * JUSTIFICATIONS:
 * 
 * ✓ php bin/console app:greet --yell
 *   Correct - L'option --yell est de type VALUE_NONE (pas de valeur), l'argument 'name' est OPTIONAL.
 * 
 * ✓ php bin/console app:greet "Jane Smith" --yell
 *   Correct - L'argument positionnel "Jane Smith" suivi de l'option --yell est valide.
 * 
 * ✓ php bin/console app:greet "Jane Smith"
 *   Correct - Seulement l'argument, sans l'option --yell (qui est optionnelle).
 * 
 * ✗ php bin/console app:greet --yell=yes
 *   Incorrect - L'option --yell est de type VALUE_NONE, elle ne doit pas avoir de valeur (=yes).
 * 
 * ✗ php bin/console app:greet --yell --name="Jane Smith"
 *   Incorrect - 'name' est un ARGUMENT, pas une OPTION. On ne peut pas utiliser --name="...".
 *   Les arguments se passent en position, pas avec --.
 * 
 * Note importante :
 * - Arguments : positionnels (ex: "Jane Smith")
 * - Options : avec -- ou - (ex: --yell, --verbose)
 * - InputOption::VALUE_NONE signifie que l'option est un flag booléen (présent/absent), sans valeur.
 */

/**
 * QUESTION 53 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following built-in security roles/attributes would you use to check 
 * if the user is an anonymous user browsing your web site?
 * 
 * Options:
 * - IS_ANONYMOUSLY_AUTHENTICATED
 * - IS_ANONYMOUS
 * - IS_NOT_AUTHENTICATED
 * - ROLE_ANONYMOUS
 * - None. Anonymous users don't have any roles.
 * 
 * RÉPONSE CORRECTE: None. Anonymous users don't have any roles.
 * 
 * JUSTIFICATION:
 * Dans Symfony 5.1+, le concept d'utilisateur anonyme a changé avec l'introduction de l'authenticator system.
 * Les utilisateurs anonymes n'ont pas de rôles spécifiques comme ROLE_ANONYMOUS ou IS_ANONYMOUS.
 * 
 * Pour vérifier si un utilisateur est anonyme (non authentifié), on utilise plutôt :
 * - $this->isGranted('IS_AUTHENTICATED_FULLY') retourne false pour les utilisateurs non authentifiés
 * - $this->getUser() === null indique qu'aucun utilisateur n'est connecté
 * - IS_AUTHENTICATED_REMEMBERED ou IS_AUTHENTICATED_FULLY pour vérifier l'authentification
 * 
 * Historique :
 * - Avant Symfony 5.1, IS_AUTHENTICATED_ANONYMOUSLY existait mais a été déprécié
 * - Le système a évolué pour simplifier la gestion des utilisateurs non authentifiés
 * - Les rôles comme ROLE_ANONYMOUS ou IS_ANONYMOUS n'ont jamais existé dans le système standard
 * 
 * Pour vérifier si un utilisateur n'est PAS authentifié :
 * if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
 *     // Utilisateur anonyme/non authentifié
 * }
 */

/**
 * QUESTION 54 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following configuration applied to a default Symfony application:
 * 
 * # config/packages/framework.yaml
 * framework:
 *     # ...
 *     trusted_proxies: '192.0.0.1,10.0.0.0/8'
 *     trusted_headers: ['x-forwarded-for', 'x-forwarded-host', 'x-forwarded-proto', 'x-forwarded-port', 'x-forwarded-prefix']
 * 
 * Question: How will this configuration change the application behavior?
 * 
 * Options:
 * - ESI caching will only work for reverse proxies located at those IP addresses.
 * - Symfony will balance HTTP Caching between those proxies using a round-robin scheduling algorithm.
 * - The content of the x-forwarded-* headers (if any) will only be trusted for proxies located at those IP addresses.
 * - Authentication with X-509 certificates will only work for requests originating at those IP addresses.
 * - None of the above.
 * 
 * RÉPONSE CORRECTE: The content of the x-forwarded-* headers (if any) will only be trusted for proxies located at those IP addresses.
 * 
 * JUSTIFICATION:
 * La configuration trusted_proxies et trusted_headers définit quels proxies sont de confiance et quels headers ils peuvent envoyer.
 * 
 * Fonctionnement :
 * - trusted_proxies : liste des adresses IP des proxies de confiance (reverse proxies, load balancers)
 * - trusted_headers : liste des headers HTTP que ces proxies sont autorisés à envoyer
 * 
 * Pourquoi c'est important :
 * Les headers X-Forwarded-* contiennent des informations sensibles :
 * - X-Forwarded-For : l'IP réelle du client
 * - X-Forwarded-Host : le nom d'hôte original
 * - X-Forwarded-Proto : le protocole (http/https)
 * - X-Forwarded-Port : le port original
 * - X-Forwarded-Prefix : le préfixe de chemin
 * 
 * Sécurité :
 * Sans cette configuration, un attaquant pourrait envoyer de faux headers X-Forwarded-* 
 * pour usurper son IP, bypasser des restrictions, ou manipuler le comportement de l'application.
 * 
 * Avec trusted_proxies, Symfony ne fait confiance à ces headers QUE si la requête 
 * provient d'une IP listée dans trusted_proxies.
 * 
 * Les autres options sont incorrectes :
 * - ESI caching : non lié à trusted_proxies
 * - Load balancing : Symfony ne fait pas de load balancing
 * - X-509 certificates : non lié à cette configuration
 */

/**
 * QUESTION 55 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following controller code:
 * 
 * # src/Controller/AdminController.php
 * namespace App\Controller\AdminController;
 * 
 * use App\Persistence\Comment;
 * use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 * use Symfony\Component\Security\Core\Exception\AccessDeniedException;
 * 
 * class AdminController extends AbstractController
 * {
 *     public function editComment(Comment $comment)
 *     {
 *         ???
 * 
 *         // ...
 *     }
 * }
 * 
 * Question: Which statement does ??? successfully replace in order to throw an AccessDeniedException exception 
 * if the current authenticated user has not been granted the EDIT_COMMENT permission on the $comment object?
 * 
 * Options:
 * - $this->throwAccessDeniedExceptionUnless('EDIT_COMMENT', $comment);
 * - $this->grantIf('EDIT_COMMENT', $comment);
 * - $this->disallowIfNotGranted('EDIT_COMMENT', $comment);
 * - $this->denyAccessUnlessGranted('EDIT_COMMENT', $comment);
 * - $this->forbidAccessIfNotGranted('EDIT_COMMENT', $comment);
 * 
 * RÉPONSE CORRECTE: $this->denyAccessUnlessGranted('EDIT_COMMENT', $comment);
 * 
 * JUSTIFICATION:
 * La méthode denyAccessUnlessGranted() est la méthode standard dans AbstractController pour vérifier les permissions.
 * 
 * Fonctionnement :
 * - Vérifie si l'utilisateur actuel a la permission 'EDIT_COMMENT' sur l'objet $comment
 * - Si la permission est refusée, lance une AccessDeniedException automatiquement
 * - Si la permission est accordée, l'exécution continue normalement
 * 
 * Syntaxe :
 * $this->denyAccessUnlessGranted(mixed $attribute, mixed $subject = null, string $message = 'Access Denied.')
 * 
 * Exemples d'utilisation :
 * - $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Vérifier un rôle
 * - $this->denyAccessUnlessGranted('EDIT', $post); // Vérifier une permission sur un objet
 * - $this->denyAccessUnlessGranted('VIEW', $comment, 'You cannot view this comment'); // Avec message personnalisé
 * 
 * Les autres options n'existent pas dans AbstractController :
 * - throwAccessDeniedExceptionUnless() : n'existe pas
 * - grantIf() : n'existe pas
 * - disallowIfNotGranted() : n'existe pas
 * - forbidAccessIfNotGranted() : n'existe pas
 * 
 * Note : Pour vérifier une permission sans lancer d'exception, utiliser :
 * if ($this->isGranted('EDIT_COMMENT', $comment)) {
 *     // Permission accordée
 * }
 */

/**
 * QUESTION 56 - RÉPONSES MULTIPLES
 * =================================
 * 
 * Question: Which of the following features are provided by Symfony's access_control security mechanism?
 * 
 * Options:
 * - Restrict access by role, ensuring that the user has the required roles to access the resource.
 * - Restrict access by the type of security certificate used to access the site.
 * - Restrict access by location, ensuring that the user accesses from an allowed country or region.
 * - Restrict access by user IP address.
 * - Restrict access for requests not using HTTPS.
 * 
 * RÉPONSES CORRECTES: 
 * - Restrict access by role, ensuring that the user has the required roles to access the resource.
 * - Restrict access by user IP address.
 * - Restrict access for requests not using HTTPS.
 * 
 * JUSTIFICATIONS:
 * 
 * Le mécanisme access_control dans security.yaml permet de configurer des règles d'accès basées sur :
 * 
 * ✓ Rôles (roles) :
 *   access_control:
 *     - { path: ^/admin, roles: ROLE_ADMIN }
 * 
 * ✓ IP address :
 *   access_control:
 *     - { path: ^/admin, ip: 127.0.0.1 }
 *     - { path: ^/api, ips: [192.168.1.0/24, 10.0.0.0/8] }
 * 
 * ✓ HTTPS requirement :
 *   access_control:
 *     - { path: ^/secure, requires_channel: https }
 * 
 * ✗ Type de certificat de sécurité :
 *   Cette fonctionnalité n'est pas gérée par access_control.
 *   L'authentification par certificat X.509 se configure via les firewalls, pas access_control.
 * 
 * ✗ Restriction géographique (pays/région) :
 *   access_control ne fournit pas de géolocalisation native.
 *   Cela nécessiterait une implémentation personnalisée avec un voter ou un event listener.
 * 
 * Exemple complet de configuration access_control :
 * security:
 *     access_control:
 *         - { path: ^/login, roles: PUBLIC_ACCESS }
 *         - { path: ^/admin, roles: ROLE_ADMIN, ip: 127.0.0.1 }
 *         - { path: ^/payment, requires_channel: https }
 *         - { path: ^/api, roles: ROLE_API_USER, ips: [192.168.1.0/24] }
 */

/**
 * QUESTION 57 - RÉPONSES MULTIPLES
 * =================================
 * 
 * Question: Which of the following information can you retrieve thanks to the 
 * Symfony\Component\Security\Http\Authentication\AuthenticationUtils service?
 * 
 * Options:
 * - The current authenticated user.
 * - The last username tried on the last unsuccessful authentication attempt.
 * - The last five user password hashes and the last authentication error exception.
 * - The list of the current authenticated user's granted roles.
 * - The last authentication error exception.
 * 
 * RÉPONSES CORRECTES:
 * - The last username tried on the last unsuccessful authentication attempt.
 * - The last authentication error exception.
 * 
 * JUSTIFICATIONS:
 * 
 * Le service AuthenticationUtils fournit des utilitaires pour gérer les échecs d'authentification.
 * 
 * ✓ getLastUsername() :
 *   Retourne le dernier nom d'utilisateur saisi lors d'une tentative d'authentification échouée.
 *   Utile pour pré-remplir le formulaire de connexion après un échec.
 * 
 * ✓ getLastAuthenticationError() :
 *   Retourne l'exception d'authentification de la dernière tentative échouée.
 *   Permet d'afficher un message d'erreur approprié à l'utilisateur.
 * 
 * Exemple d'utilisation dans un contrôleur de login :
 * public function login(AuthenticationUtils $authenticationUtils): Response
 * {
 *     $error = $authenticationUtils->getLastAuthenticationError();
 *     $lastUsername = $authenticationUtils->getLastUsername();
 * 
 *     return $this->render('security/login.html.twig', [
 *         'last_username' => $lastUsername,
 *         'error' => $error,
 *     ]);
 * }
 * 
 * ✗ L'utilisateur actuellement authentifié :
 *   Pour obtenir l'utilisateur actuel, on utilise $this->getUser() dans un contrôleur
 *   ou on injecte Security ou TokenStorageInterface.
 * 
 * ✗ Les hashs de mots de passe :
 *   AuthenticationUtils ne stocke ni ne fournit les hashs de mots de passe pour des raisons de sécurité.
 * 
 * ✗ La liste des rôles de l'utilisateur :
 *   Les rôles sont obtenus via $user->getRoles() ou $this->isGranted().
 */

/**
 * QUESTION 58 - CHOIX UNIQUE
 * =========================
 * 
 * Question: The crawler object used in functional tests only works when the response is an XML or an HTML document.
 * 
 * Options:
 * - True
 * - False
 * 
 * RÉPONSE CORRECTE: True
 * 
 * JUSTIFICATION:
 * Le Crawler de Symfony utilise DomCrawler component qui parse du XML ou du HTML.
 * 
 * Le Crawler fonctionne pour :
 * ✓ HTML (text/html)
 * ✓ XML (text/xml, application/xml)
 * 
 * Le Crawler ne fonctionne PAS pour :
 * ✗ JSON (application/json)
 * ✗ Texte brut (text/plain)
 * ✗ Binaires (images, PDFs, etc.)
 * 
 * Pour les réponses JSON, utiliser plutôt :
 * $response = $client->getResponse();
 * $data = json_decode($response->getContent(), true);
 * 
 * Exemple avec le Crawler (HTML/XML) :
 * $crawler = $client->request('GET', '/');
 * $this->assertSelectorTextContains('h1', 'Welcome');
 * $link = $crawler->selectLink('Click here')->link();
 * 
 * Le Crawler fournit des méthodes comme :
 * - filter() : sélecteur CSS
 * - selectLink() : sélectionner un lien
 * - selectButton() : sélectionner un bouton
 * - form() : extraire un formulaire
 * 
 * Ces méthodes nécessitent une structure DOM (HTML/XML) pour fonctionner.
 */

/**
 * QUESTION 59 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following functional test code:
 * 
 * $client = static::createClient();
 * $client->enableProfiler();
 * $client->request('GET', '/');
 * 
 * $token = $client->getProfile()->getToken();
 * 
 * Question: Which value is held by the $token variable in this functional test?
 * 
 * Options:
 * - The current security authentication token (ie: AnonymousToken, UsernamePasswordToken etc.).
 * - The CSRF token if there is any form in the page, null otherwise.
 * - The value of the secret configuration option (set in APP_SECRET env var).
 * - A string that uniquely identifies each performed request inside the Symfony Profile and Web Debug Toolbar.
 * 
 * RÉPONSE CORRECTE: A string that uniquely identifies each performed request inside the Symfony Profile and Web Debug Toolbar.
 * 
 * JUSTIFICATION:
 * Dans le contexte du Profiler Symfony, getToken() retourne un identifiant unique pour la requête profilée.
 * 
 * Ce token est :
 * - Une chaîne unique générée pour chaque requête
 * - Utilisé pour identifier le profil dans la Web Debug Toolbar
 * - Permet d'accéder au profil via l'URL : /_profiler/{token}
 * 
 * Exemple d'utilisation complète :
 * $client = static::createClient();
 * $client->enableProfiler();
 * $crawler = $client->request('GET', '/');
 * 
 * $profile = $client->getProfile();
 * $token = $profile->getToken();
 * 
 * // Accéder aux collecteurs de données
 * $dbCollector = $profile->getCollector('db');
 * $requestCollector = $profile->getCollector('request');
 * 
 * // Nombre de requêtes SQL
 * $queryCount = $dbCollector->getQueryCount();
 * 
 * // URL du profiler
 * $profilerUrl = '/_profiler/' . $token;
 * 
 * Ce token N'EST PAS :
 * ✗ Un token de sécurité (authentification) - pour cela, utiliser $client->getContainer()->get('security.token_storage')->getToken()
 * ✗ Un token CSRF - pour cela, utiliser $client->getContainer()->get('security.csrf.token_manager')
 * ✗ Le APP_SECRET - c'est une variable d'environnement, pas un token de profil
 */

/**
 * QUESTION 60 - CHOIX UNIQUE
 * =========================
 * 
 * Supposing that the /assets/style.css URL is generated by the Symfony application 
 * and serves the following CSS code:
 * 
 * body { font-family: sans-serif; }
 * p { font-size: 16px; color: #000; }
 * 
 * Question: Will the following test pass?
 * 
 * namespace App\Tests\Controller;
 * 
 * use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
 * 
 * class DemoControllerTest extends WebTestCase
 * {
 *     public function testStyles()
 *     {
 *         $client = static::createClient();
 *         $crawler = $client->request('GET', '/assets/style.css');
 * 
 *         $color = $crawler->filter('p')->attr('color');
 *         $this->assertEquals('#000', $color);
 *     }
 * }
 * 
 * Options:
 * - No, because the test execution results in an error.
 * - No. The variable $color will be null because the filter should be $crawler->filter('p')->getNode(0)->attr('color');
 * - Yes, because the value of $color variable will be #000.
 * - Yes, if there is an Internet connection.
 * 
 * RÉPONSE CORRECTE: No, because the test execution results in an error.
 * 
 * JUSTIFICATION:
 * Le Crawler Symfony (DomCrawler) est conçu pour parser du HTML ou XML, pas du CSS pur.
 * 
 * Pourquoi le test échoue :
 * 1. Le contenu CSS n'est pas un document HTML/XML valide
 * 2. Le Crawler ne peut pas parser le texte CSS comme du DOM
 * 3. filter('p') cherche un élément HTML <p>, pas une règle CSS "p { }"
 * 4. Même si on pouvait filtrer, attr('color') cherche un attribut HTML, pas une propriété CSS
 * 
 * Ce qui se passe :
 * - Le Crawler essaie de parser le CSS comme du HTML
 * - Cela échoue ou retourne un résultat inattendu
 * - filter('p') ne trouve pas d'élément <p> dans le CSS
 * - L'appel à attr('color') provoque une erreur
 * 
 * Solutions correctes pour tester du CSS :
 * 
 * 1. Tester le contenu brut :
 * $response = $client->getResponse();
 * $content = $response->getContent();
 * $this->assertStringContainsString('color: #000', $content);
 * 
 * 2. Parser le CSS avec une bibliothèque dédiée :
 * $cssParser = new CssParser();
 * $rules = $cssParser->parse($response->getContent());
 * 
 * 3. Tester le CSS dans une page HTML :
 * $crawler = $client->request('GET', '/page-with-css');
 * $paragraph = $crawler->filter('p')->first();
 * // Puis vérifier les styles appliqués ou le contenu du <style>
 * 
 * Le Crawler est fait pour :
 * ✓ Naviguer dans le DOM HTML
 * ✓ Sélectionner des éléments avec des sélecteurs CSS
 * ✓ Extraire des attributs HTML
 * ✓ Soumettre des formulaires
 * ✓ Cliquer sur des liens
 * 
 * Le Crawler n'est PAS fait pour :
 * ✗ Parser du CSS
 * ✗ Parser du JSON
 * ✗ Parser du texte brut
 * ✗ Parser des fichiers binaires
 */

/**
 * QUESTION 61 - CHOIX UNIQUE
 * =========================
 * 
 * Question: What is the recommended file path for the functional test of a controller called UserController 
 * in a default Symfony application?
 * 
 * Options:
 * - src/Tests/Functional/UserControllerTest.php
 * - %kernel.tests_dir%/Controllers/UserController.php
 * - test/Security/Controller/UserTest.php
 * - tests/Controller/UserControllerTest.php
 * - src/Controller/Tests/UserController.php
 * 
 * RÉPONSE CORRECTE: tests/Controller/UserControllerTest.php
 * 
 * JUSTIFICATION:
 * Dans une application Symfony standard, la structure recommandée pour les tests est :
 * 
 * Structure standard :
 * tests/
 *   ├── Controller/         # Tests des contrôleurs
 *   ├── Entity/            # Tests des entités
 *   ├── Repository/        # Tests des repositories
 *   ├── Service/           # Tests des services
 *   └── Unit/              # Tests unitaires généraux
 * 
 * Pour un contrôleur UserController situé dans src/Controller/UserController.php,
 * le test fonctionnel devrait être : tests/Controller/UserControllerTest.php
 * 
 * Convention de nommage :
 * - Le fichier de test porte le nom de la classe testée + "Test"
 * - UserController → UserControllerTest
 * - La structure de répertoires sous tests/ reflète celle sous src/
 * 
 * Les autres options sont incorrectes :
 * ✗ src/Tests/... : Les tests ne vont pas dans src/, mais dans tests/
 * ✗ %kernel.tests_dir% : Ce n'est pas un paramètre Symfony standard
 * ✗ test/ (singulier) : Le répertoire correct est tests/ (pluriel)
 * ✗ src/Controller/Tests/ : Les tests ne doivent pas être mélangés avec le code source
 * 
 * Configuration dans composer.json :
 * {
 *     "autoload-dev": {
 *         "psr-4": {
 *             "App\\Tests\\": "tests/"
 *         }
 *     }
 * }
 */

/**
 * QUESTION 62 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following functional test snippet:
 * 
 * $client1 = static::createClient();
 * $client2 = static::createClient(['environment' => 'prod']);
 * 
 * $client1->insulate();
 * $client2->insulate();
 * 
 * Question: In which environment does each HTTP client run?
 * 
 * Options:
 * - You cannot create two different clients in the same test.
 * - Both clients will run in the test environment.
 * - Both clients will run in the prod environment.
 * - $client1 will run in the test environment and $client2 will run in the prod environment.
 * - None of the above answers is correct.
 * 
 * RÉPONSE CORRECTE: $client1 will run in the test environment and $client2 will run in the prod environment.
 * 
 * JUSTIFICATION:
 * Chaque client peut être configuré pour s'exécuter dans un environnement spécifique.
 * 
 * Analyse du code :
 * - $client1 = static::createClient(); 
 *   → Crée un client dans l'environnement par défaut des tests : 'test'
 * 
 * - $client2 = static::createClient(['environment' => 'prod']);
 *   → Crée un client explicitement dans l'environnement 'prod'
 * 
 * La méthode insulate() :
 * - Isole chaque requête dans un processus PHP séparé
 * - Permet d'éviter les effets de bord entre les requêtes
 * - Utile pour tester des changements d'état ou de configuration
 * - N'affecte PAS l'environnement, juste l'isolation des requêtes
 * 
 * Cas d'usage typiques :
 * 
 * 1. Tester avec différentes configurations :
 * $clientDev = static::createClient(['environment' => 'dev']);
 * $clientProd = static::createClient(['environment' => 'prod']);
 * 
 * 2. Tester avec différents paramètres :
 * $client1 = static::createClient(['debug' => true]);
 * $client2 = static::createClient(['debug' => false]);
 * 
 * 3. Utiliser insulate() pour des tests d'isolation :
 * $client->insulate();
 * $client->request('GET', '/page1'); // Processus séparé
 * $client->request('GET', '/page2'); // Nouveau processus séparé
 * 
 * Note: On peut créer autant de clients que nécessaire dans un même test.
 */

/**
 * QUESTION 63 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following code snippet that uses Symfony's HttpClient to make 30 HTTP requests to the same web site:
 * 
 * use Symfony\Component\HttpClient\CurlHttpClient;
 * 
 * $client = new CurlHttpClient();
 * $responses = [];
 * 
 * for ($i = 0; $i < 30; ++$i) {
 *     $uri = "https://http2.akamai.com/demo/tile-$i.png";
 *     $responses[] = $client->request('GET', $uri);
 * }
 * 
 * Question: How will these requests be performed by the application?
 * 
 * Options:
 * - Sequentially (each request waits until the previous one is finished).
 * - In parallel, all the 30 requests at the same time.
 * 
 * RÉPONSE CORRECTE: In parallel, all the 30 requests at the same time.
 * 
 * JUSTIFICATION:
 * Le HttpClient de Symfony effectue les requêtes de manière asynchrone et parallèle par défaut.
 * 
 * Fonctionnement :
 * - $client->request() retourne immédiatement un objet ResponseInterface
 * - La requête réelle est lancée mais ne bloque pas l'exécution
 * - Les 30 requêtes sont donc toutes lancées rapidement
 * - Elles s'exécutent en parallèle via HTTP/2 multiplexing ou connexions multiples
 * 
 * Quand le contenu est-il récupéré ?
 * La récupération réelle des données se fait quand on appelle :
 * - $response->getContent() : Bloque jusqu'à ce que la réponse soit complète
 * - $response->toArray() : Bloque et parse le JSON
 * - $response->getStatusCode() : Peut bloquer brièvement
 * 
 * Exemple d'utilisation :
 * // Lancer toutes les requêtes (non-bloquant)
 * $responses = [];
 * for ($i = 0; $i < 30; ++$i) {
 *     $responses[] = $client->request('GET', $uri);
 * }
 * 
 * // Récupérer les contenus (bloquant)
 * foreach ($responses as $response) {
 *     $content = $response->getContent(); // Attend la réponse si pas encore reçue
 * }
 * 
 * Optimisations HTTP/2 :
 * - Si le serveur supporte HTTP/2, une seule connexion TCP est utilisée
 * - Les 30 requêtes sont multiplexées sur cette connexion
 * - Très efficace pour les ressources du même domaine
 * 
 * Pour forcer un comportement séquentiel :
 * for ($i = 0; $i < 30; ++$i) {
 *     $response = $client->request('GET', $uri);
 *     $content = $response->getContent(); // Bloque ici
 * }
 */

/**
 * QUESTION 64 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following code snippet related to the Symfony Filesystem component:
 * 
 * use Symfony\Component\Filesystem\Filesystem;
 * 
 * $filesystem = new Filesystem();
 * $filesystem->copy('/path/to/dir1', '/path/to/dir2');
 * 
 * Question: If dir1 exists and Symfony has permission to create dir2, will this code copy 
 * the contents of dir1 into dir2?
 * 
 * Options:
 * - Yes.
 * - No, because when copying directories, you need to pass true as the third argument of copy().
 * - No, because copy() does not copy directories, only files.
 * 
 * RÉPONSE CORRECTE: No, because copy() does not copy directories, only files.
 * 
 * JUSTIFICATION:
 * La méthode copy() du composant Filesystem de Symfony ne copie QUE des fichiers, pas des répertoires.
 * 
 * Signature de copy() :
 * copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = false): void
 * 
 * Comportement :
 * - copy() est conçu pour copier un fichier unique
 * - Si vous passez un répertoire, une exception sera levée
 * - Il n'y a pas de paramètre pour copier récursivement
 * 
 * Pour copier un répertoire entier, utiliser mirror() :
 * $filesystem->mirror('/path/to/source', '/path/to/destination');
 * 
 * Différences entre copy() et mirror() :
 * 
 * copy() :
 * - Copie UN fichier
 * - $filesystem->copy('/path/file1.txt', '/path/file2.txt');
 * - Paramètre $overwriteNewerFiles pour contrôler l'écrasement
 * 
 * mirror() :
 * - Copie TOUT un répertoire récursivement
 * - $filesystem->mirror('/source', '/destination');
 * - Crée la structure de répertoires
 * - Copie tous les fichiers et sous-répertoires
 * 
 * Autres méthodes utiles du Filesystem :
 * - mkdir() : Créer un répertoire
 * - remove() : Supprimer un fichier ou répertoire
 * - exists() : Vérifier l'existence
 * - chmod() : Changer les permissions
 * - touch() : Créer ou modifier la date d'un fichier
 * - symlink() : Créer un lien symbolique
 * - rename() : Renommer/déplacer
 * 
 * Exemple complet pour copier un répertoire :
 * $filesystem = new Filesystem();
 * 
 * // Copier tout le répertoire
 * $filesystem->mirror('/source', '/destination');
 * 
 * // Options avec callback pour filtrer
 * $filesystem->mirror('/source', '/destination', null, [
 *     'override' => true,
 *     'delete' => true
 * ]);
 */

/**
 * QUESTION 65 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which one of the following directories doesn't belong to a Symfony 7 application 
 * using the default directory structure?
 * 
 * Options:
 * - app/
 * - config/
 * - public/
 * - src/
 * - vendor/
 * 
 * RÉPONSE CORRECTE: app/
 * 
 * JUSTIFICATION:
 * Le répertoire app/ n'existe plus dans Symfony 4+ (incluant Symfony 7).
 * 
 * Structure Symfony 7 (et 4+) :
 * project/
 *   ├── bin/              # Scripts exécutables (console)
 *   ├── config/           # Fichiers de configuration
 *   ├── migrations/       # Migrations de base de données
 *   ├── public/           # Point d'entrée web (index.php)
 *   ├── src/              # Code source de l'application
 *   ├── templates/        # Templates Twig
 *   ├── tests/            # Tests
 *   ├── translations/     # Fichiers de traduction
 *   ├── var/              # Fichiers générés (cache, logs)
 *   └── vendor/           # Dépendances Composer
 * 
 * Historique du répertoire app/ :
 * - Symfony 2 et 3 utilisaient app/ pour :
 *   - app/config/ : Configuration
 *   - app/Resources/ : Templates et assets
 *   - app/AppKernel.php : Noyau de l'application
 * 
 * - Symfony 4+ a simplifié :
 *   - config/ remplace app/config/
 *   - templates/ remplace app/Resources/views/
 *   - src/Kernel.php remplace app/AppKernel.php
 * 
 * Migration Symfony 3 → 4+ :
 * Ancien (Symfony 3)          → Nouveau (Symfony 4+)
 * app/config/                 → config/
 * app/Resources/views/        → templates/
 * app/AppKernel.php           → src/Kernel.php
 * web/                        → public/
 * 
 * Avantages de la nouvelle structure :
 * - Plus simple et intuitive
 * - Moins de niveaux de répertoires
 * - Cohérent avec les standards modernes PHP
 * - Facilite l'onboarding des nouveaux développeurs
 */

/**
 * QUESTION 66 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following methods should you use to check if some lock 
 * (stored in the $lock variable) has already been acquired by some process?
 * 
 * Options:
 * - if ($lock->isAcquired()) { echo "Was acquired by someone else"; }
 * - if ( ! $lock->acquire()) { echo "Was acquired by someone else"; }
 * 
 * RÉPONSE CORRECTE: if ( ! $lock->acquire()) { echo "Was acquired by someone else"; }
 * 
 * JUSTIFICATION:
 * Le composant Lock de Symfony n'a pas de méthode isAcquired().
 * La manière correcte de vérifier et d'acquérir un lock est d'utiliser acquire().
 * 
 * Fonctionnement de acquire() :
 * - $lock->acquire() : Tente d'acquérir le lock
 * - Retourne true si le lock a été acquis avec succès
 * - Retourne false si le lock est déjà détenu par un autre processus
 * - Par défaut, bloque jusqu'à l'acquisition (comportement configurable)
 * 
 * Utilisation correcte :
 * use Symfony\Component\Lock\LockFactory;
 * use Symfony\Component\Lock\Store\SemaphoreStore;
 * 
 * $store = new SemaphoreStore();
 * $factory = new LockFactory($store);
 * $lock = $factory->createLock('my-lock');
 * 
 * // Tentative non-bloquante
 * if ($lock->acquire(false)) {
 *     echo "Lock acquired, doing work...";
 *     // Faire le travail
 *     $lock->release();
 * } else {
 *     echo "Lock already acquired by someone else";
 * }
 * 
 * Méthodes disponibles sur Lock :
 * - acquire(bool $blocking = true) : Acquérir le lock
 * - refresh() : Rafraîchir le TTL du lock
 * - release() : Libérer le lock
 * - isAcquired() : N'EXISTE PAS dans l'API Symfony Lock
 * 
 * Différents modes d'acquisition :
 * 
 * 1. Bloquant (par défaut) :
 * $lock->acquire(); // Attend jusqu'à l'acquisition
 * 
 * 2. Non-bloquant :
 * if (!$lock->acquire(false)) {
 *     echo "Can't acquire lock";
 *     return;
 * }
 * 
 * 3. Avec timeout :
 * $lock = $factory->createLock('my-lock', 300); // TTL de 300 secondes
 * $lock->acquire();
 * 
 * Stores disponibles :
 * - SemaphoreStore : Utilise les sémaphores PHP
 * - FlockStore : Utilise le file locking
 * - RedisStore : Utilise Redis
 * - MemcachedStore : Utilise Memcached
 * - PdoStore : Utilise une base de données
 * - InMemoryStore : Pour les tests
 */

/**
 * QUESTION 67 - CHOIX UNIQUE
 * =========================
 * 
 * In your computer, the two following files are part of your application:
 * 
 * # .env
 * APP_ENV=prod
 * 
 * # .env.local
 * APP_ENV=dev
 * 
 * Question: If the application doesn't define its execution environment in any other way, 
 * in which environment will the application run?
 * 
 * Options:
 * - prod
 * - dev
 * 
 * RÉPONSE CORRECTE: dev
 * 
 * JUSTIFICATION:
 * Symfony charge les fichiers d'environnement dans un ordre spécifique, 
 * et .env.local a la priorité sur .env.
 * 
 * Ordre de chargement des fichiers .env (du moins prioritaire au plus prioritaire) :
 * 1. .env                    # Configuration par défaut (committée dans git)
 * 2. .env.local              # Overrides locaux (ignoré par git)
 * 3. .env.{APP_ENV}          # Configuration spécifique à l'environnement
 * 4. .env.{APP_ENV}.local    # Overrides locaux spécifiques à l'environnement
 * 
 * Dans ce cas :
 * 1. .env définit APP_ENV=prod
 * 2. .env.local définit APP_ENV=dev et OVERRIDE la valeur de .env
 * 3. Résultat final : APP_ENV=dev
 * 
 * Cas d'usage typiques :
 * 
 * .env (commité) :
 * APP_ENV=prod
 * APP_DEBUG=0
 * DATABASE_URL=mysql://prod_default
 * 
 * .env.local (non commité, machine locale) :
 * APP_ENV=dev
 * APP_DEBUG=1
 * DATABASE_URL=mysql://localhost/my_db
 * 
 * .env.test (commité, pour les tests) :
 * APP_ENV=test
 * DATABASE_URL=mysql://localhost/test_db
 * 
 * .env.prod.local (non commité, serveur de production) :
 * DATABASE_URL=mysql://prod_server/real_db
 * API_KEY=secret_production_key
 * 
 * Priorité complète :
 * Variables d'environnement réelles (système) > .env.{APP_ENV}.local > .env.{APP_ENV} > .env.local > .env
 * 
 * Note importante :
 * Si APP_ENV est défini comme variable d'environnement système, elle a priorité sur TOUS les fichiers .env.
 */

/**
 * QUESTION 68 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following configuration used in a default Symfony application:
 * 
 * # config/packages/twig.yaml
 * twig:
 *     strict_variables: true
 * 
 * ???:
 *     twig:
 *         strict_variables: false
 * 
 * Question: Which statement does ??? successfully replace to make the strict_variables option 
 * to be false in the prod environment?
 * 
 * Options:
 * - config@prod
 * - @prod
 * - prod
 * - when@prod
 * - env('prod')
 * 
 * RÉPONSE CORRECTE: when@prod
 * 
 * JUSTIFICATION:
 * La syntaxe when@{environment} permet de définir des configurations spécifiques à un environnement.
 * 
 * Syntaxe correcte :
 * # config/packages/twig.yaml
 * twig:
 *     strict_variables: true  # Valeur par défaut pour tous les environnements
 * 
 * when@prod:
 *     twig:
 *         strict_variables: false  # Override pour l'environnement prod
 * 
 * when@dev:
 *     twig:
 *         strict_variables: true   # Override pour l'environnement dev
 * 
 * when@test:
 *     twig:
 *         strict_variables: true   # Override pour l'environnement test
 * 
 * Fonctionnement :
 * - La configuration de base s'applique à tous les environnements
 * - when@{env} override la configuration pour un environnement spécifique
 * - Plusieurs when@{env} peuvent coexister dans le même fichier
 * 
 * Avantages de when@ :
 * - Tout dans un seul fichier au lieu de multiples fichiers
 * - Plus lisible et maintenable
 * - Évite la duplication de configuration
 * 
 * Ancienne méthode (avant Symfony 5.3) :
 * Créer des fichiers séparés :
 * - config/packages/twig.yaml (configuration par défaut)
 * - config/packages/prod/twig.yaml (override pour prod)
 * - config/packages/dev/twig.yaml (override pour dev)
 * 
 * Nouvelle méthode (Symfony 5.3+) :
 * Tout dans config/packages/twig.yaml avec when@
 * 
 * Les autres syntaxes proposées n'existent pas :
 * ✗ config@prod : N'existe pas
 * ✗ @prod : N'existe pas
 * ✗ prod : Seul, ce n'est pas une syntaxe valide
 * ✗ env('prod') : Ce n'est pas pour la configuration d'environnement
 * 
 * Exemple complet :
 * # config/packages/framework.yaml
 * framework:
 *     secret: '%env(APP_SECRET)%'
 *     router:
 *         utf8: true
 * 
 * when@dev:
 *     framework:
 *         router:
 *             strict_requirements: true
 * 
 * when@prod:
 *     framework:
 *         router:
 *             strict_requirements: null
 */

/**
 * QUESTION 69 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following cache configuration:
 * 
 * # config/packages/cache.yaml
 * framework:
 *     cache:
 *         pools:
 *             my_cache_pool:
 *                 adapter: cache.adapter.array
 * 
 * Question: In a default Symfony application which uses autowiring, which constructor argument 
 * do you have to use to get the my_cache_pool cache pool injected in your services?
 * 
 * Options:
 * - CacheInterface $myCachePool
 * - MyCachePool $cache
 * - ArrayCacheInterface $myCachePool
 * - CacheInterface $cacheAdapterArray
 * - CachePoolInterface $cacheAdapterArray
 * 
 * RÉPONSE CORRECTE: CacheInterface $myCachePool
 * 
 * JUSTIFICATION:
 * Avec l'autowiring Symfony, les pools de cache sont injectés via le type-hint + le nom du paramètre.
 * 
 * Convention de nommage :
 * - Le nom du pool en snake_case : my_cache_pool
 * - Se transforme en camelCase pour le paramètre : $myCachePool
 * - Le type-hint doit être CacheInterface (de Psr\Cache ou Symfony\Contracts\Cache)
 * 
 * Exemple d'injection :
 * use Symfony\Contracts\Cache\CacheInterface;
 * 
 * class MyService
 * {
 *     public function __construct(
 *         private CacheInterface $myCachePool  // Injecte my_cache_pool
 *     ) {}
 * }
 * 
 * Autres pools :
 * # config/packages/cache.yaml
 * framework:
 *     cache:
 *         pools:
 *             my_cache_pool:
 *                 adapter: cache.adapter.array
 *             api_cache:
 *                 adapter: cache.adapter.redis
 *             file_cache:
 *                 adapter: cache.adapter.filesystem
 * 
 * Injection correspondante :
 * public function __construct(
 *     CacheInterface $myCachePool,    // Injecte my_cache_pool
 *     CacheInterface $apiCache,       // Injecte api_cache
 *     CacheInterface $fileCache,      // Injecte file_cache
 * ) {}
 * 
 * Alternative avec l'attribut #[Autowire] :
 * use Symfony\Component\DependencyInjection\Attribute\Autowire;
 * 
 * public function __construct(
 *     #[Autowire(service: 'my_cache_pool')]
 *     private CacheInterface $cache
 * ) {}
 * 
 * Utilisation du cache :
 * $value = $this->myCachePool->get('my_key', function (ItemInterface $item) {
 *     $item->expiresAfter(3600);
 *     return 'computed value';
 * });
 * 
 * Les autres options sont incorrectes :
 * ✗ MyCachePool : Ce n'est pas une classe/interface existante
 * ✗ ArrayCacheInterface : Ce type n'existe pas
 * ✗ $cacheAdapterArray : Le nom ne correspond pas au pool my_cache_pool
 * ✗ CachePoolInterface : On utilise CacheInterface, pas CachePoolInterface pour l'autowiring
 */

/**
 * QUESTION 70 - RÉPONSES MULTIPLES
 * =================================
 * 
 * Question: Which of the following templates are valid for customizing 404 error pages 
 * in a Symfony web application that has installed and configured Twig?
 * 
 * Options:
 * - templates/Resources/TwigBundle/views/error/404.html.twig
 * - templates/bundles/TwigBundle/Exception/error404.html.twig
 * - templates/bundles/TwigBundle/Exception/error.html.twig
 * - bundles/TwigBundle/Exception/404.html.twig
 * - templates/TwigBundle/error.404.twig
 * 
 * RÉPONSES CORRECTES:
 * - templates/bundles/TwigBundle/Exception/error404.html.twig
 * - templates/bundles/TwigBundle/Exception/error.html.twig
 * 
 * JUSTIFICATION:
 * Symfony permet de personnaliser les pages d'erreur en créant des templates dans templates/bundles/TwigBundle/Exception/
 * 
 * Structure correcte pour les pages d'erreur :
 * templates/
 *   └── bundles/
 *       └── TwigBundle/
 *           └── Exception/
 *               ├── error.html.twig           # Page d'erreur générique
 *               ├── error404.html.twig        # Page 404 spécifique
 *               ├── error403.html.twig        # Page 403 spécifique
 *               ├── error500.html.twig        # Page 500 spécifique
 *               └── errorXXX.html.twig        # Autres codes HTTP
 * 
 * Priorité de résolution :
 * 1. error{code}.html.twig (ex: error404.html.twig) - Le plus spécifique
 * 2. error{code}.{format}.twig (ex: error404.json.twig) - Spécifique au format
 * 3. error.html.twig - Page d'erreur générique (fallback)
 * 
 * Exemples valides :
 * ✓ templates/bundles/TwigBundle/Exception/error404.html.twig
 * ✓ templates/bundles/TwigBundle/Exception/error.html.twig
 * ✓ templates/bundles/TwigBundle/Exception/error500.html.twig
 * ✓ templates/bundles/TwigBundle/Exception/error404.json.twig
 * 
 * Exemples invalides :
 * ✗ templates/Resources/TwigBundle/... : Mauvais chemin
 * ✗ bundles/TwigBundle/... : Doit commencer par templates/
 * ✗ templates/TwigBundle/error.404.twig : Mauvaise structure
 * 
 * Variables disponibles dans les templates d'erreur :
 * - status_code : Code HTTP (404, 500, etc.)
 * - status_text : Texte du statut (Not Found, Internal Server Error, etc.)
 * - exception : L'objet exception (uniquement en mode debug)
 * 
 * Exemple de template error404.html.twig :
 * {% extends 'base.html.twig' %}
 * 
 * {% block body %}
 *     <h1>Page not found</h1>
 *     <p>
 *         The requested page couldn't be located. 
 *         Error {{ status_code }}: {{ status_text }}
 *     </p>
 * {% endblock %}
 * 
 * Note pour le développement :
 * En environnement dev (APP_DEBUG=1), les pages d'erreur personnalisées ne sont pas affichées.
 * Pour les tester en dev, visiter : /_error/{code} (ex: /_error/404)
 */

/**
 * QUESTION 71 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following Symfony components is responsible for getting information 
 * about class properties by using different sources of metadata?
 * 
 * Options:
 * - PropertyAccess
 * - Validator
 * - PropertyInfo
 * - Finder
 * - VarDumper
 * 
 * RÉPONSE CORRECTE: PropertyInfo
 * 
 * JUSTIFICATION:
 * Le composant PropertyInfo extrait des informations (métadonnées) sur les propriétés d'une classe.
 * 
 * Fonctionnalités de PropertyInfo :
 * - Détecte les types de propriétés (string, int, array, objets, etc.)
 * - Identifie si une propriété est accessible (readable/writable)
 * - Extrait des informations depuis plusieurs sources :
 *   * Réflexion PHP (type hints)
 *   * PHPDoc (@var, @return)
 *   * Accesseurs (getters/setters)
 *   * Constructeur property promotion
 *   * Attributs PHP 8+
 * 
 * Exemple d'utilisation :
 * use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
 * 
 * $propertyInfo = new PropertyInfoExtractor([...extractors...]);
 * 
 * // Obtenir le type d'une propriété
 * $types = $propertyInfo->getTypes(User::class, 'email');
 * // Retourne : Type[] avec 'string'
 * 
 * // Vérifier si une propriété est accessible en lecture
 * $readable = $propertyInfo->isReadable(User::class, 'email');
 * 
 * // Vérifier si une propriété est modifiable
 * $writable = $propertyInfo->isWritable(User::class, 'email');
 * 
 * // Obtenir toutes les propriétés d'une classe
 * $properties = $propertyInfo->getProperties(User::class);
 * 
 * Sources d'extraction (extractors) :
 * - PhpDocExtractor : Lit les annotations @var, @return
 * - ReflectionExtractor : Utilise la réflexion PHP (types déclarés)
 * - SerializerExtractor : Utilise les métadonnées du Serializer
 * - DoctrineExtractor : Utilise les métadonnées Doctrine
 * - PropertyAccessExtractor : Détecte les accesseurs
 * 
 * Cas d'usage :
 * - Serializer : Déterminer comment sérialiser un objet
 * - Validator : Savoir quelles propriétés valider
 * - Formulaires : Deviner le type de champ automatiquement
 * - API Platform : Générer automatiquement la documentation OpenAPI
 * - Normalizers personnalisés
 * 
 * Les autres composants ont des rôles différents :
 * - PropertyAccess : Lit/écrit des valeurs de propriétés via différentes syntaxes
 * - Validator : Valide des données selon des contraintes
 * - Finder : Recherche de fichiers et répertoires
 * - VarDumper : Dump/affichage amélioré de variables pour le debug
 */

/**
 * QUESTION 72 - CHOIX UNIQUE
 * =========================
 * 
 * Question: If two listeners are associated with the same event and they have exactly the same priority, 
 * Symfony only executes the listener which was first defined.
 * 
 * Options:
 * - True
 * - False
 * 
 * RÉPONSE CORRECTE: False
 * 
 * JUSTIFICATION:
 * Quand deux listeners ont exactement la même priorité, Symfony exécute LES DEUX, 
 * dans l'ordre de leur enregistrement.
 * 
 * Comportement correct :
 * - Symfony n'exécute pas "seulement" le premier
 * - Les deux listeners sont exécutés
 * - L'ordre d'exécution est déterminé par l'ordre d'enregistrement
 * - Le premier enregistré s'exécute en premier
 * 
 * Système de priorité :
 * - Priorité plus élevée = exécution plus tôt
 * - Priorité par défaut : 0
 * - Peut être positive ou négative
 * 
 * Exemple :
 * # config/services.yaml
 * services:
 *     App\EventListener\FirstListener:
 *         tags:
 *             - { name: kernel.event_listener, event: kernel.request, priority: 10 }
 * 
 *     App\EventListener\SecondListener:
 *         tags:
 *             - { name: kernel.event_listener, event: kernel.request, priority: 10 }
 * 
 *     App\EventListener\ThirdListener:
 *         tags:
 *             - { name: kernel.event_listener, event: kernel.request, priority: 10 }
 * 
 * Ordre d'exécution :
 * 1. FirstListener (priorité 10, enregistré en premier)
 * 2. SecondListener (priorité 10, enregistré en deuxième)
 * 3. ThirdListener (priorité 10, enregistré en troisième)
 * 
 * Avec des priorités différentes :
 * FirstListener (priorité 20) → s'exécute en premier
 * SecondListener (priorité 10) → s'exécute en deuxième
 * ThirdListener (priorité 5) → s'exécute en troisième
 * 
 * Bonnes pratiques :
 * - Utiliser des priorités différentes si l'ordre est important
 * - Ne pas se fier uniquement à l'ordre d'enregistrement
 * - Documenter pourquoi une priorité spécifique est utilisée
 * 
 * Priorités standard dans Symfony :
 * - 255 : Très haute priorité
 * - 100 : Haute priorité
 * - 0 : Priorité normale (défaut)
 * - -100 : Basse priorité
 * - -255 : Très basse priorité
 */

/**
 * QUESTION 73 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which command can you run in production to improve performance when using env vars 
 * for configuration in a default Symfony application?
 * 
 * Options:
 * - composer dump-env prod
 * - composer create:env-prod
 * - composer generate:env-file --prod
 * - composer dump --optimize
 * - composer dump env-var
 * 
 * RÉPONSE CORRECTE: composer dump-env prod
 * 
 * JUSTIFICATION:
 * La commande composer dump-env prod optimise l'utilisation des variables d'environnement.
 * 
 * Ce que fait cette commande :
 * - Parse tous les fichiers .env* et .env.local.php
 * - Génère un fichier .env.local.php optimisé
 * - Ce fichier PHP est plus rapide à charger que les fichiers .env texte
 * - Améliore significativement les performances en production
 * 
 * Syntaxe complète :
 * composer dump-env prod
 * 
 * Résultat :
 * Crée/met à jour le fichier .env.local.php avec toutes les variables compilées :
 * 
 * <?php
 * // .env.local.php
 * return [
 *     'APP_ENV' => 'prod',
 *     'APP_SECRET' => 'xyz123...',
 *     'DATABASE_URL' => 'mysql://...',
 *     // ... toutes les autres variables
 * ];
 * 
 * Avantages en production :
 * - Pas besoin de parser les fichiers .env texte à chaque requête
 * - Le fichier PHP est chargé via opcache (encore plus rapide)
 * - Réduit les I/O disque
 * - Amélioration de performance mesurable
 * 
 * Workflow de déploiement recommandé :
 * 1. git pull (récupérer le code)
 * 2. composer install --no-dev --optimize-autoloader
 * 3. composer dump-env prod (optimiser les variables d'environnement)
 * 4. php bin/console cache:clear --env=prod
 * 5. php bin/console cache:warmup --env=prod
 * 
 * Pour supprimer le fichier optimisé (retour au mode dev) :
 * composer dump-env dev
 * ou
 * rm .env.local.php
 * 
 * Différence avec d'autres environnements :
 * composer dump-env dev    # Pour développement
 * composer dump-env test   # Pour tests
 * composer dump-env prod   # Pour production
 * 
 * Note importante :
 * - Le fichier .env.local.php ne doit PAS être commité dans git
 * - Ajouter .env.local.php au .gitignore
 * - Les vraies variables d'environnement système (export, etc.) ont toujours priorité
 * 
 * Les autres commandes proposées n'existent pas.
 */

/**
 * QUESTION 74 - CHOIX UNIQUE
 * =========================
 * 
 * Consider the following code from an event listener:
 * 
 * use App\Events\Blog\CommentPublishedEvent;
 * 
 * class BlogListener
 * {
 *     public function onBlogComment(CommentPublishedEvent $event): void
 *     {
 *         // ...
 * 
 *         $event->???();
 *     }
 * }
 * 
 * Question: Which method does ??? successfully replace in order to prevent other listeners 
 * from responding to this same event?
 * 
 * Options:
 * - cancelPropagation()
 * - stop()
 * - skip()
 * - stopPropagation()
 * - cancelNext()
 * 
 * RÉPONSE CORRECTE: stopPropagation()
 * 
 * JUSTIFICATION:
 * La méthode stopPropagation() empêche l'exécution des listeners suivants pour le même événement.
 * 
 * Fonctionnement :
 * - Tous les événements Symfony étendent Event ou implémentent StoppableEventInterface
 * - stopPropagation() marque l'événement comme "arrêté"
 * - Les listeners suivants (avec priorité plus basse) ne sont pas exécutés
 * - Les listeners déjà exécutés ne sont pas affectés
 * 
 * Exemple d'utilisation :
 * use Symfony\Contracts\EventDispatcher\Event;
 * 
 * class CommentPublishedEvent extends Event
 * {
 *     public function __construct(
 *         private Comment $comment
 *     ) {}
 * 
 *     public function getComment(): Comment
 *     {
 *         return $this->comment;
 *     }
 * }
 * 
 * // Listener avec haute priorité
 * class SpamCheckListener
 * {
 *     public function onCommentPublished(CommentPublishedEvent $event): void
 *     {
 *         if ($this->isSpam($event->getComment())) {
 *             // Bloquer le commentaire et empêcher les autres listeners
 *             $event->stopPropagation();
 *             return;
 *         }
 *     }
 * }
 * 
 * // Listener avec basse priorité (ne s'exécutera pas si spam)
 * class NotificationListener
 * {
 *     public function onCommentPublished(CommentPublishedEvent $event): void
 *     {
 *         // Envoyer des notifications
 *         // Ne s'exécute PAS si stopPropagation() a été appelé
 *     }
 * }
 * 
 * Vérifier si la propagation a été arrêtée :
 * if ($event->isPropagationStopped()) {
 *     // L'événement a été arrêté par un listener précédent
 * }
 * 
 * Configuration des priorités :
 * # config/services.yaml
 * services:
 *     App\EventListener\SpamCheckListener:
 *         tags:
 *             - { name: kernel.event_listener, event: comment.published, priority: 100 }
 * 
 *     App\EventListener\NotificationListener:
 *         tags:
 *             - { name: kernel.event_listener, event: comment.published, priority: 0 }
 * 
 * Cas d'usage typiques :
 * - Validation qui doit bloquer le traitement
 * - Authentification/autorisation échouée
 * - Détection de spam ou contenu inapproprié
 * - Circuit breaker pattern
 * - Optimisation (éviter du traitement inutile)
 * 
 * Les autres méthodes n'existent pas :
 * ✗ cancelPropagation() : N'existe pas
 * ✗ stop() : Trop vague, n'existe pas
 * ✗ skip() : N'existe pas
 * ✗ cancelNext() : N'existe pas
 */

/**
 * QUESTION 75 - CHOIX UNIQUE
 * =========================
 * 
 * Question: Which of the following is not a class defined in the Intl component to provide 
 * access to ICU (International Components for Unicode) data?
 * 
 * Options:
 * - Currencies
 * - NumberFormats
 * - Locales
 * - Countries
 * - Languages
 * 
 * RÉPONSE CORRECTE: NumberFormats
 * 
 * JUSTIFICATION:
 * Le composant Intl de Symfony/PHP fournit plusieurs classes pour accéder aux données ICU, 
 * mais "NumberFormats" n'en fait pas partie.
 * 
 * Classes valides du composant Intl :
 * 
 * ✓ Currencies :
 * use Symfony\Component\Intl\Currencies;
 * 
 * $currencies = Currencies::getNames();        // ['USD' => 'US Dollar', 'EUR' => 'Euro', ...]
 * $symbol = Currencies::getSymbol('USD');      // '$'
 * $name = Currencies::getName('EUR', 'fr');    // 'euro'
 * 
 * ✓ Countries :
 * use Symfony\Component\Intl\Countries;
 * 
 * $countries = Countries::getNames();          // ['US' => 'United States', 'FR' => 'France', ...]
 * $name = Countries::getName('US', 'fr');      // 'États-Unis'
 * $alpha3 = Countries::getAlpha3Code('US');    // 'USA'
 * 
 * ✓ Languages :
 * use Symfony\Component\Intl\Languages;
 * 
 * $languages = Languages::getNames();          // ['en' => 'English', 'fr' => 'French', ...]
 * $name = Languages::getName('fr', 'en');      // 'French'
 * 
 * ✓ Locales :
 * use Symfony\Component\Intl\Locales;
 * 
 * $locales = Locales::getNames();              // ['en' => 'English', 'fr' => 'français', ...]
 * $name = Locales::getName('fr_FR', 'en');     // 'French (France)'
 * 
 * ✗ NumberFormats : N'EXISTE PAS
 * 
 * Pour formater des nombres, utiliser plutôt :
 * 1. NumberFormatter (classe PHP native de l'extension intl) :
 * $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::DECIMAL);
 * echo $formatter->format(1234.56); // '1 234,56'
 * 
 * 2. IntlNumberFormatter (même chose) :
 * $formatter = \NumberFormatter::create('en_US', \NumberFormatter::CURRENCY);
 * echo $formatter->formatCurrency(1234.56, 'USD'); // '$1,234.56'
 * 
 * Autres classes utiles :
 * - Scripts : Systèmes d'écriture (Latin, Cyrillic, etc.)
 * - Timezones : Fuseaux horaires
 * 
 * Exemple complet d'utilisation :
 * use Symfony\Component\Intl\Countries;
 * use Symfony\Component\Intl\Languages;
 * use Symfony\Component\Intl\Currencies;
 * 
 * // Dans un formulaire Symfony
 * $builder
 *     ->add('country', CountryType::class)        // Utilise Countries
 *     ->add('language', LanguageType::class)      // Utilise Languages
 *     ->add('currency', CurrencyType::class)      // Utilise Currencies
 *     ->add('locale', LocaleType::class);         // Utilise Locales
 * 
 * Ces classes fournissent les données pour les form types correspondants.
 */