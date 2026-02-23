<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 45
 * Symfony 7.4 / 8.0 new features - Part 1
 * Topics: Controllers, Routing, Dependency Injection, Security, Console
 */
class CertificationQuestionsFixtures45 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures44::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Required categories not found. Please load AppFixtures first.');
        }

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
            // Q1 - Controllers - ControllerHelper (Symfony 7.4)
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Controllers'),
                'text' => '<p>In Symfony 7.4, a new <code>ControllerHelper</code> class was introduced. What is its primary purpose?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ControllerHelper exposes all the helper methods from AbstractController (render(), redirectToRoute(), addFlash(), etc.) as standalone public methods. This allows controllers that do NOT extend AbstractController to use these helpers via dependency injection, promoting framework-agnostic and decoupled code.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-decoupled-controller-helpers',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'To provide all <code>AbstractController</code> helper methods as standalone injectable services, enabling controllers without inheritance', 'correct' => true],
                    ['text' => 'To replace <code>AbstractController</code> entirely and deprecate class-based controllers', 'correct' => false],
                    ['text' => 'To add caching capabilities to controller responses automatically', 'correct' => false],
                    ['text' => 'To generate controller boilerplate code via a Maker command', 'correct' => false],
                ],
            ],

            // Q2 - Controllers - ControllerHelper + AutowireMethodOf
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Controllers'),
                'text' => '<p>Consider the following Symfony 7.4 controller that does NOT extend <code>AbstractController</code>:</p>
<pre><code class="language-php">use Symfony\Bundle\FrameworkBundle\Controller\ControllerHelper;
use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;

class ProductController
{
    public function __construct(
        #[AutowireMethodOf(ControllerHelper::class)]
        private \Closure $render,
    ) {}

    public function show(): Response
    {
        return ($this->render)(\'product/show.html.twig\', [\'id\' => 42]);
    }
}</code></pre>
<p>What does this code achieve?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The #[AutowireMethodOf] attribute injects a specific method from ControllerHelper as a Closure. This allows using only the render() helper without inheriting all of AbstractController, achieving fine-grained dependency injection.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-decoupled-controller-helpers',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'It injects only the <code>render()</code> method from <code>ControllerHelper</code> as a Closure, enabling template rendering without extending <code>AbstractController</code>', 'correct' => true],
                    ['text' => 'It will throw an error because controllers must extend <code>AbstractController</code> to use <code>render()</code>', 'correct' => false],
                    ['text' => 'It creates a new instance of <code>ControllerHelper</code> for each request', 'correct' => false],
                    ['text' => 'It automatically registers the controller as a service with a <code>controller.service_arguments</code> tag', 'correct' => false],
                ],
            ],

            // Q3 - Routing - Multiple environments in #[Route]
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Routing'),
                'text' => '<p>In Symfony 7.4, a route is defined as follows:</p>
<pre><code class="language-php">#[Route(\'/_debug/mail-preview\', name: \'debug_mail\', env: [\'dev\', \'test\'])]
public function previewEmail(): Response
{
    // ...
}</code></pre>
<p>What happens when a request is made to <code>/_debug/mail-preview</code> in the <code>prod</code> environment?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The env option in the #[Route] attribute restricts a route to specific configuration environments. In Symfony 7.4, it supports an array of environments. Since "prod" is not in the list [\'dev\', \'test\'], the route simply does not exist in that environment, resulting in a 404 Not Found response.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Symfony returns a <code>404 Not Found</code> response because the route does not exist in the <code>prod</code> environment', 'correct' => true],
                    ['text' => 'Symfony returns a <code>403 Forbidden</code> response', 'correct' => false],
                    ['text' => 'The route is matched but the controller throws an exception', 'correct' => false],
                    ['text' => 'Symfony ignores the <code>env</code> option and processes the request normally', 'correct' => false],
                ],
            ],

            // Q4 - Routing - Route auto-registration
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Routing'),
                'text' => '<p>In Symfony 7.4, route auto-registration was improved. Consider the following simplified <code>config/routes.yaml</code>:</p>
<pre><code class="language-yaml">controllers:
    resource: routing.controllers</code></pre>
<p>How does Symfony discover which classes should have their routes loaded?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 7.4 applies a service tag called "routing.controller" to any class that uses the #[Route] attribute. A compiler pass then collects those tagged services and automatically registers their routes. This means routes defined with #[Route] in ANY directory of the application will be discovered, not just the Controller directory.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'A <code>routing.controller</code> service tag is automatically applied to classes using <code>#[Route]</code>, and a compiler pass registers their routes', 'correct' => true],
                    ['text' => 'Symfony scans the entire <code>src/</code> directory for any PHP file containing route annotations', 'correct' => false],
                    ['text' => 'Only classes in the <code>src/Controller/</code> directory are registered, regardless of configuration', 'correct' => false],
                    ['text' => 'The <code>routing.controllers</code> resource is an alias for <code>../src/Controller/</code>', 'correct' => false],
                ],
            ],

            // Q5 - Security - #[IsGranted] with methods option
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'text' => '<p>Consider the following Symfony 7.4 controller:</p>
<pre><code class="language-php">#[IsGranted(\'ROLE_ADMIN\', methods: [\'POST\', \'PUT\'])]
public function manageProduct(Request $request): Response
{
    // ...
}</code></pre>
<p>What happens when an unauthenticated user sends a <code>GET</code> request to this action?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 7.4, the #[IsGranted] attribute supports a new "methods" option. The access check only runs when the HTTP method matches one of the configured methods. Since GET is not in [\'POST\', \'PUT\'], the attribute is completely ignored and the action executes normally, even for unauthenticated users.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'The action executes normally because the access check is skipped for <code>GET</code> requests', 'correct' => true],
                    ['text' => 'Symfony returns a <code>403 Forbidden</code> response', 'correct' => false],
                    ['text' => 'Symfony redirects to the login page', 'correct' => false],
                    ['text' => 'Symfony throws an <code>AccessDeniedException</code>', 'correct' => false],
                ],
            ],

            // Q6 - Security - #[IsSignatureValid] attribute
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'text' => '<p>Symfony 7.4 introduces the <code>#[IsSignatureValid]</code> attribute. What is its purpose?</p>
<pre><code class="language-php">class SomeController extends AbstractController
{
    #[IsSignatureValid]
    public function confirmEmail(): Response
    {
        // ...
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[IsSignatureValid] attribute automatically performs URI signature validation before executing the controller action. Symfony has utilities to sign URIs, and this attribute simplifies verifying that the URI has not been tampered with. It can be applied at method or class level.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'It automatically validates that the request URI has a valid cryptographic signature before the controller executes', 'correct' => true],
                    ['text' => 'It verifies the CSRF token attached to the form submission', 'correct' => false],
                    ['text' => 'It checks that the request body contains a valid JSON Web Token (JWT)', 'correct' => false],
                    ['text' => 'It enables HTTPS-only access for the controller action', 'correct' => false],
                ],
            ],

            // Q7 - Security - #[CurrentUser] with union types
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'text' => '<p>In Symfony 7.4, the following controller code is valid:</p>
<pre><code class="language-php">use App\Entity\AdminUser;
use App\Entity\Customer;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiController extends AbstractController
{
    public function index(#[CurrentUser] AdminUser|Customer $user): Response
    {
        // ...
    }
}</code></pre>
<p>What does the union type <code>AdminUser|Customer</code> achieve here?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 7.4, the #[CurrentUser] attribute supports union types. This allows the controller to accept the currently authenticated user when it matches any of the specified types. If the authenticated user is neither AdminUser nor Customer, the argument resolver will not match.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'The controller accepts the authenticated user if it is an instance of either <code>AdminUser</code> or <code>Customer</code>', 'correct' => true],
                    ['text' => 'The controller creates a new user object by merging <code>AdminUser</code> and <code>Customer</code> properties', 'correct' => false],
                    ['text' => 'Symfony loads both the admin user and customer and returns the first one found', 'correct' => false],
                    ['text' => 'This code is invalid because <code>#[CurrentUser]</code> only accepts a single type', 'correct' => false],
                ],
            ],

            // Q8 - DI - Repeatable #[AsDecorator]
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Dependency Injection'),
                'text' => '<p>In Symfony 7.4, the following service definition is valid:</p>
<pre><code class="language-php">use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(\'api1.client\')]
#[AsDecorator(\'api2.client\')]
#[AsDecorator(\'api3.client\')]
class LoggableHttpClient implements HttpClientInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
    ) {}
    // ...
}</code></pre>
<p>What does the repeatable <code>#[AsDecorator]</code> attribute achieve?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 7.4, the #[AsDecorator] attribute is now repeatable. A single class can decorate multiple services. Each decorated service gets its own instance of the decorator wrapping it. This is useful for cross-cutting concerns like logging across multiple similar services.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'The same class decorates all three services independently, each getting its own wrapper instance', 'correct' => true],
                    ['text' => 'Only the last <code>#[AsDecorator]</code> is applied; the others are ignored', 'correct' => false],
                    ['text' => 'All three services are merged into a single decorated service', 'correct' => false],
                    ['text' => 'This code is invalid because <code>#[AsDecorator]</code> can only be used once per class', 'correct' => false],
                ],
            ],

            // Q9 - Console - Enum support in invokable commands
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Console'),
                'text' => '<p>In Symfony 7.4, the following invokable command is defined:</p>
<pre><code class="language-php">enum CloudRegion: string
{
    case East = \'east\';
    case West = \'west\';
}

#[AsCommand(\'app:deploy\')]
class DeployCommand
{
    public function __invoke(
        #[Argument] CloudRegion $region,
    ): int {
        // ...
        return Command::SUCCESS;
    }
}</code></pre>
<p>What happens when a user runs <code>php bin/console app:deploy north</code>?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 7.4, invokable commands support backed enums in #[Argument] and #[Option] attributes. The string input is automatically converted to the corresponding enum case. If the value doesn\'t match any enum case, Symfony displays a clear error message showing the valid values.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-improved-invokable-commands',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Symfony displays an error like <em>"The value \'north\' is not valid. Supported values are \'east\', \'west\'."</em>', 'correct' => true],
                    ['text' => 'The command executes with <code>$region</code> set to <code>null</code>', 'correct' => false],
                    ['text' => 'A PHP <code>TypeError</code> is thrown', 'correct' => false],
                    ['text' => 'Symfony silently ignores the invalid value and defaults to the first enum case', 'correct' => false],
                ],
            ],

            // Q10 - Console - #[MapInput] attribute
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Console'),
                'text' => '<p>Symfony 7.4 introduces the <code>#[MapInput]</code> attribute for console commands. What is its purpose?</p>
<pre><code class="language-php">class ServerInput
{
    #[Argument]
    public string $name;

    #[Option]
    public int $port = 8080;
}

#[AsCommand(\'app:start-server\')]
class StartServerCommand
{
    public function __invoke(
        OutputInterface $output,
        #[MapInput] ServerInput $server,
    ): int {
        // use $server->name and $server->port
        return Command::SUCCESS;
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '#[MapInput] allows grouping command arguments and options into a DTO (Data Transfer Object) class. This reduces clutter in the __invoke() method when commands have many arguments/options. The DTO properties must be public and non-static, with #[Argument] and #[Option] attributes.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-improved-invokable-commands',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'It maps console arguments and options to a DTO class, reducing method parameter clutter', 'correct' => true],
                    ['text' => 'It validates the input against a JSON schema before passing it to the command', 'correct' => false],
                    ['text' => 'It reads input from a configuration file instead of command-line arguments', 'correct' => false],
                    ['text' => 'It serializes the input into a message for asynchronous processing', 'correct' => false],
                ],
            ],

            // Q11 - Console - #[Ask] and #[Interact] attributes
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Console'),
                'text' => '<p>In Symfony 7.4, the following invokable command uses the <code>#[Ask]</code> attribute:</p>
<pre><code class="language-php">#[AsCommand(\'app:add-server\')]
class AddServerCommand
{
    public function __invoke(
        #[Argument, Ask(\'Enter the cloud region name\')]
        string $region,
    ): int {
        // ...
        return Command::SUCCESS;
    }
}</code></pre>
<p>When does the <code>#[Ask]</code> prompt appear to the user?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[Ask] attribute provides a concise way to prompt users for missing argument/option values during interactive mode. If the user provides the value on the command line, the prompt is skipped. If the argument is missing, Symfony displays the configured question to ask for it.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-improved-invokable-commands',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Only when the argument is not provided on the command line and the command runs in interactive mode', 'correct' => true],
                    ['text' => 'Always, regardless of whether the argument was provided or not', 'correct' => false],
                    ['text' => 'Only when the <code>--no-interaction</code> flag is set', 'correct' => false],
                    ['text' => 'Only in debug mode', 'correct' => false],
                ],
            ],

            // Q12 - HttpFoundation - Request::get() deprecation
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HttpFoundation'),
                'text' => '<p>In Symfony 7.4, the <code>Request::get()</code> method has been deprecated. What was the issue with this method?</p>
<pre><code class="language-php">// Deprecated in Symfony 7.4
$value = $request->get(\'some_key\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Request::get() mixed values from route attributes, GET parameters, and POST data, checking them in that order and returning the first match. This made the source of data ambiguous and could lead to security issues. The recommended approach is to use $request->attributes->get(), $request->query->get(), or $request->request->get() explicitly.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-request-class-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'It mixed values from route attributes, query string, and POST data, making the data source ambiguous', 'correct' => true],
                    ['text' => 'It was too slow because it performed database lookups for each call', 'correct' => false],
                    ['text' => 'It returned raw, unsanitized input without any encoding', 'correct' => false],
                    ['text' => 'It only worked with GET requests and failed for other HTTP methods', 'correct' => false],
                ],
            ],

            // Q13 - HttpFoundation - Body parsing for non-POST methods
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HttpFoundation'),
                'text' => '<p>Starting with Symfony 7.4 (which requires PHP 8.4), <code>Request::createFromGlobals()</code> can now parse the request body for which HTTP methods?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP 8.4 introduces request_parse_body(), which allows parsing multipart/form-data and application/x-www-form-urlencoded for any HTTP method. Symfony 7.4 leverages this so Request::createFromGlobals() now works for POST, PUT, DELETE, PATCH and QUERY requests, not just POST.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-request-class-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'POST', 'correct' => true],
                    ['text' => 'PUT', 'correct' => true],
                    ['text' => 'DELETE', 'correct' => true],
                    ['text' => 'PATCH', 'correct' => true],
                    ['text' => 'OPTIONS', 'correct' => false],
                ],
            ],

            // Q14 - HttpFoundation - HTTP method override restrictions
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HttpFoundation'),
                'text' => '<p>In Symfony 7.4, HTTP method overriding has been restricted. Which of the following methods can NO longer be overridden using the <code>_method</code> field?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 7.4 deprecated overriding GET, HEAD, CONNECT and TRACE methods to make applications safer by default. Only methods like PUT, PATCH, and DELETE should be overridden (since browsers only support GET/POST in HTML forms).',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-request-class-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'GET', 'correct' => true],
                    ['text' => 'HEAD', 'correct' => true],
                    ['text' => 'CONNECT', 'correct' => true],
                    ['text' => 'TRACE', 'correct' => true],
                    ['text' => 'PUT', 'correct' => false],
                    ['text' => 'DELETE', 'correct' => false],
                ],
            ],

            // Q15 - Routing - 405 Method Not Allowed
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Routing'),
                'text' => '<p>A Symfony 8 application has the following route definition:</p>
<pre><code class="language-php">#[Route(\'/api/products\', name: \'api_products\', methods: [\'POST\'])]
public function createProduct(): JsonResponse
{
    // ...
}</code></pre>
<p>No other route matches the path <code>/api/products</code>. What happens when a client sends a <code>GET</code> request to <code>/api/products</code>?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When a route exists for the given path but not for the requested HTTP method, Symfony returns a 405 Method Not Allowed response (not 404). This is the correct behavior according to the HTTP specification (RFC 9110).',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Symfony returns a <code>405 Method Not Allowed</code> response', 'correct' => true],
                    ['text' => 'Symfony returns a <code>404 Not Found</code> response', 'correct' => false],
                    ['text' => 'Symfony routes the request normally and the controller decides what to do', 'correct' => false],
                    ['text' => 'Symfony redirects the <code>GET</code> request to <code>POST</code>', 'correct' => false],
                ],
            ],
        ];
    }
}
