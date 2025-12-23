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
 * Certification-style questions - Batch 2
 */
class CertificationQuestionsFixtures2 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;
    public static function getGroups(): array
    {
        return ['certification', 'questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found.');
        }

        // Create additional subcategories
        $newSubcategories = [
            'Symfony' => [
                'Event Dispatcher' => 'Event Dispatcher component for decoupled code',
                'VarDumper' => 'VarDumper component for debugging',
                'Translation' => 'Translation component for i18n',
                'Cache' => 'Cache component for application caching',
                'Expression Language' => 'Expression Language component',
                'HttpFoundation' => 'HttpFoundation component for HTTP abstraction',
                'HttpClient' => 'HttpClient component for HTTP requests',
            ],
            'PHP' => [
                'Closures' => 'Anonymous functions and closures',
                'JSON' => 'JSON encoding and decoding',
            ],
        ];

        $subcategories = [];

        // Get all existing subcategories
        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        foreach ($subcategoryRepo->findAll() as $sub) {
            $subcategories[$sub->getCategory()->getName() . ':' . $sub->getName()] = $sub;
        }

        // Create new subcategories
        foreach ($newSubcategories['Symfony'] as $name => $description) {
            $key = 'Symfony:' . $name;
            if (!isset($subcategories[$key])) {
                $sub = new Subcategory();
                $sub->setName($name);
                $sub->setDescription($description);
                $sub->setCategory($symfony);
                $manager->persist($sub);
                $subcategories[$key] = $sub;
            }
        }

        foreach ($newSubcategories['PHP'] as $name => $description) {
            $key = 'PHP:' . $name;
            if (!isset($subcategories[$key])) {
                $sub = new Subcategory();
                $sub->setName($name);
                $sub->setDescription($description);
                $sub->setCategory($php);
                $manager->persist($sub);
                $subcategories[$key] = $sub;
            }
        }

        $manager->flush();

        $questions = [
            // Q2: Event Dispatcher - dispatch return value
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'What is returned by <code>$dispatcher->dispatch(\'bar.event\', $event)</code> in the following code?<pre><code class="language-php">$event = new OrderPlacedEvent($order);
$dispatcher->dispatch(\'bar.event\', $event);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The dispatch() method returns the event object that was passed to it, allowing for method chaining and inspection of the event after dispatch.',
                'resourceUrl' => 'https://symfony.com/doc/2.3/components/event_dispatcher/introduction.html#dispatcher-shortcuts',
                'answers' => [
                    ['text' => '<code>$event</code>', 'correct' => true],
                    ['text' => '<code>true</code>', 'correct' => false],
                    ['text' => '<code>$dispatcher</code>', 'correct' => false],
                    ['text' => 'Nothing', 'correct' => false],
                ],
            ],
            // Q3: PHP Closure binding
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Closures'],
                'text' => 'Considering the following code snippet:<pre><code class="language-php">&lt;?php

class Bar
{
    private $foo = \'private\';
}

function getter()
{
    return function() {
        return $this->foo;
    };
}

????</code></pre>
Which statement should replace the <code>????</code> placeholder in order to make this code snippet output the string <code>private</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'To access private properties via bindTo(), you need to pass both the object instance AND the class scope. bindTo(new Bar(), \'Bar\') or bindTo(new Bar(), new Bar()) both work.',
                'resourceUrl' => 'https://www.php.net/manual/en/class.closure.php',
                'answers' => [
                    ['text' => '<code>echo getter()->bindTo(new Bar(), \'Bar\')();</code>', 'correct' => true],
                    ['text' => '<code>echo getter()->bindTo(new Bar(), new Bar())();</code>', 'correct' => true],
                    ['text' => '<code>echo getter()->bindTo(new Bar())();</code>', 'correct' => false],
                    ['text' => '<code>echo getter()->bindTo(\'Bar\', \'Bar\')();</code>', 'correct' => false],
                    ['text' => 'This is not possible.', 'correct' => false],
                ],
            ],
            // Q4: DI Compiler Passes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'How can you register a new compiler pass?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'You register a compiler pass by calling the addCompilerPass() method on a ContainerBuilder instance.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/compiler_passes.html',
                'answers' => [
                    ['text' => 'By passing an instance of the <code>CompilerPass</code> to the <code>addCompilerPass</code> of a <code>ContainerBuilder</code>.', 'correct' => true],
                    ['text' => 'By creating a new service with the tag <code>compiler_pass</code>.', 'correct' => false],
                    ['text' => 'By passing an instance of the <code>CompilerPass</code> to the <code>pushCompilerPass</code> of a <code>ContainerBuilder</code>.', 'correct' => false],
                    ['text' => 'By passing an instance of the <code>CompilerPass</code> to the <code>registerCompilerPass</code> of a <code>ContainerBuilder</code>.', 'correct' => false],
                    ['text' => 'By creating a new service with the tag <code>compiler.pass</code>.', 'correct' => false],
                ],
            ],
            // Q5: Validator File extension
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'What is the most recently added validation option for <code>#[Assert\\File]</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The extensions option was added in Symfony 6.2 to validate file extensions directly.',
                'resourceUrl' => 'https://symfony.com/doc/6.2/reference/constraints/File.html#extensions',
                'answers' => [
                    ['text' => '<code>extensions</code>', 'correct' => true],
                    ['text' => '<code>mimeTypes</code>', 'correct' => false],
                    ['text' => '<code>maxSize</code>', 'correct' => false],
                ],
            ],
            // Q6: VarDumper setTheme
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarDumper'],
                'text' => 'Is the following code valid?<pre><code class="language-php">&lt;?php

use Symfony\Component\VarDumper\Dumper\HtmlDumper;

$dumper = new HtmlDumper();
$dumper->setTheme(\'light\');</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, HtmlDumper has a setTheme() method that accepts \'light\' or \'dark\' themes.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/components/var_dumper/advanced.html#dumpers',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Q7: Yaml::parse
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'],
                'text' => 'Is the following code valid?<pre><code class="language-php">&lt;?php

use Symfony\Component\Yaml\Yaml;

$value = Yaml::parse(file_get_contents(\'/path/to/file.yml\'));</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, Yaml::parse() accepts a YAML string as input. Using file_get_contents() to read the file content is a valid approach.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/yaml/introduction.html#reading-yaml-files',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Q8: Translation LoaderInterface
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'Which method signature must be implemented by classes that implement <code>Symfony\\Component\\Translation\\Loader\\LoaderInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The LoaderInterface requires a load() method with three required parameters: $resource, $locale, and $domain.',
                'resourceUrl' => 'https://github.com/symfony/translation/blob/2.3/Loader/LoaderInterface.php',
                'answers' => [
                    ['text' => '<code>public function load($resource, $locale, $domain);</code>', 'correct' => false],
                    ['text' => '<code>public function load($locale = \'en\', $domain = \'messages\');</code>', 'correct' => false],
                    ['text' => '<code>public function load($locale, $domain);</code>', 'correct' => false],
                    ['text' => '<code>public function load($resource, $locale, $domain = \'messages\');</code>', 'correct' => true],
                    ['text' => '<code>public function load($resource, $locale = \'en\', $domain = \'messages\');</code>', 'correct' => false],
                ],
            ],
            // Q9: FrozenParameterBag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could a parameter be removed from a <code>FrozenParameterBag</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, FrozenParameterBag is immutable. Any attempt to modify it throws a LogicException.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Q10: Cache Pools for testing
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Which cache pool can be used for testing purposes, because contents are stored in memory and not persisted in any way?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Array Cache Adapter stores all data in PHP arrays, making it perfect for testing as nothing is persisted.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/cache/cache_pools.html#creating-cache-pools',
                'answers' => [
                    ['text' => 'Array Cache Adapter', 'correct' => true],
                    ['text' => 'Memory Cache Adapter', 'correct' => false],
                    ['text' => 'APCu Cache Adapter', 'correct' => false],
                    ['text' => 'Redis Cache Adapter', 'correct' => false],
                    ['text' => 'Chain Cache Adapter', 'correct' => false],
                ],
            ],
            // Q11: ExpressionLanguage compile
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'What will be displayed by the following code?<pre><code class="language-php">&lt;?php

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$language = new ExpressionLanguage();

var_dump($language->compile(\'1 + 2\'));</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The compile() method returns PHP code as a string that represents the expression, not the evaluated result. It returns "(1 + 2)" as a string.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/expression_language.html#usage',
                'answers' => [
                    ['text' => '<code>(1 + 2)</code>', 'correct' => true],
                    ['text' => '<code>3</code>', 'correct' => false],
                    ['text' => '<code>integer</code>', 'correct' => false],
                    ['text' => '<code>true</code>', 'correct' => false],
                    ['text' => '<code>false</code>', 'correct' => false],
                ],
            ],
            // Q12: Cookie from string
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could a <code>Cookie</code> be created from a string?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, Symfony\'s Cookie class has a static fromString() method to create a Cookie from a Set-Cookie header string.',
                'resourceUrl' => 'https://symfony.com/doc/3.3/components/http_foundation.html#setting-cookies',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Q13: HttpClient max retries
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'How can you control the maximum number of retry attempts with <code>Symfony\\Component\\HttpClient\\RetryableHttpClient</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The max retries can be set via the constructor as a third parameter (either as an integer or as an array with max_retries key).',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-4-dx-improvements-part-2#maximum-retries-in-http-client',
                'answers' => [
                    ['text' => '<code>$client = new RetryableHttpClient(HttpClient::create(), null, 4);</code>', 'correct' => true],
                    ['text' => '<code>$client = new RetryableHttpClient(HttpClient::create(), null, [\'max_retries\' => 4]);</code>', 'correct' => false],
                    ['text' => '<code>$client = new RetryableHttpClient(HttpClient::create());
                    $client = $client->withOptions([\'max_retries\' => 4]);</code>', 'correct' => true],
                    ['text' => '<code>$client = new RetryableHttpClient(HttpClient::create());
                    $client->request(\'GET\', \'/url\', [\'max_retries\' => 4]);</code>', 'correct' => true],
                ],
            ],
            // Q14: Twig block names
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following are valid Twig block names?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Valid block names follow identifier rules: they can contain letters, numbers, and underscores, but cannot start with a number or contain dots/dashes.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/block.html',
                'answers' => [
                    ['text' => 'foo_bar', 'correct' => true],
                    ['text' => 'foo123', 'correct' => true],
                    ['text' => '_foo', 'correct' => true],
                    ['text' => '.foo', 'correct' => false],
                    ['text' => 'foo.bar', 'correct' => false],
                    ['text' => '123foo', 'correct' => false],
                    ['text' => '-foo', 'correct' => false],
                ],
            ],
            // Q15: PHP JSON closures
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:JSON'],
                'text' => 'Which of the following variables is NOT serializable to JSON with the <code>json_encode()</code> native function?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Closures (anonymous functions) cannot be serialized to JSON. json_encode() will return false or an error when trying to encode a closure.',
                'resourceUrl' => 'https://www.php.net/json-encode',
                'answers' => [
                    ['text' => '<code>$data = function ($content) { return trim(strtoupper(strip_tags($content))); };</code>', 'correct' => true],
                    ['text' => '<code>$data = [\'full_name\' => \'Pierre Dupont\', \'location\' => [\'city\' => \'Paris\']];</code>', 'correct' => false],
                    ['text' => '<code>$data = \'foo bar\';</code>', 'correct' => false],
                    ['text' => '<code>$data = new SomeClass(\'some string\');</code>', 'correct' => false],
                ],
            ],
            // Q16: HttpFoundation getPathInfo
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'For a request to <code>http://example.com/blog/index.php/post/hello-world</code>, what will be the value of <code>$pathInfo</code>?<pre><code class="language-php">$pathInfo = $request->getPathInfo();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'getPathInfo() returns the path after the script name. For /blog/index.php/post/hello-world, it returns /post/hello-world.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_foundation.html#identifying-a-request',
                'answers' => [
                    ['text' => '<code>/post/hello-world</code>', 'correct' => true],
                    ['text' => '<code>/blog/index.php/post/hello-world</code>', 'correct' => false],
                    ['text' => '<code>/index.php/post/hello-world</code>', 'correct' => false],
                    ['text' => '<code>example.com/blog/index.php/post/hello-world</code>', 'correct' => false],
                ],
            ],
            // Q17: Twig format_datetime calendar
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'When using <code>format_datetime()</code> in Twig, could the calendar be changed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the format_datetime filter accepts a calendar argument that allows switching between gregorian, japanese, buddhist, etc.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/filters/format_datetime.html#arguments',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $q) {
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }
}
