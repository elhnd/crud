<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 43
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures43 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures42::class];
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
            // Q1 - DI - Abstract services
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'Abstract services parent declaration',
                'text' => 'Should a parent service be declared as <code>abstract</code> if no class is set in its service definition?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, a parent service without a class should be declared as abstract to prevent it from being instantiated directly.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/service_container/parent_services.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q2 - Runtime - RunnerInterface::run() return type
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'],
                'title' => 'RunnerInterface::run() return type',
                'text' => 'Which internal type must be returned by <code>RunnerInterface::run()</code>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'RunnerInterface::run() must return an integer (exit code).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/Runtime/RunnerInterface.php',
                'answers' => [
                    ['text' => 'An integer', 'correct' => true],
                    ['text' => 'An array', 'correct' => false],
                    ['text' => 'Nothing', 'correct' => false],
                    ['text' => 'A string', 'correct' => false],
                ],
            ],

            // Q3 - PHP - Class constants with expressions
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'title' => 'Class constants with expressions',
                'text' => 'What will be the output of the following code?
<pre><code class="language-php">class Foo
{
    const BAR = 4+1;
}
echo Foo::BAR;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP supports constant expressions since PHP 5.6. The expression 4+1 is evaluated at compile time.',
                'resourceUrl' => 'http://php.net/manual/en/language.constants.syntax.php',
                'answers' => [
                    ['text' => '<pre><code>5</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>4</code></pre>', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '<pre><code>1</code></pre>', 'correct' => false],
                ],
            ],

            // Q4 - PHP - CLI superglobal
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'title' => 'CLI command line arguments superglobal',
                'text' => 'Which PHP superglobal variable contains the command line arguments when the script runs in CLI mode?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '$_SERVER contains the command line arguments in CLI mode (via $argv which is registered in $_SERVER).',
                'resourceUrl' => 'http://php.net/manual/en/language.variables.superglobals.php',
                'answers' => [
                    ['text' => '<code>$_SERVER</code>', 'correct' => true],
                    ['text' => 'PHP cannot run from the command line interface.', 'correct' => false],
                    ['text' => '<code>$_POST</code>', 'correct' => false],
                    ['text' => '<code>$_ENV</code>', 'correct' => false],
                    ['text' => '<code>$_CLI</code>', 'correct' => false],
                ],
            ],

            // Q5 - CssSelector - XPath vs CssSelector
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:CssSelector'],
                'title' => 'XPath vs CssSelector difference',
                'text' => 'What is the main difference between <code>XPath</code> and <code>CssSelector</code> syntax?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'CssSelector has a simpler syntax but is less powerful than XPath, which can traverse the DOM in more complex ways.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/css_selector.html',
                'answers' => [
                    ['text' => 'CssSelector has a simpler syntax but is less powerful than XPath', 'correct' => true],
                    ['text' => 'XPath has just been invented before CssSelector syntax but both have the exact same features', 'correct' => false],
                    ['text' => 'XPath can only be used with XML files, while CssSelector supports both XML and HTML files', 'correct' => false],
                ],
            ],

            // Q6 - Cache - Key/value store differences
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'title' => 'Cache vs key/value store differences',
                'text' => 'How does a cache differ from a key / value store?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'A cache can be deleted without crashing the app and should not be used for persistent data. Key/value stores are for safe persistent storage.',
                'resourceUrl' => 'http://www.aerospike.com/what-is-a-key-value-store/',
                'answers' => [
                    ['text' => 'It can be deleted without making the application crash.', 'correct' => true],
                    ['text' => 'It should not be used to persist data.', 'correct' => true],
                    ['text' => 'It is safe to store data in it.', 'correct' => false],
                ],
            ],

            // Q7 - HttpKernel - kernel->handle() result
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'title' => 'HttpKernel handle() return type',
                'text' => 'Consider the following code:
<pre><code class="language-php">$result = $kernel->handle($request);</code></pre>
<p>What does the <code>$result</code> variable contain at the end of this script?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The HttpKernel::handle() method returns a Symfony\Component\HttpFoundation\Response instance.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_kernel/introduction.html',
                'answers' => [
                    ['text' => 'A <code>Symfony\Component\HttpFoundation\Response</code> instance.', 'correct' => true],
                    ['text' => 'An array.', 'correct' => false],
                    ['text' => 'A string.', 'correct' => false],
                    ['text' => 'A <code>Symfony\Component\BrowserKit\Response</code> instance.', 'correct' => false],
                    ['text' => 'A <code>Symfony\Component\HttpFoundation\Request</code> instance.', 'correct' => false],
                    ['text' => 'A <code>Symfony\Component\BrowserKit\Request</code> instance.', 'correct' => false],
                ],
            ],

            // Q8 - Filesystem - Path::getRoot()
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'title' => 'Filesystem Path::getRoot() usage',
                'text' => 'Given the following code, what will be displayed?
<pre><code class="language-php">&lt;?php

use Symfony\Component\Filesystem\Path;

echo Path::getRoot("/etc/apache2/sites-available");</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Path::getRoot() returns the root of the filesystem path, which is "/" on Unix systems.',
                'resourceUrl' => 'https://symfony.com/doc/5.4/components/filesystem.html#finding-directories-root-directories',
                'answers' => [
                    ['text' => '<code>/</code>', 'correct' => true],
                    ['text' => '<code>/etc/apache2</code>', 'correct' => false],
                    ['text' => '<code>/etc/apache2/sites-available</code>', 'correct' => false],
                    ['text' => '<code>/etc/apache2/</code>', 'correct' => false],
                    ['text' => '<code>/etc/</code>', 'correct' => false],
                    ['text' => '<code>/etc</code>', 'correct' => false],
                ],
            ],

            // Q9 - HttpKernel - NO_AUTO_CACHE_CONTROL_HEADER on sub-requests
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'title' => 'NO_AUTO_CACHE_CONTROL_HEADER on sub-requests',
                'text' => 'Could the <code>AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER</code> header directive be used on sub-requests?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'No, the NO_AUTO_CACHE_CONTROL_HEADER header directive only works on main requests, not sub-requests.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/http_cache.html#http-caching-and-user-sessions',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q10 - HttpCache - SSI render_ssi
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpCache'],
                'title' => 'Server Side Includes render_ssi usage',
                'text' => 'Is the following code valid when using <code>Server Side Includes</code>?
<pre><code class="language-twig">{{ render_ssi(controller(\'App\\Controller\\ProfileController::gdpr\')) }}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, render_ssi() can be used with controller() to render a controller action using Server Side Includes.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/http_cache/ssi.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q11 - HttpFoundation - Accessing $_GET data
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'Accessing $_GET data from Request',
                'text' => 'How to access <code>$_GET</code> data when using a <code>Symfony\Component\HttpFoundation\Request</code> <code>$request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The query property of the Request object contains the $_GET data.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#accessing-request-data',
                'answers' => [
                    ['text' => '<pre><code>$request->query</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>$request->getData()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getQueryData()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getGetData()</code></pre>', 'correct' => false],
                ],
            ],

            // Q12 - Filesystem - lazy vs eager
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'title' => 'Filesystem lazy vs eager implementation',
                'text' => 'Is the Filesystem component based on a lazy or eager implementation?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Filesystem component uses a lazy implementation - operations are performed immediately when called.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/Filesystem/Filesystem.php',
                'answers' => [
                    ['text' => 'Lazy', 'correct' => true],
                    ['text' => 'Eager', 'correct' => false],
                ],
            ],

            // Q13 - DI - Constants in parameters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'Using constants in service parameters',
                'text' => 'Is it possible to use a constant in a parameter?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Constants can be used in PHP and XML format configurations, but not directly in YAML without expression language.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/service_container/parameters.html#constants-as-parameters',
                'answers' => [
                    ['text' => 'Yes, in PHP', 'correct' => true],
                    ['text' => 'Yes in XML format', 'correct' => true],
                    ['text' => 'Yes, in the YAML format (thanks to the expression language component)', 'correct' => false],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q14 - HttpKernel - Events order
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'title' => 'Kernel events order',
                'text' => 'In which order does Symfony trigger the following events?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The correct order is: kernel.request → kernel.controller → kernel.view → kernel.response → kernel.terminate.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/HttpKernel/KernelEvents.php',
                'answers' => [
                    ['text' => '<ol><li><code>kernel.request</code></li><li><code>kernel.controller</code></li><li><code>kernel.view</code></li><li><code>kernel.response</code></li><li><code>kernel.terminate</code></li></ol>', 'correct' => true],
                    ['text' => '<ol><li><code>kernel.request</code></li><li><code>kernel.controller</code></li><li><code>kernel.view</code></li><li><code>kernel.terminate</code></li><li><code>kernel.response</code></li></ol>', 'correct' => false],
                    ['text' => '<ol><li><code>kernel.request</code></li><li><code>kernel.view</code></li><li><code>kernel.controller</code></li><li><code>kernel.response</code></li><li><code>kernel.terminate</code></li></ol>', 'correct' => false],
                    ['text' => '<ol><li><code>kernel.request</code></li><li><code>kernel.view</code></li><li><code>kernel.controller</code></li><li><code>kernel.terminate</code></li><li><code>kernel.response</code></li></ol>', 'correct' => false],
                ],
            ],

            // Q15 - Twig - Template inheritance error
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig template inheritance syntax error',
                'text' => 'Consider the following Twig code snippet:
<pre><code class="language-twig">{% extends \'layout.html.twig\' %}

{% block title \'Hello World!\' %}

My name is Amanda.
</code></pre>
<p>What will be the result of evaluating this Twig template?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When extending a template, you cannot have content outside of blocks. This will raise a Twig_Error_Syntax exception.',
                'resourceUrl' => 'http://twig.symfony.com/doc/tags/extends.html',
                'answers' => [
                    ['text' => 'Twig will raise a <code>Twig_Error_Syntax</code> exception preventing the template from being evaluated.', 'correct' => true],
                    ['text' => 'The template is successfully evaluated and the string <em>My name is Amanda</em> will be displayed in the web browser.', 'correct' => false],
                ],
            ],

            // Q16 - Twig - Operators (first-page parsing)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig operators with hyphenated keys',
                'text' => 'What is the value of <code>$render</code> with the following code?
<pre><code class="language-php">$data = [
    \'first\' => 0,
    \'first-page\' => 1
];

$render = $twig->render(\'index.html.twig\', [\'page\' => 5,  \'data\' => $data]);</code></pre>
<pre><code class="language-twig">{# index.html.twig #}
{{ data.first-page }}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Twig interprets data.first-page as data.first minus page (0 - 5 = -5), not as the key "first-page".',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/templates.html#math',
                'answers' => [
                    ['text' => '<code>\'-5\'</code>', 'correct' => true],
                    ['text' => '<code>\'1\'</code>', 'correct' => false],
                    ['text' => '<code>\'0\'</code>', 'correct' => false],
                    ['text' => 'null', 'correct' => false],
                ],
            ],

            // Q17 - Console - Verbosity levels
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'title' => 'Console verbosity levels',
                'text' => 'What are the console verbosity levels?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The valid verbosity levels are QUIET, NORMAL, VERBOSE, VERY_VERBOSE, and DEBUG.',
                'resourceUrl' => 'http://symfony.com/doc/current/console/verbosity.html',
                'answers' => [
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_NORMAL</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_DEBUG</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_QUIET</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_VERBOSE</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_VERY_VERBOSE</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_NONE</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_VERY_VERY_VERBOSE</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_NO_DEBUG</code></pre>', 'correct' => false],
                ],
            ],

            // Q18 - Twig - Loaders definition
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig loaders responsibility',
                'text' => 'What are Twig loaders responsible for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Loaders are responsible for loading templates from a resource name (filesystem, database, etc.).',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/api.html#loaders',
                'answers' => [
                    ['text' => 'Loaders are responsible for loading templates from a resource name.', 'correct' => true],
                    ['text' => 'Loaders are responsible for loading token parsers.', 'correct' => false],
                    ['text' => 'Loaders are responsible for loading environments such as Twig_Evironment.', 'correct' => false],
                    ['text' => 'Loaders are responsible for loading extensions.', 'correct' => false],
                ],
            ],

            // Q19 - HttpFoundation - Response::create() since 6.0
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'Response::create() removal in Symfony 6.0',
                'text' => 'Since <code>6.0</code>, could a response be created via <code>Response::create()</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, Response::create() was deprecated in 5.4 and removed in 6.0. Use new Response() instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/HttpFoundation/Response.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q20 - PHP - PSR-6 definition
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PSR'],
                'title' => 'PSR-6 definition',
                'text' => 'What is PSR-6?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PSR-6 defines a common caching interface for PHP applications.',
                'resourceUrl' => 'http://www.php-fig.org/psr/psr-6/',
                'answers' => [
                    ['text' => 'A caching interface.', 'correct' => true],
                    ['text' => 'A common logger interface.', 'correct' => false],
                    ['text' => 'A PHP Doc interface.', 'correct' => false],
                    ['text' => 'A basic coding standard.', 'correct' => false],
                ],
            ],

            // Q21 - HttpFoundation - IP anonymization
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'IP address anonymization',
                'text' => 'Could an IP address be anonymized?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the IpUtils class provides methods to anonymize IP addresses.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/components/http_foundation.html#anonymizing-ip-addresses',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q22 - HttpFoundation - Accessing session
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'Accessing the session from Request',
                'text' => 'What is the way to access the session from the <code>$request</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Use $request->getSession() to access the session from a Request object.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/components/http_foundation.html#accessing-the-session',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$request->getSession()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$request->getPhpSession()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$request->fetchSession()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$request->session</code></pre>', 'correct' => false],
                ],
            ],

            // Q23 - PHP - __METHOD__ constant
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'title' => '__METHOD__ predefined constant',
                'text' => 'What will be the output of the following code?
<pre><code class="language-php">&lt;?php

namespace Foo;

class Bar
{
    public function baz()
    {
        return __METHOD__;
    }
}

$b = new Bar;
echo $b->baz();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '__METHOD__ returns the fully qualified method name including the namespace.',
                'resourceUrl' => 'http://php.net/manual/en/language.constants.predefined.php',
                'answers' => [
                    ['text' => '<pre><code>Foo\\Bar::baz</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>baz</code></pre>', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '<pre><code>Bar::baz</code></pre>', 'correct' => false],
                ],
            ],

            // Q24 - Console - Testing commands with ApplicationTester
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'title' => 'Testing commands with console events',
                'text' => 'Which <code>Tester</code> class should be used when testing a command that relies on console events (e.g. the <code>ConsoleEvents::TERMINATE</code> event)?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ApplicationTester should be used when testing commands that rely on console events, as CommandTester doesn\'t dispatch events.',
                'resourceUrl' => 'https://symfony.com/doc/3.x/console.html#command-lifecycle',
                'answers' => [
                    ['text' => '<code>Symfony\\Component\\Console\\Tester\\ApplicationTester</code>', 'correct' => true],
                    ['text' => '<code>Symfony\\Component\\Console\\Tester\\CommandCompletionTester</code>', 'correct' => false],
                    ['text' => '<code>Symfony\\Component\\Console\\Tester\\CommandTester</code>', 'correct' => false],
                ],
            ],

            // Q25 - Templating - lint:twig deprecations
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'lint:twig deprecations display',
                'text' => 'Could deprecations be displayed when using <code>bin/console lint:twig</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, lint:twig can display deprecations since Symfony 4.4.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/templates.html#linting-twig-templates',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q26 - HttpFoundation - setSharedMaxAge
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'Response::setSharedMaxAge() behavior',
                'text' => 'Using the <code>Response::setSharedMaxAge()</code> method is equivalent to using both <code>Response::setPublic()</code> and <code>Response::setMaxAge()</code>.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'False. setSharedMaxAge() sets the s-maxage directive which is different from max-age. It also makes the response public, but it\'s not equivalent to setMaxAge().',
                'resourceUrl' => 'https://symfony.com/doc/6.0/http_cache/expiration.html#expiration-with-the-cache-control-header',
                'answers' => [
                    ['text' => 'False', 'correct' => true],
                    ['text' => 'True', 'correct' => false],
                ],
            ],

            // Q27 - Twig - strict_variables off
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig strict_variables off behavior',
                'text' => 'Consider the following Twig code snippet:
<pre><code class="language-twig">The {{ color }} car!</code></pre>
<p>What will be the result of evaluating this template without passing it a <code>color</code> variable when the <code>strict_variables</code> global setting is off?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'With strict_variables off, undefined variables are replaced with empty strings.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/templates.html#variables',
                'answers' => [
                    ['text' => 'The template will be succesfully evaluated and the string <code>The  car!</code> will be displayed in the web browser.', 'correct' => true],
                    ['text' => 'Twig will raise a <code>Twig_Error_Runtime</code> exception preventing the template from being evaluated.', 'correct' => false],
                    ['text' => 'The template will be succesfully evaluated and the string <code>The empty car!</code> will be displayed in the web browser.', 'correct' => false],
                    ['text' => 'The template will be partially evaluated and the string <code>The</code> will be displayed in the web browser.', 'correct' => false],
                ],
            ],

            // Q28 - Form - HTTP method override
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'title' => 'Form HTTP method override',
                'text' => 'Consider the following HTML generated from a Symfony form:
<pre><code class="language-html">&lt;form method="POST" action="/"&gt;
    &lt;input type="hidden" name="_method" value="PUT" /&gt;
&lt;/form&gt;</code></pre>
<p>Which HTTP method will be present in the Symfony <code>Request</code> object assuming HTTP methods overriding setting is turned on?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When HTTP method overriding is enabled, the _method field overrides the form method, so the Request will contain PUT.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/form/action_method.html',
                'answers' => [
                    ['text' => 'PUT', 'correct' => true],
                    ['text' => 'POST', 'correct' => false],
                    ['text' => 'GET', 'correct' => false],
                    ['text' => 'PATCH', 'correct' => false],
                ],
            ],

            // Q29 - HttpFoundation - JSONP callback
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'JSONP callback support',
                'text' => 'Could a JSONP callback be set?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, JsonResponse provides setCallback() method for JSONP support.',
                'resourceUrl' => 'https://symfony.com/doc/2.1/components/http_foundation/introduction.html#jsonp-callback',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q30 - Validator - ConstraintViolationListInterface __toString
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'title' => 'ConstraintViolationListInterface __toString requirement',
                'text' => 'Could <code>ConstraintViolationListInterface</code> be implemented without implementing <code>__toString()</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, since Symfony 6.1, ConstraintViolationListInterface requires __toString() to be implemented.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.1.md#validator',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q31 - Form - LanguageType choices
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'title' => 'LanguageType choices provider',
                'text' => 'By default, which function provides the choices of the <code>Symfony\\Component\\Form\\Extension\\Core\\Type\\LanguageType</code> form type?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Since Symfony 4.3, Languages::getNames() from the Intl component provides the choices.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/reference/forms/types/language.html#choices',
                'answers' => [
                    ['text' => '<code>Symfony\\Component\\Intl\\Languages::getNames()</code>', 'correct' => true],
                    ['text' => '<code>Symfony\\Component\\Intl\\Intl::getLanguageBundle()->getLanguageNames()</code>', 'correct' => false],
                    ['text' => '<code>Intl::getLanguages()</code>', 'correct' => false],
                    ['text' => '<code>Symfony\\Component\\Form\\Extension\\Core\\Type\\LanguageType::getChoices()</code>', 'correct' => false],
                ],
            ],

            // Q32 - DI - Enumerations in env vars
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'Enumerations in environment variables',
                'text' => 'Can enumerations be used in environment variables?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 6.2, enumerations can be used in environment variables.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-2-improved-enum-support#enums-in-environment-variables',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q33 - HttpKernel - kernel.view event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'title' => 'kernel.view event dispatch condition',
                'text' => 'What kernel event is dispatched when a controller does not return a <code>Response</code> object ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The kernel.view event is dispatched when the controller doesn\'t return a Response object.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/reference/events.html',
                'answers' => [
                    ['text' => '<code>kernel.view</code>', 'correct' => true],
                    ['text' => '<code>kernel.finish_request</code>', 'correct' => false],
                    ['text' => '<code>kernel.terminate</code>', 'correct' => false],
                    ['text' => '<code>kernel.response</code>', 'correct' => false],
                ],
            ],

            // Q34 - HttpFoundation - Response classes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'Valid Response subclasses',
                'text' => 'Which of the following are valid Symfony response classes extending the base <code>Symfony\\Component\\HttpFoundation\\Response</code> class?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The valid Response subclasses are JsonResponse, RedirectResponse, StreamedResponse, and BinaryFileResponse.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation/introduction.html',
                'answers' => [
                    ['text' => '<code>JsonResponse</code>', 'correct' => true],
                    ['text' => '<code>RedirectResponse</code>', 'correct' => true],
                    ['text' => '<code>StreamedResponse</code>', 'correct' => true],
                    ['text' => '<code>BinaryFileResponse</code>', 'correct' => true],
                    ['text' => '<code>RedirectedResponse</code>', 'correct' => false],
                    ['text' => '<code>ImageFileResponse</code>', 'correct' => false],
                    ['text' => '<code>ImageResponse</code>', 'correct' => false],
                    ['text' => '<code>NotFoundResponse</code>', 'correct' => false],
                    ['text' => '<code>BinaryResponse</code>', 'correct' => false],
                    ['text' => '<code>StreamResponse</code>', 'correct' => false],
                    ['text' => '<code>FileResponse</code>', 'correct' => false],
                ],
            ],

            // Q35 - Console - Terminal width
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'title' => 'Terminal::getWidth() fallback value',
                'text' => 'What will be the value stored in <code>$width</code> when using the following code within a command and assuming that no environment variables are set and the <code>stty</code> command isn\'t available.
<pre><code class="language-php">&lt;?php

use Symfony\Component\Console\Terminal;

$width = (new Terminal())->getWidth();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'When terminal dimensions cannot be determined, Terminal::getWidth() returns 80 as the default fallback.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/Console/Terminal.php',
                'answers' => [
                    ['text' => '<code>80</code>', 'correct' => true],
                    ['text' => '<code>0</code>', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '<code>200</code>', 'correct' => false],
                    ['text' => '<code>120</code>', 'correct' => false],
                    ['text' => 'Nothing', 'correct' => false],
                    ['text' => 'The width of the actual terminal', 'correct' => false],
                ],
            ],

            // Q36 - Twig - Template inheritance extends position
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig extends tag position',
                'text' => 'Is this Twig template valid ?
<pre><code class="language-twig">&lt;h1&gt;{{ title }}&lt;/h1&gt;

{% extends \'base.html.twig\' %}

{% block content %}
    &lt;p&gt;{{ content }}&lt;/p&gt;
{% endblock %}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, the extends tag must be the first tag in a template. Having content before it is invalid.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/extends.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
        ];
    }
}
