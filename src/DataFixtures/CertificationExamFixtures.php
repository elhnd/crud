<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Real Certification Exam Questions
 * These questions are ONLY used in certification exam mode (75 questions)
 * They are marked with isCertification = true
 */
class CertificationExamFixtures extends Fixture implements FixtureGroupInterface
{
    use UpsertQuestionTrait;
    public static function getGroups(): array
    {
        return ['exam'];
    }

    public function load(ObjectManager $manager): void
    {
        // Get existing categories
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found. Run AppFixtures first.');
        }

        // Load existing subcategories from AppFixtures
        $subcategories = $this->loadSubcategories($manager);

        // Define certification exam questions
        $questions = $this->getCertificationQuestions($symfony, $php, $subcategories);

        // Persist all questions using upsert
        foreach ($questions as $q) {
            $q['isCertification'] = true; // Mark as certification question
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }

    private function getCertificationQuestions(Category $symfony, Category $php, array $subcategories): array
    {
        return [
            // QUESTION 1 - PHP match expression
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following class:
<pre><code class="language-php">class Order
{
    // ...

    public function finishCheckout(): void
    {
        match (true) {
            $this->inStock() => $this->processorOrder(),
            $this->stockComingSoon() => $this->notifyUser(),
            $this->outOfStock() => $this->orderNewStock(),
        };
    }
}</code></pre>

Will the finishCheckout() method throw any exception because of an error in the match() statement?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The match() expression in PHP 8+ does not require its return value to be captured. It is valid to pass true to match(). A "default" arm is not required as long as all cases are covered by the conditions.',
                'resourceUrl' => 'https://www.php.net/manual/en/control-structures.match.php',
                'answers' => [
                    ['text' => 'Yes, because you always need to capture the value returned by match() (even if it\'s void).', 'correct' => false],
                    ['text' => 'Yes, because you can\'t pass true to match().', 'correct' => false],
                    ['text' => 'Yes, because match() must always define a default expression.', 'correct' => false],
                    ['text' => 'No.', 'correct' => true],
                ],
            ],

            // QUESTION 2 - PHP Named arguments
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following function definition:
<pre><code class="language-php">function sum(int $a, int $b): int
{
    return $a + $b;
}</code></pre>

When using PHP 8 or higher, can you call this function as follows without triggering any exception?

<pre><code class="language-php">echo sum(a: 3, 7);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'The syntax "sum(a: 3, 7)" is INVALID because after using a named argument (a: 3), you cannot use a positional argument (7). The correct syntax would be: sum(a: 3, b: 7) or sum(3, 7).',
                'resourceUrl' => 'https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],

            // QUESTION 3 - PHP Constructor Property Promotion
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Is the following class definition valid when using PHP 8 and higher?
<pre><code class="language-php">class Point
{
    public function __construct(private int|float $x, private int|float $y)
    {
    }

    // ...
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'PHP 8 introduced Constructor Property Promotion, which allows declaring and initializing properties directly in the constructor parameters. Union types (int|float) are also supported.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.deferred.php',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // QUESTION 4 - PHP Interface implementation
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">interface SayHelloInterface
{
    public function greet(string $who): string;
}

class Person implements SayHelloInterface
{
    public function greet(string $who, bool $scream = false): string
    {
        return sprintf($scream ? \'WELCOME %s!\' : \'Welcome %s!\', $who);
    }
}

echo (new Person())->greet(\'Alice\', true);</code></pre>

What is the result of executing this code snippet?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The method signature in the implementing class can have additional parameters with default values. Since $scream = true, "WELCOME Alice!" is displayed.',
                'answers' => [
                    ['text' => 'Welcome Alice!', 'correct' => false],
                    ['text' => 'WELCOME Alice!', 'correct' => true],
                    ['text' => 'WELCOME ALICE!', 'correct' => false],
                    ['text' => 'It produces a fatal error because the two greet() method signatures mismatch.', 'correct' => false],
                ],
            ],

            // QUESTION 5 - Symfony 404 error (Multiple choice)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Controllers'],
                'text' => 'Find all the working solutions for generating a 404 error page from a controller that extends Symfony\'s AbstractController:',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Options 2, 3, and 4 are correct. throw createNotFoundException() throws an exception, NotFoundHttpException generates a 404, and Response with status code 404 also works.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller.html#managing-errors-and-404-pages',
                'answers' => [
                    ['text' => '<code>return $this->error404();</code>', 'correct' => false],
                    ['text' => '<code>throw $this->createNotFoundException(\'Page not found\');</code>', 'correct' => true],
                    ['text' => '<code>throw new NotFoundHttpException(\'Page not found\');</code>', 'correct' => true],
                    ['text' => '<code>return new Response(\'Page not found\', 404);</code>', 'correct' => true],
                    ['text' => 'return $this->createNotFoundException(\'Page not found\');', 'correct' => false],
                ],
            ],

            // QUESTION 6 - Symfony forward controller
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Controllers'],
                'text' => 'Consider the following code:
<pre><code class="language-php"># src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function index()
    {
        return $this->forward(???);
    }
}

# src/Controller/BlogController.php
namespace App\Controller;

class BlogController extends AbstractController
{
    public function index()
    {
        // ...
    }
}</code></pre>

Which statement does ??? successfully replace to forward the execution to the index method of the BlogController?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The forward() method accepts a controller as a string using the syntax \'Namespace\\Controller::method\'.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller/forwarding.html',
                'answers' => [
                    ['text' => '[BlogController::class, \'index\']', 'correct' => false],
                    ['text' => '\'App\\\\Controller\\\\BlogController::index\'', 'correct' => true],
                    ['text' => '[\'App\\\\Controller\\\\BlogController::index\']', 'correct' => false],
                    ['text' => 'BlogController::class', 'correct' => false],
                    ['text' => '\'App\\\\Controller\\\\BlogController@index\'', 'correct' => false],
                ],
            ],

            // QUESTION 7 - Symfony controller naming convention
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Controllers'],
                'text' => 'What\'s the recommended naming convention for action methods in Symfony controllers?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Since Symfony 4+, the recommended convention is to use the action name in camelCase without any prefix or suffix.',
                'resourceUrl' => 'https://symfony.com/doc/current/best_practices.html#controllers',
                'answers' => [
                    ['text' => 'do[actionName]() (e.g. doShow())', 'correct' => false],
                    ['text' => 'actionName() (e.g. show())', 'correct' => true],
                    ['text' => '[actionName]Action() (e.g. showAction())', 'correct' => false],
                    ['text' => 'perform[actionName]() (e.g. performShow())', 'correct' => false],
                ],
            ],

            // QUESTION 8 - Symfony redirect status code
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Controllers'],
                'text' => 'Consider the following controller code:
<pre><code class="language-php"># src/Controller/DemoController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DemoController extends AbstractController
{
    public function index()
    {
        return $this->redirect(\'/\');
    }
}</code></pre>

What is the HTTP status code of the returned response?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The redirect() method of AbstractController returns an HTTP 302 (Found) status code by default for a temporary redirect.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller.html#redirecting',
                'answers' => [
                    ['text' => '301 (Moved Permanently)', 'correct' => false],
                    ['text' => '302 (Found)', 'correct' => true],
                    ['text' => '303 (See Other)', 'correct' => false],
                    ['text' => '304 (Not Modified)', 'correct' => false],
                    ['text' => '307 (Temporary Redirect)', 'correct' => false],
                ],
            ],

            // QUESTION 9 - Symfony flash messages
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Session'],
                'text' => 'In a controller that extends Symfony\'s AbstractController and receives the current Request object in an argument called $request, which of the following statements allows to store a temporary message in the session in order to display it after a redirect?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The addFlash() method is the recommended way in Symfony to create temporary flash messages.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller.html#flash-messages',
                'answers' => [
                    ['text' => '$this->addFlash(\'notice\', \'Item added successfully\');', 'correct' => true],
                    ['text' => '$this->getSession()->store(\'notice\', \'Item added successfully\');', 'correct' => false],
                    ['text' => '$request->flashes->set(\'notice\', \'Item added successfully\');', 'correct' => false],
                    ['text' => '$request->session->getFlashes()->set(\'notice\', \'Item added successfully\');', 'correct' => false],
                ],
            ],

            // QUESTION 10 - Symfony Autowire attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'Consider the following controller in a default Symfony application with both autowiring and autoconfiguration enabled:
<pre><code class="language-php">use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
// ...

class SomeController extends AbstractController
{
    public function index(
        #[ ??? (service: \'app.request_logger\')] LoggerInterface $logger,
    ): Response
    {
        // ...
    }
}</code></pre>

Which statement does ??? successfully replace in order to inject the service with ID app.request_logger in the $logger controller argument?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[Autowire(service: \'id\')] attribute is used to inject a specific service by its ID.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/autowiring.html#dealing-with-multiple-implementations-of-the-same-type',
                'answers' => [
                    ['text' => 'Autoconfigure', 'correct' => false],
                    ['text' => 'Autowire', 'correct' => true],
                    ['text' => 'AsService', 'correct' => false],
                    ['text' => 'Target', 'correct' => false],
                    ['text' => 'Inject', 'correct' => false],
                ],
            ],

            // QUESTION 11 - Session set value
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Session'],
                'text' => 'Which of the following is the valid way to persist a value in the user\'s session?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The set() method is the standard way to store a value in the Symfony session.',
                'resourceUrl' => 'https://symfony.com/doc/current/session.html',
                'answers' => [
                    ['text' => '$session->write(\'foo\', \'bar\');', 'correct' => false],
                    ['text' => '$session->add(\'foo\', \'bar\');', 'correct' => false],
                    ['text' => '$session->set(\'foo\', \'bar\');', 'correct' => true],
                    ['text' => '$session->store(\'foo\', \'bar\');', 'correct' => false],
                    ['text' => '$session->save(\'foo\', \'bar\');', 'correct' => false],
                ],
            ],

            // QUESTION 12 - Route locale generation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Consider the following route available in two locales (en and nl):
<pre><code class="language-yaml"># config/routes.yaml
contact:
    controller: App\Controller\ContactController::send
    path:
        en: /send-us-an-email
        nl: /stuur-ons-een-email</code></pre>

Which URL will generate the following code which doesn\'t specify the locale?

<pre><code class="language-php">$url = $urlGenerator->generate(\'contact\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'When generating a URL without specifying the locale, Symfony uses the current user locale. If it does not match any defined locale (en or nl), an exception is thrown.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#localized-routes-i18n',
                'answers' => [
                    ['text' => 'The first defined URL (in this case, the one for en locale).', 'correct' => false],
                    ['text' => 'The last defined URL (in this case, the one for nl locale).', 'correct' => false],
                    ['text' => 'The most appropriate URL depending on the geo-location of the user.', 'correct' => false],
                    ['text' => 'The URL associated with the user locale or an exception if user locale is not en or nl.', 'correct' => true],
                ],
            ],

            // QUESTION 13 - Route paths definition
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'A developer defines the following route to match multiple URLs with the same controller:
<pre><code class="language-yaml"># config/routes.yaml
demo_route:
    paths: [\'/\', \'/demo\', \'/demos\', \'/demos-about-xxx\']
    controller: \'App\Controller\MainController::demo\'</code></pre>

Is this a valid route definition in Symfony?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'The "paths" key with a plain array is not a valid Symfony route definition. The correct key is "path" (singular).',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],

            // QUESTION 14 - Controller method name mismatch
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Consider the following controller code:
<pre><code class="language-php"># src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class BlogController
{
    public function showAction(): Response
    {
        // ...
    }
}</code></pre>

And the following YAML route configuration:

<pre><code class="language-yaml"># config/routes.yaml
app_blog:
    path: /blog
    controller: \'App\Controller\BlogController::show\'
    methods: [\'GET\']</code></pre>

Will the showAction() method be executed if a user requests the /blog URL using the address bar of a web browser?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'The route points to \'show\' but the method is called \'showAction()\'. Symfony will look for show() which does not exist.',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],

            // QUESTION 15 - Debug router command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Identify the correct command name to complete the following sentence:

"The .......... command can be used to get all the information about a specific route."',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The debug:router command displays all configured routes in the application and provides detailed information about any specific route.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#debugging-routes',
                'answers' => [
                    ['text' => 'routing:route', 'correct' => false],
                    ['text' => 'router:dump-routes', 'correct' => false],
                    ['text' => 'router:info', 'correct' => false],
                    ['text' => 'debug:router', 'correct' => true],
                    ['text' => 'router:match', 'correct' => false],
                ],
            ],

            // QUESTION 16 - Route _fragment parameter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Consider the following route definition:
<pre><code class="language-php">#[Route(\'/blog/{id}\', defaults: [\'_fragment\' => \'comments\'], name: \'blog_post_comments\')]
public function blogPost(): Response { /* ... */ }</code></pre>

Which will be the value of the following $url variable?

<pre><code class="language-php">$url = $urlGenerator->generate(\'blog_post_comments\', [\'id\' => 37]);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The special \'_fragment\' parameter generates a URL fragment (hash/anchor). Result: /blog/37#comments',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#generating-urls',
                'answers' => [
                    ['text' => '/blog/37/_fragment/comments', 'correct' => false],
                    ['text' => '/blog?id=37&_fragment=comments', 'correct' => false],
                    ['text' => '/blog/?id=37&_fragment=comments', 'correct' => false],
                    ['text' => '/blog/37', 'correct' => false],
                    ['text' => '/blog/37#comments', 'correct' => true],
                ],
            ],

            // QUESTION 17 - Route name prefix
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Consider the following route definition:
<pre><code class="language-php">#[Route(\'/blog\', name: \'blog_\')]
class PostController
{
    #[Route(\'/{id}\', name: \'show\')]
    public function show(int $id): Response
    {
        // ...
    }
}</code></pre>

Which will be the name of the route associated with the show() method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When #[Route] is defined at the class level with a \'name\' parameter, that name is prefixed to the method route names. blog_ + show = blog_show',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#route-groups-and-prefixes',
                'answers' => [
                    ['text' => 'show', 'correct' => false],
                    ['text' => 'blog_1 (if show() is the first method, blog_2 if it\'s the second, etc.)', 'correct' => false],
                    ['text' => 'This code will throw an exception (the parent #[Route] cannot define a name parameter).', 'correct' => false],
                    ['text' => 'blog_show', 'correct' => true],
                    ['text' => 'blog_', 'correct' => false],
                ],
            ],

            // QUESTION 18 - Import ignore_errors
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'Consider the following services configuration:
<pre><code class="language-yaml"># config/services.yaml
imports:
    - { resource: \'./parameters.yaml\', ignore_errors: true }</code></pre>

What will happen if the parameters.yaml file does not exist in the same config/ directory from where it\'s being imported?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The \'ignore_errors: true\' parameter tells Symfony to silently ignore errors. The application will continue working normally.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html#importing-other-container-configuration-files',
                'answers' => [
                    ['text' => 'You\'ll get an exception.', 'correct' => false],
                    ['text' => 'The application will return a 404 (not found) HTTP response.', 'correct' => false],
                    ['text' => 'The application will keep working (and that file won\'t be imported).', 'correct' => true],
                ],
            ],

            // QUESTION 19 - Multiple logger autowiring
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'A default Symfony application with both autowiring and autoconfiguration enabled defines many different logger services. Can you still use autowiring to inject some specific logger in a service?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When multiple services implement the same interface, you need to use the #[Autowire] attribute or configure a service alias to specify which one to inject.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/autowiring.html',
                'answers' => [
                    ['text' => 'Yes, and you don\'t have to do or configure anything.', 'correct' => false],
                    ['text' => 'Yes, but you have to add some service configuration or use some PHP attributes in your service class.', 'correct' => true],
                    ['text' => 'No, you can\'t use autowiring in that case because Symfony doesn\'t know which exact logger to inject.', 'correct' => false],
                ],
            ],

            // QUESTION 20 - RequestStack type hint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'In a Symfony application that uses autowiring, which of the following classes should you use to type-hint a class constructor argument in order to inject the current request stack?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'RequestStack is the concrete class in the HttpFoundation component used to manage the request stack.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html#fetching-the-request',
                'answers' => [
                    ['text' => 'Symfony\\Component\\HttpFoundation\\RequestStack', 'correct' => true],
                    ['text' => 'Symfony\\Component\\Routing\\RequestStackInterface', 'correct' => false],
                    ['text' => 'Psr\\Psr7\\RequestStackInterface', 'correct' => false],
                    ['text' => 'Symfony\\Component\\HttpKernel\\RequestStack', 'correct' => false],
                    ['text' => 'Symfony\\Component\\HttpFoundation\\RequestStackInterface', 'correct' => false],
                ],
            ],

            // QUESTION 21 - bind option
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'Consider the following services configuration:
<pre><code class="language-yaml"># config/services.yaml
services:
    _defaults:
        ???:
            $projectDir: \'%kernel.project_dir%\'</code></pre>

Which option does ??? successfully replace to inject the parameter in every constructor argument called $projectDir?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The \'bind\' option in _defaults allows automatically binding values to constructor arguments by name or type.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html#binding-arguments-by-name-or-type',
                'answers' => [
                    ['text' => 'inject_parameters', 'correct' => false],
                    ['text' => 'bind', 'correct' => true],
                    ['text' => 'bind_parameters', 'correct' => false],
                    ['text' => 'inject', 'correct' => false],
                    ['text' => 'parameters', 'correct' => false],
                ],
            ],

            // QUESTION 22 - request_stack service
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'Which service should you pass as a dependency of another service that needs to get access to the current Request object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The \'request_stack\' service is the recommended way to access the current request from within a service.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html#fetching-the-request',
                'answers' => [
                    ['text' => 'event_dispatcher', 'correct' => false],
                    ['text' => 'router.request_context', 'correct' => false],
                    ['text' => 'http_kernel', 'correct' => false],
                    ['text' => 'request', 'correct' => false],
                    ['text' => 'request_stack', 'correct' => true],
                ],
            ],

            // QUESTION 23 - bind scope
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'Consider the following service configuration:
<pre><code class="language-yaml"># config/services.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true
        bind:
            string $projectDir: \'%kernel.project_dir%\'</code></pre>

In which services will Symfony inject the value of kernel.project_dir in any constructor argument called $projectDir?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The _defaults section only applies to services defined in the same configuration file.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html#binding-arguments-by-name-or-type',
                'answers' => [
                    ['text' => 'In none. This bind option does not exist.', 'correct' => false],
                    ['text' => 'In all services (both your own services and the vendor services).', 'correct' => false],
                    ['text' => 'In all of your own services.', 'correct' => false],
                    ['text' => 'In all services defined/created in this config/services.yaml file.', 'correct' => true],
                ],
            ],

            // QUESTION 24 - Twig paths configuration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'A Symfony application wants to store the templates in the resources/views/ directory instead of the default templates/ directory. Which statements do xxx and yyy successfully replace in the following config to achieve that?

<pre><code class="language-yaml"># config/packages/twig.yaml
twig:
    xxx: [\'yyy\']</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The \'paths\' option (plural) in the Twig configuration allows defining additional directories for templates.',
                'resourceUrl' => 'https://symfony.com/doc/current/templates.html#template-namespaces',
                'answers' => [
                    ['text' => 'paths and \'%kernel.project_dir%/resources/views/\'', 'correct' => true],
                    ['text' => 'templates and \'%kernel.root_dir%/resources/views/\'', 'correct' => false],
                    ['text' => 'path and \'@resources/views/\'', 'correct' => false],
                    ['text' => 'path and \'@framework/views/\'', 'correct' => false],
                ],
            ],

            // QUESTION 25 - Twig parent()
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following simple Twig template inheritance:

<pre><code class="language-twig">{# parent.html.twig #}
<head>
    <title>
        {% block title %}Lorem ipsum{% endblock %}
    </title>
    ...
</head>

{# child.html.twig #}
{% extends "parent.html.twig" %}
{% block title %}???{% endblock %}</code></pre>

In the child.html.twig template, which statement does ??? successfully replace to render "Lorem ipsum - Dolor Sit Amet" as the page title?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The parent() function in Twig renders the content of the parent block.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/functions/parent.html',
                'answers' => [
                    ['text' => 'parent(\'title\') - Dolor Sit Amet', 'correct' => false],
                    ['text' => '{{ parent() }} - Dolor Sit Amet', 'correct' => true],
                    ['text' => '{{ block(\'title\') ~ "- Dolor Sit Amet" }}', 'correct' => false],
                    ['text' => '{{ parent(\'title\') }} - {{ Dolor Sit Amet }}', 'correct' => false],
                ],
            ],

            // QUESTION 26 - Twig rtrim filter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following Twig filters would you apply to remove the white spaces only from the right side of the contents of a given variable?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Twig does not have a dedicated |rtrim filter. To remove whitespace only from the right side, use the |trim filter with the side parameter: |trim(side = \'right\').',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/filters/trim.html',
                'answers' => [
                    ['text' => '|rtrim', 'correct' => false],
                    ['text' => '|right_trim', 'correct' => false],
                    ['text' => '|trim', 'correct' => false],
                    ['text' => '|trim(side = \'right\')', 'correct' => true],
                ],
            ],

            // QUESTION 27 - Asset versioning
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Assets'],
                'text' => 'A default Symfony application defines the following asset configuration:

<pre><code class="language-yaml"># config/packages/framework.yaml
framework:
    assets:
        version: \'v2\'
        version_format: \'%%s?version=%%s\'
        packages:
            docs:
                base_path: /docs/pdf</code></pre>

A Twig template has the following code:

{{ asset(\'terms_and_conditions.pdf\', \'docs\') }}

What will be the link generated by the above Twig snippet?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The asset() function generates a path using the package\'s base_path and applies the version_format with the configured version string.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/configuration/framework.html#assets',
                'answers' => [
                    ['text' => '/docs/pdf/v2/terms_and_conditions.pdf', 'correct' => false],
                    ['text' => '/docs/pdf/terms_and_conditions.pdf?version=v2', 'correct' => true],
                    ['text' => '/v2/docs/pdf/terms_and_conditions.pdf', 'correct' => false],
                    ['text' => '/docs/pdf/terms_and_conditions.pdf', 'correct' => false],
                    ['text' => 'terms_and_conditions.pdf?version=v2', 'correct' => false],
                ],
            ],

            // QUESTION 28 - Twig block from template
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which will be the result of executing this Twig snippet?

<code>{{ block("footer", "base.html.twig") }}</code>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The block() function with two arguments renders the content of the specified block from the given template.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/functions/block.html',
                'answers' => [
                    ['text' => 'It renders the contents of the footer block. If that block is undefined, it renders the contents of the base.html.twig template as a fallback content.', 'correct' => false],
                    ['text' => 'It renders the contents of the footer block (the second argument base.html.twig is ignored).', 'correct' => false],
                    ['text' => 'It renders the contents of the footer block from the base.html.twig template.', 'correct' => true],
                    ['text' => 'This code throws an exception because block() doesn\'t accept more than 1 argument.', 'correct' => false],
                ],
            ],

            // QUESTION 29 - Twig include shorthand
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Is the following a valid Twig statement that won\'t produce any error? (consider that the variable title is defined in the template and the some_template.html.twig exists)

<code>{% include \'some_template.html.twig\' with { title } only %}</code>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'This syntax is valid in Twig 3. { title } is equivalent to { \'title\': title } — it is a shorthand syntax.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/tags/include.html',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // QUESTION 30 - Twig loop.index0
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following Twig snippet:
<pre><code class="language-twig">{% set result = null %}
{% for i in 1..5 %}
    {% set result = result + loop.index0 %}
{% endfor %}

The result is: {{ result }}</code></pre>

What will be the output when rendering this template?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'loop.index0 goes from 0 to 4 for a loop with 5 iterations. Sum: 0+1+2+3+4 = 10',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/tags/for.html#the-loop-variable',
                'answers' => [
                    ['text' => 'The result is: 10', 'correct' => true],
                    ['text' => 'The result is: [0, 1, 3, 6, 10]', 'correct' => false],
                    ['text' => 'The result is: null', 'correct' => false],
                    ['text' => 'The result is: 15', 'correct' => false],
                    ['text' => 'The result is: 5', 'correct' => false],
                ],
            ],
        ];
    }
}
