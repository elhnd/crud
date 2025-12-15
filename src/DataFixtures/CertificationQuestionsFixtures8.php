<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 8
 */
class CertificationQuestionsFixtures8 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['certification', 'questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures7::class];
    }

    public function load(ObjectManager $manager): void
    {
        $symfonyRepo = $manager->getRepository(Category::class);
        $symfony = $symfonyRepo->findOneBy(['name' => 'Symfony']);
        $php = $symfonyRepo->findOneBy(['name' => 'PHP']);

        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        $subcategories = [];

        // Load existing subcategories
        foreach ($subcategoryRepo->findAll() as $sub) {
            $key = $sub->getCategory()->getName() . ':' . $sub->getName();
            $subcategories[$key] = $sub;
        }

        // Create new subcategories if needed
        $newSubcategories = [
            ['Data Format & Types', $php],
        ];

        foreach ($newSubcategories as [$name, $category]) {
            $key = $category->getName() . ':' . $name;
            if (!isset($subcategories[$key])) {
                $subcategory = new Subcategory();
                $subcategory->setName($name);
                $subcategory->setCategory($category);
                $manager->persist($subcategory);
                $subcategories[$key] = $subcategory;
            }
        }

        $manager->flush();

        $questions = [
            // Event Dispatcher - ImmutableEventDispatcher
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Could an event be dispatched from the <code>ImmutableEventDispatcher</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, ImmutableEventDispatcher can dispatch events. It only prevents adding/removing listeners after instantiation.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/EventDispatcher/ImmutableEventDispatcher.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Mime - MessageConverter::toEmail
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mime'],
                'text' => 'Which type of <code>Message</code> can be used to <code>MessageConverter::toEmail()</code> in order to create an <code>Email</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'MessageConverter::toEmail() accepts a Message instance, not RawMessage or string.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/Mime/MessageConverter.php#L31',
                'answers' => [
                    ['text' => '<code>Message</code>', 'correct' => true],
                    ['text' => '<code>RawMessage</code>', 'correct' => false],
                    ['text' => '<code>string</code>', 'correct' => false],
                ],
            ],
            // PHP OOP - Interfaces and traits
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Is it allowed to make an interface use <em>traits</em>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'No, interfaces cannot use traits. Traits can only be used by classes.',
                'resourceUrl' => 'http://php.net/manual/fr/language.oop5.interfaces.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // HttpKernel - Container build hash
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP Kernel'],
                'text' => 'Could the build hash of the container be configured?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, the container.build_hash parameter is exposed but cannot be configured. The hash is obtained via ContainerBuilder::hash() during compilation.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/reference/configuration/kernel.html#container-build-time',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // HttpFoundation - FlashBag override
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could all <code>FlashBag</code> messages be overridden?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, FlashBag has a setAll() method that allows overriding all messages.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/HttpFoundation/Session/Flash/FlashBag.php#L127',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Clock - MockClock returns same date
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'Which <code>Clock</code> class always returns the same date?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'MockClock returns the same date unless explicitly modified via sleep() or modify().',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/MockClock.php',
                'answers' => [
                    ['text' => '<code>MockClock</code>', 'correct' => true],
                    ['text' => '<code>NativeClock</code>', 'correct' => false],
                    ['text' => '<code>MonotonicClock</code>', 'correct' => false],
                    ['text' => '<code>Clock</code>', 'correct' => false],
                ],
            ],
            // HttpClient - MockHttpClient request count
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'Could the amount of requests processed by <code>MockHttpClient</code> be accessed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, MockHttpClient provides getRequestsCount() method to access the number of requests made.',
                'resourceUrl' => 'https://symfony.com/doc/5.1/http_client.html#testing-http-clients-and-responses',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Messenger - Handler result retrieval
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'How can you retrieve a result generated by a handler?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Handler results can be retrieved using HandledStamp from the Envelope.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/messenger/handler_results.html',
                'answers' => [
                    ['text' => 'With a stamp', 'correct' => true],
                    ['text' => 'With the <code>handler.registry</code> service', 'correct' => false],
                    ['text' => 'It\'s not possible due to the asynchronous behavior of the messenger component', 'correct' => false],
                ],
            ],
            // Twig - Built-in loaders
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following classes are Twig loaders available by default?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Twig provides ArrayLoader, FilesystemLoader, and ChainLoader by default. DoctrineLoader and CacheLoader do not exist.',
                'resourceUrl' => 'https://github.com/twigphp/Twig/tree/2.x/src/Loader',
                'answers' => [
                    ['text' => 'ArrayLoader', 'correct' => true],
                    ['text' => 'FilesystemLoader', 'correct' => true],
                    ['text' => 'ChainLoader', 'correct' => true],
                    ['text' => 'DoctrineLoader', 'correct' => false],
                    ['text' => 'CacheLoader', 'correct' => false],
                ],
            ],
            // Console - Non built-in events
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Which of these events are NOT built-in Console events?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Console has COMMAND, TERMINATE, ERROR, and SIGNAL events. VIEW and HANDLE_COMMAND do not exist.',
                'resourceUrl' => 'https://symfony.com/doc/4.0/components/console/events.html',
                'answers' => [
                    ['text' => '<code>Symfony\\Component\\Console\\ConsoleEvents::VIEW</code>', 'correct' => true],
                    ['text' => '<code>Symfony\\Component\\Console\\ConsoleEvents::HANDLE_COMMAND</code>', 'correct' => true],
                    ['text' => '<code>Symfony\\Component\\Console\\ConsoleEvents::COMMAND</code>', 'correct' => false],
                    ['text' => '<code>Symfony\\Component\\Console\\ConsoleEvents::TERMINATE</code>', 'correct' => false],
                    ['text' => '<code>Symfony\\Component\\Console\\ConsoleEvents::ERROR</code>', 'correct' => false],
                ],
            ],
            // Validator - validate() return type
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Consider the following code snippet:<pre><code class="language-php">$result = $validator->validate($someObject);</code></pre>What will be the expected outcome when running this piece of code?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The validate() method returns a ConstraintViolationListInterface, not an array, boolean, or null.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Validator/Validator/ValidatorInterface.php',
                'answers' => [
                    ['text' => 'The <code>$result</code> variable will contain a valid implementation of the <code>Symfony\\Component\\Validator\\ConstraintViolationListInterface</code> interface.', 'correct' => true],
                    ['text' => 'The <code>$result</code> variable will contain an array of <code>Symfony\\Component\\Validator\\ConstraintViolation</code> instances.', 'correct' => false],
                    ['text' => 'The <code>$result</code> variable will contain a simple boolean value (<code>true</code> or <code>false</code>).', 'correct' => false],
                    ['text' => 'The <code>$result</code> variable will contain <code>null</code> because the <code>validate</code> method must always return <code>void</code>.', 'correct' => false],
                    ['text' => 'The <code>validate</code> method will throw a <code>Symfony\\Component\\Validator\\Exception\\ValidatorException</code> exception if the given object\'s state doesn\'t match its mapped validation constraint rules.', 'correct' => false],
                ],
            ],
            // PHP - Float epsilon constant
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Data Format & Types'],
                'text' => 'How is called the PHP constant representing the smallest possible number <code>n</code>, so that <code>1.0 + n != 1.0</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP_FLOAT_EPSILON represents the smallest representable positive number x, such that x + 1.0 != 1.0.',
                'resourceUrl' => 'https://www.php.net/manual/en/reserved.constants.php',
                'answers' => [
                    ['text' => '<code>PHP_FLOAT_EPSILON</code>', 'correct' => true],
                    ['text' => '<code>PHP_FLOAT_MIN</code>', 'correct' => false],
                    ['text' => '<code>PHP_FLOAT_DIG</code>', 'correct' => false],
                    ['text' => '<code>PHP_FLOAT_SMALLEST</code>', 'correct' => false],
                ],
            ],
            // Serializer - Date format context
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'How to specify the date format for a date attribute in a serialization context?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Since Symfony 5.3, you can use the #[Context] attribute or @Context annotation with DateTimeNormalizer::FORMAT_KEY.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-3-inlined-serialization-context',
                'answers' => [
                    ['text' => '<pre><code class="language-php">#[Serializer\\Context([DateTimeNormalizer::FORMAT_KEY => \'Y-m-d\'])]
public \\DateTime $date;</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">/**
 * @Serializer\\Context({ DateTimeNormalizer::FORMAT_KEY = \'Y-m-d\' })
 */
public \\DateTime $date;</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">/**
 * @Serializer\\DateFormat(\'Y-m-d\')
 */
public \\DateTime $date;</code></pre>', 'correct' => false],
                    ['text' => 'It\'s not possible', 'correct' => false],
                ],
            ],
            // Security - Token retrieval
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Could the current token be retrieved from <code>Symfony\\Component\\Security\\Core\\Security</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, the Security class provides getToken() method to retrieve the current security token.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/Security/Core/Security.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // DI - Container parameters after compilation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which of the following container parameters will be accessible after compilation?<pre><code class="language-php">$containerBuilder->setParameter(\'foo\', \'bar\');
$containerBuilder->setParameter(\'_foo\', \'bar\');
$containerBuilder->setParameter(\'.foo\', \'bar\');</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Since Symfony 6.3, parameters starting with a dot (.) are build-time parameters and are removed after compilation. Regular and underscore-prefixed parameters remain.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-dx-improvements-part-2#build-parameters-in-service-container',
                'answers' => [
                    ['text' => 'foo', 'correct' => true],
                    ['text' => '_foo', 'correct' => true],
                    ['text' => '.foo', 'correct' => false],
                ],
            ],
            // DI - Deprecated service definitions
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is it possible to flag a <em>service definition</em> as deprecated?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, since Symfony 2.8, you can mark service definitions as deprecated using the deprecated() method.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-2-8-deprecated-service-definitions',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Twig - Escape strategies
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which escape strategies are valid for HTML documents?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Valid escape strategies for HTML are: html, js, css, url, and html_attr. There is no "asset" strategy.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/escape.html',
                'answers' => [
                    ['text' => '<code>html</code>', 'correct' => true],
                    ['text' => '<code>js</code>', 'correct' => true],
                    ['text' => '<code>css</code>', 'correct' => true],
                    ['text' => '<code>url</code>', 'correct' => true],
                    ['text' => '<code>html_attr</code>', 'correct' => true],
                    ['text' => '<code>asset</code>', 'correct' => false],
                ],
            ],
            // HttpKernel - MapQueryString context
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP Kernel'],
                'text' => 'Could the serialization context be configured when using <code>MapQueryString</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, MapQueryString accepts a serializationContext parameter to configure the serialization.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-mapping-request-data-to-typed-objects',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Validator - IsFalse with '0'
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Will the following snippet throw an <code>InvalidArgumentException</code>?<pre><code class="language-php">&lt;?php

use Symfony\\Component\\Validator\\Validation;
use Symfony\\Component\\Validator\\Constraints\\IsFalse;

$expectedFalse = \'0\';

$validator = Validation::createValidator();
$violations = $validator->validate($expectedFalse, [new IsFalse()]);

if (0 !== count($violations)) {
    throw new InvalidArgumentException(\'The value is not false !\');
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'No, the IsFalse constraint accepts "0", 0, false, and null as valid false values.',
                'resourceUrl' => 'https://symfony.com/doc/2.3/reference/constraints/IsFalse.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // HttpKernel - Events order
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP Kernel'],
                'text' => 'In which order does Symfony trigger the following events?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The correct order is: kernel.request → kernel.controller → kernel.view → kernel.response → kernel.terminate',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/HttpKernel/KernelEvents.php',
                'answers' => [
                    ['text' => '1. kernel.request 2. kernel.controller 3. kernel.view 4. kernel.response 5. kernel.terminate', 'correct' => true],
                    ['text' => '1. kernel.request 2. kernel.view 3. kernel.controller 4. kernel.response 5. kernel.terminate', 'correct' => false],
                    ['text' => '1. kernel.request 2. kernel.controller 3. kernel.view 4. kernel.terminate 5. kernel.response', 'correct' => false],
                    ['text' => '1. kernel.request 2. kernel.view 3. kernel.controller 4. kernel.terminate 5. kernel.response', 'correct' => false],
                ],
            ],
            // Event Dispatcher - GenericEvent
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Could an event be dispatched without creating a custom event class?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, you can use GenericEvent or simply pass an object that is the subject of the event.',
                'resourceUrl' => 'https://symfony.com/doc/2.1/components/event_dispatcher/generic_event.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // PHP OOP - Private property visibility
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'What will be produced by the following snippet?<pre><code class="language-php">&lt;?php

class someClass {
    private $privateProperty;
    public function __construct($propertyValue)
    {
        $this->privateProperty = $propertyValue;
    }
    public function getPrivate(someClass $someOtherClass){
        return $someOtherClass->privateProperty;
    }
}

$foo = new someClass(\'foo\');
$bar = new someClass(\'bar\');

echo $bar->getPrivate($foo);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In PHP, private properties are accessible from within the same class, even on different instances. So $bar can access $foo\'s private property.',
                'resourceUrl' => 'https://secure.php.net/manual/en/language.oop5.visibility.php',
                'answers' => [
                    ['text' => '<code>foo</code>', 'correct' => true],
                    ['text' => '<code>bar</code>', 'correct' => false],
                    ['text' => '<code>Fatal error: Uncaught Error: Cannot access private property someClass::$privateProperty</code>', 'correct' => false],
                ],
            ],
            // PHP Basics - String reference
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is the output?<pre><code class="language-php">&lt;?php

$a = \'bar\';
$b = &$a[2];
$b = \'z\';
echo $a;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'References to string offsets work in PHP. Changing $b modifies the character at index 2 of $a.',
                'resourceUrl' => 'http://www.php.net/manual/en/language.references.php',
                'answers' => [
                    ['text' => 'baz', 'correct' => true],
                    ['text' => 'bar', 'correct' => false],
                    ['text' => 'ba', 'correct' => false],
                    ['text' => 'baa', 'correct' => false],
                    ['text' => 'An error is thrown', 'correct' => false],
                ],
            ],
            // DI - Container dump single file
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could the container be dumped into a single file?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the PhpDumper supports dumping the container into a single file using the as_files option set to false.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/performance.html#dump-the-service-container-into-a-single-file',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // HttpFoundation - Invalid Request creation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Which of the following is NOT a valid way to create an instance of <code>Symfony\\Component\\HttpFoundation\\Request</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'createRequestFromFactory() does not exist. Valid methods are: new Request(), Request::create(), and Request::createFromGlobals().',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#request',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$request = Request::createRequestFromFactory(/* ... */);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$request = new Request(/* ... */);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$request = Request::create(/* ... */);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$request = Request::createFromGlobals(/* ... */);</code></pre>', 'correct' => false],
                ],
            ],
            // HttpKernel - NO_AUTO_CACHE_CONTROL_HEADER
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP Kernel'],
                'text' => 'Given the <code>AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER</code> header directive is set, is an expiration date defined in the response?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, when NO_AUTO_CACHE_CONTROL_HEADER is set, Symfony will not automatically add cache control headers.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/http_cache.html#http-caching-and-user-sessions',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // HttpKernel - Service injection in controller
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP Kernel'],
                'text' => 'Given the context where <code>FooService</code> is defined as a service, is the following code valid?<pre><code class="language-php">&lt;?php

class HomeController
{
  #[Route(\'/\', name: \'home\')]
  public function __invoke(FooService $fooService): Response
  {
    // ...
  }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, the ServiceValueResolver allows injecting services into controller action arguments.',
                'resourceUrl' => 'https://symfony.com/doc/3.3/controller/argument_value_resolver.html#functionality-shipped-with-the-httpkernel',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Process - isRunning
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'],
                'text' => 'While running a process asynchronously with the <code>Process</code> component, which method would you use to check if the process is done?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Use isRunning() to check if the process is still running. When it returns false, the process is done.',
                'resourceUrl' => 'http://symfony.com/doc/2.7/components/process.html#running-processes-asynchronously',
                'answers' => [
                    ['text' => '<pre><code class="language-php">isRunning()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">isStarted()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">isAsync()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">isProcessing()</code></pre>', 'correct' => false],
                ],
            ],
            // Routing - ExpressionLanguage functions
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Which functions can be used in the <code>ExpressionLanguage</code> expression when using the <code>condition</code> option on a Route?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Since Symfony 6.1, the available functions in route conditions are service() and env().',
                'resourceUrl' => 'https://symfony.com/doc/6.1/routing.html#matching-expressions',
                'answers' => [
                    ['text' => '<code>service()</code>', 'correct' => true],
                    ['text' => '<code>env()</code>', 'correct' => true],
                    ['text' => '<code>parameter()</code>', 'correct' => false],
                    ['text' => '<code>expression()</code>', 'correct' => false],
                ],
            ],
            // Form - CSRF token field name
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'By default, what is the form field containing the CSRF token?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The default CSRF token field name is _token.',
                'resourceUrl' => 'http://symfony.com/doc/current/security/csrf.html#csrf-protection-in-symfony-forms',
                'answers' => [
                    ['text' => '_token', 'correct' => true],
                    ['text' => '_csrf', 'correct' => false],
                    ['text' => '_secret', 'correct' => false],
                    ['text' => 'ThisTokenIsNotSoSecretChangeIt', 'correct' => false],
                ],
            ],
            // Twig - Minus operator
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What is the value of <code>$render</code> with the following code?<pre><code class="language-php">$data = [
    \'first\' => 0,
    \'first-page\' => 1
];

$render = $twig->render(\'index.html.twig\', [\'page\' => 5,  \'data\' => $data]);</code></pre><pre><code class="language-twig">{# index.html.twig #}
{{ data.first-page }}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Twig, data.first-page is interpreted as data.first minus page (0 - 5 = -5), not as accessing the key "first-page".',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/templates.html#math',
                'answers' => [
                    ['text' => '<code>\'-5\'</code>', 'correct' => true],
                    ['text' => '<code>\'1\'</code>', 'correct' => false],
                    ['text' => '<code>\'0\'</code>', 'correct' => false],
                    ['text' => 'null', 'correct' => false],
                ],
            ],
            // Security - BadgeInterface and firewall
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Could the current firewall be retrieved from a <code>BadgeInterface</code> implementation?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, BadgeInterface implementations do not provide firewall information. Badges are for marking Passport capabilities.',
                'resourceUrl' => 'https://github.com/symfony/symfony/tree/5.1/src/Symfony/Component/Security/Http/Authenticator/Passport/Badge',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Twig - Valid block names
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following are valid block names in Twig?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Block names must start with a letter or underscore, followed by letters, digits, or underscores. Dots, dashes, and starting with digits are invalid.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/block.html',
                'answers' => [
                    ['text' => '_foo', 'correct' => true],
                    ['text' => 'foo_bar', 'correct' => true],
                    ['text' => 'foo123', 'correct' => true],
                    ['text' => 'foo.bar', 'correct' => false],
                    ['text' => '.foo', 'correct' => false],
                    ['text' => '-foo', 'correct' => false],
                    ['text' => '123foo', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $questionData) {
            $this->upsertQuestion($manager, $questionData);
        }

        $manager->flush();
    }
}
