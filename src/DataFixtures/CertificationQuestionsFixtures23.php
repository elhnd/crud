<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 23
 * 
 * Source: https://certification.symfony.com/
 * Topics: HttpKernel, FrameworkBundle, PHP, HTTP, Routing, Form, Security, 
 *         Doctrine, Expression Language, Yaml, Best Practices, VarDumper, 
 *         Twig, Arrays, Validator, Console, Dependency Injection, Event Dispatcher,
 *         Cache, Asset
 */
class CertificationQuestionsFixtures23 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures22::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Required categories not found. Please load CertificationExamFixtures first.');
        }

        // Load existing subcategories from AppFixtures
        $subcategories = $this->loadSubcategories($manager);
        $questions = $this->getCertificationQuestions($symfony, $php, $subcategories);

        foreach ($questions as $q) {
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }

    private function getCertificationQuestions(Category $symfony, Category $php, array $subcategories): array
    {
        return [

            // Q1 - HttpKernel - ValidateRequestListener
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the aim of the <code>Symfony\Component\HttpKernel\EventListener\ValidateRequestListener::onKernelRequest()</code> listener on <code>kernel.request</code> event?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'ValidateRequestListener valide que les headers et autres informations indiquant l\'adresse IP du client dans une requête sont cohérents. Il détecte les inconsistances entre les différents headers comme X-Forwarded-For et Client-Ip.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.7/src/Symfony/Component/HttpKernel/EventListener/ValidateRequestListener.php',
                'answers' => [
                    ['text' => 'Validates that the headers and other information indicating the client IP address of a request are consistent.', 'correct' => true],
                    ['text' => 'Validates that the request matches the RFC requirements.', 'correct' => false],
                    ['text' => 'Validates that the request path matches the available route paths.', 'correct' => false],
                ],
            ],

            // Q2 - FrameworkBundle - Container debug command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the command to display the debug information of the container?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La commande debug:container affiche toutes les informations de débogage sur le conteneur de services, incluant les services, paramètres, tags, etc.',
                'resourceUrl' => 'http://symfony.com/doc/current/book/service_container.html#debugging-services',
                'answers' => [
                    ['text' => '<code>debug:container</code>', 'correct' => true],
                    ['text' => '<code>debug:services</code>', 'correct' => false],
                    ['text' => '<code>container:info</code>', 'correct' => false],
                    ['text' => '<code>container:debug</code>', 'correct' => false],
                    ['text' => '<code>services:debug</code>', 'correct' => false],
                ],
            ],

            // Q3 - PHP - Exceptions with finally block
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Exceptions'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">try {
    try {
      echo \'a-\';
      throw new exception();
      echo \'b-\';
    } catch (Exception $e) {
        echo \'caught-\';
        throw $e;
    } finally {
        echo \'finished-\';
    }
} catch (Exception $e) {
    echo \'end-\';
}</code></pre>
What is the expected output when executing this script?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le bloc finally s\'exécute TOUJOURS, même si une exception est relancée. L\'ordre est : echo "a-", exception lancée, catch interne (echo "caught-"), finally (echo "finished-"), puis le catch externe (echo "end-").',
                'resourceUrl' => 'http://php.net/manual/en/language.exceptions.php',
                'answers' => [
                    ['text' => '<pre><code>a-caught-finished-end-</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>a-b-caught-finished-end-</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>a-caught-end-finished-</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>a-b-caught-end-</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>a-caught-end-</code></pre>', 'correct' => false],
                ],
            ],

            // Q4 - HTTP - Security protocols
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which protocols secure HTTP?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'SSL (Secure Sockets Layer) et TLS (Transport Layer Security) sont les deux protocoles qui sécurisent HTTP pour créer HTTPS. TLS est le successeur moderne de SSL.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2818, https://tools.ietf.org/html/rfc6101',
                'answers' => [
                    ['text' => 'SSL', 'correct' => true],
                    ['text' => 'TLS', 'correct' => true],
                    ['text' => 'SSH', 'correct' => false],
                    ['text' => 'SMTP', 'correct' => false],
                ],
            ],

            // Q5 - Routing - Slash in route parameters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Considering the following definition of route:
<pre><code class="language-php">use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();
$collection->add(\'_hello\', new Route(\'/hello/{username}\', array(
    \'_controller\' => \'App\Controller\DemoController:hello\',
), array(
    \'username\' => \'.+\',
)));</code></pre>
Will the <code>/hello/John/Doe</code> URI match this route?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la route correspondra car le requirement \'.+\' permet de capturer n\'importe quel caractère incluant les slashes. Par défaut, les paramètres de route ne capturent pas les slashes, mais avec le regex \'.+\', c\'est autorisé.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/routing.html#slash-characters-in-route-parameters',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - HttpKernel - UserInterface without Security component
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Given the context of an application without the <code>Security</code> component installed, is the following code valid?
<pre><code class="language-php">&lt;?php

// ...

use Symfony\Component\Security\Core\User\UserInterface;

class HomeController
{
  #[Route(\'/\', name: \'home\')]
  public function __invoke(UserInterface $user): Response
  {
    // ...
  }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, sans le composant Security installé, l\'ArgumentResolver pour UserInterface n\'est pas disponible. Le SecurityUserValueResolver est fourni par SecurityBundle et ne sera pas enregistré sans ce bundle.',
                'resourceUrl' => 'https://github.com/symfony/symfony/tree/4.1/src/Symfony/Component/HttpKernel/Controller/ArgumentResolver, https://github.com/symfony/symfony/blob/4.1/src/Symfony/Bundle/SecurityBundle/SecurityUserValueResolver.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q7 - HTTP - Expiration Cache Model
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following HTTP response headers belong to the expiration caching model?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le modèle d\'expiration utilise Expires et Cache-Control pour définir quand une réponse devient périmée. Etag et Last-Modified appartiennent au modèle de validation, pas d\'expiration.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2616#page-79',
                'answers' => [
                    ['text' => '<code>Cache-Control</code>', 'correct' => true],
                    ['text' => '<code>Expires</code>', 'correct' => true],
                    ['text' => '<code>Etag</code>', 'correct' => false],
                    ['text' => '<code>Last-Modified</code>', 'correct' => false],
                    ['text' => '<code>Pragma</code>', 'correct' => false],
                ],
            ],

            // Q8 - HttpClient - Debug info
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'Which method call allows to retrieve detailed logs about the requests and the responses of an http transaction?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode getInfo(\'debug\') permet de récupérer les logs détaillés des requêtes et réponses HTTP effectuées par le client HTTP de Symfony.',
                'resourceUrl' => 'https://symfony.com/doc/5.0/components/http_client.html',
                'answers' => [
                    ['text' => 'getInfo(\'debug\')', 'correct' => true],
                    ['text' => 'getInfoDebug()', 'correct' => false],
                    ['text' => 'getDebug(\'info\')', 'correct' => false],
                    ['text' => 'getDebugInfo()', 'correct' => false],
                ],
            ],

            // Q9 - SecurityBundle - Expression language provider tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the tag to register a provider for expression language functions in security?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le tag security.expression_language_provider permet d\'enregistrer un fournisseur de fonctions pour l\'Expression Language dans le contexte de sécurité.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/dic_tags.html#security-expression-language-provider',
                'answers' => [
                    ['text' => '<code>security.expression_language_provider</code>', 'correct' => true],
                    ['text' => '<code>security.provider</code>', 'correct' => false],
                    ['text' => '<code>security_provider</code>', 'correct' => false],
                ],
            ],

            // Q10 - Doctrine - EntityManager::persist
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'What does the <code>EntityManagerInterface::persist($entity)</code> method do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode persist() indique à l\'UnitOfWork de commencer à suivre (tracker) l\'entité. Elle ne persiste PAS immédiatement en base de données - cela ne se produit qu\'au moment du flush().',
                'resourceUrl' => 'https://symfony.com/doc/current/doctrine.html',
                'answers' => [
                    ['text' => 'It tells the UnitOfWork to start tracking the <code>$entity</code>.', 'correct' => true],
                    ['text' => 'It persists <code>$entity</code> to the persistence layer (database).', 'correct' => false],
                    ['text' => 'It persists <code>$entity</code> to the persistence layer (database) if it doesn\'t exist already.', 'correct' => false],
                ],
            ],

            // Q11 - Expression Language - AST dump
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'Could the AST be dumped?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le composant Expression Language de Symfony permet de dumper l\'AST (Abstract Syntax Tree) pour inspecter la structure de l\'expression parsée.',
                'resourceUrl' => 'https://symfony.com/doc/3.2/components/expression_language/ast.html#dumping-the-ast',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q12 - Yaml - Date Handling
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'],
                'text' => 'What will be stored in <code>$yaml</code> with the following code:
<pre><code class="language-php">&lt;?php

use Symfony\Component\Yaml\Yaml;

$yaml = Yaml::parse(\'1983-07-01\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Par défaut, le composant YAML de Symfony convertit les dates au format ISO 8601 en timestamps Unix (entier). La date 1983-07-01 devient 425865600.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/yaml.html#date-handling',
                'answers' => [
                    ['text' => '<pre><code>425865600</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>1983-07-01</code></pre>', 'correct' => false],
                    ['text' => 'An array<pre><code>array:1 [
  0 => 425865600
]</code></pre>', 'correct' => false],
                    ['text' => 'A DateTime object<pre><code>DateTime {
  +"date": "1983-07-01 00:00:00.000000"
  +"timezone_type": 3
  +"timezone": "UTC"
}</code></pre>', 'correct' => false],
                ],
            ],

            // Q13 - Best Practices - Entities mapping format
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'According to the official Symfony <em>Best Practices Guide</em>, which format do you need to use to define the mapping information of the Doctrine entities?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Depuis Symfony 5.3, les bonnes pratiques officielles recommandent d\'utiliser les Attributes PHP (disponibles depuis PHP 8.0) pour définir le mapping Doctrine plutôt que les annotations ou les fichiers YAML/XML.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/best_practices.html#use-attributes-to-define-the-doctrine-entity-mapping',
                'answers' => [
                    ['text' => 'Attributes', 'correct' => true],
                    ['text' => 'Annotations', 'correct' => false],
                    ['text' => 'PHP', 'correct' => false],
                    ['text' => 'Xml', 'correct' => false],
                    ['text' => 'Yaml', 'correct' => false],
                ],
            ],

            // Q15 - PHP Arrays - asort SORT_FLAG_CASE
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays & Collections'],
                'text' => 'Given the following code, what will be displayed?
<pre><code class="language-php">&lt;?php

$data = [\'php\', \'symfony\', \'twig\', \'sensiolabs\'];

asort($data, SORT_FLAG_CASE);

echo implode(\', \', $data);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'asort() trie le tableau en conservant les clés. Avec SORT_FLAG_CASE, le tri est insensible à la casse. L\'ordre alphabétique donne : php, sensiolabs, symfony, twig.',
                'resourceUrl' => 'https://www.php.net/manual/en/function.asort.php',
                'answers' => [
                    ['text' => '<code>php</code>, <code>sensiolabs</code>, <code>symfony</code>, <code>twig</code>', 'correct' => true],
                    ['text' => '<code>php</code>, <code>twig</code>, <code>symfony</code>, <code>sensiolabs</code>', 'correct' => false],
                    ['text' => '<code>php</code>, <code>sensiolabs</code>, <code>twig</code>, <code>symfony</code>', 'correct' => false],
                    ['text' => '<code>php</code>, <code>symfony</code>, <code>twig</code>, <code>sensiolabs</code>', 'correct' => false],
                    ['text' => '<code>sensiolabs</code>, <code>symfony</code>, <code>twig</code>, <code>php</code>', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                ],
            ],

            // Q16 - PHP - Enumerations magic methods
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Which magic methods are allowed to be defined in an enumeration?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Les énumérations PHP ne peuvent définir que les méthodes magiques __call, __callStatic et __invoke. Les autres méthodes magiques comme __construct, __get, __set ne sont pas autorisées car les enums sont immuables.',
                'resourceUrl' => 'https://www.php.net/manual/fr/language.enumerations.object-differences.php',
                'answers' => [
                    ['text' => '<code>__call</code>, <code>__callStatic</code> and <code>__invoke</code>', 'correct' => true],
                    ['text' => '<code>__serialize</code>, <code>__sleep</code> and <code>__wakeup</code>', 'correct' => false],
                    ['text' => '<code>__call</code>, <code>__get</code> and <code>__toString</code>', 'correct' => false],
                    ['text' => '<code>__toString</code>, <code>__serialize</code> and <code>__isset</code>', 'correct' => false],
                ],
            ],

            // Q17 - Twig - Internals Compiler
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following Twig internal objects is responsible for transforming an AST (Abstract Syntax Tree) into PHP code?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le Compiler transforme l\'AST en code PHP exécutable. Le Lexer tokenise le template, le Parser construit l\'AST à partir des tokens.',
                'resourceUrl' => 'http://twig.symfony.com/doc/internals.html',
                'answers' => [
                    ['text' => 'The Compiler', 'correct' => true],
                    ['text' => 'The Lexer', 'correct' => false],
                    ['text' => 'The Parser', 'correct' => false],
                    ['text' => 'The Environment', 'correct' => false],
                ],
            ],

            // Q19 - Routing - Optional parameters URL generation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'What will be the <strong>generated</strong> URL when calling <code>path(\'list\')</code> from a Twig template?
<pre><code class="language-php">/**
 * @Route(
 *  "/blog/{page}",
 *  name="list",
 *  requirements={"page": "\d+"},
 *  defaults={"page": 1}
 * )
 */
public function list(int $page): Response
{
    // ...
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Quand path() est appelé sans paramètres pour une route avec un paramètre optionnel ayant une valeur par défaut, Symfony génère l\'URL la plus courte possible: /blog sans le paramètre page.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/routing/optional_placeholders.html, https://symfony.com/doc/2.x/reference/twig_reference.html#path',
                'answers' => [
                    ['text' => '<code>/blog</code>', 'correct' => true],
                    ['text' => '<code>/blog/</code>', 'correct' => false],
                    ['text' => '<code>/blog/1</code>', 'correct' => false],
                    ['text' => 'A <code>MissingMandatoryParametersException</code> will be raised.', 'correct' => false],
                ],
            ],

            // Q20 - PHP OOP - ArrayAccess interface
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Which interface should an object implement to use brackets notation as an array?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'interface ArrayAccess permet à un objet d\'être accédé comme un tableau avec la notation crochets []. Elle requiert l\'implémentation de offsetExists, offsetGet, offsetSet et offsetUnset.',
                'resourceUrl' => 'https://www.php.net/manual/en/class.arrayaccess.php',
                'answers' => [
                    ['text' => 'ArrayAccess', 'correct' => true],
                    ['text' => 'IteratorAggregate', 'correct' => false],
                    ['text' => 'Traversable', 'correct' => false],
                    ['text' => 'Iterator', 'correct' => false],
                ],
            ],

            // Q23 - Console - setCode method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Given the following console event subscriber:
<pre><code class="language-php">use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\ConsoleEvents;

class ConsoleListener implements EventSubscriberInterface
{
  private AppChecker $appChecker;

  public function __construct(AppChecker $appChecker)
  {
    $this->appChecker = $appChecker;
  }

  public function onTerminate(ConsoleEvent $event): void
  {
    if (!$this->appChecker->isOk()) {
      $event->getCommand()->setCode(232);
    }
  }

  public static function getSubscribedEvents(): array
  {
    return [
        ConsoleEvents::TERMINATE => \'onTerminate\'
    ];
  }
}</code></pre>
What can be said about the <code>setCode</code> command method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'La méthode setCode() attend un callable, pas un entier. Passer un entier (232) provoquera une erreur de type. De plus, modifier le code d\'une commande dans l\'événement TERMINATE n\'a pas de sens car la commande a déjà été exécutée.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Console/Command/Command.php#L252',
                'answers' => [
                    ['text' => 'The code above will result to an error', 'correct' => true],
                    ['text' => 'The code above will change the command exit status code to 232', 'correct' => false],
                    ['text' => 'This method doesn\'t exist', 'correct' => false],
                ],
            ],

            // Q26 - Security - Anonymous users in voter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'How to handle anonymous users in a custom voter?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Depuis Symfony 5.2, pour vérifier si un utilisateur est anonyme dans un voter, on vérifie si $token->getUser() n\'est pas une instance de UserInterface. AnonymousToken a été déprécié.',
                'resourceUrl' => 'https://symfony.com/doc/5.2/security/experimental_authenticators.html#granting-anonymous-users-access-in-a-custom-voter',
                'answers' => [
                    ['text' => '<pre><code class="language-php">if (!$token->getUser() instanceof UserInterface) {
    return $subject->isPublic();
}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">if ($token instanceof AnonymousToken) {
    return $subject->isPublic();
}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">if ($token instanceof PublicToken) {
    return $subject->isPublic();
}</code></pre>', 'correct' => false],
                ],
            ],

            // Q27 - Cache - Pools debug
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Could the available pools list be displayed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, la commande php bin/console cache:pool:list affiche tous les pools de cache disponibles. On peut aussi utiliser cache:pool:clear pour les vider.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/cache.html#clearing-the-cache',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q28 - Routing - Route priority with defaults
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Given the following two routes, what controller will be executed for the URL <code>/book/123</code>?
<pre><code class="language-yaml"> # config/routes.yaml
 book_detail_section:
     path:       /book/{id}/{section}
     controller: \'App\Controller\BookController::detailSection\'
     defaults:   { section: home }
 book_detail:
     path:      /book/{id}
     controller: \'App\Controller\BookController::detail\'</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'La première route déclarée (book_detail_section) correspond car elle a une valeur par défaut pour {section}. L\'ordre de déclaration importe: la première route qui matche est utilisée.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#priority-parameter, https://symfony.com/doc/current/routing.html#extra-parameters',
                'answers' => [
                    ['text' => '<code>App\Controller\BookController::detailSection</code>', 'correct' => true],
                    ['text' => '<code>App\Controller\BookController::detail</code>', 'correct' => false],
                    ['text' => 'Error: The routing file contains unsupported keys for "defaults"', 'correct' => false],
                    ['text' => 'Error: No route found', 'correct' => false],
                ],
            ],

            // Q29 - DI - EnvPlaceholderParameterBag merge
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could an <code>EnvPlaceholderParameterBag</code> be merged into another one?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, EnvPlaceholderParameterBag possède une méthode mergeEnvPlaceholders() qui permet de fusionner les placeholders d\'un autre bag.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/DependencyInjection/ParameterBag/EnvPlaceholderParameterBag.php#L69',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q30 - Validator - Validation constraints locations
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Which of the following elements can contain validation constraints?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Depuis Symfony 5.3, les contraintes peuvent être placées sur les classes, les propriétés publiques, les propriétés privées/protégées, et les getters publics. Les getters privés/protégés ne sont pas supportés.',
                'resourceUrl' => 'https://symfony.com/doc/current/validation.html#index-6',
                'answers' => [
                    ['text' => 'Classes', 'correct' => true],
                    ['text' => 'Public properties', 'correct' => true],
                    ['text' => 'Private and protected properties', 'correct' => true],
                    ['text' => 'Public getters/issers', 'correct' => true],
                    ['text' => 'Private and protected getters/issers', 'correct' => false],
                ],
            ],

            // Q31 - Twig - Environment yield configuration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Could templates be configured to exclusively use <code>yield</code> instead of <code>echo</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'Oui, depuis Twig 3.x, l\'option use_yield peut être activée dans l\'Environment pour que les templates compilés utilisent yield au lieu de echo.',
                'resourceUrl' => 'https://github.com/twigphp/Twig/blob/v3.9.0/src/Environment.php#L104',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
        ];
    }
}
