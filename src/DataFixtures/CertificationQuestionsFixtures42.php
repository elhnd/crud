<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 42
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures42 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures41::class];
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
            // Q1 - DI - ContainerConfigurator imports
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'ContainerConfigurator imports usage',
                'text' => 'Could imports be configured using <code>ContainerConfigurator</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, ContainerConfigurator provides an import() method to import configuration files.',
                'resourceUrl' => 'https://github.com/symfony/dependency-injection/blob/3.4/Loader/Configurator/ContainerConfigurator.php#L54',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q2 - Routing - Multiple routes same path different methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'title' => 'Routing Method Argument',
                'text' => 'What happens if multiple routes have the same path but different HTTP methods?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony will match based on the HTTP method, allowing different routes for the same path with different methods.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/routing.html#matching-http-methods',
                'answers' => [
                    ['text' => 'Symfony will match based on the HTTP method', 'correct' => true],
                    ['text' => 'Symfony merges all routes', 'correct' => false],
                    ['text' => 'Symfony ignores all but the first route', 'correct' => false],
                    ['text' => 'Symfony will throw an exception', 'correct' => false],
                ],
            ],

            // Q3 - Routing - Route matching with requirements
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'title' => 'Route matching with regex requirements',
                'text' => 'According to the following definition of route, which ones are matching?
<pre><code class="language-yaml">blog_page:
    path: /blog/{page}
    requirements:
        page: \d*
    defaults:
        page: 1</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The regex \d* matches zero or more digits. /blog/1, /blog/, and /blog all match because the parameter is optional with a default value.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/routing.html#adding-requirements',
                'answers' => [
                    ['text' => '/blog/1', 'correct' => true],
                    ['text' => '/blog/', 'correct' => true],
                    ['text' => '/blog', 'correct' => true],
                    ['text' => '/blog/page', 'correct' => false],
                    ['text' => '/blog/page-1', 'correct' => false],
                    ['text' => '/blog/page1', 'correct' => false],
                ],
            ],

            // Q4 - BrowserKit - Cookie assertions
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:BrowserKit'],
                'title' => 'BrowserKit Cookie assertions',
                'text' => 'Could assertions be performed on the fact that the current browser has a certain cookie?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, BrowserKit provides BrowserHasCookie constraint for testing cookie presence.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/BrowserKit/Test/Constraint/BrowserHasCookie.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q5 - DI - ContainerBuilder no-dev mode
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'ContainerBuilder no-dev mode',
                'text' => 'Could the fact that a class is available and will remain available in the <code>--no-dev</code> mode of Composer be obtained when using <code>ContainerBuilder</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'Yes, ContainerBuilder provides methods to check if a class will be available in production mode.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/DependencyInjection/ContainerBuilder.php#L1454',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - PSR - PSR-6 Cache
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PSR'],
                'title' => 'PSR-6 Symfony implementation',
                'text' => 'Which Symfony component has been created to provide a PSR-6 implementation?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Cache component provides a PSR-6 compatible caching implementation.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/cache.html',
                'answers' => [
                    ['text' => 'Cache', 'correct' => true],
                    ['text' => 'Inflector', 'correct' => false],
                    ['text' => 'PropertyAccess', 'correct' => false],
                    ['text' => 'Filesystem', 'correct' => false],
                ],
            ],

            // Q7 - HTTP - Vary header usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'title' => 'Vary header usage',
                'text' => 'Which of the followings are valid usage of the <code>Vary</code> header?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Vary header can use any request header field name, including User-Agent, Accept-Encoding, Cookie, Referer, and * (wildcard).',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Vary',
                'answers' => [
                    ['text' => '<code>Vary: User-Agent</code>', 'correct' => true],
                    ['text' => '<code>Vary: Accept-Encoding</code>', 'correct' => true],
                    ['text' => '<code>Vary: *</code>', 'correct' => true],
                    ['text' => '<code>Vary: Cookie</code>', 'correct' => true],
                    ['text' => '<code>Vary: Referer</code>', 'correct' => true],
                ],
            ],

            // Q8 - Security - AccessDecisionManager default strategy
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'title' => 'AccessDecisionManager default strategy',
                'text' => 'Which of the following is the <code>AccessDecisionManager</code> default strategy?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The default strategy is "affirmative" - access is granted as soon as one voter grants access.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Security/Core/Authorization/AccessDecisionManager.php#L30',
                'answers' => [
                    ['text' => '<code>affirmative</code>', 'correct' => true],
                    ['text' => '<code>unanimous</code>', 'correct' => false],
                    ['text' => '<code>consensus</code>', 'correct' => false],
                ],
            ],

            // Q9 - HTTP - 502 status code
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'title' => 'HTTP 502 status code',
                'text' => 'What does the 502 HTTP status code stand for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'HTTP 502 indicates Bad Gateway - the server received an invalid response from an upstream server.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/502',
                'answers' => [
                    ['text' => 'Bad Gateway', 'correct' => true],
                    ['text' => 'Service Unavailable', 'correct' => false],
                    ['text' => 'Gateway timeout', 'correct' => false],
                    ['text' => 'Not implemented', 'correct' => false],
                ],
            ],

            // Q10 - DI - Parameters imports
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'Parameters in imports',
                'text' => 'Is this code valid ?
<pre><code class="language-yaml"># app/config/config.yml
imports:
    - { resource: "%kernel.root_dir%/parameters.yml" }</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, since Symfony 3.4, the imports directive does not support parameters in the resource path.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/configuration/configuration_organization.html#different-directories-per-environment',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q11 - Event Dispatcher - __invoke listener
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'title' => 'EventListener with __invoke',
                'text' => 'Could an event listener be registered while using the <code>__invoke()</code> method to listen to an event?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, invokable classes can be used as event listeners since Symfony 4.1.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/event_dispatcher.html#creating-an-event-listener',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q12 - FrameworkBundle - Redirection query parameters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'title' => 'Redirection query parameters',
                'text' => 'Given the context where the user is redirected to another page, could the original query parameters be maintained?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, AbstractController provides methods to maintain query parameters during redirects.',
                'resourceUrl' => 'https://symfony.com/doc/5.1/controller.html#redirecting',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q13 - PHP Arrays - array_filter
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'title' => 'array_filter with falsy values',
                'text' => 'What is the output of the following PHP code?
<pre><code class="language-php">&lt;?php
$myArray = [
    0,
    NULL,
    \'\',
    \'0\',
    -1
];

echo count(
    array_filter($myArray)
);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_filter without callback removes falsy values. Only -1 remains (truthy), so count is 1.',
                'resourceUrl' => 'https://php.net/manual/en/function.array-filter.php',
                'answers' => [
                    ['text' => '1', 'correct' => true],
                    ['text' => '3', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '4', 'correct' => false],
                    ['text' => '5', 'correct' => false],
                ],
            ],

            // Q14 - PHP OOP - Magic methods
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'title' => 'PHP Magic methods',
                'text' => 'Which of the following are a magic method?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'All listed methods (__set_state, __get, __serialize, __invoke, __wakeup) are PHP magic methods.',
                'resourceUrl' => 'http://php.net/manual/en/language.oop5.magic.php',
                'answers' => [
                    ['text' => '<code>__set_state()</code>', 'correct' => true],
                    ['text' => '<code>__get()</code>', 'correct' => true],
                    ['text' => '<code>__serialize()</code>', 'correct' => true],
                    ['text' => '<code>__invoke()</code>', 'correct' => true],
                    ['text' => '<code>__wakeup()</code>', 'correct' => true],
                ],
            ],

            // Q15 - DomCrawler - attr() method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:DomCrawler'],
                'title' => 'DomCrawler attr() with default value',
                'text' => 'Given the following HTML code:
<pre><code class="language-html">&lt;!-- ... --&gt;

&lt;a class=\'home-link\' href=\'/home\'&gt;Go to home&lt;/a&gt;
</code></pre>
<p>And the following code using the <code>DomCrawler</code>:</p>
<pre><code class="language-php">$value = $crawler->filter(\'a.home-link\')->attr(\'data\', \'href\');</code></pre>
<p>What will be the content of <code>$value</code>?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Since Symfony 6.4, attr() accepts a second parameter as default value. Since "data" attribute doesn\'t exist, the default "href" is returned.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-4-dx-improvements-part-2#default-crawler-attributes',
                'answers' => [
                    ['text' => '<code>href</code>', 'correct' => true],
                    ['text' => '<code>/home</code>', 'correct' => false],
                    ['text' => 'An empty string', 'correct' => false],
                    ['text' => 'Nothing, an exception is thrown', 'correct' => false],
                ],
            ],

            // Q16 - FrameworkBundle - Fragment renderer tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'title' => 'Fragment renderer tag',
                'text' => 'What is the tag to add a new HTTP content rendering strategy?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The kernel.fragment_renderer tag is used to register custom fragment rendering strategies.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/dic_tags.html#kernel-fragment-renderer',
                'answers' => [
                    ['text' => '<code>kernel.fragment_renderer</code>', 'correct' => true],
                    ['text' => '<code>content_renderer</code>', 'correct' => false],
                    ['text' => '<code>kernel.renderer</code>', 'correct' => false],
                    ['text' => '<code>kernel.content_renderer</code>', 'correct' => false],
                    ['text' => '<code>renderer</code>', 'correct' => false],
                    ['text' => '<code>fragment_renderer</code>', 'correct' => false],
                ],
            ],

            // Q17 - Twig - Functions & filters at runtime
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig functions & filters at runtime',
                'text' => 'Could functions and filters be defined at runtime without any overhead?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, Twig allows defining undefined functions and filters on the fly using callbacks.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/recipes.html#defining-undefined-functions-and-filters-on-the-fly',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q18 - Twig - with tag scope
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig with tag variable scope',
                'text' => 'Given that Twig is configured with "strict_variables" set to true.

Consider the following Twig snippet:
<pre><code class="language-twig">{% with %}
    {% set maxItems = 7 %}
    {# ... #}
{% endwith %}

{# ... #}

{% for i in 1..maxItems %}
    {# ... #}
{% endfor %}</code></pre>
<p>Will the Twig template work as expected?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The "with" tag creates a new scope. Variables defined inside are not available outside, causing an error with strict_variables enabled.',
                'resourceUrl' => 'http://twig.symfony.com/doc/tags/with.html',
                'answers' => [
                    ['text' => 'No. The template will display an error because the <code>maxItems</code> variable is not defined outside the <code>with</code> tag.', 'correct' => true],
                    ['text' => 'No. The template will display an error because the <code>with</code> tag is not defined.', 'correct' => false],
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No. The template won\'t iterate from <code>1</code> to <code>7</code>. It will execute the <code>for</code> loop just one time (where <code>i</code> is <code>1</code>).', 'correct' => false],
                ],
            ],

            // Q19 - Doctrine DBAL - Connection URL
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'title' => 'Doctrine DBAL Connection URL',
                'text' => 'Which of the following are valid database URLs that can be used in the <code>dbal.url</code> option in Symfony applications?
<pre><code class="language-yaml"># app/config/config.yml
doctrine:
    dbal:
        url: ...</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Valid formats include sqlite:///:memory:, mysql://user:pass@host/db, pgsql://user:pass@host/db. The format mysql://host/db@user:pass is invalid.',
                'resourceUrl' => 'https://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html',
                'answers' => [
                    ['text' => '<code>sqlite:///:memory:</code>', 'correct' => true],
                    ['text' => '<code>sqlite:///data.db</code>', 'correct' => true],
                    ['text' => '<code>mysql://localhost:4486/foo?charset=UTF-8</code>', 'correct' => true],
                    ['text' => '<code>pgsql://localhost:5432</code>', 'correct' => true],
                    ['text' => '<code>mysql://localhost/mydb@user:secret</code>', 'correct' => false],
                ],
            ],

            // Q20 - Routing - Duplicate placeholder
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'title' => 'Route definition with duplicate placeholder',
                'text' => 'Is the following definition of route correct?
<pre><code class="language-php">use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add(\'blog_show\', new Route(\'/blog/{page}/category/{slug}/{page}\', array(
  \'_controller\' => \'AppBundle:Blog:show\',
)));

return $collection;</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, the route has the {page} placeholder twice, which is not allowed.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#routing-examples',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q22 - Filesystem - Copy method signature
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'title' => 'Filesystem copy method signature',
                'text' => 'What is the correct signature of the <code>Symfony\Component\Filesystem\Filesystem::copy</code> method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The correct signature is copy($originFile, $targetFile, $overwriteNewerFiles = false).',
                'resourceUrl' => 'https://symfony.com/doc/2.7/components/filesystem.html#copy',
                'answers' => [
                    ['text' => '<pre><code class="language-php">public function copy($originFile, $targetFile, $overwriteNewerFiles = false)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">public function copy($targetFile, $originFile, $overwriteNewerFiles = false)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function copy($originFile, $targetFile, $overwriteNewerFiles)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function copy($targetFile, $originFile, $overwriteNewerFiles)</code></pre>', 'correct' => false],
                ],
            ],

            // Q23 - Form - PreSubmitEvent getData
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'title' => 'PreSubmitEvent getData return type',
                'text' => 'What is returned by the <code>getData()</code> method of <code>PreSubmitEvent</code> ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PreSubmitEvent::getData() returns an array containing the raw submitted data.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/form/events.html',
                'answers' => [
                    ['text' => 'an array', 'correct' => true],
                    ['text' => 'the view data of the form', 'correct' => false],
                    ['text' => 'the norm data of the form', 'correct' => false],
                    ['text' => 'The model data of the form', 'correct' => false],
                ],
            ],

            // Q24 - Filesystem - Mirror override
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'title' => 'Filesystem mirror override',
                'text' => 'When using <code>mirror(...)</code>, could existing files be overridden?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the mirror() method accepts an "override" option to overwrite existing files.',
                'resourceUrl' => 'https://symfony.com/doc/2.2/components/filesystem.html#mirror',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q25 - Clock - Return current time
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'title' => 'Clock return current time',
                'text' => 'Could a <code>Clock</code> return the current time?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, ClockInterface provides a now() method to get the current time.',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/ClockInterface.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q26 - PHP OOP - Nested anonymous class
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'title' => 'Nested anonymous class output',
                'text' => 'What is the output of the following script?
<pre><code class="language-php">&lt;?php

class Foo
{
    protected $property;

    public function __construct($property = \'property\') {
        $this->property = $property;
    }

    public function setProperty($property) {
        $this->property = $property;
    }

    public function getAnonymousClass() {
        return new class extends Foo {
            public function getProperty() {
                return $this->property;
            }
        };
    }
}

$foo = new Foo();
$foo->setProperty(\'bar\');
$anonymousClass = $foo->getAnonymousClass();

echo $anonymousClass->getProperty();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The anonymous class is a new instance extending Foo, so it uses the default constructor value "property", not "bar".',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.anonymous.php',
                'answers' => [
                    ['text' => '<code>property</code>', 'correct' => true],
                    ['text' => '<code>bar</code>', 'correct' => false],
                    ['text' => 'A notice or a warning is raised', 'correct' => false],
                ],
            ],

            // Q27 - Doctrine - Bidirectional associations
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'title' => 'Doctrine Bidirectional Associations',
                'text' => 'Which of the following rules are true about bidirectional associations ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The inverse side uses mappedBy and the owning side uses inversedBy. ManyToOne is always the owning side.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/unitofwork-associations.html#bidirectional-associations',
                'answers' => [
                    ['text' => 'The inverse side has to use the mappedBy attribute and the owning side the inversedBy attribute of the OneToOne, ManyToOne, or ManyToMany mapping declaration.', 'correct' => true],
                    ['text' => 'ManyToOne is always the owning side and OneToMany the inverse side of a bidirectional association.', 'correct' => true],
                    ['text' => 'OneToMany is always the owning side and ManyToOne the inverse side of a bidirectional association.', 'correct' => false],
                    ['text' => 'The owning side has to use the mappedBy attribute and the inverse side the inversedBy attribute of the OneToOne, ManyToOne, or ManyToMany mapping declaration.', 'correct' => false],
                ],
            ],

            // Q28 - Form - Form Extension
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'title' => 'Form Extension registration',
                'text' => 'How to add an extension <code>MyForm</code> to the <code>Form</code> component ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Use the addExtension() method on FormFactoryBuilder to register form extensions.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/components/form.html#request-handling',
                'answers' => [
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Form\Forms;

$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new MyFormExtension())
    ->getFormFactory();</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Form\Forms;

$formFactory = Forms::createFormFactoryBuilder()
    ->registerExtension(new MyFormExtension())
    ->getFormFactory();</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Form\Forms;

$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(\'text\', new MyFormExtension())
    ->getFormFactory();</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Form\Forms;

$formFactory = Forms::createFormFactoryBuilder()
    ->add(new MyFormExtension())
    ->getFormFactory();</code></pre>', 'correct' => false],
                ],
            ],

            // Q29 - Console - Table appendRow
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'title' => 'Console Table appendRow',
                'text' => 'Given the following console table creation:
<pre><code class="language-php">&lt;?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;

class MyCommand extends Command
{
  // ...
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
     $table = new Table($output);
     $table->setRows([[\'foo1\', \'foo2\']]);
     $table->render();
     $table->appendRow([\'bar1\', \'bar2\']);

     return 0;
  }
}</code></pre>
<p>What will happen ?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'appendRow() adds a row to an already rendered table and renders it immediately, resulting in two rows.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/components/console/helpers/table.html',
                'answers' => [
                    ['text' => 'The table will have two rows with two values each', 'correct' => true],
                    ['text' => 'The table will have only one row with two values', 'correct' => false],
                    ['text' => 'An exception will be thrown', 'correct' => false],
                ],
            ],

            // Q30 - PHP - Basic functions exit/die
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'title' => 'PHP exit and halt functions',
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">$a = 1;
$b = 0;

????

$c = $a / $b;</code></pre>
<p>Which statement does the <code>????</code> placeholder replace in order to make this program execute without any errors?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'exit(), die(), and __halt_compiler() all stop script execution before the division by zero.',
                'resourceUrl' => 'http://www.php.net/manual/en/function.exit.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">exit();</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">die();</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">__halt_compiler();</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">abort();</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">quit();</code></pre>', 'correct' => false],
                ],
            ],

            // Q31 - Clock - Sleep
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'title' => 'Clock sleep capability',
                'text' => 'Could a <code>Clock</code> sleep?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, ClockInterface provides a sleep() method.',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/ClockInterface.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q32 - HttpFoundation - RequestStack pop
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'RequestStack pop request',
                'text' => 'Given the context where a single request is stored in the <code>RequestStack</code>, could the current request be removed from the request stack?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, RequestStack provides a pop() method to remove the current request.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.4/src/Symfony/Component/HttpFoundation/RequestStack.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q33 - FrameworkBundle - ControllerTrait isGranted
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'title' => 'ControllerTrait isGranted location',
                'text' => 'Up to Symfony 4, where was defined the <code>isGranted()</code> method available for any controller?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The isGranted() method was defined in ControllerTrait until Symfony 4.',
                'resourceUrl' => 'https://github.com/symfony/framework-bundle/blob/4.4/Controller/ControllerTrait.php#L175',
                'answers' => [
                    ['text' => '<code>ControllerTrait</code>', 'correct' => true],
                    ['text' => '<code>Controller</code>', 'correct' => false],
                    ['text' => '<code>AbstractController</code>', 'correct' => false],
                    ['text' => '<code>ServiceContainerAware</code>', 'correct' => false],
                ],
            ],

            // Q34 - Cache - Check for cached item
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'title' => 'Cache isHit method',
                'text' => 'Considering the following code:
<pre><code class="language-php">use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$cache = new FilesystemAdapter();

// retrieve the cache item
$numProducts = $cache->getItem(\'stats.num_products\');</code></pre>
<p>How would you check if an item already exists in the cache?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Use the isHit() method on the cache item to check if it exists in the cache.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/cache.html#basic-usage-psr-6',
                'answers' => [
                    ['text' => '<pre><code class="language-php">if (!$numProducts->isHit()) {
    // ... item does not exists in the cache
}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">if (!$numProducts->isFound()) {
    // ... item does not exists in the cache
}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">if (!$numProducts->isCached()) {
    // ... item does not exists in the cache
}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">if (!$numProducts->exists()) {
    // ... item does not exists in the cache
}</code></pre>', 'correct' => false],
                ],
            ],

            // Q35 - Security - voteOnAttribute signature
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'title' => 'Voter voteOnAttribute signature',
                'text' => 'What is the signature of the <code>voteOnAttribute()</code> method from <code>Symfony\Component\Security\Core\Authorization\Voter\Voter</code> abstract class?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The correct signature is voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/v6.0.0/src/Symfony/Component/Security/Core/Authorization/Voter/Voter.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">voteOnAttribute(TokenInterface $token, string $attribute, mixed $subject)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">voteOnAttribute(TokenInterface $token, mixed $subject, string $attribute)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">voteOnAttribute(string $attribute, TokenInterface $token, mixed $subject)</code></pre>', 'correct' => false],
                ],
            ],

            // Q36 - HTTP - 301 caching
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'title' => 'HTTP 301 caching',
                'text' => 'Could a response that use the <code>301</code> status code be cached without taking in consideration its header directives?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'No, HTTP 301 responses must respect cache headers. They are not implicitly cacheable forever.',
                'resourceUrl' => 'https://datatracker.ietf.org/doc/html/rfc7231#section-6.4.2',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q37 - Twig - setEscaper method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig escaper registration method',
                'text' => 'Which method need to be called when adding a new Twig escaper ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Use setEscaper() method to register a custom escaper.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/escape.html',
                'answers' => [
                    ['text' => '<code>setEscaper()</code>', 'correct' => true],
                    ['text' => '<code>newEscaper()</code>', 'correct' => false],
                    ['text' => '<code>createEscaper()</code>', 'correct' => false],
                    ['text' => '<code>register()</code>', 'correct' => false],
                ],
            ],

            // Q38 - HTTP - 2xx status codes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'title' => 'HTTP Success status codes',
                'text' => 'What are the HTTP status codes for <strong>Success</strong>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => '2xx status codes indicate success.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2616#section-6.1.1',
                'answers' => [
                    ['text' => '2xx', 'correct' => true],
                    ['text' => '1xx', 'correct' => false],
                    ['text' => '3xx', 'correct' => false],
                    ['text' => '4xx', 'correct' => false],
                    ['text' => '5xx', 'correct' => false],
                ],
            ],

            // Q39 - Twig - Custom escaper
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig custom escaper creation',
                'text' => 'Can we create a custom escaper for Twig ?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, Twig allows creating custom escapers.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/escape.html#custom-escapers',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
        ];
    }
}
