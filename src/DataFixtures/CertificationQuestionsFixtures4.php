<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 4
 */
class CertificationQuestionsFixtures4 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;
    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures3::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found.');
        }

        // Load existing subcategories from AppFixtures
        $subcategories = $this->loadSubcategories($manager);

        $questions = [
            // LocaleSwitcher features
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'What are the main features of <code>LocaleSwitcher</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'LocaleSwitcher can: set the default locale via setLocale(), retrieve the current locale, and execute a callback with a given locale using runWithLocale().',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/Translation/LocaleSwitcher.php',
                'answers' => [
                    ['text' => 'Set the default locale that will be used in the code after calling <code>setLocale()</code>', 'correct' => true],
                    ['text' => 'Retrieve the current locale of the application', 'correct' => true],
                    ['text' => 'Execute only a callback function with a given locale', 'correct' => true],
                    ['text' => 'Easily switch from timezone to timezone for a given <code>DateTime</code>', 'correct' => false],
                    ['text' => 'Switch from a currency to another by doing the good conversion', 'correct' => false],
                ],
            ],
            // ReverseContainer
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could a service identifier be returned from a <code>ReverseContainer</code> if the service is not tagged as <code>container.reversible</code> and defined as <code>private</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'No, ReverseContainer requires services to be either public or tagged with container.reversible to return their identifier.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/DependencyInjection/ReverseContainer.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Form EnumType
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Could PHP enumerations be used to display a list of choices in Symfony Forms?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, Symfony 5.4+ supports EnumType which allows using PHP 8.1 enums as form choices.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-4-php-enumerations-support#php-enums-support-in-symfony-forms',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // PHP Named arguments as array
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Is it valid to use the spread operator with an associative array for named arguments in PHP 8.0+?<pre><code class="language-php">$args = [\'secondArgument\' => \'arg\', \'firstArgument\' => true];
$this->bar(...$args);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, PHP 8.0 allows using the spread operator with associative arrays to pass named arguments to functions.',
                'resourceUrl' => 'https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Twig loaders
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What are Twig loaders responsible for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Twig loaders are responsible for loading templates from a resource name (like filesystem paths, database, etc.).',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/api.html#loaders',
                'answers' => [
                    ['text' => 'Loaders are responsible for loading templates from a resource name.', 'correct' => true],
                    ['text' => 'Loaders are responsible for loading token parsers.', 'correct' => false],
                    ['text' => 'Loaders are responsible for loading environments such as Twig_Environment.', 'correct' => false],
                    ['text' => 'Loaders are responsible for loading extensions.', 'correct' => false],
                ],
            ],
            // PhpSubprocess vs Process
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'],
                'text' => 'If <code>memory_limit</code> is <code>-1</code> in php.ini, what will be the output of <code>php -d memory_limit=256M bin/console app:process</code> when using Process vs PhpSubprocess?<pre><code class="language-php">// First: new Process([\'php\', \'-r\', \'echo ini_get("memory_limit");\']);
// Second: new PhpSubprocess([\'-r\', \'echo ini_get("memory_limit");\']);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Process spawns a new PHP with default ini settings (-1). PhpSubprocess preserves the parent PHP configuration (256M).',
                'resourceUrl' => 'https://symfony.com/doc/6.4/components/process.html#executing-a-php-child-process-with-the-same-configuration',
                'answers' => [
                    ['text' => '-1 then 256M', 'correct' => true],
                    ['text' => '256M then 256M', 'correct' => false],
                    ['text' => '-1 then -1', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                ],
            ],
            // Validator GroupSequenceProvider
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'With GroupSequenceProvider returning <code>[[\'foo\', \'Product\']]</code>, how are properties validated?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'When groups are nested in an inner array, they are validated simultaneously. So $foo, $foo2 (group foo) and $bar (group Product) are validated at the same time.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/validation/sequence_provider.html',
                'answers' => [
                    ['text' => '<code>$foo</code>, <code>$foo2</code>, <code>$bar</code> will be validated at the same time', 'correct' => true],
                    ['text' => 'First <code>$foo</code>, <code>$foo2</code> then <code>$bar</code>', 'correct' => false],
                    ['text' => 'First <code>$foo</code>, <code>$foo2</code> then <code>$bar</code>, <code>$bar2</code>', 'correct' => false],
                    ['text' => 'All four properties validated at the same time', 'correct' => false],
                ],
            ],
            // MIME multipart/alternative
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mime'],
                'text' => 'What is the purpose of the <code>multipart/alternative</code> MIME message part?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'multipart/alternative is used when two or more parts are alternatives of the same content (e.g., text and HTML versions of an email). The preferred format must be added last.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/mime#creating-raw-email-messages',
                'answers' => [
                    ['text' => 'Used when two or more parts are alternatives of the same (or very similar) content. The preferred format must be added last', 'correct' => true],
                    ['text' => 'Used to indicate that each message part is a component of an aggregate whole (for embedded images)', 'correct' => false],
                    ['text' => 'Used to send different content types in the same message, such as when attaching files', 'correct' => false],
                ],
            ],
            // env(defined:) processor
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'In which case will <code>%env(defined:FOO)%</code> evaluate to <code>false</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The defined: processor only returns false when the environment variable does not exist. Empty strings, null values, or "false" strings still mean the variable is defined.',
                'resourceUrl' => 'https://symfony.com/doc/6.4/configuration/env_var_processors.html',
                'answers' => [
                    ['text' => 'When the <code>FOO</code> environment variable doesn\'t exist', 'correct' => true],
                    ['text' => 'When <code>FOO</code> equals to <code>"false"</code>', 'correct' => false],
                    ['text' => 'When <code>FOO</code> is an empty string', 'correct' => false],
                    ['text' => 'When <code>FOO</code> is <code>null</code>', 'correct' => false],
                ],
            ],
            // #[Exclude] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is the purpose of the <code>#[Exclude]</code> attribute in Symfony DI?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[Exclude] attribute prevents a class from being registered as a service during autowiring resource loading.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-dependency-injection-improvements#exclude-classes-with-attributes',
                'answers' => [
                    ['text' => 'To prevent a class from being registered as a service', 'correct' => true],
                    ['text' => 'To prevent a class from being set as a public service', 'correct' => false],
                    ['text' => 'To prevent a class from being autowired', 'correct' => false],
                ],
            ],
            // Dotenv debug
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dotenv'],
                'text' => 'Could the environment variables be debugged in Symfony?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, Symfony 5.4+ provides a debug:dotenv command to debug environment variables.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-4-misc-features-part-2#new-command-to-debug-environment-variables',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // debug:dotenv command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dotenv'],
                'text' => 'Which built-in console command can you use to debug the values of environment variables?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The debug:dotenv command displays all environment variables and their sources.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration.html#listing-environment-variables',
                'answers' => [
                    ['text' => '<code>bin/console debug:dotenv</code>', 'correct' => true],
                    ['text' => '<code>bin/console debug:container --env</code>', 'correct' => false],
                    ['text' => '<code>bin/console debug:container --parameters</code>', 'correct' => false],
                    ['text' => 'No built-in command exists to debug environment variables.', 'correct' => false],
                ],
            ],
            // JSONP callback
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could a JSONP callback be set in Symfony\'s JsonResponse?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, JsonResponse has a setCallback() method to set a JSONP callback function.',
                'resourceUrl' => 'https://symfony.com/doc/2.1/components/http_foundation/introduction.html#jsonp-callback',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // ExpressionLanguage passing variables
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'What is the correct way to return <code>$apple->variety</code> using ExpressionLanguage?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Variables must be passed as an associative array where keys are variable names used in the expression.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/expression_language.html#passing-in-variables',
                'answers' => [
                    ['text' => '<code>$language->evaluate(\'fruit.variety\', [\'fruit\' => $apple])</code>', 'correct' => true],
                    ['text' => '<code>$language->evaluate(\'variety\', $apple)</code>', 'correct' => false],
                    ['text' => '<code>$language->compile(\'apple.variety\', $apple)</code>', 'correct' => false],
                    ['text' => '<code>$language->evaluate(\'apple.variety\', $apple)</code>', 'correct' => false],
                ],
            ],
            // PHP is loosely-typed imperative
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Which of the following assertions about PHP is correct?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'PHP is a loosely-typed (dynamically typed) imperative language with support for OOP and some functional programming features.',
                'resourceUrl' => 'https://en.wikipedia.org/wiki/Programming_paradigm',
                'answers' => [
                    ['text' => 'PHP is a loosely-typed imperative language.', 'correct' => true],
                    ['text' => 'PHP is a strongly-typed imperative language.', 'correct' => false],
                    ['text' => 'PHP is a loosely-typed functional language.', 'correct' => false],
                    ['text' => 'PHP is a strongly-typed functional language.', 'correct' => false],
                    ['text' => 'PHP is a loosely-typed declarative language.', 'correct' => false],
                ],
            ],
            // Twig block() with second argument
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What does <code>block(\'footer\', \'common_blocks.html.twig\')</code> do in Twig?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The block() function with a second argument renders a block from a different template file.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/functions/block.html',
                'answers' => [
                    ['text' => 'The code checks if the <code>common_blocks.html.twig</code> template contains a Twig block called <code>footer</code>', 'correct' => true],
                    ['text' => 'The code is wrong because the <code>block()</code> function doesn\'t allow a second argument.', 'correct' => false],
                    ['text' => 'The code is wrong because the <code>is defined</code> test cannot be used with <code>block()</code>.', 'correct' => false],
                    ['text' => 'The condition will be false if the footer block exists but is empty.', 'correct' => false],
                ],
            ],
            // Service decoration priority
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could the priority of a decorating service be defined?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, you can set decoration_priority to control the order when multiple decorators are applied to the same service.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/service_container/service_decoration.html#decoration-priority',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // NotBlank with "0"
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Will validating the string <code>"0"</code> with the <code>NotBlank</code> constraint throw a violation?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, the string "0" is not considered blank. NotBlank only rejects null, empty string "", and false.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/NotBlank.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Messenger buses debug
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Could Messenger buses be debugged?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, you can use debug:messenger to see all message buses and their handlers.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/messenger/multiple_buses.html#debugging-the-buses',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Form Button children
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Could a Form <code>Button</code> have children?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'No, Buttons cannot have children. The add() method throws a BadMethodCallException.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Form/Button.php#L120',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // ControllerArgumentsEvent named arguments
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Could the controller named arguments be retrieved from within <code>ControllerArgumentsEvent</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 6.2 you can use getNamedArguments() to get controller arguments by name.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/HttpKernel/Event/ControllerArgumentsEvent.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Twig block rendering from different template
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In a 3-level template hierarchy (base > layout > index), what is rendered by <code>{{ block(\'title\', \'base.html.twig\') }}</code> in index.html.twig?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'When using block() with a second argument, it renders the block from that specific template file, ignoring inheritance overrides.',
                'resourceUrl' => 'https://twig.symfony.com/doc/functions/block.html',
                'answers' => [
                    ['text' => 'ACME (the default value from base.html.twig)', 'correct' => true],
                    ['text' => 'Welcome to ACME! (from layout.html.twig)', 'correct' => false],
                    ['text' => 'An empty string', 'correct' => false],
                    ['text' => 'An error because block() only takes one argument', 'correct' => false],
                ],
            ],
            // AbstractSessionListener NO_AUTO_CACHE_CONTROL
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Could the <code>AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER</code> directive be used on sub-requests?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'No, this header only affects the main request, not sub-requests.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/http_cache.html#http-caching-and-user-sessions',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // ExpressionLanguage registering functions
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'What are the possible ways to register new functions in ExpressionLanguage?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'You can register functions using register() method or by calling registerProvider() with an ExpressionFunctionProviderInterface.',
                'resourceUrl' => 'https://symfony.com/doc/2.6/components/expression_language/extending.html',
                'answers' => [
                    ['text' => 'By calling the <code>register()</code> method.', 'correct' => true],
                    ['text' => 'By calling the <code>registerProvider()</code> method.', 'correct' => true],
                    ['text' => 'By calling the <code>createFunction()</code> method.', 'correct' => false],
                    ['text' => 'By calling the <code>addFunction()</code> method.', 'correct' => false],
                    ['text' => 'By calling the <code>setFunctions()</code> method.', 'correct' => false],
                ],
            ],
            // Recommended injection types
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What are the RECOMMENDED types of dependency injection in Symfony?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Constructor injection, setter injection, and immutable-setter injection are recommended. Property injection is NOT recommended.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/service_container/injection_types.html',
                'answers' => [
                    ['text' => 'Constructor injection', 'correct' => true],
                    ['text' => 'Setter injection', 'correct' => true],
                    ['text' => 'Immutable-setter Injection', 'correct' => true],
                    ['text' => 'Property injection', 'correct' => true],
                    ['text' => 'Getter injection', 'correct' => false],
                ],
            ],
            // quotemeta function
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Strings'],
                'text' => 'What is the main purpose of PHP\'s <code>quotemeta()</code> function?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'quotemeta() escapes a limited set of special characters (. \\ + * ? [ ^ ] ( $ )), not all regex special characters.',
                'resourceUrl' => 'https://www.php.net/manual/en/function.quotemeta.php',
                'answers' => [
                    ['text' => 'It protects some special characters (<code>. \\ + * ? [ ^ ] ( $ )</code>) by adding a <code>\\</code> before them', 'correct' => true],
                    ['text' => 'It protects all regex special characters by adding a <code>\\</code> before them', 'correct' => false],
                    ['text' => 'It formats meta-data of your web page for search engines', 'correct' => false],
                ],
            ],
            // Request properties
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Which of the following are valid statements to read request data from the <code>Request</code> object?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Request has files, headers, server, and session properties. There is no env property.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/components/http_foundation.html#accessing-request-data',
                'answers' => [
                    ['text' => '<code>$request->files->get(\'avatar\')</code>', 'correct' => true],
                    ['text' => '<code>$request->headers->get(\'Accept-Language\')</code>', 'correct' => true],
                    ['text' => '<code>$request->server->get(\'HTTP_HOST\')</code>', 'correct' => true],
                    ['text' => '<code>$request->session->get(\'security._last_username\')</code>', 'correct' => true],
                    ['text' => '<code>$request->env->get(\'CLI_COLOR\')</code>', 'correct' => false],
                ],
            ],
            // Cache isHit
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'How do you check if an item exists in the cache using PSR-6?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The isHit() method returns true if the item was found in the cache.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/cache.html#basic-usage-psr-6',
                'answers' => [
                    ['text' => '<code>$item->isHit()</code>', 'correct' => true],
                    ['text' => '<code>$item->exists()</code>', 'correct' => false],
                    ['text' => '<code>$item->isFound()</code>', 'correct' => false],
                    ['text' => '<code>$item->isCached()</code>', 'correct' => false],
                ],
            ],
            // _controller request attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the name of the special request attribute used by <code>RouterListener</code> to know which callable to invoke?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The _controller attribute contains the callable (controller) that should handle the request.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/components/http_kernel.html#1-the-kernel-request-event',
                'answers' => [
                    ['text' => '<code>_controller</code>', 'correct' => true],
                    ['text' => '<code>_route</code>', 'correct' => false],
                    ['text' => '<code>_action</code>', 'correct' => false],
                    ['text' => '<code>_request</code>', 'correct' => false],
                ],
            ],
            // ContainerConfigurator env
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could environment variables be configured using <code>ContainerConfigurator</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, ContainerConfigurator has an env() method to configure environment variable defaults.',
                'resourceUrl' => 'https://github.com/symfony/dependency-injection/blob/5.3/Loader/Configurator/ContainerConfigurator.php#L198',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // PHP DOM createTextNode
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:DOM'],
                'text' => 'How do you create a text node with "Hello, World!" in PHP DOM?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Use $dom->createTextNode() to create a text node in the DOM.',
                'resourceUrl' => 'http://php.net/manual/en/domdocument.createtextnode.php',
                'answers' => [
                    ['text' => '<code>$dom->createTextNode(\'Hello, World!\')</code>', 'correct' => true],
                    ['text' => '<code>$dom->appendTextNode($title, "Hello, World!")</code>', 'correct' => false],
                    ['text' => '<code>$dom->createElement(\'text\', \'Hello, World!\')</code>', 'correct' => false],
                    ['text' => '<code>$dom->appendElement($title, \'text\', \'Hello, World!\')</code>', 'correct' => false],
                ],
            ],
            // Twig dynamic filters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'For a Twig dynamic filter defined as <code>*_path_*</code>, how are arguments passed when using <code>{{ \'foo\'|a_path_b }}</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Dynamic filter/function wildcards are passed as separate arguments before the actual value: (\'a\', \'b\', \'foo\').',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/advanced.html#dynamic-filters',
                'answers' => [
                    ['text' => 'The callable receives <code>(\'a\', \'b\', \'foo\')</code>', 'correct' => true],
                    ['text' => 'The callable receives <code>(\'foo\', \'a\', \'b\')</code>', 'correct' => false],
                    ['text' => 'The callable receives <code>(\'foo\', [\'a\', \'b\'])</code>', 'correct' => false],
                    ['text' => 'The callable receives <code>(\'foo\', [\'patterns\' => [\'a\', \'b\']])</code>', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $q) {
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }
}
