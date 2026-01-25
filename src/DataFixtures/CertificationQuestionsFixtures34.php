<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 34
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures34 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures33::class];
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
            // Q1 - PHP Basics - PSR-0 and PSR-4
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
                    ['text' => 'A common logger interface.', 'correct' => false],
                    ['text' => 'A coding style guide.', 'correct' => false],
                    ['text' => 'A utility to convert non-namespaced PHP classes into namespaced ones.', 'correct' => false],
                ],
            ],

            // Q2 - HTTP - Status code Created
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'What is the status code for Created?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'HTTP 201 Created indicates that the request has been fulfilled and a new resource has been created.',
                'resourceUrl' => 'https://developer.mozilla.org/fr/docs/Web/HTTP/Status/201',
                'answers' => [
                    ['text' => '201', 'correct' => true],
                    ['text' => '200', 'correct' => false],
                    ['text' => '301', 'correct' => false],
                    ['text' => '302', 'correct' => false],
                    ['text' => '204', 'correct' => false],
                ],
            ],

            // Q3 - Security - SecurityBundle services
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which of the following services exist since Symfony 2.3 and still exist in any Symfony 3.x versions?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'security.firewall and security.encoder_factory exist since 2.3 and still exist in 3.x. security.context was deprecated in 2.6 and removed in 3.0. security.secure_random was removed.',
                'resourceUrl' => 'https://github.com/symfony/symfony/tree/3.0/src/Symfony/Bundle/SecurityBundle/Resources/config',
                'answers' => [
                    ['text' => '<code>security.firewall</code>', 'correct' => true],
                    ['text' => '<code>security.encoder_factory</code>', 'correct' => true],
                    ['text' => '<code>security.authentication_utils</code>', 'correct' => true],
                    ['text' => '<code>security.context</code>', 'correct' => false],
                    ['text' => '<code>security.secure_random</code>', 'correct' => false],
                ],
            ],

            // Q4 - Best Practices - Configuration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'According to the official Symfony <em>Best Practices Guide</em>, where should the application specific configuration parameters be stored?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'According to Symfony Best Practices, application-specific configuration should be stored in config/services.yaml.',
                'resourceUrl' => 'http://symfony.com/doc/4.0/best_practices/configuration.html#application-related-configuration',
                'answers' => [
                    ['text' => 'In the <code>config/services.yaml</code> file.', 'correct' => true],
                    ['text' => 'In the <code>.app.yml</code> file at the root of the project directory.', 'correct' => false],
                    ['text' => 'In global environment variables.', 'correct' => false],
                    ['text' => 'In the <code>config/parameters.yaml</code> file.', 'correct' => false],
                    ['text' => 'In the <code>src/Kernel.php</code> file.', 'correct' => false],
                ],
            ],

            // Q5 - PHP - Anonymous functions
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What will be the output of the following code?
<pre><code class="language-php">&lt;?php

// NB : `str_repeat(string $string, int $times): string`

function foo(int $y)
{
    return function(int $x) use ($y): string {
        return str_repeat((string) $y, $x); 
    };
}

$a = foo(3);
$b = foo(2);

echo $a(2), $b(3);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '$a = foo(3) returns a closure that repeats "3" x times. $a(2) = "33". $b = foo(2) returns a closure that repeats "2" x times. $b(3) = "222". Output: "33222".',
                'resourceUrl' => 'http://php.net/manual/en/functions.anonymous.php',
                'answers' => [
                    ['text' => '<pre><code>33222</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>66</code></pre>', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '<pre><code>22233</code></pre>', 'correct' => false],
                ],
            ],

            // Q6 - Twig - Custom escaper
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Can we create a custom escaper for Twig?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, Twig allows creating custom escapers using the setEscaper() method on the Environment.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/escape.html#custom-escapers',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q7 - HttpFoundation - Accessing cookies
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'How to access <code>$_COOKIE</code> data when using a <code>Symfony\Component\HttpFoundation\Request</code> <code>$request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The Request object provides access to cookies through the $request->cookies property.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#accessing-request-data',
                'answers' => [
                    ['text' => '<pre><code>$request->cookies</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>$request->getCookie()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getCookieData()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->cookie</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getCookies()</code></pre>', 'correct' => false],
                ],
            ],

            // Q8 - PHP OOP - Magic methods
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Which of the following are a magic method?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '__set_state(), __get(), __serialize(), __invoke(), and __wakeup() are all PHP magic methods.',
                'resourceUrl' => 'http://php.net/manual/en/language.oop5.magic.php',
                'answers' => [
                    ['text' => '<code>__set_state()</code>', 'correct' => true],
                    ['text' => '<code>__get()</code>', 'correct' => true],
                    ['text' => '<code>__serialize()</code>', 'correct' => true],
                    ['text' => '<code>__invoke()</code>', 'correct' => true],
                    ['text' => '<code>__wakeup()</code>', 'correct' => true],
                ],
            ],

            // Q9 - DI - Private services
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is it possible to create a service that is not publicly accessible?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, services can be marked as private using the public: false configuration.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/service_container/alias_private.html#marking-services-as-public-private',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q10 - FrameworkBundle - Controller registration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What are the different ways to register <code>IndexController::index</code> as a controller and/or have it respond to requests?
<pre><code class="language-php">class IndexController
{
    public function index(): Response
    {
        // ...
    }
}</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Controllers can be registered by extending AbstractController, using #[AsController] on the class, or using #[Route] attribute on the class or method.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-3-service-autoconfiguration-and-attributes',
                'answers' => [
                    ['text' => 'Add the <code>#[Route]</code> attribute to the method', 'correct' => true],
                    ['text' => 'Make the class extend <code>AbstractController</code>', 'correct' => true],
                    ['text' => 'Add the <code>#[Route]</code> attribute to the class', 'correct' => true],
                    ['text' => 'Add the <code>#[AsController]</code> attribute to the class', 'correct' => true],
                    ['text' => 'Add the <code>#[Controller]</code> attribute to the class', 'correct' => false],
                    ['text' => 'Add the <code>#[AsController]</code> attribute to the method', 'correct' => false],
                    ['text' => 'Add the <code>#[Controller]</code> attribute to the method', 'correct' => false],
                ],
            ],

            // Q11 - DI - Container parameters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => '<pre><code class="language-php">$containerBuilder->setParameter(\'foo\', \'bar\');
$containerBuilder->setParameter(\'_foo\', \'bar\');
$containerBuilder->setParameter(\'.foo\', \'bar\');</code></pre>
<p>Which of the following container parameters will be accessible after the compilation?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Parameters starting with a dot (.) are "build parameters" and are removed after compilation. Regular parameters and those starting with underscore remain accessible.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-dx-improvements-part-2#build-parameters-in-service-container',
                'answers' => [
                    ['text' => 'foo', 'correct' => true],
                    ['text' => '_foo', 'correct' => true],
                    ['text' => '.foo', 'correct' => false],
                ],
            ],

            // Q12 - VarDumper - Dumpers
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarDumper'],
                'text' => 'What is a <strong>Dumper</strong>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'A dumper is responsible for outputting a string representation of a PHP variable, using the cloned data from a Cloner.',
                'resourceUrl' => 'https://symfony.com/doc/2.6/components/var_dumper/advanced.html#dumpers',
                'answers' => [
                    ['text' => 'A dumper is responsible for outputting a string representation of a PHP variable.', 'correct' => true],
                    ['text' => 'A dumper is responsible for getting each property values of PHP object.', 'correct' => false],
                    ['text' => 'A dumper is responsible for creating a <code>var_dump</code> of any PHP variable.', 'correct' => false],
                    ['text' => 'A dumper is responsible for creating a <code>var_export</code> of any PHP variable.', 'correct' => false],
                ],
            ],

            // Q13 - Lock - Auto release
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Lock'],
                'text' => 'Could lock be prevented to be released when the related <code>Lock</code> object is destroyed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, using the createLock() method with autoRelease set to false, the lock will not be automatically released when the Lock object is destroyed.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/components/lock.html#automatically-releasing-the-lock',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q14 - DI - FrozenParameterBag exception
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which exception is thrown when clearing parameters from a <code>FrozenParameterBag</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'FrozenParameterBag throws a LogicException when trying to modify it (clear, set, add, remove).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php',
                'answers' => [
                    ['text' => '<code>LogicException</code>', 'correct' => true],
                    ['text' => '<code>RuntimeException</code>', 'correct' => false],
                    ['text' => '<code>InvalidArgumentException</code>', 'correct' => false],
                    ['text' => '<code>BadMethodCallException</code>', 'correct' => false],
                ],
            ],

            // Q15 - Process - SIGKILL signal
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'],
                'text' => 'Given <code>$process</code> is a <code>Process</code> object that runs a command asynchronously; calling <code>$process->stop(3)</code> will immediately send a <code>SIGKILL</code> signal to the running command.',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'False. The stop() method first sends a SIGTERM signal, then waits for the timeout (3 seconds), and only then sends SIGKILL if the process is still running.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Process/Process.php#L636',
                'answers' => [
                    ['text' => 'False', 'correct' => true],
                    ['text' => 'True', 'correct' => false],
                ],
            ],

            // Q16 - Translation - LocaleSwitcher
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'What are the main features of <code>LocaleSwitcher</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'LocaleSwitcher allows setting the locale, retrieving the current locale, and executing a callback with a specific locale (runWithLocale).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/Translation/LocaleSwitcher.php',
                'answers' => [
                    ['text' => 'Set the default locale that will be used in the code after calling <code>setLocale()</code>', 'correct' => true],
                    ['text' => 'Retrieve the current locale of the application', 'correct' => true],
                    ['text' => 'Execute only a callback function with a given locale', 'correct' => true],
                    ['text' => 'Switch from a currency to another by doing the good conversion', 'correct' => false],
                    ['text' => 'Easily switch from timezone to timezone for a given <code>DateTime</code>', 'correct' => false],
                ],
            ],

            // Q17 - Doctrine - Identity Map
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'Assuming there is an article with the title "Hello World" and the id 1234, what will be the output?
<pre><code class="language-php">$article = $entityManager->find(\'Article\', 1234);
$article->setTitle(\'Hello World dude!\');

$article2 = $entityManager->find(\'Article\', 1234);
echo $article2->getTitle();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Due to the Identity Map pattern, Doctrine returns the same instance for the same entity ID. The modified title will be returned.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/working-with-objects.html#entities-and-the-identity-map',
                'answers' => [
                    ['text' => 'Hello World dude!', 'correct' => true],
                    ['text' => 'Hello World', 'correct' => false],
                ],
            ],

            // Q18 - HTTP - ETag validation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which Request header is used to check the ETag validity?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The If-None-Match header is used by the client to send the ETag value for validation.',
                'resourceUrl' => 'http://symfony.com/doc/current/http_cache/validation.html#validation-with-the-etag-header',
                'answers' => [
                    ['text' => '<code>If-None-Match</code>', 'correct' => true],
                    ['text' => '<code>Last-Modified</code>', 'correct' => false],
                    ['text' => '<code>Cache-Control</code>', 'correct' => false],
                    ['text' => '<code>Etag</code>', 'correct' => false],
                ],
            ],

            // Q19 - Twig - Strict variables mode
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following Twig code snippet:
<pre><code class="language-twig">The {{ color }} car!</code></pre>
<p>What will be the result of evaluating this template without passing it a <code>color</code> variable when the <code>strict_variables</code> global setting is on?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When strict_variables is enabled, accessing an undefined variable throws a Twig_Error_Runtime exception.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/api.html#environment-options',
                'answers' => [
                    ['text' => 'Twig will raise a <code>Twig_Error_Runtime</code> exception preventing the template from being evaluated.', 'correct' => true],
                    ['text' => 'The template will be partially evaluated and the string <code>The</code> will be displayed in the web browser.', 'correct' => false],
                    ['text' => 'The template will be succesfully evaluated and the string <code>The  car!</code> will be displayed in the web browser.', 'correct' => false],
                    ['text' => 'The template will be succesfully evaluated and the string <code>The empty car!</code> will be displayed in the web browser.', 'correct' => false],
                ],
            ],

            // Q20 - FrameworkBundle - Core element
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'In a Symfony application, what is the element that links the core components together?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The FrameworkBundle is the bundle that ties together the core Symfony components to provide a full-stack framework.',
                'resourceUrl' => 'https://symfony.com/components/Framework%20Bundle',
                'answers' => [
                    ['text' => 'The <code>FrameworkBundle</code>.', 'correct' => true],
                    ['text' => 'The <code>Container</code>.', 'correct' => false],
                    ['text' => 'The <code>Kernel</code>.', 'correct' => false],
                ],
            ],

            // Q21 - Twig - Twig Internals (Lexer)
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
                    ['text' => 'The Parser', 'correct' => false],
                    ['text' => 'The Compiler', 'correct' => false],
                ],
            ],

            // Q22 - HttpFoundation - Request modification
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Can the Request object be modified during its handling?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, the Request object is mutable and can be modified during the request lifecycle.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_foundation.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q23 - HttpKernel - kernel.view event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What kernel event is dispatched when a controller does not return a <code>Response</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The kernel.view event is dispatched when the controller does not return a Response object, allowing listeners to create a Response from the return value.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/reference/events.html',
                'answers' => [
                    ['text' => '<code>kernel.view</code>', 'correct' => true],
                    ['text' => '<code>kernel.finish_request</code>', 'correct' => false],
                    ['text' => '<code>kernel.response</code>', 'correct' => false],
                    ['text' => '<code>kernel.terminate</code>', 'correct' => false],
                ],
            ],

            // Q24 - HttpFoundation - RequestStack
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Given the context where a single request is stored in the <code>RequestStack</code>, could the current request be removed from the request stack?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, using the pop() method, the current request can be removed from the RequestStack.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.4/src/Symfony/Component/HttpFoundation/RequestStack.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q25 - Security - Voter signature
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the signature of the <code>voteOnAttribute()</code> method from <code>Symfony\Component\Security\Core\Authorization\Voter\Voter</code> abstract class?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The correct signature is voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/v6.0.0/src/Symfony/Component/Security/Core/Authorization/Voter/Voter.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">voteOnAttribute(TokenInterface $token, mixed $subject, string $attribute)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">voteOnAttribute(TokenInterface $token, string $attribute, mixed $subject)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">voteOnAttribute(string $attribute, TokenInterface $token, mixed $subject)</code></pre>', 'correct' => false],
                ],
            ],

            // Q26 - PHP OOP - Class visibility
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Is it possible for a PHP class to be declared <code>private</code> or <code>protected</code> in order to limit its scope to the current namespace only?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'No, PHP classes cannot be declared private or protected. Only class members (properties and methods) can have visibility modifiers.',
                'resourceUrl' => 'http://php.net/manual/en/language.oop5.visibility.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q27 - CssSelector - XPath vs CssSelector
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:CssSelector'],
                'text' => 'What is the main difference between <code>XPath</code> and <code>CssSelector</code> syntax?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'CssSelector has a simpler, more familiar syntax for web developers but is less powerful than XPath which can traverse the DOM in more complex ways.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/css_selector.html',
                'answers' => [
                    ['text' => 'CssSelector has a simpler syntax but is less powerful than XPath', 'correct' => true],
                    ['text' => 'XPath can only be used with XML files, while CssSelector supports both XML and HTML files', 'correct' => false],
                    ['text' => 'XPath has just been invented before CssSelector syntax but both have the exact same features', 'correct' => false],
                ],
            ],

            // Q28 - Forms - CheckboxType getData
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Given the following form configuration:
<pre><code class="language-php">$form = $this->createFormBuilder()
      ->add(\'foo\', CheckboxType::class, [\'value\' => \'bar\'])
      ->getForm();</code></pre>
<p>What will be returned by <code>$form[\'foo\']->getData()</code> when the checkbox is checked and the form submitted?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'CheckboxType returns true or false based on whether the checkbox is checked, not the value option.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/reference/forms/types/checkbox.html',
                'answers' => [
                    ['text' => '<code>true</code>', 'correct' => true],
                    ['text' => 'An exception is thrown', 'correct' => false],
                    ['text' => '<code>bar</code>', 'correct' => false],
                    ['text' => '<code>false</code>', 'correct' => false],
                ],
            ],

            // Q29 - DI - Authorization checker service
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is the name of the top-level service that checks authorization?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The security.authorization_checker service is used to check if the current user is granted a specific role or attribute.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.7/src/Symfony/Component/Security/Core/Authorization/AuthorizationChecker.php',
                'answers' => [
                    ['text' => '<code>security.authorization_checker</code>', 'correct' => true],
                    ['text' => '<code>security.checker</code>', 'correct' => false],
                    ['text' => '<code>security.voter_handler</code>', 'correct' => false],
                    ['text' => '<code>security.voter</code>', 'correct' => false],
                    ['text' => '<code>security.token_storage</code>', 'correct' => false],
                ],
            ],

            // Q30 - DI - Expression as service factory
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could expressions be used as service factories?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 6.1, expressions can be used as service factories.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-1-expressions-as-service-factories',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q31 - Twig - Multiple use tags
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Could multiple <code>use</code> be used in a single template?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, multiple use tags can be used in a single Twig template to import blocks from different templates.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/use.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q32 - Forms - Button child
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Could a <code>Button</code> have a child?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'No, a Button cannot have children. The add() method throws a BadMethodCallException.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Form/Button.php#L120',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q33 - BrowserKit - Client restart
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:BrowserKit'],
                'text' => 'Once started, could the <code>Client</code> be restarted?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, the Client can be restarted using the restart() method.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/BrowserKit/Client.php#L534',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q34 - Security - Form login target path
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'By default, what parameter can you use inside the login form to specify the target of the redirection?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The _target_path parameter is used to specify where to redirect the user after successful login.',
                'resourceUrl' => 'http://symfony.com/doc/current/security/form_login.html#control-the-redirect-url-from-inside-the-form',
                'answers' => [
                    ['text' => '<code>_target_path</code>', 'correct' => true],
                    ['text' => '<code>_path</code>', 'correct' => false],
                    ['text' => '<code>_default_target</code>', 'correct' => false],
                    ['text' => '<code>_path_target</code>', 'correct' => false],
                    ['text' => '<code>_target</code>', 'correct' => false],
                ],
            ],

            // Q35 - PHP Arrays - array_push with functions
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'What line should be added to the <code>cleanArray()</code> function below to ensure this script outputs <code>1525hello</code>?
<pre><code class="language-php">&lt;?php

function cleanArray($arr)
{
    $functions = [];

    /** line **/

    $ret = $arr;

    foreach ($functions as $func) {
        $ret = $func($ret);
    }

    return $ret;
}

$values = [15, \'\', 0, 25, \'hello\', 15];

foreach (cleanArray($values) as $v) {
    echo $v;
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_filter removes empty values (\'\' and 0), and array_unique removes the duplicate 15. Result: [15, 25, \'hello\'].',
                'resourceUrl' => 'https://php.net/manual/en/function.array-filter.php',
                'answers' => [
                    ['text' => '<code>array_push($functions, \'array_filter\', \'array_unique\');</code>', 'correct' => true],
                    ['text' => '<code>array_push($functions, \'array_reduce\');</code>', 'correct' => false],
                    ['text' => '<code>$arr = array_clean($arr);</code>', 'correct' => false],
                    ['text' => '<code>array_pop($functions, \'array_clean\');</code>', 'correct' => false],
                ],
            ],

            // Q36 - Event Dispatcher - addListener third argument
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'What is the third argument of the <code>addListener</code> method of the <code>Symfony\Component\EventDispatcher\EventDispatcher</code> class?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The third argument is the priority (integer). Higher priority listeners are called before lower priority ones.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/event_dispatcher/introduction.html#connecting-listeners',
                'answers' => [
                    ['text' => 'A priority integer that determines when a listener is triggered versus other listeners.', 'correct' => true],
                    ['text' => 'The event name (string) that this listener wants to listen to.', 'correct' => false],
                    ['text' => 'An Event object.', 'correct' => false],
                    ['text' => 'A PHP callable that will be executed when the specified event is dispatched', 'correct' => false],
                ],
            ],

            // Q37 - PropertyAccess - Reading from arrays
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'What is the way to get the value of the <code>first_name</code> index of the <code>$person</code> array?
<pre><code class="language-php">$person = array(
    \'first_name\' => \'Wouter\',
);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'For arrays, the PropertyAccess component uses square brackets to access indexes.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/property_access/introduction.html#reading-from-arrays',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->getValue($person, \'[first_name]\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->getValue($person, \'first_name\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->readProperty($person, \'first_name\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->readIndex($person, \'first_name\');</code></pre>', 'correct' => false],
                ],
            ],

            // Q38 - Forms - PreSubmitEvent getData
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What is returned by the <code>getData()</code> method of <code>PreSubmitEvent</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PreSubmitEvent::getData() returns an array containing the raw submitted data before any transformation.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/form/events.html',
                'answers' => [
                    ['text' => 'an array', 'correct' => true],
                    ['text' => 'the norm data of the form', 'correct' => false],
                    ['text' => 'The model data of the form', 'correct' => false],
                    ['text' => 'the view data of the form', 'correct' => false],
                ],
            ],

            // Q39 - BrowserKit - HttpBrowser restart
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:BrowserKit'],
                'text' => 'Once started, could the <code>HttpBrowser</code> be restarted?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, HttpBrowser extends AbstractBrowser which has a restart() method.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/BrowserKit/HttpBrowser.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q40 - PHP OOP - Visibility modifiers
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Which of the following are supported visibilities for class attributes and methods in PHP?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'PHP supports public, protected, and private visibility modifiers. Friend and Global are not valid PHP visibilities.',
                'resourceUrl' => 'http://php.net/manual/en/language.oop5.visibility.php',
                'answers' => [
                    ['text' => 'Protected', 'correct' => true],
                    ['text' => 'Private', 'correct' => true],
                    ['text' => 'Public', 'correct' => true],
                    ['text' => 'Friend', 'correct' => false],
                    ['text' => 'Global', 'correct' => false],
                ],
            ],
        ];
    }
}
