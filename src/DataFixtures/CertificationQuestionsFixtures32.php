<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 32
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures32 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures31::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found. Run AppFixtures first.');
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
            // Q1 - HttpFoundation - CHIPS cookies
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What are the correct ways to create a CHIPS cookie?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'CHIPS (Cookies Having Independent Partitioned State) cookies can be created using withPartitioned() method or the partitioned constructor parameter.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_foundation.html#setting-cookies',
                'answers' => [
                    ['text' => '<pre><code class="language-php">use Symfony\Component\HttpFoundation\Cookie;

$cookie = Cookie::create(\'cookie-name\', \'cookie-value\', \'...\')-&gt;withPartitioned();</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\HttpFoundation\Cookie;

$cookie = new Cookie(\'cookie-name\', \'cookie-value\', \'...\', partitioned: true);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\HttpFoundation\Chips;

$cookie = new Chips(\'cookie-name\', \'cookie-value\', \'...\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\HttpFoundation\Cookie;

$cookie = Cookie::create(\'cookie-name\', \'cookie-value\', \'...\')-&gt;asChips();</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\HttpFoundation\Cookie;

$cookie = new Cookie(\'cookie-name\', \'cookie-value\', \'...\', chips: true);</code></pre>', 'correct' => false],
                ],
            ],

            // Q2 - PHP Basics - Closures and callables
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Functions'],
                'text' => 'From PHP 8.1, how can this code snippet be replaced?
<pre><code class="language-php">$callable = Closure::fromCallable(\'strtoupper\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP 8.1 introduced first-class callable syntax using the ... operator, allowing $callable = strtoupper(...); as a shorthand for Closure::fromCallable().',
                'resourceUrl' => 'https://www.php.net/manual/en/functions.first_class_callable_syntax.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$callable = strtoupper(...);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$callable = \from_callable(\'strtoupper\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$callable = \from_callable(strtoupper(...));</code></pre>', 'correct' => false],
                    ['text' => 'It can\'t', 'correct' => false],
                ],
            ],

            // Q3 - Security - CSRF token generation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Could a csrf token be generated in the template rather than in the form?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, Symfony provides the csrf_token() Twig function to generate CSRF tokens directly in templates.',
                'resourceUrl' => 'https://symfony.com/doc/2.1/reference/twig_reference.html#functions',
                'answers' => [
                    ['text' => 'Yes, using <code>csrf_token()</code>', 'correct' => true],
                    ['text' => 'Yes, using <code>csrf_create_token()</code>', 'correct' => false],
                    ['text' => 'Yes, using <code>generate_token</code>', 'correct' => false],
                    ['text' => 'It\'s not possible', 'correct' => false],
                    ['text' => 'Yes, using <code>generate_csrf_token()</code>', 'correct' => false],
                ],
            ],

            // Q4 - PHP Basics - PHP and HTTP
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'How would you access the data sent to your PHP server using the PUT HTTP method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP does not have a $_PUT superglobal. Data sent via PUT must be read from the php://input stream.',
                'resourceUrl' => 'http://php.net/manual/en/features.file-upload.put-method.php',
                'answers' => [
                    ['text' => 'Using the <code>php://input</code> stream', 'correct' => true],
                    ['text' => 'It is not possible', 'correct' => false],
                    ['text' => 'Using <code>$HTTP_PUT_VARS</code>', 'correct' => false],
                    ['text' => 'Using <code>$_PUT</code>', 'correct' => false],
                    ['text' => 'Using <code>$_POST</code>', 'correct' => false],
                ],
            ],

            // Q5 - DI - Required services
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could services be tagged as always required when bootstrapping the container?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, using the container.hot_path tag, services can be marked as always required during container bootstrapping.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/reference/dic_tags.html#container-hot-path',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - Security - Security usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Could the current user be retrieved from <code>Symfony\Component\Security\Core\Security</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the Security class provides a getUser() method to retrieve the current authenticated user.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/security.html#securing-other-services',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q7 - Intl - Language usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Intl'],
                'text' => 'Could the alpha3 code of a language be retrieved?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the Languages class in the Intl component provides methods to retrieve alpha3 codes for languages.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/Intl/Languages.php#L72',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q8 - Twig - Operator precedence
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In Twig, which of the following operators has the <strong>highest</strong> precedence?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Twig, the filter operator | has the highest precedence among the listed operators.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/templates.html#expressions',
                'answers' => [
                    ['text' => '<code>|</code> (filters)', 'correct' => true],
                    ['text' => '<code>in</code>', 'correct' => false],
                    ['text' => '<code>and</code>', 'correct' => false],
                    ['text' => '<code>==</code>', 'correct' => false],
                    ['text' => '<code>or</code>', 'correct' => false],
                    ['text' => '<code>&lt;=&gt;</code>', 'correct' => false],
                ],
            ],

            // Q9 - PHP Arrays - Array functions
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays & Collections'],
                'text' => 'How do you remove an element with the key <code>0</code> from the array <code>$numbers</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'unset($numbers[0]) removes the element at key 0. array_shift removes the first element but reindexes, array_pop removes the last element.',
                'resourceUrl' => 'https://php.net/manual/en/function.unset.php',
                'answers' => [
                    ['text' => '<code>unset($numbers[0]);</code>', 'correct' => true],
                    ['text' => '<code>array_shift($numbers);</code>', 'correct' => false],
                    ['text' => '<code>array_pop($numbers);</code>', 'correct' => false],
                    ['text' => '<code>$numbers[0] = null;</code>', 'correct' => false],
                ],
            ],

            // Q10 - FrameworkBundle - Template controller
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the main purpose of the built-in <code>Symfony\Bundle\FrameworkBundle\Controller:TemplateController</code> controller?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'TemplateController is designed to render templates that don\'t require any controller logic, such as static pages.',
                'resourceUrl' => 'https://symfony.com/doc/current/cookbook/templating/render_without_controller.html',
                'answers' => [
                    ['text' => 'Render templates that do not require a controller, such as static pages.', 'correct' => true],
                    ['text' => 'Render custom error templates.', 'correct' => false],
                    ['text' => 'Extract translation keys/strings from templates.', 'correct' => false],
                    ['text' => 'Provide information about the template being rendered for the profiler.', 'correct' => false],
                ],
            ],

            // Q11 - Form - Types
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Among the following, which one is not a built-in form type?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'IbanType is not a built-in Symfony form type. PasswordType, NumberType, and MoneyType are all built-in types.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/forms/types.html',
                'answers' => [
                    ['text' => '<code>IbanType</code>', 'correct' => true],
                    ['text' => '<code>PasswordType</code>', 'correct' => false],
                    ['text' => '<code>NumberType</code>', 'correct' => false],
                    ['text' => '<code>MoneyType</code>', 'correct' => false],
                ],
            ],

            // Q12 - Twig - Node class property
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'When writing a <code>Twig_Test</code>, what is a <code>node_class</code> for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The node_class option allows the test to be compiled into PHP primitives instead of calling a PHP callable at runtime.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/advanced.html#tests',
                'answers' => [
                    ['text' => 'The given test will be compiled into PHP primitives.', 'correct' => true],
                    ['text' => 'The given test will use a semantic validation in addition to the basic evaluation.', 'correct' => false],
                    ['text' => 'The given test will rely on a custom <code>Twig_NodeVisitorInterface</code>.', 'correct' => false],
                    ['text' => 'The <code>node_class</code> is a mandatory option to get defined in a <code>Twig_Environment</code>.', 'correct' => false],
                ],
            ],

            // Q13 - FrameworkBundle - Route debug
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'Given the following output, which command could be used to format it?
<pre><code class="language-xml">&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;route name="home" class="Symfony\Component\Routing\Route"&gt;
    &lt;path regex="{^/(?P&amp;lt;id&amp;gt;[^/]++)?$}sDu"&gt;/{id}&lt;/path&gt;
    &lt;defaults&gt;
    &lt;default key="id"&gt;5&lt;/default&gt;
        &lt;default key="_controller"&gt;App\Controller\HomeController&lt;/default&gt;
    &lt;/defaults&gt;
    &lt;options&gt;
      &lt;option key="compiler_class"&gt;Symfony\Component\Routing\RouteCompiler&lt;/option&gt;
      &lt;option key="utf8"&gt;true&lt;/option&gt;
    &lt;/options&gt;
&lt;/route&gt;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The debug:router command with a route name and --format xml option outputs route details in XML format.',
                'resourceUrl' => 'https://symfony.com/doc/6.0/routing.html#debugging-routes',
                'answers' => [
                    ['text' => '<pre><code class="language-bash">bin/console debug:router home --format xml</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-bash">bin/console router:format home --format xml</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-bash">bin/console router:match /1 --format xml</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-bash">bin/console router:debug home --format xml</code></pre>', 'correct' => false],
                ],
            ],

            // Q14 - FrameworkBundle - Debug command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What does the command <code>debug:container router</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The debug:container command with a service name displays information about that specific service, not the routes.',
                'resourceUrl' => 'http://symfony.com/doc/current/book/service_container.html#debugging-services',
                'answers' => [
                    ['text' => 'Displays information for the service router', 'correct' => true],
                    ['text' => 'Displays the configured routes', 'correct' => false],
                ],
            ],

            // Q15 - Expression Language - Registering Functions
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'What are the possibles ways to register new functions in <code>Symfony\Component\ExpressionLanguage\ExpressionLanguage</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Functions can be registered using register(), addFunction(), or registerProvider() methods.',
                'resourceUrl' => 'https://symfony.com/doc/2.6/components/expression_language/extending.html',
                'answers' => [
                    ['text' => 'By calling the <code>register()</code> method.', 'correct' => true],
                    ['text' => 'By calling the <code>addFunction()</code> method.', 'correct' => true],
                    ['text' => 'By calling the <code>registerProvider()</code> method.', 'correct' => true],
                    ['text' => 'By calling the <code>createFunction()</code> method.', 'correct' => false],
                    ['text' => 'By calling the <code>setFunctions()</code> method.', 'correct' => false],
                ],
            ],

            // Q16 - FrameworkBundle - Container
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the command to display the debug information of the container?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The debug:container command displays information about all services registered in the container.',
                'resourceUrl' => 'http://symfony.com/doc/current/book/service_container.html#debugging-services',
                'answers' => [
                    ['text' => '<code>debug:container</code>', 'correct' => true],
                    ['text' => '<code>container:info</code>', 'correct' => false],
                    ['text' => '<code>container:debug</code>', 'correct' => false],
                    ['text' => '<code>debug:services</code>', 'correct' => false],
                    ['text' => '<code>services:debug</code>', 'correct' => false],
                ],
            ],

            // Q17 - Doctrine DBAL - Extending
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'It is possible to add the support of new databases?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, Doctrine DBAL is designed to be extensible and supports adding new database platforms.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/supporting-other-databases.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q18 - HTTP - Status code
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'What is the status code for <strong>Gone</strong>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'HTTP 410 Gone indicates that the resource is no longer available and will not be available again.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2616#section-10.4.11',
                'answers' => [
                    ['text' => '<code>410</code>', 'correct' => true],
                    ['text' => '<code>403</code>', 'correct' => false],
                    ['text' => '<code>409</code>', 'correct' => false],
                    ['text' => '<code>411</code>', 'correct' => false],
                    ['text' => '<code>404</code>', 'correct' => false],
                ],
            ],

            // Q19 - HTTP - Stale response
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Could a stale response be marked as reusable when an origin server responds with an error (<code>500</code>, <code>502</code>, <code>503</code> or <code>504</code>)?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, using the stale-if-error Cache-Control directive, a stale response can be served when the origin server returns these error codes.',
                'resourceUrl' => 'https://datatracker.ietf.org/doc/html/rfc5861#section-4',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q20 - Messenger - Handler results
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'How can you retrieve a result generated by a handler?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Handler results can be retrieved using the HandledStamp that is added to the envelope after handling.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/messenger/handler_results.html',
                'answers' => [
                    ['text' => 'With a stamp', 'correct' => true],
                    ['text' => 'With the <code>handler.registry</code> service', 'correct' => false],
                    ['text' => 'It\'s not possible due to the asyncronous behavior of the messenger component', 'correct' => false],
                ],
            ],

            // Q21 - PHP OOP - Properties
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'How would one access the <code>$a</code> property from within the commented part of the following code?
<pre><code class="language-php">&lt;?php

class Foo
{
    public $a;

    public function __construct()
    {
        // ...
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'In PHP, instance properties are accessed using $this->propertyName syntax.',
                'resourceUrl' => 'http://php.net/manual/en/language.oop5.properties.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$this-&gt;a;</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">self-&gt;$a;</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$self-&gt;a;</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">self::$a;</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$this-&gt;$a;</code></pre>', 'correct' => false],
                ],
            ],

            // Q22 - PHP Basics - Throwable interface
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Is the following exception class valid?
<pre><code class="language-php">class MyException implements Throwable
{
  private $message;
  private $code;
  private $file;
  private $line;
  private $trace;
  private $previous;

  public function __construct($message, $code, $file, $line, array $trace, Throwable $previous)
  {
    $this-&gt;message = $message;
    $this-&gt;code = $code;
    $this-&gt;file = $file;
    $this-&gt;line = $line;
    $this-&gt;trace = $trace;
    $this-&gt;throwable = $throwable;
  }

  public function getMessage() { return $this-&gt;message; }
  public function getCode() { return $this-&gt;code; }
  public function getFile() { return $this-&gt;file; }
  public function getLine() { return $this-&gt;line; }
  public function getTrace() { return $this-&gt;trace; }
  public function getTraceAsString() { return serialize($this-&gt;trace); }
  public function getPrevious() { return $this-&gt;previous; }
  public function __toString() { return sprintf(\'%d: %s\', $this-&gt;code, $this-&gt;message); }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, you cannot implement Throwable directly. You must extend Exception or Error instead.',
                'resourceUrl' => 'https://www.php.net/manual/en/class.throwable.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q23 - HttpKernel - Request parameter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the name of the special request attribute used by the <code>RouterListener</code> object to know which callable to invoke through the <code>HttpKernel</code> engine?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The _controller attribute is used by the HttpKernel to determine which controller should handle the request.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/components/http_kernel.html#1-the-kernel-request-event',
                'answers' => [
                    ['text' => '<code>_controller</code>', 'correct' => true],
                    ['text' => '<code>_action</code>', 'correct' => false],
                    ['text' => '<code>_request</code>', 'correct' => false],
                    ['text' => '<code>_route</code>', 'correct' => false],
                ],
            ],

            // Q24 - Validator - ConstraintViolationList implementation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Could <code>ConstraintViolationListInterface</code> be implemented without implementing <code>__toString()</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, since Symfony 6.1, the __toString() method is part of the ConstraintViolationListInterface.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.1.md#validator',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q25 - DI - Synthetic services
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is the following code valid when registering a synthetic service?
<pre><code class="language-php">// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $configurator) {
    $services = $configurator-&gt;services();

    $services-&gt;set(\'app.synthetic_service\')
        -&gt;synthetic();
};</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, this is the correct way to register a synthetic service using the PHP configuration format.',
                'resourceUrl' => 'https://symfony.com/doc/5.0/service_container/synthetic_services.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q26 - Translation - Pluralization interval
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'Which of the following are <strong>not</strong> valid interval notations?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Valid intervals use -Inf/Inf (not +Inf), and Inf cannot be at the start of an interval. {0,10,100,1000,Inf} is also invalid as Inf cannot be in a set.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/components/translation/usage.html#explicit-interval-pluralization',
                'answers' => [
                    ['text' => '<code>]Inf, 0]</code>', 'correct' => true],
                    ['text' => '<code>{0,10,100,1000,Inf}</code>', 'correct' => true],
                    ['text' => '<code>]1,Inf[</code>', 'correct' => false],
                    ['text' => '<code>]-Inf,Inf[</code>', 'correct' => false],
                    ['text' => '<code>[1,+Inf[</code>', 'correct' => false],
                ],
            ],

            // Q27 - Event Dispatcher - Event aliases
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

namespace App;

use App\Event\MyCustomEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\AddEventAliasesPass;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
  protected function build(ContainerBuilder $container)
  {
    $container-&gt;addCompilerPass(new AddEventAliasesPass([
      MyCustomEvent::class =&gt; \'my_custom_event\',
    ]));
  }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, AddEventAliasesPass can be used to register event aliases in the kernel\'s build method.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/event_dispatcher.html#event-aliases',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q28 - Translation - Translator constructor
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'What is the first argument of the constructor of <code>Symfony\Component\Translation\Translator</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The first argument of the Translator constructor is the locale string.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Translation/Translator.php#L72',
                'answers' => [
                    ['text' => 'The locale', 'correct' => true],
                    ['text' => 'The translation directory', 'correct' => false],
                    ['text' => 'A translator provider', 'correct' => false],
                    ['text' => 'A translator loader', 'correct' => false],
                ],
            ],

            // Q29 - Form - ChoiceType choice_attr option
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which types are allowed for the <code>choice_attr</code> option of the <code>Symfony\Component\Form\Extension\Core\Type\ChoiceType</code> form type?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The choice_attr option accepts an array, a callable, or a string (for attribute name lookup).',
                'resourceUrl' => 'https://symfony.com/doc/2.7/reference/forms/types/choice.html#choice-attr',
                'answers' => [
                    ['text' => '<code>array</code>', 'correct' => true],
                    ['text' => '<code>callable</code>', 'correct' => true],
                    ['text' => '<code>string</code>', 'correct' => true],
                    ['text' => '<code>boolean</code>', 'correct' => false],
                    ['text' => '<code>integer</code>', 'correct' => false],
                ],
            ],

            // Q30 - Yaml - Date Handling
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'],
                'text' => 'What will be stored in <code>$yaml</code> with the following code:
<pre><code class="language-php">&lt;?php

use Symfony\Component\Yaml\Yaml;

$yaml = Yaml::parse(\'1983-07-01\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Yaml component parses dates in ISO format and converts them to Unix timestamps.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/yaml.html#date-handling',
                'answers' => [
                    ['text' => '<pre><code>425865600</code></pre>', 'correct' => true],
                    ['text' => 'An array<pre><code>array:1 [
  0 =&gt; \'1983-07-01\'
]</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>1983-07-01</code></pre>', 'correct' => false],
                    ['text' => 'A DateTime object', 'correct' => false],
                ],
            ],

            // Q31 - HttpKernel - ControllerArgumentsEvent usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Could the controller attributes be retrieved from within <code>ControllerArgumentsEvent</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 6.2, ControllerArgumentsEvent provides methods to access controller attributes.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/HttpKernel/Event/ControllerArgumentsEvent.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q32 - Yaml - Nulls elements
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'],
                'text' => 'Which of the following values are available to define a null element in YAML?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In YAML, null can be represented by ~ (tilde) or the word null.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/yaml/yaml_format.html#nulls',
                'answers' => [
                    ['text' => '<code>~</code>', 'correct' => true],
                    ['text' => '<code>null</code>', 'correct' => true],
                    ['text' => '<code>false</code>', 'correct' => false],
                    ['text' => '<code>-</code>', 'correct' => false],
                ],
            ],

            // Q33 - Security - Voters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the signature of the <code>vote()</code> method from <code>VoterInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The VoterInterface::vote() method takes token, subject, and attributes array in that order.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/Security/Core/Authorization/Voter/VoterInterface.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, $subject, array $attributes)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, $object, array $attributes)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, array $attributes, $object)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, array $attributes, $subject)</code></pre>', 'correct' => false],
                ],
            ],

            // Q34 - DI - Service Decoration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is true about service decoration?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Service decoration uses the decorator pattern which wraps the original service without modifying it. The #[AsDecorator] attribute can be used since Symfony 6.1.',
                'resourceUrl' => 'https://symfony.com/doc/6.1/service_container/service_decoration.html',
                'answers' => [
                    ['text' => 'It can be done through the <code>#[AsDecorator]</code> attribute.', 'correct' => true],
                    ['text' => 'It doesn\'t affect the behavior of the original service.', 'correct' => true],
                    ['text' => 'It changes the behavior of the original service.', 'correct' => false],
                    ['text' => 'Final classes can\'t be decorated.', 'correct' => false],
                ],
            ],

            // Q35 - DI - FrozenParameterBag usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which exception is thrown when removing a parameter from a <code>FrozenParameterBag</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'FrozenParameterBag throws a LogicException when attempting to modify it (add, set, or remove parameters).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php',
                'answers' => [
                    ['text' => '<code>LogicException</code>', 'correct' => true],
                    ['text' => '<code>InvalidArgumentException</code>', 'correct' => false],
                    ['text' => '<code>RuntimeException</code>', 'correct' => false],
                    ['text' => '<code>BadMethodCallException</code>', 'correct' => false],
                ],
            ],

            // Q36 - Twig - Code interpretation (range)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What is the output of the following twig code?
<pre><code class="language-twig">{% for i in range(1, 10, 2) %}
    {{ i }}{% if not loop.last %},{% endif %}
{% endfor %}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The range function generates a sequence from 1 to 10 with step 2, resulting in 1,3,5,7,9.',
                'resourceUrl' => 'http://twig.symfony.com/doc/functions/range.html',
                'answers' => [
                    ['text' => '1,3,5,7,9', 'correct' => true],
                    ['text' => '2,4,6,8,10', 'correct' => false],
                    ['text' => '1,10,1,10', 'correct' => false],
                    ['text' => '1,2,3,4,5,6,7,8,9,10', 'correct' => false],
                ],
            ],

            // Q37 - Twig - Twig Internals
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following Twig internal objects is responsible for transforming an AST (Abstract Syntax Tree) into PHP code?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The Compiler is responsible for transforming the AST into executable PHP code.',
                'resourceUrl' => 'http://twig.symfony.com/doc/internals.html',
                'answers' => [
                    ['text' => 'The Compiler', 'correct' => true],
                    ['text' => 'The Environment', 'correct' => false],
                    ['text' => 'The Parser', 'correct' => false],
                    ['text' => 'The Lexer', 'correct' => false],
                ],
            ],

            // Q38 - Validator - Validation constraints
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Which of the following elements can contain validation constraints?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Validation constraints can be applied to public properties, private/protected properties, classes, and public getters/issers.',
                'resourceUrl' => 'https://symfony.com/doc/6.0/validation.html#getters',
                'answers' => [
                    ['text' => 'Public properties', 'correct' => true],
                    ['text' => 'Classes', 'correct' => true],
                    ['text' => 'Public getters/issers', 'correct' => true],
                    ['text' => 'Private and protected properties', 'correct' => true],
                    ['text' => 'Private and protected getters/issers', 'correct' => false],
                ],
            ],

            // Q39 - Twig - Strict Variables Mode
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following Twig code snippet:
<pre><code class="language-twig">The {{ color }} car!</code></pre>
<p>What will be the result of evaluating this template without passing it a <code>color</code> variable when the <code>strict_variables</code> global setting is on?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When strict_variables is enabled, Twig throws a Twig_Error_Runtime exception for undefined variables.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/api.html#environment-options',
                'answers' => [
                    ['text' => 'Twig will raise a <code>Twig_Error_Runtime</code> exception preventing the template from being evaluated.', 'correct' => true],
                    ['text' => 'The template will be succesfully evaluated and the string <code>The empty car!</code> will be displayed in the web browser.', 'correct' => false],
                    ['text' => 'The template will be partially evaluated and the string <code>The</code> will be displayed in the web browser.', 'correct' => false],
                    ['text' => 'The template will be succesfully evaluated and the string <code>The  car!</code> will be displayed in the web browser.', 'correct' => false],
                ],
            ],

            // Q40 - Event Dispatcher - Design pattern (duplicate check - already in fixtures but included for completeness)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Which design pattern does the <code>EventDispatcher</code> component implement?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The EventDispatcher component implements the Mediator pattern, allowing decoupled communication between objects.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/event_dispatcher/introduction.html#introduction',
                'answers' => [
                    ['text' => 'Mediator', 'correct' => true],
                    ['text' => 'Adapter', 'correct' => false],
                    ['text' => 'Factory Method', 'correct' => false],
                    ['text' => 'Strategy', 'correct' => false],
                ],
            ],
        ];
    }
}
