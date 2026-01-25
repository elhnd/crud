<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 33
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures33 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures32::class];
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
            // Q1 - FrameworkBundle - Core Symfony
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'In a Symfony application, what is the element that links the core components together?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The FrameworkBundle is the bundle that ties together the core Symfony components into a full-stack framework.',
                'resourceUrl' => 'https://symfony.com/components/Framework%20Bundle',
                'answers' => [
                    ['text' => 'The <code>FrameworkBundle</code>.', 'correct' => true],
                    ['text' => 'The <code>Kernel</code>.', 'correct' => false],
                    ['text' => 'The <code>Container</code>.', 'correct' => false],
                ],
            ],

            // Q2 - HTTP - Safe usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Is the following code valid when using the <code>safe</code> directive?
<pre><code class="language-text">GET /foo.html HTTP/1.1
Host: www.example.org
User-Agent: ExampleBrowser/1.0
Prefer: safe</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, the Prefer: safe header is valid according to RFC 8674 section 2.',
                'resourceUrl' => 'https://datatracker.ietf.org/doc/html/rfc8674#section-2',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q3 - BrowserKit - Cookie assertions
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:BrowserKit'],
                'text' => 'Could assertions be performed on the value of a certain cookie?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, BrowserKit provides BrowserCookieValueSame constraint for asserting cookie values.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/BrowserKit/Test/Constraint/BrowserCookieValueSame.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q4 - PasswordHasher - Password Hasher usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PasswordHasher'],
                'text' => 'Could the password hasher be programmatically specified in the <code>User</code> entity?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, thanks to PasswordHasherAwareInterface, the desired hasher can be specified programmatically.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/security/passwords.html#named-password-hashers',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q5 - HttpClient - PSR-18 usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'It is possible to use PSR-18 for your requests?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, by using Symfony\Component\HttpClient\Psr18Client, you can use PSR-18 for HTTP requests.',
                'resourceUrl' => 'https://symfony.com/doc/5.0/http_client.html#psr-18-and-psr-17',
                'answers' => [
                    ['text' => 'Yes, by using <code>Symfony\Component\HttpClient\Psr18Client</code>', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - Clock - ClockAwareTrait
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'What method does the <code>ClockAwareTrait</code> adds?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The ClockAwareTrait adds setClock() and now() methods.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Clock/ClockAwareTrait.php',
                'answers' => [
                    ['text' => '<code>setClock</code>', 'correct' => true],
                    ['text' => '<code>now</code>', 'correct' => true],
                    ['text' => '<code>modify</code>', 'correct' => false],
                    ['text' => '<code>sleep</code>', 'correct' => false],
                    ['text' => '<code>getClock</code>', 'correct' => false],
                ],
            ],

            // Q7 - HTTP - Must-understand usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Could a response that use the <code>must-understand</code> directive be stored if the cache does not understand the directive?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'No, if the cache does not understand the must-understand directive, it must not store the response.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control#directives',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q9 - DI - Environment variables usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Given the following configuration and the fact that the <code>.env</code> file exist with a key <code>APP_SECRET=bar</code>, which value will be used in <code>framework.secret</code>?
<pre><code class="language-yaml">
# config/packages/framework.yaml
parameters:
    env(SECRET): \'foo\'

framework:
    secret: \'%env(APP_SECRET)%\'</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The value from .env file (bar) will be used because APP_SECRET is different from SECRET. The env(SECRET) sets a default for SECRET, not APP_SECRET.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/configuration/env_var_processors.html#built-in-environment-variable-processors',
                'answers' => [
                    ['text' => '<code>bar</code>', 'correct' => true],
                    ['text' => '<code>foo</code>', 'correct' => false],
                    ['text' => 'An error will be thrown', 'correct' => false],
                ],
            ],

            // Q10 - Validator - Validation constraints
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Which of the followings are not validation constraints?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Search, All, and Password are not validation constraints. File is a valid constraint.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/constraints.html',
                'answers' => [
                    ['text' => 'Search', 'correct' => true],
                    ['text' => 'All', 'correct' => true],
                    ['text' => 'Password', 'correct' => true],
                    ['text' => 'File', 'correct' => false],
                ],
            ],

            // Q11 - PHP Basics - Streams
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Which of the following is NOT a default PHP input or output stream?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'php://error does not exist. The correct stream is php://stderr.',
                'resourceUrl' => 'http://php.net/manual/en/features.commandline.io-streams.php',
                'answers' => [
                    ['text' => '<code>php://error</code>', 'correct' => true],
                    ['text' => '<code>php://output</code>', 'correct' => false],
                    ['text' => '<code>php://stdin</code>', 'correct' => false],
                    ['text' => '<code>php://input</code>', 'correct' => false],
                    ['text' => '<code>php://stdout</code>', 'correct' => false],
                ],
            ],

            // Q12 - Runtime - HttpFoundation integration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__).\'/vendor/autoload_runtime.php\';

return function (): Response {
    return new Response(\'Hello world\');
};</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, the Runtime component supports returning a Response directly from the closure.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/components/runtime.html#using-the-runtime',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q13 - HttpClient - getInfo debug
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'Which method call allows to retrieve detailed logs about the requests and the responses of an http transaction?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The getInfo(\'debug\') method returns detailed logs about the HTTP transaction.',
                'resourceUrl' => 'https://symfony.com/doc/5.0/components/http_client.html',
                'answers' => [
                    ['text' => 'getInfo(\'debug\')', 'correct' => true],
                    ['text' => 'getDebugInfo()', 'correct' => false],
                    ['text' => 'getInfoDebug()', 'correct' => false],
                    ['text' => 'getDebug(\'info\')', 'correct' => false],
                ],
            ],

            // Q14 - VarDumper - global function
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarDumper'],
                'text' => 'What is the name of the global function added by the VarDumper Component?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The VarDumper component adds the dump() global function.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/var_dumper/introduction.html#the-dump-function',
                'answers' => [
                    ['text' => '<code>dump()</code>', 'correct' => true],
                    ['text' => '<code>var_dumper()</code>', 'correct' => false],
                    ['text' => '<code>var_dump()</code>', 'correct' => false],
                    ['text' => '<code>debug()</code>', 'correct' => false],
                ],
            ],

            // Q15 - Doctrine DBAL - Data types
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'Which of the following are built-in Doctrine DBAL data types?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'guid and datetimetz are valid Doctrine DBAL types. varchar, hash, and numeric are not.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html',
                'answers' => [
                    ['text' => '<code>guid</code>', 'correct' => true],
                    ['text' => '<code>datetimetz</code>', 'correct' => true],
                    ['text' => '<code>varchar</code>', 'correct' => false],
                    ['text' => '<code>hash</code>', 'correct' => false],
                    ['text' => '<code>numeric</code>', 'correct' => false],
                ],
            ],

            // Q16 - PHP OOP - Interfaces
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Is it allowed to make an interface use <em>traits</em>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'No, interfaces cannot use traits. Only classes can use traits.',
                'resourceUrl' => 'http://php.net/manual/fr/language.oop5.interfaces.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q17 - PHP Arrays - Extract usage
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'Should <code>extract</code> be used on <code>$_GET</code>, <code>$_FILES</code> and other unsecured data sources?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'No, using extract() on untrusted data like $_GET or $_FILES is a security risk as it can overwrite existing variables.',
                'resourceUrl' => 'https://www.php.net/manual/en/function.extract.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q18 - Twig - Block names
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following are valid block names?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Valid block names must start with a letter or underscore and can contain letters, numbers, and underscores.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/block.html',
                'answers' => [
                    ['text' => 'foo_bar', 'correct' => true],
                    ['text' => 'foo123', 'correct' => true],
                    ['text' => '_foo', 'correct' => true],
                    ['text' => 'foo.bar', 'correct' => false],
                    ['text' => '.foo', 'correct' => false],
                    ['text' => '123foo', 'correct' => false],
                    ['text' => '-foo', 'correct' => false],
                ],
            ],

            // Q19 - Twig - Escaping
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given <code>var</code> and <code>bar</code> are existing variables, among the following, which expressions are escaped?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The raw filter must be the last filter applied. {{ var|raw|upper }} and {{ var|raw~bar }} will be escaped because raw is not the last operation.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/raw.html',
                'answers' => [
                    ['text' => '<code>{{ var|raw|upper }}</code>', 'correct' => true],
                    ['text' => '<code>{{ var|raw~bar }}</code>', 'correct' => true],
                    ['text' => '<code>{{ var|upper|raw }}</code>', 'correct' => false],
                ],
            ],

            // Q21 - Forms - PreSubmitEvent getData
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What is returned by the <code>getData()</code> method of <code>PreSubmitEvent</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The getData() method of PreSubmitEvent returns an array containing the raw submitted data.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/form/events.html',
                'answers' => [
                    ['text' => 'an array', 'correct' => true],
                    ['text' => 'The model data of the form', 'correct' => false],
                    ['text' => 'the norm data of the form', 'correct' => false],
                    ['text' => 'the view data of the form', 'correct' => false],
                ],
            ],

            // Q22 - BrowserKit - HttpBrowser lifecycle
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:BrowserKit'],
                'text' => 'Once started, could the <code>HttpBrowser</code> be restarted?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, the HttpBrowser can be restarted using the restart() method.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/BrowserKit/HttpBrowser.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q23 - PHP OOP - Visibility
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Which of the following are supported visibilities for class attributes and methods in PHP?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'PHP supports public, protected, and private visibility. Global and Friend are not PHP visibility keywords.',
                'resourceUrl' => 'http://php.net/manual/en/language.oop5.visibility.php',
                'answers' => [
                    ['text' => 'Protected', 'correct' => true],
                    ['text' => 'Private', 'correct' => true],
                    ['text' => 'Public', 'correct' => true],
                    ['text' => 'Global', 'correct' => false],
                    ['text' => 'Friend', 'correct' => false],
                ],
            ],

            // Q25 - Console - Command interact
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'When is the <code>Symfony\Component\Console\Command::interact</code> method executed?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The interact() method is executed after initialize(), before the InputDefinition is validated, and before execute().',
                'resourceUrl' => 'http://symfony.com/doc/current/console.html#command-lifecycle',
                'answers' => [
                    ['text' => 'Before <code>Symfony\Component\Console\Command::execute</code> method.', 'correct' => true],
                    ['text' => 'After <code>Symfony\Component\Console\Command::initialize</code> method.', 'correct' => true],
                    ['text' => 'Before the <code>InputDefinition</code> is validated.', 'correct' => true],
                    ['text' => 'After the <code>InputDefinition</code> is validated.', 'correct' => false],
                    ['text' => 'Before <code>Symfony\Component\Console\Command::initialize</code> method.', 'correct' => false],
                    ['text' => 'After <code>Symfony\Component\Console\Command::execute</code> method.', 'correct' => false],
                ],
            ],

            // Q26 - HttpFoundation - Simulate a Request
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'How could you simulate a GET request to <code>/hello-world</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Request::create() takes URI as first argument and method as second. GET is the default method.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/components/http_foundation.html#simulating-a-request',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$request = Request::create(
    \'/hello-world\',
    \'GET\'
);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$request = Request::create(
    \'/hello-world\'
);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$request = Request::create(
    \'GET\',
    \'/hello-world\'
);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$request = Request::create(
    null,
    \'/hello-world\'
);</code></pre>', 'correct' => false],
                ],
            ],

            // Q27 - PHP Functions - Function arguments
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Functions'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">function bake()
{
    $third = ??? ;
    // ...
}

bake(\'flour\', \'spinach\', \'egg\', \'tomato\', \'salt\');</code></pre>
<p>Which statement does the <code>???</code> placeholder replace in order to store the third passed arguments in the <code>$third</code> variable?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'func_get_arg(2) returns the third argument (0-indexed), and func_get_args()[2] also returns the third element.',
                'resourceUrl' => 'http://php.net/manual/en/function.func-get-arg.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">func_get_arg(2)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">func_get_args()[2]</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$_ARGS[2]</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$argv[3]</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">func_get_arg(3)</code></pre>', 'correct' => false],
                ],
            ],

            // Q28 - Mailer - Multiple transports
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mailer'],
                'text' => 'Can you configure multiple transports to ensure that emails are sent even if one mailer server fails?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, Symfony Mailer supports high availability configuration with multiple transports.',
                'resourceUrl' => 'https://symfony.com/doc/6.0/mailer#high-availability',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q29 - HttpKernel - ControllerArgumentsEvent usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Could the controller named arguments be retrieved from within <code>ControllerArgumentsEvent</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 6.2, ControllerArgumentsEvent provides getNamedArguments() method.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/HttpKernel/Event/ControllerArgumentsEvent.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q30 - Forms - CollectionType usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Could empty <code>CollectionType</code> entries be removed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, using the delete_empty option on CollectionType will remove empty entries.',
                'resourceUrl' => 'https://symfony.com/doc/2.5/reference/forms/types/collection.html#delete-empty',
                'answers' => [
                    ['text' => 'Yes, using <code>delete_empty</code> option', 'correct' => true],
                    ['text' => 'Yes, using <code>resize_when_empty</code> option', 'correct' => false],
                    ['text' => 'Yes, using <code>remove_empty</code> option', 'correct' => false],
                    ['text' => 'It\'s not possible', 'correct' => false],
                    ['text' => 'The <code>CollectionType</code> automatically resize itself', 'correct' => false],
                ],
            ],

            // Q31 - Validator - Number constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Is it possible to check that a number is positive or equal to zero?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, Symfony provides the PositiveOrZero constraint for this purpose.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/reference/constraints/PositiveOrZero.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q32 - Expression Language - AST usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'Could the AST be dumped?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, the AST (Abstract Syntax Tree) can be dumped using the Expression Language component.',
                'resourceUrl' => 'https://symfony.com/doc/3.2/components/expression_language/ast.html#dumping-the-ast',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q33 - Finder - Read the contents
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Finder'],
                'text' => 'Which of the following code is correct to read the contents of returned files?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The getContents() method is the correct way to read file contents from Finder results.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/finder.html#reading-contents-of-returned-files',
                'answers' => [
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder-&gt;files()-&gt;in(__DIR__);

foreach ($finder as $file) {
    $contents = $file-&gt;getContents();

    // ...
}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder-&gt;files()-&gt;in(__DIR__);

foreach ($finder as $file) {
    $contents = $file-&gt;readFile();

    // ...
}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder-&gt;files()-&gt;in(__DIR__);

foreach ($finder as $file) {
    $contents = $file-&gt;read()-&gt;contents();

    // ...
}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder-&gt;files()-&gt;in(__DIR__);

foreach ($finder as $file) {
    $contents = $file-&gt;readContents();

    // ...
}</code></pre>', 'correct' => false],
                ],
            ],

            // Q34 - Process - Process handling
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'],
                'text' => 'Given a new process created in front of a Symfony command that return a <code>1</code> code, what will be returned by <code>Process::mustRun()</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'If the process returns a non-zero exit code, mustRun() throws a ProcessFailedException.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.5/src/Symfony/Component/Process/Process.php#L209',
                'answers' => [
                    ['text' => 'Nothing, a <code>ProcessFailedException</code> will be thrown', 'correct' => true],
                    ['text' => 'An instance of <code>Process</code>', 'correct' => false],
                    ['text' => '<code>false</code>', 'correct' => false],
                    ['text' => '<code>true</code>', 'correct' => false],
                    ['text' => '<code>1</code>', 'correct' => false],
                ],
            ],

            // Q35 - Twig - Twig loaders
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following classes are Twig loaders available by default?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'FilesystemLoader, ArrayLoader, and ChainLoader are available by default. CacheLoader and DoctrineLoader do not exist.',
                'resourceUrl' => 'https://github.com/twigphp/Twig/tree/2.x/src/Loader',
                'answers' => [
                    ['text' => 'FilesystemLoader', 'correct' => true],
                    ['text' => 'ArrayLoader', 'correct' => true],
                    ['text' => 'ChainLoader', 'correct' => true],
                    ['text' => 'CacheLoader', 'correct' => false],
                    ['text' => 'DoctrineLoader', 'correct' => false],
                ],
            ],

            // Q36 - HttpKernel - Arguments resolution
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Could arguments be resolved in a controller not tagged with the <code>controller.service_arguments</code> tag?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'No, controllers must be tagged with controller.service_arguments to have their action arguments resolved.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/HttpKernel/Controller/ArgumentResolver/NotTaggedControllerValueResolver.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q37 - Twig - PHP constant
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Is it possible to display the value of a constant of a PHP class in a Twig template?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, using the constant() function in Twig.',
                'resourceUrl' => 'http://twig.symfony.com/doc/functions/constant.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
        ];
    }
}
