<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 35
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures35 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures34::class];
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
            // Q1 - Security - Authentication error service
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the name of the service that allows to retrieve an authentication error when a user doesn\'t manage to be authenticated by the application?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The security.authentication_utils service provides methods to get the last authentication error and the last username entered.',
                'resourceUrl' => 'https://symfony.com/doc/5.4/security.html#form-login',
                'answers' => [
                    ['text' => '<code>security.authentication_utils</code>', 'correct' => true],
                    ['text' => '<code>security.auth_errors_utils</code>', 'correct' => false],
                    ['text' => '<code>security.helper</code>', 'correct' => false],
                    ['text' => '<code>security.authentication_error_manager</code>', 'correct' => false],
                    ['text' => '<code>security.authentication_errors</code>', 'correct' => false],
                ],
            ],

            // Q2 - HttpKernel - NO_AUTO_CACHE_CONTROL_HEADER
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Which sentences about <code>AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER</code> are true?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'NO_AUTO_CACHE_CONTROL_HEADER is a response header that prevents automatic cache-control modification related to session storage and public/private directives.',
                'resourceUrl' => 'https://github.com/symfony/http-kernel/blob/4.1/EventListener/AbstractSessionListener.php',
                'answers' => [
                    ['text' => 'it\'s designed to be used as a response header', 'correct' => true],
                    ['text' => 'it\'s related to the <code>public/private</code> property of <code>cache-control</code>', 'correct' => true],
                    ['text' => 'it\'s related to session storage strategy', 'correct' => true],
                    ['text' => 'it\'s related to the <code>no-store</code> property of <code>cache-control</code>', 'correct' => false],
                    ['text' => 'it\'s related to the <code>no-cache</code> property of <code>cache-control</code>', 'correct' => false],
                    ['text' => 'it\'s designed to be used as a <code>SessionInterface</code> option', 'correct' => false],
                    ['text' => 'it\'s designed to be used as a request header', 'correct' => false],
                ],
            ],

            // Q3 - HttpClient - MockHttpClient
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'Is it possible to simulate an HTTP request without mocking the <code>HttpClient</code> thanks to <code>createMock()</code> from PHPUnit?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony provides MockHttpClient which allows simulating HTTP requests without using PHPUnit mocks.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_client.html#testing-http-clients-and-responses',
                'answers' => [
                    ['text' => 'Yes, by using <code>Symfony\Component\HttpClient\MockHttpClient</code>', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q4 - PHP - Never return type
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is the purpose of the <code>never</code> return type?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The never return type indicates that a function never returns normally - it either throws an exception, calls exit()/die(), or runs forever.',
                'resourceUrl' => 'https://www.php.net/manual/fr/language.types.never.php',
                'answers' => [
                    ['text' => 'It is used for functions that never returns (e.g. method raising an exception, always calling <code>die()</code> or <code>exit()</code>, etc.)', 'correct' => true],
                    ['text' => 'It is used to automatically raise a <code>LogicException</code> easily when the call of a function is done', 'correct' => false],
                    ['text' => 'It should never be used, as it is a type used internally by PHP\'s internals', 'correct' => false],
                ],
            ],

            // Q5 - HTTP - Location header 308
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Given a response using the <code>308</code> status code and containing a <code>Location</code> header, must the client use the header URI for automatic redirection?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'According to RFC 7538, the user agent MAY use the Location field value for automatic redirection, it is not a requirement.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc7538#section-3',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q6 - Cache - Async recomputing
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Could an item be recomputed asynchronously?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 5.2, cache items can be recomputed asynchronously using early expiration.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-2-async-cache-recomputing',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q7 - Finder - Depth matching
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Finder'],
                'text' => 'Which solution will match <code>/home/me/myFile.txt</code> file?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'To match /home/me/myFile.txt, you need to search in /home/me with depth 0, or search in /home with depth 1. depth < 1 means only depth 0, and depth > 1 means depth 2+.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/finder.html#directory-depth',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$finder->in(\'/home/me\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$finder->in(\'/home/me\')->depth(\'== 0\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$finder->files()->in(\'/home\')->depth(\'== 1\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$finder->files()->in(\'/home\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$finder->directories()->in(\'/home\')->depth(\'== 1\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$finder->files()->in(\'/home\')->depth(\'< 1\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$finder->files()->in(\'/home\')->depth(\'> 1\');</code></pre>', 'correct' => false],
                ],
            ],

            // Q8 - Console - InputArgument constants
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Which of the following constants do not exist?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'InputArgument has REQUIRED, OPTIONAL, and IS_ARRAY constants. NONE and NEGATABLE are InputOption constants, not InputArgument.',
                'resourceUrl' => 'https://github.com/symfony/console/blob/6.0/Input/InputArgument.php#L24-L26',
                'answers' => [
                    ['text' => '<code>Symfony\Component\Console\Input\InputArgument::NONE</code>', 'correct' => true],
                    ['text' => '<code>Symfony\Component\Console\Input\InputArgument::NEGATABLE</code>', 'correct' => true],
                    ['text' => '<code>Symfony\Component\Console\Input\InputArgument::IS_ARRAY</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Console\Input\InputArgument::OPTIONAL</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Console\Input\InputArgument::REQUIRED</code>', 'correct' => false],
                ],
            ],

            // Q9 - DI - Change service class
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'It is possible to change the class of a service using the <code>ContainerBuilder</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, using the Definition::setClass() method, you can change the class of a service.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/dependency_injection/definitions.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q10 - Forms - FormEvents constants
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which of the following are built-in Symfony form events?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony Form component has 5 events: PRE_SET_DATA, POST_SET_DATA, PRE_SUBMIT, SUBMIT, and POST_SUBMIT.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/form/form_events.html',
                'answers' => [
                    ['text' => '<code>FormEvents::POST_SET_DATA</code>', 'correct' => true],
                    ['text' => '<code>FormEvents::PRE_SET_DATA</code>', 'correct' => true],
                    ['text' => '<code>FormEvents::POST_SUBMIT</code>', 'correct' => true],
                    ['text' => '<code>FormEvents::PRE_SUBMIT</code>', 'correct' => true],
                    ['text' => '<code>FormEvents::SUBMIT</code>', 'correct' => true],
                    ['text' => '<code>FormEvents::SUBMIT_DATA</code>', 'correct' => false],
                    ['text' => '<code>FormEvents::POST</code>', 'correct' => false],
                    ['text' => '<code>FormEvents::POST_DATA</code>', 'correct' => false],
                ],
            ],

            // Q11 - PHP OOP - Multiple interfaces
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Can a class implement multiple interfaces?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, a PHP class can implement multiple interfaces by listing them after the implements keyword, separated by commas.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.interfaces.php',
                'answers' => [
                    ['text' => 'Yes.', 'correct' => true],
                    ['text' => 'No.', 'correct' => false],
                ],
            ],

            // Q12 - DI - Autowiring with multiple implementations
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Will the following autowiring declaration work?
<pre><code class="language-yaml">services:
    rot13:
        class:    Acme\Transformer\Rot13Transformer
        arguments: [true]

    rot13_2:
        class:    Acme\Transformer\Rot13Transformer
        arguments: [false]

    twitter_client:
        class:    Acme\TwitterClient
        autowire: true</code></pre>
<pre><code class="language-php">namespace Acme;

use Acme\Transformer\Rot13Transformer;

class TwitterClient
{
    private $transformer;

    public function __construct(Rot13Transformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function tweet($user, $key, $status)
    {
        $transformedStatus = $this->transformer->transform($status);

        // ... connect to Twitter and send the encoded status
    }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, autowiring cannot determine which service to use when there are multiple services of the same class.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/dependency_injection/autowiring.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q13 - Routing - ExpressionLanguage variables
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Which variables can be used in the <code>ExpressionLanguage</code> expression when using the <code>condition</code> option on a Route?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In route conditions, you can use context (RequestContext), request (Request), and params (route parameters).',
                'resourceUrl' => 'https://symfony.com/doc/6.1/routing.html#matching-expressions',
                'answers' => [
                    ['text' => '<code>context</code>', 'correct' => true],
                    ['text' => '<code>request</code>', 'correct' => true],
                    ['text' => '<code>params</code>', 'correct' => true],
                    ['text' => '<code>container</code>', 'correct' => false],
                    ['text' => '<code>user</code>', 'correct' => false],
                    ['text' => '<code>this</code>', 'correct' => false],
                    ['text' => '<code>service</code>', 'correct' => false],
                    ['text' => '<code>object</code>', 'correct' => false],
                ],
            ],

            // Q14 - HttpFoundation - FlashBag override
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could all <code>FlashBag</code> messages be overridden?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, using the setAll() method, all FlashBag messages can be overridden.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/HttpFoundation/Session/Flash/FlashBag.php#L127',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q15 - HTTP - Validation caching headers
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which HTTP response headers are involved in the validation caching model?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Etag and Last-Modified are validation headers. Cache-Control max-age and s-max-age are expiration model headers.',
                'resourceUrl' => 'https://datatracker.ietf.org/doc/html/rfc7234#section-4.3',
                'answers' => [
                    ['text' => 'Etag', 'correct' => true],
                    ['text' => 'Last-Modified', 'correct' => true],
                    ['text' => 'Cache-Control: s-max-age', 'correct' => false],
                    ['text' => 'Cache-Control: max-age', 'correct' => false],
                ],
            ],

            // Q16 - PHP Arrays - array_unshift
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'What is the output of the following code?
<pre><code class="language-php">&lt;?php
$myArray = [];

array_unshift($myArray, 10, 20);

echo $myArray[0];</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_unshift adds elements to the beginning of an array. The first argument (10) becomes index 0.',
                'resourceUrl' => 'https://php.net/manual/fr/function.array-unshift.php',
                'answers' => [
                    ['text' => '10', 'correct' => true],
                    ['text' => '30', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '200', 'correct' => false],
                    ['text' => '20', 'correct' => false],
                ],
            ],

            // Q17 - Doctrine DBAL - Affected rows
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'In which of these cases will <code>$result</code> contain the number of affected rows?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'executeUpdate() and delete() return the number of affected rows. executeQuery() returns a Result object. prepare()->execute() returns true/false.',
                'resourceUrl' => 'https://github.com/doctrine/dbal/blob/71140662c0a954602e81271667b6e03d9f53ea34/lib/Doctrine/DBAL/Connection.php#L577',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$result = $conn->executeUpdate(\'DELETE FROM user WHERE id = 1\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$result = $conn->delete(\'user\', [\'id\' => 1]);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$result = $conn->executeQuery(\'DELETE FROM user WHERE id = 1\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$result = $conn->prepare(\'DELETE FROM user WHERE id = 1\')->execute();</code></pre>', 'correct' => false],
                ],
            ],

            // Q18 - Twig - AST to PHP (Compiler)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following Twig internal objects is responsible for transforming an AST (Abstract Syntax Tree) into PHP code?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Compiler is responsible for transforming the AST into executable PHP code.',
                'resourceUrl' => 'http://twig.symfony.com/doc/internals.html',
                'answers' => [
                    ['text' => 'The Compiler', 'correct' => true],
                    ['text' => 'The Parser', 'correct' => false],
                    ['text' => 'The Lexer', 'correct' => false],
                    ['text' => 'The Environment', 'correct' => false],
                ],
            ],

            // Q19 - Twig - Lexer (tokenizing)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following Twig internal objects is responsible for tokenizing the template source code into smaller pieces for easier processing?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Lexer is responsible for tokenizing the template source code into a token stream.',
                'resourceUrl' => 'http://twig.symfony.com/doc/internals.html',
                'answers' => [
                    ['text' => 'The Lexer', 'correct' => true],
                    ['text' => 'The Environment', 'correct' => false],
                    ['text' => 'The Compiler', 'correct' => false],
                    ['text' => 'The Parser', 'correct' => false],
                ],
            ],

            // Q20 - PHP - PSR-0 and PSR-4
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What are PSR-0 and PSR-4?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PSR-0 and PSR-4 are PHP-FIG standards that define specifications for autoloading classes from file paths.',
                'resourceUrl' => 'http://www.php-fig.org/psr/psr-4/',
                'answers' => [
                    ['text' => 'A specification for autoloading classes from file paths.', 'correct' => true],
                    ['text' => 'A coding style guide.', 'correct' => false],
                    ['text' => 'A common logger interface.', 'correct' => false],
                    ['text' => 'A utility to convert non-namespaced PHP classes into namespaced ones.', 'correct' => false],
                ],
            ],

            // Q21 - Twig - Filter is_safe option
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following <code>$options</code> allow a <code>Twig_Filter</code> decide how to escape data by itself?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The is_safe option must be an array of contexts (like [\'html\']) where the filter output is safe.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping',
                'answers' => [
                    ['text' => '<pre><code class="language-php">[\'is_safe\' => [\'html\']]</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">[\'is_safe\' => true]</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">[\'is_safe\']</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">[\'is_safe\' => \'html\']</code></pre>', 'correct' => false],
                ],
            ],

            // Q22 - PHP - PHP_FLOAT_EPSILON
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Data Format & Types'],
                'text' => 'How is called the PHP constant representing the smallest possible number <code>n</code>, so that <code>1.0 + n != 1.0</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP_FLOAT_EPSILON represents the smallest x such that 1.0 + x != 1.0.',
                'resourceUrl' => 'https://www.php.net/manual/en/reserved.constants.php',
                'answers' => [
                    ['text' => '<code>PHP_FLOAT_EPSILON</code>', 'correct' => true],
                    ['text' => '<code>PHP_FLOAT_DIG</code>', 'correct' => false],
                    ['text' => '<code>PHP_FLOAT_SMALLEST</code>', 'correct' => false],
                    ['text' => '<code>PHP_FLOAT_MIN</code>', 'correct' => false],
                ],
            ],

            // Q23 - DI - Environment variables resolution count
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could the number of time each environment variables has been resolved be obtained when using <code>ContainerBuilder</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the ContainerBuilder tracks environment variable usage and the count can be retrieved.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/DependencyInjection/ContainerBuilder.php#L1077',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q24 - Filesystem - Mirror delete
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'When using <code>mirror(...)</code>, could files that are not present in the source directory be deleted?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the mirror() method has a delete option that removes files in the target that are not in the source.',
                'resourceUrl' => 'https://symfony.com/doc/2.2/components/filesystem.html#mirror',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q25 - PropertyAccess - isReadable
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'What is the method to call to check if <code>getValue</code> can safely be called?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The isReadable() method checks if a property path can be read.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/property_access/introduction.html#checking-property-paths',
                'answers' => [
                    ['text' => '<code>isReadable()</code>', 'correct' => true],
                    ['text' => '<code>checkValue()</code>', 'correct' => false],
                    ['text' => '<code>exists()</code>', 'correct' => false],
                    ['text' => '<code>canRead()</code>', 'correct' => false],
                ],
            ],

            // Q27 - DI - defined env var processor
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'In which cases the <code>env_var</code> parameter will be evaluated to <code>false</code>?
<pre><code class="language-yaml"># config/services.yaml
parameters:
    env_var: \'%env(defined:FOO)%\'</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The defined: processor returns false only when the environment variable does not exist at all.',
                'resourceUrl' => 'https://symfony.com/doc/6.4/configuration/env_var_processors.html',
                'answers' => [
                    ['text' => 'When the <code>FOO</code> environment variable doesn\'t exist', 'correct' => true],
                    ['text' => 'When <code>FOO</code> is an empty string', 'correct' => false],
                    ['text' => 'When <code>FOO</code> contains only spaces', 'correct' => false],
                    ['text' => 'When <code>FOO</code> equals to <code>"false"</code>', 'correct' => false],
                    ['text' => 'When <code>FOO</code> is <code>null</code>', 'correct' => false],
                ],
            ],

            // Q28 - Security - eraseCredentials purpose
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the main purpose of the <code>eraseCredentials()</code> method from the <code>Symfony\Component\Security\Core\User\UserInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'eraseCredentials() is called after authentication to remove sensitive data like plain-text passwords from the user object.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Security/Core/User/UserInterface.php#L49-L55',
                'answers' => [
                    ['text' => 'Remove sensitive data from the user', 'correct' => true],
                    ['text' => 'Reload user from database', 'correct' => false],
                    ['text' => 'Disable user\'s account', 'correct' => false],
                    ['text' => 'Reset user\'s credentials from database', 'correct' => false],
                ],
            ],

            // Q29 - Expression Language - Logical operators
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'What will be displayed by the following code?
<pre><code class="language-php">var_dump($language->evaluate(
    \'life < universe or life < everything\',
    array(
        \'life\' => 10,
        \'universe\' => 10,
        \'everything\' => 22,
    )
));</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => '10 < 10 is false, but 10 < 22 is true. With "or", if one condition is true, the result is true.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/expression_language/syntax.html#logical-operators',
                'answers' => [
                    ['text' => 'true', 'correct' => true],
                    ['text' => 'false', 'correct' => false],
                ],
            ],

            // Q30 - Event Dispatcher - kernel.event_listener tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'What is the tag to use to listen to different events/hooks in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The kernel.event_listener tag is used to register event listeners in Symfony.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/dic_tags.html#kernel-event-listener',
                'answers' => [
                    ['text' => '<code>kernel.event_listener</code>', 'correct' => true],
                    ['text' => '<code>event_listener</code>', 'correct' => false],
                    ['text' => '<code>dispatcher.event_listener</code>', 'correct' => false],
                    ['text' => '<code>event_dispatcher.event_listener</code>', 'correct' => false],
                    ['text' => '<code>kernel.listener</code>', 'correct' => false],
                    ['text' => '<code>dispatcher.listener</code>', 'correct' => false],
                ],
            ],

            // Q31 - Expression Language - Parser cache
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'Could the parser cache be changed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, the ExpressionLanguage component allows configuring a custom cache for parsed expressions.',
                'resourceUrl' => 'https://symfony.com/doc/2.4/components/expression_language/caching.html#the-workflow',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q32 - DI - Setter injection
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Can you inject a dependency to a service without passing it to the constructor?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, using setter injection or property injection, dependencies can be injected without the constructor.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/injection_types.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q33 - Runtime - Composer plugin event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'],
                'text' => 'Which event is listened by the Composer plugin?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Symfony Runtime Composer plugin listens to POST_AUTOLOAD_DUMP event.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/Runtime/Internal/ComposerPlugin.php',
                'answers' => [
                    ['text' => '<code>POST_AUTOLOAD_DUMP</code>', 'correct' => true],
                    ['text' => '<code>PRE_AUTOLOAD_DUMP</code>', 'correct' => false],
                    ['text' => '<code>POST_INSTALL</code>', 'correct' => false],
                    ['text' => '<code>PRE_INSTALL</code>', 'correct' => false],
                ],
            ],

            // Q34 - PHP Arrays - Constant as array key
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'Is the following PHP code valid or will it generate an error, warning or notice?
<pre><code class="language-php">&lt;?php

error_reporting(E_ALL | E_STRICT);

$newArray[E_STRICT] = \'foo\';</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'This is valid PHP. Constants can be used as array keys, and arrays can be created implicitly.',
                'resourceUrl' => 'https://php.net/array',
                'answers' => [
                    ['text' => 'Yes, it\'s completely valid', 'correct' => true],
                    ['text' => 'Invalid, you cannot use a constant as an array key', 'correct' => false],
                    ['text' => 'Invalid, <code>E_STRICT</code> is not defined', 'correct' => false],
                    ['text' => 'Invalid, you must define <code>$newArray</code> by calling <code>array()</code> first', 'correct' => false],
                ],
            ],

            // Q35 - Messenger - Multiple buses
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Can you have multiple buses in a single application?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, Symfony Messenger supports configuring multiple message buses (command bus, event bus, query bus, etc.).',
                'resourceUrl' => 'https://symfony.com/doc/4.4/messenger/multiple_buses.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q36 - Forms - Callback mapping
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Could values be mapped to fields using callbacks?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, since Symfony 5.2, you can use getter and setter callbacks for field mapping.',
                'resourceUrl' => 'https://symfony.com/doc/5.2/form/data_mappers.html#mapping-form-fields-using-callbacks',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q37 - Twig - Countable empty test
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given an object <code>Foo</code> which implements <code>\Countable</code> and the method <code>count()</code> which return <code>1</code>, what will be displayed?
<pre><code class="language-twig">{% if foo is empty %}
    {{ foo.get(\'name\') }}
{% endif %}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The "empty" test in Twig uses count() for Countable objects. Since count() returns 1, foo is not empty and nothing is displayed.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tests/empty.html',
                'answers' => [
                    ['text' => 'Nothing', 'correct' => true],
                    ['text' => 'The value of <code>foo.get(\'name\')</code>', 'correct' => false],
                ],
            ],

            // Q38 - Twig - Lexer (duplicate for wrong answer context)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which Twig internal object is responsible for tokenizing the template source code?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Lexer tokenizes template source code into a stream of tokens.',
                'resourceUrl' => 'http://twig.symfony.com/doc/internals.html',
                'answers' => [
                    ['text' => 'The Lexer', 'correct' => true],
                    ['text' => 'The Parser', 'correct' => false],
                    ['text' => 'The Environment', 'correct' => false],
                    ['text' => 'The Compiler', 'correct' => false],
                ],
            ],

            // Q39 - HttpFoundation - Request modification
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Can the Request object be modified during its handling?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, the Request object is mutable and can be modified during the request lifecycle.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_foundation.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q40 - HttpKernel - kernel.view event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What kernel event is dispatched when a controller does not return a <code>Response</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The kernel.view event is dispatched when the controller returns a non-Response value.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/reference/events.html',
                'answers' => [
                    ['text' => '<code>kernel.view</code>', 'correct' => true],
                    ['text' => '<code>kernel.terminate</code>', 'correct' => false],
                    ['text' => '<code>kernel.response</code>', 'correct' => false],
                    ['text' => '<code>kernel.finish_request</code>', 'correct' => false],
                ],
            ],
        ];
    }
}
