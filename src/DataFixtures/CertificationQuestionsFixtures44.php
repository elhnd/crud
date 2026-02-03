<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 44
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures44 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures43::class];
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
            // Q1 - HttpFoundation - Query string with dots
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'Query string parameter with dots',
                'text' => 'Regarding this URI : <code>/example?tags.id=2</code>
<p>What will be the content of <code>$request->query->all()</code> ?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP converts dots in query string parameter names to underscores. So tags.id becomes tags_id.',
                'resourceUrl' => 'http://www.php.net/manual/en/language.variables.external.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">[ 
    \'tags_id\' => 2 
]</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">[ 
    \'tags\' => [\'id\' => 2] 
]</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">[ 
    \'tags.id\' => 2 
]</code></pre>', 'correct' => false],
                ],
            ],

            // Q2 - Twig - extends with array
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig extends tag with array',
                'text' => 'Is the following extends tag valid ?
<pre><code>{% extends [\'layout1.html.twig\', \'layout2.html.twig\'] %}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, Twig supports dynamic inheritance. When an array is passed, Twig will use the first template that exists.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/extends.html#dynamic-inheritance',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q3 - Doctrine DBAL - Supported databases
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'title' => 'Doctrine DBAL supported databases',
                'text' => 'Which databases are supported out of the box by doctrine DBAL?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Doctrine DBAL supports MySQL, PostgreSQL, SQLite, Oracle, Microsoft SQL Server, SAP Sybase SQL Anywhere, and Drizzle out of the box.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/introduction.html',
                'answers' => [
                    ['text' => 'PostgreSQL', 'correct' => true],
                    ['text' => 'MySQL', 'correct' => true],
                    ['text' => 'SQLite', 'correct' => true],
                    ['text' => 'Oracle', 'correct' => true],
                    ['text' => 'Microsoft SQL Server', 'correct' => true],
                    ['text' => 'SAP Sybase SQL Anywhere', 'correct' => true],
                    ['text' => 'Drizzle', 'correct' => true],
                    ['text' => 'DB2', 'correct' => false],
                    ['text' => 'Firebird', 'correct' => false],
                    ['text' => 'Microsoft Access', 'correct' => false],
                ],
            ],

            // Q4 - Console - InputArgument constants
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'title' => 'InputArgument non-existent constants',
                'text' => 'Which of the following constants do not exist?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'InputArgument has REQUIRED, OPTIONAL, and IS_ARRAY constants. NONE and NEGATABLE do not exist for InputArgument (NEGATABLE is for InputOption).',
                'resourceUrl' => 'https://github.com/symfony/console/blob/6.0/Input/InputArgument.php#L24-L26',
                'answers' => [
                    ['text' => '<code>Symfony\\Component\\Console\\Input\\InputArgument::NONE</code>', 'correct' => true],
                    ['text' => '<code>Symfony\\Component\\Console\\Input\\InputArgument::NEGATABLE</code>', 'correct' => true],
                    ['text' => '<code>Symfony\\Component\\Console\\Input\\InputArgument::IS_ARRAY</code>', 'correct' => false],
                    ['text' => '<code>Symfony\\Component\\Console\\Input\\InputArgument::REQUIRED</code>', 'correct' => false],
                    ['text' => '<code>Symfony\\Component\\Console\\Input\\InputArgument::OPTIONAL</code>', 'correct' => false],
                ],
            ],

            // Q5 - Routing - Route compilation exception
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'title' => 'Route compilation exception type',
                'text' => 'Which exception is thrown when a <code>Route</code> defined with <code>/page/{2foo}</code> cannot be compiled?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'A DomainException is thrown when a route cannot be compiled due to invalid placeholder names.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/Routing/RouteCompiler.php#L39',
                'answers' => [
                    ['text' => '<code>DomainException</code>', 'correct' => true],
                    ['text' => '<code>RuntimeException</code>', 'correct' => false],
                    ['text' => '<code>InvalidRouteCompilationContextException</code>', 'correct' => false],
                    ['text' => '<code>RouteCompilationException</code>', 'correct' => false],
                    ['text' => '<code>InvalidArgumentException</code>', 'correct' => false],
                    ['text' => '<code>LogicException</code>', 'correct' => false],
                ],
            ],

            // Q6 - PHP - extract() function
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'title' => 'PHP extract() function',
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">$data = [\'bar\' => \'foo\'];
???
echo $bar;</code></pre>
<p>Which statement does the <code>???</code> placeholder replace in order to make the script print the string <code>foo</code> on the standard output?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The extract() function imports variables from an array into the current symbol table.',
                'resourceUrl' => 'http://php.net/manual/en/function.extract.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">extract($data);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">parse_array($data);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">http_extract_data($data);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">import_variables();</code></pre>', 'correct' => false],
                ],
            ],

            // Q7 - Mime - MimeTypes::getExtensions return type
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mime'],
                'title' => 'MimeTypes::getExtensions() invalid MIME type result',
                'text' => 'What will return <code>Symfony\\Component\\Mime\\MimeTypes::getExtensions(string $mimeType)</code> when passing it an invalid MIME type?
<p>Example: <code>$mimeTypes->getExtensions(\'not/a-valid-type\')</code></p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'MimeTypes::getExtensions() returns an empty array when the MIME type is not found.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Mime/MimeTypes.php#L90',
                'answers' => [
                    ['text' => 'An empty array', 'correct' => true],
                    ['text' => '<code>null</code>', 'correct' => false],
                    ['text' => 'An Exception will be thrown.', 'correct' => false],
                ],
            ],

            // Q8 - HTTP - must-understand directive
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'title' => 'HTTP must-understand directive storage',
                'text' => 'Which information is used to store a response that uses the <code>must-understand</code> directive?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The must-understand directive indicates that the cache should store the response only if it understands the requirements of the status code.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control#must-understand',
                'answers' => [
                    ['text' => 'The status code', 'correct' => true],
                    ['text' => 'The value of the <code>Last-Modified</code> directive', 'correct' => false],
                    ['text' => 'The value of the <code>Etag</code> directive', 'correct' => false],
                    ['text' => 'The value of the <code>Expires</code> directive', 'correct' => false],
                ],
            ],

            // Q9 - Twig - Block rendering with template reference
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig block() function with template reference',
                'text' => 'Consider 3 files with the following typical 3-level hierarchy of Twig templates:
<pre><code class="language-twig">{# base.html.twig #}
&lt;head&gt;
    &lt;title&gt;{% block title \'ACME\' %}&lt;/title&gt;
&lt;/head&gt;
...</code></pre>
<pre><code class="language-twig">{# layout.html.twig #}
{% extends \'base.html.twig\' %}

{% block title \'Welcome to ACME!\' %}</code></pre>
<pre><code class="language-twig">{# index.html.twig #}
{% extends \'layout.html.twig\' %}
{% block title %}
    {{ block(\'title\', \'base.html.twig\') }}
{% endblock %}</code></pre>
<p>What will be the value of the <code>&lt;title&gt;</code> element when rendering the <code>index.html.twig</code> template?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The block() function with a second argument renders the block from the specified template. Since base.html.twig defines "ACME" as the default, that\'s what will be displayed.',
                'resourceUrl' => 'https://twig.symfony.com/doc/functions/block.html',
                'answers' => [
                    ['text' => 'ACME', 'correct' => true],
                    ['text' => 'ACME Welcome to ACME!', 'correct' => false],
                    ['text' => 'Welcome to ACME!', 'correct' => false],
                    ['text' => 'An empty string.', 'correct' => false],
                    ['text' => 'This template will display an error because the <code>block()</code> function defines only one argument.', 'correct' => false],
                ],
            ],

            // Q10 - Validator - Simple validator types
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'title' => 'Simple validator supported types',
                'text' => 'With this following simple code:
<pre><code class="language-php">use Symfony\\Component\\Validator\\Validation;

$validator = Validation::createValidator();</code></pre>
<p>Which variables types this <code>$validator</code> object can validate ?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'A simple validator created with createValidator() can validate strings, numbers, and arrays. For objects, you need to configure annotation or attribute mapping.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/validator.html#retrieving-a-validator-instance',
                'answers' => [
                    ['text' => 'strings', 'correct' => true],
                    ['text' => 'numbers', 'correct' => true],
                    ['text' => 'arrays', 'correct' => true],
                    ['text' => 'objects', 'correct' => false],
                ],
            ],

            // Q11 - DI - shared option
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'Getting new service instances',
                'text' => 'What is the way to always get a new instance of a service?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Setting the shared option to false ensures a new instance is created each time the service is requested.',
                'resourceUrl' => 'https://symfony.com/doc/current/cookbook/service_container/shared.html',
                'answers' => [
                    ['text' => 'Setting the option <code>shared</code> to false.', 'correct' => true],
                    ['text' => 'Setting the option <code>singleton</code> to false.', 'correct' => false],
                    ['text' => 'By passing an instance of the <code>CompilerPass</code> to the <code>pushCompilerPass</code> of a <code>ContainerBuilder</code>.', 'correct' => false],
                    ['text' => 'Setting the option <code>scope</code> to <code>prototype</code>.', 'correct' => false],
                    ['text' => 'Setting the option <code>scope</code> to <code>request</code>.', 'correct' => false],
                ],
            ],

            // Q12 - Console - Command::INVALID
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'title' => 'Console Command::INVALID status code',
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

// ...

class FooCommand extends Command
{
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    // ...

    return Command::INVALID;
  }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, Command::INVALID was introduced in Symfony 5.3 to indicate that the command was run with invalid input.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/console.html#creating-a-command',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q13 - HTTP - Validation caching headers
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'title' => 'HTTP validation caching model headers',
                'text' => 'Which of the following headers are valid ones in the validation caching model?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The validation caching model uses ETag, Last-Modified, If-None-Match, and If-Modified-Since headers. Age is part of expiration model.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2616#page-85',
                'answers' => [
                    ['text' => '<code>Etag</code>', 'correct' => true],
                    ['text' => '<code>Last-Modified</code>', 'correct' => true],
                    ['text' => '<code>If-None-Match</code>', 'correct' => true],
                    ['text' => '<code>If-Modified-Since</code>', 'correct' => true],
                    ['text' => '<code>Age</code>', 'correct' => false],
                ],
            ],

            // Q14 - PHP OOP - __debugInfo() return type
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'title' => '__debugInfo() magic method return type',
                'text' => 'What type is the <code>__debugInfo()</code> magic method supposed to return?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The __debugInfo() magic method must return an array that will be used by var_dump().',
                'resourceUrl' => 'http://php.net/manual/en/language.oop5.magic.php#object.debuginfo',
                'answers' => [
                    ['text' => 'Array', 'correct' => true],
                    ['text' => 'String', 'correct' => false],
                    ['text' => 'Any type', 'correct' => false],
                    ['text' => 'Object', 'correct' => false],
                    ['text' => 'Nothing', 'correct' => false],
                    ['text' => 'Boolean', 'correct' => false],
                ],
            ],

            // Q15 - Security - IS_AUTHENTICATED role
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'title' => 'Check if user is logged in role',
                'text' => 'Which role allows you to check that a user is logged in (whatever the means, ex: remember_me cookie, login form, etc)?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Since Symfony 6.0, IS_AUTHENTICATED is used to check if a user is authenticated by any means. IS_AUTHENTICATED_FULLY checks only full authentication (not remember_me).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/b101b71ddacfa664485bb09ec6272971e458f49f/src/Symfony/Component/Security/Core/Authorization/Voter/AuthenticatedVoter.php#L75',
                'answers' => [
                    ['text' => '<code>IS_AUTHENTICATED</code>', 'correct' => true],
                    ['text' => '<code>IS_AUTHENTICATED_FULLY</code>', 'correct' => false],
                    ['text' => '<code>IS_FULLY_AUTHENTICATED</code>', 'correct' => false],
                    ['text' => '<code>IS_AUTHENTICATED_ANONYMOUSLY</code>', 'correct' => false],
                    ['text' => '<code>IS_AUTHENTICATED_REMEMBERED</code>', 'correct' => false],
                ],
            ],

            // Q16 - Serializer - Ignoring attributes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'title' => 'Serializer ignoring attributes',
                'text' => 'What is the correct way to ignore attributes?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Since Symfony 5.0, you must pass IGNORED_ATTRIBUTES via the context array.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/components/serializer.html#ignoring-attributes',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$serializer->serialize($person, \'json\', [
    AbstractNormalizer::IGNORED_ATTRIBUTES => [\'age\'],
]);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$serializer->setIgnoredAttributes([\'age\']);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$normalizer->setIgnoredAttributes([\'age\']);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$serializer->ignoreAttributes([\'age\']);</code></pre>', 'correct' => false],
                ],
            ],

            // Q17 - PHP - random_int()
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'title' => 'Cryptographically secure random integer',
                'text' => 'Which native function should you use to generate a cryptographically secure random integer?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'random_int() generates cryptographically secure pseudo-random integers. mt_rand() and rand() are not cryptographically secure.',
                'resourceUrl' => 'https://www.php.net/manual/en/ref.random.php',
                'answers' => [
                    ['text' => '<code>random_int()</code>', 'correct' => true],
                    ['text' => '<code>lcg_value()</code>', 'correct' => false],
                    ['text' => '<code>mt_rand()</code>', 'correct' => false],
                    ['text' => '<code>rand()</code>', 'correct' => false],
                ],
            ],

            // Q18 - Best Practices - access_control
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'title' => 'Best practice for protecting URL patterns',
                'text' => 'According to the official Symfony <em>Best Practices Guide</em>, which method do you need to use in order to protect broad URL patterns?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Symfony Best Practices recommend using access_control in the security configuration for protecting broad URL patterns.',
                'resourceUrl' => 'https://symfony.com/doc/current/security/access_control.html',
                'answers' => [
                    ['text' => 'Use <code>access_control</code> in the <code>security</code> configuration', 'correct' => true],
                    ['text' => 'Use the <code>security.authorization_checker</code> service', 'correct' => false],
                    ['text' => 'Use the <code>@Security</code> annotation', 'correct' => false],
                ],
            ],

            // Q19 - FrameworkBundle - Validator cache override
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'title' => 'Validator cache service override',
                'text' => 'Could the cache service used to store validation metadata be overridden?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the cache service for validation metadata can be configured and overridden in the framework configuration.',
                'resourceUrl' => 'https://symfony.com/doc/2.3/reference/configuration/framework.html#validation',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q20 - PHP - First class callable syntax
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'title' => 'PHP 8.1 first class callable syntax',
                'text' => 'From PHP 8.1, how can this code snippet be replaced?
<pre><code class="language-php">$callable = Closure::fromCallable(\'strtoupper\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP 8.1 introduced the first-class callable syntax using ... to create a closure from any callable.',
                'resourceUrl' => 'https://www.php.net/manual/en/functions.first_class_callable_syntax.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$callable = strtoupper(...);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$callable = \\from_callable(\'strtoupper\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$callable = \\from_callable(strtoupper(...));</code></pre>', 'correct' => false],
                    ['text' => 'It can\'t', 'correct' => false],
                ],
            ],
        ];
    }
}
