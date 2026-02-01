<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 6
 */
class CertificationQuestionsFixtures6 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures5::class];
    }

    public function load(ObjectManager $manager): void
    {
        $symfonyRepo = $manager->getRepository(Category::class);
        $symfony = $symfonyRepo->findOneBy(['name' => 'Symfony']);
        $php = $symfonyRepo->findOneBy(['name' => 'PHP']);

        // Load existing subcategories from AppFixtures
        $subcategories = $this->loadSubcategories($manager);

        $questions = [
            // Security - Built-in roles (Symfony 6.0+)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which of the following "roles" are built-in (since Symfony 6.0)?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Since Symfony 6.0, IS_AUTHENTICATED_REMEMBERED and IS_AUTHENTICATED_FULLY were removed. The built-in roles are now IS_AUTHENTICATED, IS_REMEMBERED, IS_IMPERSONATOR, and PUBLIC_ACCESS.',
                'resourceUrl' => 'http://symfony.com/doc/6.0/security.html#checking-to-see-if-a-user-is-logged-in-is-authenticated-fully',
                'answers' => [
                    ['text' => '<code>IS_AUTHENTICATED</code>', 'correct' => true],
                    ['text' => '<code>IS_REMEMBERED</code>', 'correct' => true],
                    ['text' => '<code>IS_IMPERSONATOR</code>', 'correct' => true],
                    ['text' => '<code>IS_AUTHENTICATED_REMEMBERED</code>', 'correct' => false],
                    ['text' => '<code>IS_AUTHENTICATED_FULLY</code>', 'correct' => false],
                    ['text' => '<code>IS_FULLY_AUTHENTICATED</code>', 'correct' => false],
                    ['text' => '<code>IS_AUTHENTICATED_INTERACTIVE</code>', 'correct' => false],
                ],
            ],
            // PHP OOP - Traits in interfaces
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Consider the following PHP code:<pre><code class="language-php">trait FooTrait
{
    private function foo()
    {
        return \'Hello\';
    }
}

interface BarInterface
{
    use FooTrait;

    public function bar();
}

class Demo implements BarInterface
{
    public function bar()
    {
        return \'World!\';
    }

    public function main()
    {
        return $this->foo().\' \'.$this->bar();
    }
}

$demo = new Demo();
echo $demo->main();</code></pre>What will be the outcome of executing this code snippet?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Traits cannot be used inside interfaces. PHP will raise a Fatal Error.',
                'resourceUrl' => 'http://php.net/manual/fr/language.oop5.traits.php',
                'answers' => [
                    ['text' => 'PHP will raise a <em>Fatal Error</em> telling traits are not allowed inside interfaces.', 'correct' => true],
                    ['text' => 'PHP will raise a <em>Fatal Error</em> telling method <em>foo()</em> cannot be declared <em>private</em> in the <em>FooTrait</em> trait.', 'correct' => false],
                    ['text' => 'The script will successfully run and output the string <code>Hello World!</code>.', 'correct' => false],
                ],
            ],
            // Console - Cursor position restoration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Could the <code>Cursor</code> position be restored?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, the Cursor class provides savePosition() and restorePosition() methods.',
                'resourceUrl' => 'https://symfony.com/doc/5.2/components/console/helpers/cursor.html#using-the-cursor',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // PHP Basics - Global variables
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following code snippet:<pre><code class="language-php">$a = 20;

function my_function($b)
{
    $a = 30;
    global $a, $c;

    return $c = ($b + $a);
}

print my_function(40) + $c;</code></pre>What does this script output when it\'s executed with PHP?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The local $a=30 is overwritten by global $a=20 when "global $a" is declared. The function returns 40+20=60, and $c is also set to 60 globally. Final output: 60+60=120.',
                'resourceUrl' => 'http://php.net/manual/en/language.variables.scope.php',
                'answers' => [
                    ['text' => '<code>120</code>', 'correct' => true],
                    ['text' => '<code>110</code>', 'correct' => false],
                    ['text' => '<code>70</code>', 'correct' => false],
                    ['text' => '<code>60</code>', 'correct' => false],
                    ['text' => 'An error saying something like <code>Undefined variable: ...</code>.', 'correct' => false],
                ],
            ],
            // Clock - Available classes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'Which classes are available in the <code>Clock</code> Component?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Clock component provides MockClock, MonotonicClock, and NativeClock. There is no class simply named "Clock".',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/MockClock.php',
                'answers' => [
                    ['text' => '<code>MockClock</code>', 'correct' => true],
                    ['text' => '<code>MonotonicClock</code>', 'correct' => true],
                    ['text' => '<code>NativeClock</code>', 'correct' => true],
                    ['text' => '<code>Clock</code>', 'correct' => false],
                ],
            ],
            // Translation - addLoader signature
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'What is the way to add a loader to the translator?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The addLoader method requires a format string as the first argument and the loader instance as the second.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/components/translation/usage.html',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$translator->addLoader(\'array\', new ArrayLoader());</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$translator->addLoader(new ArrayLoader());</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$translator->addArrayLoader(new ArrayLoader());</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$translator->addLoader(new ArrayLoader(), \'array\');</code></pre>', 'correct' => false],
                ],
            ],
            // DI - Enum env var processor
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is the purpose of the <code>enum</code> environment variable processor, used as shown:<pre><code class="language-yaml"># config/services.yaml
parameters:
    typed_env: \'%env(enum:App\\Enum\\Environment:APP_ENV)%\'</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The enum processor converts an environment variable value to either a BackedEnum case value or a UnitEnum case string representation.',
                'resourceUrl' => 'https://symfony.com/doc/6.2/configuration/env_var_processors.html#built-in-environment-variable-processors',
                'answers' => [
                    ['text' => 'It tries to convert a <code>BackedEnum</code> case to its corresponding value, or a <code>UnitEnum</code> case to a string', 'correct' => true],
                    ['text' => 'It tries to convert a <code>BackedEnum</code> case to its corresponding value', 'correct' => false],
                    ['text' => 'This is an alias for <code>env(const:...)</code>', 'correct' => false],
                ],
            ],
            // Routing - Default values syntax
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Which of the following allows to give a default value to a route parameter?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Route parameter defaults can be set via the defaults option, PHP method default values, or inline syntax {param?default}.',
                'resourceUrl' => 'https://symfony.com/doc/6.3/routing.html',
                'answers' => [
                    ['text' => '<pre><code class="language-php">#[Route(\'/blog/posts/page/{page}\', name:"paginated_posts", defaults:[\'page\' => 1])]
public function postPages(int $page): Response</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">#[Route(\'/blog/posts/page/{page}\', name:"paginated_posts")]
public function postPages(int $page=1): Response</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">#[Route(\'/blog/posts/page/{page?1}\', name:"paginated_posts")]
public function postPages(int $page): Response</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">#[Route(\'/blog/posts/page/{page}\', name:"paginated_posts", page:[\'defaults\' => 1])]
public function postPages(int $page): Response</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">#[Route(\'/blog/posts/page/{page?default=1}\', name:"paginated_posts")]
public function postPages(int $page): Response</code></pre>', 'correct' => false],
                ],
            ],
            // PropertyAccess - Disabling __set()
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'Could the usage of <code>__set()</code> method be disabled?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, PropertyAccessorBuilder allows disabling magic method usage via disableMagicSet().',
                'resourceUrl' => 'https://symfony.com/doc/5.2/components/property_access.html#enable-other-features',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Serializer - Class-based Groups attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Given the following class definition:<pre><code class="language-php">#[Groups([\'show_product\'])]
class Product
{
    // ...

    #[Groups([\'list_product\'])]
    private string $name;

    private string $description;
}</code></pre>Which groups the property <code>name</code> belongs to?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Since Symfony 6.4, the #[Groups] attribute can be applied to classes. Properties inherit class-level groups and can add their own.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-4-serializer-improvements#class-based-group-attributes',
                'answers' => [
                    ['text' => '<code>list_product</code> and <code>show_product</code>', 'correct' => true],
                    ['text' => '<code>list_product</code>', 'correct' => false],
                    ['text' => 'This code is not valid, <code>#[Groups]</code> is only allowed on properties and methods', 'correct' => false],
                ],
            ],
            // Twig - constant() function
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What is the correct way to display the value of a PHP constant?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The constant() function is used with escaped backslashes in the namespace.',
                'resourceUrl' => 'http://twig.symfony.com/doc/functions/constant.html',
                'answers' => [
                    ['text' => '<pre><code class="language-twig">{{ constant(\'Namespace\\\\Classname::CONSTANT_NAME\') }}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-twig">{{ Namespace\\\\Classname::CONSTANT_NAME }}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{{ constant(\'Namespace\\Classname::CONSTANT_NAME\') }}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{{ Namespace\\Classname::CONSTANT_NAME }}</code></pre>', 'correct' => false],
                ],
            ],
            // Process - addOutput method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'],
                'text' => 'Is it possible to explicitly add lines to the process output from the main script?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the Process class has an addOutput() method that allows adding content to the process output.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/Process/Process.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // HttpFoundation - overrideGlobals
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Which global variables are overridden by the <code>overrideGlobals</code> method of the <code>Request</code> class?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The overrideGlobals method overrides $_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, and $_REQUEST, but NOT $_SESSION.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/HttpFoundation/Request.php',
                'answers' => [
                    ['text' => '<code>$_GET</code>', 'correct' => true],
                    ['text' => '<code>$_POST</code>', 'correct' => true],
                    ['text' => '<code>$_COOKIE</code>', 'correct' => true],
                    ['text' => '<code>$_FILES</code>', 'correct' => true],
                    ['text' => '<code>$_SERVER</code>', 'correct' => true],
                    ['text' => '<code>$_REQUEST</code>', 'correct' => true],
                    ['text' => '<code>$_SESSION</code>', 'correct' => false],
                ],
            ],
            // Event Dispatcher - addListener signature
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'How can you register the <code>AcmeListener</code> to the <code>EventDispatcher</code> in order to call the <code>onFooAction</code> method when the <code>acme.action</code> event is dispatched with the following code?<pre><code class="language-php">&lt;?php

use Symfony\\Component\\EventDispatcher\\EventDispatcher;

$dispatcher = new EventDispatcher();

$listener = new AcmeListener();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The addListener method takes the event name and a callable (array with object and method name).',
                'resourceUrl' => 'http://symfony.com/doc/current/components/event_dispatcher/introduction.html#connecting-listeners',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$dispatcher->addListener(\'acme.action\', array($listener, \'onFooAction\'));</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$dispatcher->addListener(\'acme.action\', $listener, \'onFooAction\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$dispatcher->registerListener(\'acme.action\', array($listener, \'onFooAction\'));</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$dispatcher->registerListener(\'acme.action\', $listener, \'onFooAction\');</code></pre>', 'correct' => false],
                ],
            ],
            // Routing - Stateless routes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Could the usage of the session be deactivated per route?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, using the stateless option on a route disables session usage for that route.',
                'resourceUrl' => 'https://symfony.com/doc/5.1/routing.html#stateless-routes',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // OptionsResolver - Dependent default values
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:OptionsResolver'],
                'text' => 'Is it possible to define default values that depend on another option?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, using setDefault() with a closure that receives the OptionsResolver, you can access other options.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/options_resolver.html#default-values-that-depend-on-another-option',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Twig - Runtime functions/filters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Could functions and filters be defined at runtime without any overhead?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, Twig supports lazy-loading of functions and filters using registerUndefinedFunctionCallback() and registerUndefinedFilterCallback().',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/recipes.html#defining-undefined-functions-and-filters-on-the-fly',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // PHP Arrays - Multidimensional indexing
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'In the following code, what are the values required in <code>$a</code>, <code>$b</code>, <code>$c</code> and <code>$d</code> to output 40?<pre><code class="language-php">&lt;?php
$values = [
    [
        1 => 10,
        20,
        [30, [40]],
    ],
    [
        2 => 50,
        [
            [1 => 60, 0 => 70],
        ],
    ],
];

echo $values[$a][$b][$c][$d];</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Arrays without explicit keys continue from the last integer key. In the first array: 1=>10, 2=>20, 3=>[30,[40]]. So $values[0][3][1][0] = 40.',
                'resourceUrl' => 'https://php.net/array',
                'answers' => [
                    ['text' => '<code>$a = 0</code>, <code>$b = 3</code>, <code>$c = 1</code>, <code>$d = 0</code>', 'correct' => true],
                    ['text' => '<code>$a = 0</code>, <code>$b = 1</code>, <code>$c = 0</code>, <code>$d = 0</code>', 'correct' => false],
                    ['text' => '<code>$a = 1</code>, <code>$b = 3</code>, <code>$c = 1</code>, <code>$d = 0</code>', 'correct' => false],
                    ['text' => '<code>$a = 0</code>, <code>$b = 1</code>, <code>$c = 1</code>, <code>$d = 0</code>', 'correct' => false],
                ],
            ],
            // HttpKernel - KernelEvent base class
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the base class for events thrown in the HttpKernel component?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'All HttpKernel events extend Symfony\\Component\\HttpKernel\\Event\\KernelEvent.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/HttpKernel/Event/KernelEvent.php',
                'answers' => [
                    ['text' => '<code>Symfony\\Component\\HttpKernel\\Event\\KernelEvent</code>', 'correct' => true],
                    ['text' => '<code>Symfony\\Component\\HttpKernel\\BaseKernelEvent</code>', 'correct' => false],
                    ['text' => '<code>Symfony\\Component\\HttpKernel\\KernelEvent</code>', 'correct' => false],
                    ['text' => '<code>Symfony\\Component\\HttpKernel\\Event\\BaseKernelEvent</code>', 'correct' => false],
                    ['text' => '<code>Symfony\\Component\\HttpKernel\\HttpKernelEvent</code>', 'correct' => false],
                    ['text' => '<code>Symfony\\Component\\HttpKernel\\Event\\HttpKernelEvent</code>', 'correct' => false],
                ],
            ],
            // DI - Service decoration with .inner
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is the following code valid when decorating a service?<pre><code class="language-php">&lt;?php

namespace Symfony\\Component\\DependencyInjection\\Loader\\Configurator;

use App\\DecoratingMailer;
use App\\Mailer;

return function(ContainerConfigurator $configurator): void {
  $services = $configurator->services();

  $services->set(Mailer::class);

  $services->set(DecoratingMailer::class)
      ->decorate(Mailer::class)
      ->args([service(\'.inner\')])
  ;
};</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 5.1, you can use .inner as a shorthand to reference the decorated service.',
                'resourceUrl' => 'https://symfony.com/doc/5.1/service_container/service_decoration.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // HttpClient - MockHttpClient testing
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'Is it possible to simulate an HTTP request without mocking the <code>HttpClient</code> thanks to <code>createMock()</code> from PHPUnit?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony provides MockHttpClient for testing HTTP requests without PHPUnit mocks.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_client.html#testing-http-clients-and-responses',
                'answers' => [
                    ['text' => 'Yes, by using <code>Symfony\\Component\\HttpClient\\MockHttpClient</code>', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Routing - Route matching
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'In the list below, which url patterns match the following blog route definition?<pre><code class="language-php">$route = new Route(
    \'/blog/{id}/{slug}.{_format}\',
    [\'_controller\' => \'blogPost\', \'_format\' => \'html\'],
    [\'id\' => \'\\d+\', \'_format\' => \'html|json\']
);</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The route requires a numeric id, any slug, and optional format (defaults to html). The format must be html or json if provided.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/routing.html',
                'answers' => [
                    ['text' => '<code>/blog/12/blog</code>', 'correct' => true],
                    ['text' => '<code>/blog/8/my-blog-post.html</code>', 'correct' => true],
                    ['text' => '<code>/blog/30/a.html</code>', 'correct' => true],
                    ['text' => '<code>/blog/abc/my-blog-post.json</code>', 'correct' => false],
                    ['text' => '<code>/blog/10/mon-blog-post.xml</code>', 'correct' => false],
                ],
            ],
            // DI - Autoconfiguration purpose
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is the purpose of Autoconfiguration in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Autoconfiguration automatically configures (applies tags, method calls, etc.) services based on their class, attributes or interface.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html#services-autoconfigure',
                'answers' => [
                    ['text' => 'To automatically configure services based on their class, attributes or interface.', 'correct' => true],
                    ['text' => 'To automatically register services based on their class, attributes or interface.', 'correct' => false],
                    ['text' => 'To automatically decorate services based on their class, attributes or interface.', 'correct' => false],
                ],
            ],
            // PHP Basics - mysqlnd
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is <code>mysqlnd</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'mysqlnd (MySQL Native Driver) is a low-level connector that replaced libmysql as the default MySQL driver in PHP.',
                'resourceUrl' => 'http://www.php.net/mysqlnd',
                'answers' => [
                    ['text' => 'A low level connector designed to replace libmysql dependency', 'correct' => true],
                    ['text' => 'A new RDBMS like MySQL or MariaDB', 'correct' => false],
                    ['text' => 'A persistent connection to a MySQL server', 'correct' => false],
                    ['text' => 'A PHP extension adding some functions to interact with a MySQL server, like PDO', 'correct' => false],
                ],
            ],
            // Console - Helper::strlen removal
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Could you use <code>Helper::strlen()</code> to obtain the length of a string? (Symfony 6.0+)',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, Helper::strlen() was removed in Symfony 6.0. Use Helper::width() or Helper::length() instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/UPGRADE-6.0.md#console',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // HttpKernel - FragmentListener
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the aim of the <code>Symfony\\Component\\HttpKernel\\EventListener\\FragmentListener::onKernelRequest()</code> listener on <code>kernel.request</code> event?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The FragmentListener handles all URL paths starting with /_fragment as content fragments.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/HttpKernel/EventListener/FragmentListener.php',
                'answers' => [
                    ['text' => 'Handle as content fragments by this listener all URL paths starting with /_fragment.', 'correct' => true],
                    ['text' => 'Handle as content all files to let user download them.', 'correct' => false],
                    ['text' => 'Handle as content all image, css, javascript that not require security.', 'correct' => false],
                    ['text' => 'Handle as content all image, css, javascript that require security.', 'correct' => false],
                ],
            ],
            // Messenger - Doctrine transaction wrapping
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Given the context where the doctrine transport is used, could all the handlers be wrapped in a single transaction?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, using the doctrine_transaction middleware, all handlers can be wrapped in a single database transaction.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/messenger.html#middleware-for-doctrine',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Messenger - StatsCommand
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Could the amount of "to proceed" messages be displayed per transport?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the StatsCommand (messenger:stats) shows the number of pending messages per transport.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/Messenger/Command/StatsCommand.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Runtime - .env path configuration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'],
                'text' => 'Is it possible to set the path used to load the <code>.env</code> files?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the Runtime component allows configuring the dotenv_path option.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/components/runtime.html#using-options',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Filesystem - File locking
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Is it possible to write in a file while locking it?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the appendToFile() method supports file locking via its third parameter.',
                'resourceUrl' => 'https://symfony.com/doc/5.4/components/filesystem.html#appendtofile',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // HttpFoundation - Enum in request
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Can you get an Enum using <code>$request->query->get()</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, you need to use the getEnum() method instead to get an Enum from request parameters.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-enum-improvements',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Mime - DIC tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mime'],
                'text' => 'Which DIC tag is used by MimeType?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Mime component uses the mime_types tag for registering custom mime type guessers.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/Mime/DependencyInjection/AddMimeTypeGuesserPass.php#L30',
                'answers' => [
                    ['text' => '<code>mime_types</code>', 'correct' => true],
                    ['text' => '<code>mime_type</code>', 'correct' => false],
                    ['text' => '<code>mime.types</code>', 'correct' => false],
                ],
            ],
            // Twig - Built-in functions
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following is not a function?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'set is a tag, not a function. range, parent, source, and template_from_string are all functions.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/',
                'answers' => [
                    ['text' => '<code>set</code>', 'correct' => true],
                    ['text' => '<code>range</code>', 'correct' => false],
                    ['text' => '<code>parent</code>', 'correct' => false],
                    ['text' => '<code>source</code>', 'correct' => false],
                    ['text' => '<code>template_from_string</code>', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $questionData) {
            $this->upsertQuestion($manager, $questionData);
        }

        $manager->flush();
    }
}
