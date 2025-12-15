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
 * Certification-style questions - Batch 9
 */
class CertificationQuestionsFixtures9 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['certification', 'questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures8::class];
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
            ['PSR', $php],
            ['Expression Language', $symfony],
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
            // Question 1 - Console - Cursor usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Could the <code>Cursor</code> position be restored?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the Cursor helper supports saving and restoring cursor position.',
                'resourceUrl' => 'https://symfony.com/doc/5.2/components/console/helpers/cursor.html#using-the-cursor',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 2 - Security - Listener
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the listener that handles security exceptions and when appropriate, helps the user to authenticate?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'ExceptionListener handles security exceptions and can redirect to authentication.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/Security/Http/Firewall/ExceptionListener.php',
                'answers' => [
                    ['text' => 'Symfony\\Component\\Security\\Http\\Firewall\\SecurityListener', 'correct' => false],
                    ['text' => 'Symfony\\Component\\Security\\Http\\Firewall\\AuthenticationListener', 'correct' => false],
                    ['text' => 'Symfony\\Component\\Security\\Http\\Firewall\\ExceptionListener', 'correct' => true],
                    ['text' => 'Symfony\\Component\\Security\\Http\\Firewall\\AuthListener', 'correct' => false],
                ],
            ],
            // Question 3 - Routing - Route aliasing
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Could a route be aliased?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 5.4, routes can be aliased allowing to reference a route by multiple names.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-4-route-aliasing',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 4 - HttpFoundation - Accessing Request Data
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'How to access <code>$_COOKIE</code> data when using a <code>Symfony\\Component\\HttpFoundation\\Request</code> <code>$request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The cookies property is a ParameterBag containing all cookies from the request.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#accessing-request-data',
                'answers' => [
                    ['text' => '$request->getCookie()', 'correct' => false],
                    ['text' => '$request->getCookies()', 'correct' => false],
                    ['text' => '$request->cookie', 'correct' => false],
                    ['text' => '$request->cookies', 'correct' => true],
                    ['text' => '$request->getCookieData()', 'correct' => false],
                ],
            ],
            // Question 5 - DI - FrozenParameterBag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could parameters be cleared from a <code>FrozenParameterBag</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, FrozenParameterBag is immutable and throws an exception if you try to modify it.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Question 6 - Security - HTTPS enforcement
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Could the usage of HTTPS on specific paths be enforced?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, access_control supports the requires_channel option to enforce HTTPS.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/security/access_control.html#forcing-a-channel-http-https',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 7 - PHP Basics - PHP operators
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => "What is the output?\n<pre><code>&lt;?php\n\n\$a = \"0\";\n\necho strlen(\$a);\n\necho empty(\$a) ? \$a : 5;\n\necho \$a ?: 5;</code></pre>",
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'strlen("0") = 1, empty("0") is true in PHP so ternary returns $a which is "0", but wait the ternary is: if empty returns $a else 5. So 0. Then $a ?: 5 returns 5 because "0" is falsy. Output is 105.',
                'resourceUrl' => 'http://php.net/operators',
                'answers' => [
                    ['text' => '050', 'correct' => false],
                    ['text' => '105', 'correct' => true],
                    ['text' => '005', 'correct' => false],
                    ['text' => '150', 'correct' => false],
                    ['text' => '100', 'correct' => false],
                ],
            ],
            // Question 8 - Runtime - Custom runtime
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'],
                'text' => 'Could a custom runtime be created?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, you can create custom runtimes by implementing RuntimeInterface.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/components/runtime.html#create-your-own-runtime',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 9 - Console - ConsoleEvents::TERMINATE
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Is the <code>ConsoleEvents::TERMINATE</code> event dispatched when an <code>Exception</code> is thrown during the command execution?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, TERMINATE is always dispatched, even when an exception is thrown.',
                'resourceUrl' => 'https://symfony.com/doc/2.3/components/console/events.html#the-consoleevents-terminate-event',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 10 - DI - ParameterBag frozen
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could a <code>ParameterBag</code> be frozen?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, by using FrozenParameterBag which is an immutable version of ParameterBag.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 11 - PSR - PSR-1 compliance
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PSR'],
                'text' => "Is this content PSR-1 compliant?\n<pre><code>&lt;?php\nini_set('error_reporting', E_ALL);\n\nconst APP_ENV = 'dev';\nconst APP_DEBUG = 1;</code></pre>",
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, PSR-1 requires that files should either declare symbols (classes, functions, constants) or cause side-effects, but not both. ini_set is a side-effect.',
                'resourceUrl' => 'https://www.php-fig.org/psr/psr-1/',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Question 12 - Event Dispatcher - Get EventDispatcher from Event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'From an <code>Event</code> instance, is it possible to get the <code>EventDispatcher</code> instance that dispatched this event?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'No, since Symfony 3.0 the event dispatcher is passed to the listener call directly, not stored in the event.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/event_dispatcher.html#event-name-introspection',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Question 13 - Clock - MonotonicClock timezone
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'Could a <code>MonotonicClock</code> be created with a specific timezone as a string?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, MonotonicClock constructor accepts a timezone string.',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/MonotonicClock.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 14 - Routing - Route compilation exception
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Which exception is thrown when a <code>Route</code> defined with <code>/page/{_fragment}</code> cannot be compiled?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'InvalidArgumentException is thrown when a route uses reserved variable names like _fragment.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/Routing/RouteCompiler.php#L39',
                'answers' => [
                    ['text' => 'LogicException', 'correct' => false],
                    ['text' => 'RouteCompilationException', 'correct' => false],
                    ['text' => 'InvalidRouteCompilationContextException', 'correct' => false],
                    ['text' => 'InvalidArgumentException', 'correct' => true],
                    ['text' => 'RuntimeException', 'correct' => false],
                ],
            ],
            // Question 15 - Form - TelType
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which of the following sentences are true about TelType?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'TelType only renders HTML5 tel input, it does not perform validation on the phone number format.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/reference/forms/types/tel.html',
                'answers' => [
                    ['text' => 'PhoneType form field allows to use HTML5 input type tel and trigger some basic validation on the entered phone number', 'correct' => false],
                    ['text' => 'TelType form field allows to use HTML5 input type tel and trigger some basic validation on the entered phone number', 'correct' => false],
                    ['text' => 'TelType form field only allows to use HTML5 input type tel', 'correct' => true],
                    ['text' => 'PhoneType form field only allows to use HTML5 input type tel', 'correct' => false],
                ],
            ],
            // Question 16 - Twig - Escaping with raw filter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given <code>var</code> and <code>bar</code> are existing variables, among the following, which expressions are escaped?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The raw filter must be the last filter to prevent escaping. If raw is followed by upper, the result of upper will be escaped.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/raw.html',
                'answers' => [
                    ['text' => '{{ var|upper|raw }}', 'correct' => false],
                    ['text' => '{{ var|raw|upper }}', 'correct' => true],
                    ['text' => '{{ var|raw~bar }}', 'correct' => false],
                ],
            ],
            // Question 17 - Twig - Template Inheritance
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => "Is this Twig template valid?\n<pre><code>&lt;h1&gt;{{ title }}&lt;/h1&gt;\n\n{% extends 'base.html.twig' %}\n\n{% block content %}\n    &lt;p&gt;{{ content }}&lt;/p&gt;\n{% endblock %}</code></pre>",
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, the extends tag must be the first tag in the template (after comments).',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/tags/extends.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Question 18 - DI - Immutable setters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => "Given autowire is enabled, LoggerInterface is an alias for a logger service and we have the following service:\n<pre><code>class FooService\n{\n  private \$logger;\n\n  /**\n   * @required\n   */\n  public function withLogger(LoggerInterface \$logger)\n  {\n    \$foo = clone \$this;\n    \$foo->logger = \$logger;\n    return \$foo;\n  }\n}</code></pre>\nWhich sentence is true about FooService?",
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The @return static annotation is missing for the wither to work properly. Without it, the logger property remains null.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-4-3-configuring-services-with-immutable-setters',
                'answers' => [
                    ['text' => 'logger property value is a Logger instance', 'correct' => false],
                    ['text' => 'An exception will be thrown', 'correct' => false],
                    ['text' => 'logger property value is null', 'correct' => true],
                ],
            ],
            // Question 19 - PHP Basics - Predefined variables for form data
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'From which global arrays is it possible to read submitted form data?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Form data can be submitted via GET or POST methods. $_REQUEST contains data from GET, POST and COOKIE.',
                'resourceUrl' => 'http://php.net/manual/en/reserved.variables.request.php',
                'answers' => [
                    ['text' => '$_SESSION', 'correct' => false],
                    ['text' => '$_ENV', 'correct' => false],
                    ['text' => '$_REQUEST', 'correct' => true],
                    ['text' => '$_POST', 'correct' => true],
                    ['text' => '$_GET', 'correct' => true],
                    ['text' => '$_COOKIE', 'correct' => false],
                ],
            ],
            // Question 20 - Form - BirthdayType parent
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which of the following form types is the parent of the <code>Symfony\\Component\\Form\\Extension\\Core\\Type\\BirthdayType</code> form type?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'BirthdayType extends DateType, configuring years in the past by default.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/forms/types/birthday.html',
                'answers' => [
                    ['text' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\DateType', 'correct' => true],
                    ['text' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\DateTimeType', 'correct' => false],
                    ['text' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\TimeType', 'correct' => false],
                ],
            ],
            // Question 21 - HttpClient - MockHttpClient
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'Is it possible to simulate an HTTP request without mocking the <code>HttpClient</code> thanks to <code>createMock()</code> from PHPUnit?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony provides MockHttpClient specifically for testing HTTP clients without using PHPUnit mocks.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_client.html#testing-http-clients-and-responses',
                'answers' => [
                    ['text' => 'Yes, by using Symfony\\Component\\HttpClient\\MockHttpClient', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 22 - OptionsResolver - Callable without Options type-hint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:OptionsResolver'],
                'text' => "What happens if you remove the type-hint <code>Options</code> of <code>\$options</code>, in the callable below?\n<pre><code>\$resolver->setDefault('port', function (Options \$options) {\n    if ('ssl' === \$options['encryption']) {\n        return 465;\n    }\n    return 25;\n});</code></pre>",
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Without the Options type-hint, OptionsResolver cannot detect that the closure should be called with options. The callable itself becomes the default value.',
                'resourceUrl' => 'https://symfony.com/doc/6.2/components/options_resolver.html#default-values-that-depend-on-another-option',
                'answers' => [
                    ['text' => 'The callable itself will be considered as the default value', 'correct' => true],
                    ['text' => '$options will automatically be casted to a nullable array', 'correct' => false],
                    ['text' => 'A fatal error is thrown because of the lack of type-hint', 'correct' => false],
                    ['text' => 'Nothing special', 'correct' => false],
                ],
            ],
            // Question 23 - Translation - Escape percent character
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'Which of this following code is correct to use the percent character <code>%</code> in a translated string?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'To display a literal percent sign, use %%. So %percent%%% will show the parameter value followed by %.',
                'resourceUrl' => 'http://symfony.com/doc/current/translation.html#twig-templates',
                'answers' => [
                    ['text' => '{% trans %}Percent: %percent%[%]{% endtrans %}', 'correct' => false],
                    ['text' => '{% trans %}Percent: %percent%%%{% endtrans %}', 'correct' => true],
                    ['text' => '{% trans %}Percent: %percent%{%}{% endtrans %}', 'correct' => false],
                    ['text' => '{% trans %}Percent: %percent%\\%{% endtrans %}', 'correct' => false],
                ],
            ],
            // Question 24 - Routing - Route matching with defaults
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => "Given the following two routes, what controller will be executed for the URL <code>/book/123</code>?\n<pre><code># config/routes.yaml\nbook_detail_section:\n    path:       /book/{id}/{section}\n    controller: 'App\\Controller\\BookController::detailSection'\n    defaults:   { section: home }\nbook_detail:\n    path:      /book/{id}\n    controller: 'App\\Controller\\BookController::detail'</code></pre>",
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Routes are matched in order. The first route matches because section has a default value, making {section} optional.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#priority-parameter',
                'answers' => [
                    ['text' => 'Error: The routing file contains unsupported keys for "defaults"', 'correct' => false],
                    ['text' => 'Error: No route found', 'correct' => false],
                    ['text' => 'App\\Controller\\BookController::detail', 'correct' => false],
                    ['text' => 'App\\Controller\\BookController::detailSection', 'correct' => true],
                ],
            ],
            // Question 25 - Process - mustRun() return value
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'],
                'text' => 'Given a new process created in front of a Symfony command that returns a <code>0</code> code, what will be returned by <code>Process::mustRun()</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'mustRun() returns the Process instance (for fluent calls), or throws an exception on failure.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.5/src/Symfony/Component/Process/Process.php#L209',
                'answers' => [
                    ['text' => '0', 'correct' => false],
                    ['text' => '1', 'correct' => false],
                    ['text' => 'true', 'correct' => false],
                    ['text' => 'An instance of Process', 'correct' => true],
                    ['text' => 'false', 'correct' => false],
                ],
            ],
            // Question 26 - PHP Basics - Operators with array
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => "What will be displayed with the following code?\n<pre><code>&lt;?php\n\$bool = true;\n\$array = array();\n\$a = 14;\n\$a = \$a + \$bool - \$array;\necho \$a;</code></pre>",
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'You cannot use arrays in arithmetic operations. In PHP 8+, this throws a TypeError.',
                'resourceUrl' => 'http://php.net/manual/en/language.operators.php',
                'answers' => [
                    ['text' => '14', 'correct' => false],
                    ['text' => 'An error', 'correct' => true],
                    ['text' => '0', 'correct' => false],
                    ['text' => '15', 'correct' => false],
                ],
            ],
            // Question 27 - PropertyAccess - __set() disabled
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'Could the usage of <code>__set()</code> method be disabled?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, PropertyAccessorBuilder allows disabling magic methods.',
                'resourceUrl' => 'https://symfony.com/doc/5.2/components/property_access.html#enable-other-features',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 28 - DI - defined env var processor
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => "In which cases the <code>env_var</code> parameter will be evaluated to <code>false</code>?\n<pre><code># config/services.yaml\nparameters:\n    env_var: '%env(defined:FOO)%'</code></pre>",
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The defined processor only checks if the env var exists, regardless of its value.',
                'resourceUrl' => 'https://symfony.com/doc/6.4/configuration/env_var_processors.html',
                'answers' => [
                    ['text' => "When the FOO environment variable doesn't exist", 'correct' => true],
                    ['text' => 'When FOO is an empty string', 'correct' => false],
                    ['text' => 'When FOO equals to "false"', 'correct' => false],
                    ['text' => 'When FOO is null', 'correct' => false],
                    ['text' => 'When FOO contains only spaces', 'correct' => false],
                ],
            ],
            // Question 29 - Routing - Route mapping with regex
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => "Consider the following XML route definition:\n<pre><code>&lt;route id=\"app_agenda_event\" path=\"/agenda/{date}\" methods=\"GET\"&gt;\n    &lt;default key=\"_controller\"&gt;AppBundle:Agenda:event&lt;/default&gt;\n    &lt;requirement key=\"date\"&gt;(?:20\\d{2})-(?:(0?[1-9]|1[1-2]))-(?:(0?|[1-2])\\d|3[0-1])&lt;/requirement&gt;\n&lt;/route&gt;</code></pre>\nWhich of the following URL patterns will match the <code>app_agenda_event</code> route?",
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 4,
                'explanation' => 'The regex requires years 20XX, months 01-12 or 1-12, and valid days. Some dates like 2008-04-06 fail month validation.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/components/routing.html',
                'answers' => [
                    ['text' => 'http://localhost/agenda/2008-04-06', 'correct' => false],
                    ['text' => 'http://localhost/agenda/2018-12-12', 'correct' => true],
                    ['text' => 'http://localhost/agenda/2011-1-01', 'correct' => true],
                    ['text' => 'http://localhost/agenda/2150-12-31', 'correct' => false],
                    ['text' => 'http://localhost/agenda/2018-14-30', 'correct' => false],
                    ['text' => 'http://localhost/agenda/2020-2-30', 'correct' => true],
                ],
            ],
            // Question 30 - HttpFoundation - RequestStack main request
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could the main request be retrieved from the request stack?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, RequestStack::getMainRequest() returns the main request (since 5.4).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/HttpFoundation/RequestStack.php#L67',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 31 - Serializer - Built-in mapping loaders
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'What are the built-in mapping loaders for the definitions of serialization?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Serializer supports YAML, XML, Annotation and Attribute loaders. There is no JsonFileLoader or IniFileLoader.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/serializer.html#attributes-groups',
                'answers' => [
                    ['text' => 'YamlFileLoader', 'correct' => true],
                    ['text' => 'AnnotationLoader', 'correct' => true],
                    ['text' => 'XmlFileLoader', 'correct' => true],
                    ['text' => 'AttributeLoader', 'correct' => true],
                    ['text' => 'JsonFileLoader', 'correct' => false],
                    ['text' => 'IniFileLoader', 'correct' => false],
                ],
            ],
            // Question 32 - Expression Language - Caching methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'Which of these ExpressionLanguage methods allow to cache the parsed expression?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'parse() uses cache, and both compile() and evaluate() call parse() internally, so all three benefit from caching.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.4/src/Symfony/Component/ExpressionLanguage/ExpressionLanguage.php',
                'answers' => [
                    ['text' => 'evaluate', 'correct' => true],
                    ['text' => 'parse', 'correct' => true],
                    ['text' => 'compile', 'correct' => true],
                ],
            ],
            // Question 33 - Twig - Dynamic extends with array
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => "Is the following extends tag valid?\n<pre><code>{% extends ['layout1.html.twig', 'layout2.html.twig'] %}</code></pre>",
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, dynamic inheritance allows passing an array. Twig will use the first template that exists.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/extends.html#dynamic-inheritance',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 34 - Twig Internals - Parser
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following Twig internal objects is responsible for converting a tokens stream into a meaningful tree of nodes (aka AST or Abstract Syntax Tree)?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The Lexer tokenizes the template, the Parser builds the AST from tokens, and the Compiler generates PHP code from the AST.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/internals.html',
                'answers' => [
                    ['text' => 'The Compiler', 'correct' => false],
                    ['text' => 'The Environment', 'correct' => false],
                    ['text' => 'The Parser', 'correct' => true],
                    ['text' => 'The Lexer', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $qData) {
            $this->upsertQuestion($manager, $qData);
        }

        $manager->flush();
    }
}
