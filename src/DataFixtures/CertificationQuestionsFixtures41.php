<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 41
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures41 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures40::class];
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
            // Q1 - Form - TelType
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'title' => 'Form - TelType',
                'text' => 'Which of the following sentences are true ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'TelType form field only allows to use HTML5 input type tel. It does not trigger any validation on the entered phone number.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/reference/forms/types/tel.html',
                'answers' => [
                    ['text' => '<p>TelType form field only allows to use HTML5 input type <code>tel</code></p>', 'correct' => true],
                    ['text' => '<p>TelType form field allows to use HTML5 input type <code>tel</code> and trigger some basic validation on the entered phone number</p>', 'correct' => false],
                    ['text' => '<p>PhoneType form field allows to use HTML5 input type <code>tel</code> and trigger some basic validation on the entered phone number</p>', 'correct' => false],
                    ['text' => '<p>PhoneType form field only allows to use HTML5 input type <code>tel</code></p>', 'correct' => false],
                ],
            ],

            // Q2 - Form - ChoiceType choice_attr option
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'title' => 'ChoiceType choice_attr option',
                'text' => 'Which types are allowed for the <code>choice_attr</code> option of the <code>Symfony\Component\Form\Extension\Core\Type\ChoiceType</code> form type ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The choice_attr option accepts array, string, or callable types.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/reference/forms/types/choice.html#choice-attr',
                'answers' => [
                    ['text' => '<code>array</code>', 'correct' => true],
                    ['text' => '<code>string</code>', 'correct' => true],
                    ['text' => '<code>callable</code>', 'correct' => true],
                    ['text' => '<code>boolean</code>', 'correct' => false],
                    ['text' => '<code>integer</code>', 'correct' => false],
                ],
            ],

            // Q3 - Security - VoterInterface::vote() signature
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'title' => 'VoterInterface::vote() signature',
                'text' => 'What is the signature of the <code>vote()</code> method from <code>VoterInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The VoterInterface::vote() method signature is: public function vote(TokenInterface $token, $subject, array $attributes)',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/Security/Core/Authorization/Voter/VoterInterface.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, $subject, array $attributes)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, $object, array $attributes)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, array $attributes, $object)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, array $attributes, $subject)</code></pre>', 'correct' => false],
                ],
            ],

            // Q4 - DI - Parameters imports with kernel.root_dir
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'Parameters imports with kernel.root_dir',
                'text' => 'Is this code valid ?
<pre><code class="language-yaml"># app/config/config.yml
imports:
    - { resource: "%kernel.root_dir%/parameters.yml" }</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'No, since Symfony 3.4, the imports directive does not support parameters in the resource path.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/configuration/configuration_organization.html#different-directories-per-environment',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q5 - HttpClient - RetryableHttpClient base_uri
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'title' => 'RetryableHttpClient base_uri option',
                'text' => 'What is the expected type of the <code>base_uri</code> option in <code>RetryableHttpClient</code> ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In RetryableHttpClient, the base_uri option can be an array to allow retry over several base URIs.',
                'resourceUrl' => 'https://symfony.com/doc/6.3/http_client.html#retry-over-several-base-uris',
                'answers' => [
                    ['text' => '<code>array</code>', 'correct' => true],
                    ['text' => '<code>string</code>', 'correct' => false],
                    ['text' => '<code>Closure</code>', 'correct' => false],
                ],
            ],

            // Q6 - Translation - Loading Message Catalogs
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'title' => 'Loading Message Catalogs',
                'text' => 'Which of the followings are part of the built-in message catalogs loaders?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The built-in loaders include: PoFileLoader, PhpFileLoader, CsvFileLoader, IcuDatFileLoader, IcuResFileLoader, IniFileLoader, JsonFileLoader, MoFileLoader, QtFileLoader, XliffFileLoader, YamlFileLoader.',
                'resourceUrl' => 'https://github.com/symfony/translation/tree/2.3/Loader',
                'answers' => [
                    ['text' => 'PoFileLoader', 'correct' => true],
                    ['text' => 'PhpFileLoader', 'correct' => true],
                    ['text' => 'CsvFileLoader', 'correct' => true],
                    ['text' => 'IcuDatFileLoader', 'correct' => true],
                    ['text' => 'YamlFileLoader', 'correct' => true],
                    ['text' => 'XliffFileLoader', 'correct' => true],
                    ['text' => 'MoFileLoader', 'correct' => true],
                    ['text' => 'JsonFileLoader', 'correct' => true],
                    ['text' => 'IniFileLoader', 'correct' => true],
                    ['text' => 'PhpLoader', 'correct' => false],
                    ['text' => 'IcuFileLoader', 'correct' => false],
                ],
            ],

            // Q7 - Expression Language - Usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'title' => 'Expression Language - Usage',
                'text' => 'What will be displayed by the following code?
<pre><code class="language-php">use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$language = new ExpressionLanguage();

echo $language->evaluate(\'1 + 2\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The ExpressionLanguage component evaluates the expression "1 + 2" and returns 3.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/expression_language.html#usage',
                'answers' => [
                    ['text' => '<code>3</code>', 'correct' => true],
                    ['text' => '<code>true</code>', 'correct' => false],
                    ['text' => '<code>false</code>', 'correct' => false],
                    ['text' => '<code>integer</code>', 'correct' => false],
                    ['text' => '<code>1 + 2</code>', 'correct' => false],
                ],
            ],

            // Q8 - PHP - Null coalescing operator
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'title' => 'The null coalescing operator',
                'text' => 'What will be the output of the following code?
<pre><code class="language-php">&lt;?php

$z = \'bar\';
$f = function ($y) use ($z) {
    echo $x ?? $y ?? $z;
};

$f(null);
$f(\'foo\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The null coalescing operator (??) returns the first non-null operand. For $f(null): $x is undefined (null), $y is null, so $z (bar) is returned. For $f(\'foo\'): $x is undefined (null), $y is \'foo\', so \'foo\' is returned. Result: "barfoo".',
                'resourceUrl' => 'http://php.net/manual/en/language.operators.comparison.php',
                'answers' => [
                    ['text' => 'barfoo', 'correct' => true],
                    ['text' => 'foo', 'correct' => false],
                    ['text' => 'barbar', 'correct' => false],
                    ['text' => 'foobar', 'correct' => false],
                    ['text' => 'A <em>Fatal error: syntax error</em> will be thrown.', 'correct' => false],
                ],
            ],

            // Q9 - Security - Custom Voter methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'title' => 'Security Voters - Required methods',
                'text' => 'What methods MUST be implemented in a custom voter extending <code>Voter</code> ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When extending the abstract Voter class, you must implement supports() and voteOnAttribute() methods. The vote() method is already implemented in the abstract class.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/security/voters.html',
                'answers' => [
                    ['text' => '<code>voteOnAttribute()</code>', 'correct' => true],
                    ['text' => '<code>supports()</code>', 'correct' => true],
                    ['text' => '<code>vote()</code>', 'correct' => false],
                    ['text' => '<code>supportsSubject()</code>', 'correct' => false],
                    ['text' => '<code>voteOnAccess()</code>', 'correct' => false],
                    ['text' => '<code>supportsAccess()</code>', 'correct' => false],
                    ['text' => '<code>supportsAttribute()</code>', 'correct' => false],
                ],
            ],

            // Q10 - DI - AsDecorator attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'Service decoration with AsDecorator',
                'text' => 'Is the following code valid when decorating a service?
<pre><code class="language-php">&lt;?php

namespace App\Mailer;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: Mailer::class)]
class LoggingMailer
{
    // ...
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 6.1, you can use the #[AsDecorator] attribute to decorate services.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-1-service-decoration-attributes',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q11 - PHP - Static arrow functions
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'title' => 'Static arrow functions',
                'text' => 'We sometimes come across arrow functions declared as static, like in the code below:
<pre><code class="language-php">class Foo
{
    public function bar(): iterable
    {
        $array = \range(1, 10);

        return array_map(static fn($x) => $x*$x, $array);
    }
}</code></pre>
<p>Which of the following choices is true</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'By using the static modifier, $this (representing the current instance of Foo) won\'t be bound and "injected" in the arrow function. It will result in a faster execution, as this binding doesn\'t have to be made.',
                'resourceUrl' => 'https://wiki.php.net/rfc/arrow_functions_v2',
                'answers' => [
                    ['text' => 'By using the <code>static</code> modifier, <code>$this</code> (representing the current instance of <code>Foo</code>) won\'t be bound and "injected" in the arrow function. It will result in a faster execution, as this binding doesn\'t have to be made', 'correct' => true],
                    ['text' => 'It allows the arrow function to be able to access to <code>$this</code>, representing the current instance of <code>Foo</code> calling this method', 'correct' => false],
                    ['text' => 'Nothing actually changes', 'correct' => false],
                ],
            ],

            // Q12 - HttpKernel - KernelEvent methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'title' => 'KernelEvent methods',
                'text' => 'What are the methods available in <code>Symfony\Component\HttpKernel\Event\KernelEvent</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'KernelEvent provides: getKernel(), getRequest(), getRequestType(), and isMainRequest() methods.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/HttpKernel/Event/KernelEvent.php',
                'answers' => [
                    ['text' => '<code>getKernel</code>', 'correct' => true],
                    ['text' => '<code>getRequest</code>', 'correct' => true],
                    ['text' => '<code>getRequestType</code>', 'correct' => true],
                    ['text' => '<code>isMainRequest</code>', 'correct' => true],
                    ['text' => '<code>hasRequest</code>', 'correct' => false],
                    ['text' => '<code>getResponse</code>', 'correct' => false],
                    ['text' => '<code>hasException</code>', 'correct' => false],
                    ['text' => '<code>getException</code>', 'correct' => false],
                    ['text' => '<code>hasResponse</code>', 'correct' => false],
                ],
            ],

            // Q13 - HttpFoundation - Response isEmpty
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'Response isEmpty method',
                'text' => 'What is returned by the <code>isEmpty</code> method of <code>Symfony\Component\HttpFoundation\Response</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The isEmpty() method returns true if the response status code is 204 (No Content) or 304 (Not Modified).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/0baa58d4e4bb006c4ae68f75833b586bd3cb6e6f/src/Symfony/Component/HttpFoundation/Response.php#L1090',
                'answers' => [
                    ['text' => '<code>true</code> if the response status code are 204 or 304', 'correct' => true],
                    ['text' => '<code>true</code> if the response content is <code>null</code>', 'correct' => false],
                    ['text' => '<code>true</code> if the response has no headers', 'correct' => false],
                    ['text' => '<code>true</code> if there is a server error', 'correct' => false],
                ],
            ],

            // Q14 - Form - DataTransformer validation error
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'title' => 'DataTransformer validation error message',
                'text' => 'How to customize the validation error message of the validation error caused by a <code>TransformationFailedException</code> ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The invalid_message option is used to customize the error message when a TransformationFailedException is thrown.',
                'resourceUrl' => 'http://symfony.com/doc/current/form/data_transformers.html#creating-the-transformer',
                'answers' => [
                    ['text' => 'By using the <code>invalid_message</code> option', 'correct' => true],
                    ['text' => 'It\'s not possible', 'correct' => false],
                    ['text' => 'The exception message will be used as the validation error message', 'correct' => false],
                ],
            ],

            // Q15 - Mime - Custom type guesser
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mime'],
                'title' => 'Custom Mime type guesser',
                'text' => 'Could a custom type guesser be registered again when already registered?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, MimeTypes::registerGuesser() allows registering a guesser multiple times.',
                'resourceUrl' => 'https://symfony.com/doc/4.x/components/mime.html#adding-a-mime-type-guesser',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q16 - Twig - Block names
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig block names',
                'text' => 'Which of the following are valid block names ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Valid block names must start with a letter or underscore and contain only alphanumeric characters and underscores.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/block.html',
                'answers' => [
                    ['text' => 'foo_bar', 'correct' => true],
                    ['text' => 'foo123', 'correct' => true],
                    ['text' => '_foo', 'correct' => true],
                    ['text' => '123foo', 'correct' => false],
                    ['text' => '.foo', 'correct' => false],
                    ['text' => 'foo.bar', 'correct' => false],
                    ['text' => '-foo', 'correct' => false],
                ],
            ],

            // Q17 - Twig - Global variables
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig globals',
                'text' => 'When twig is used as a standalone library, which global variables are always available in templates?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When Twig is used standalone, only _self is available. The app variable is added by Symfony\'s TwigBundle.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/templates.html#global-variables',
                'answers' => [
                    ['text' => '<code>_self</code>', 'correct' => true],
                    ['text' => '<code>app</code>', 'correct' => false],
                    ['text' => '<code>_charset</code>', 'correct' => false],
                    ['text' => '<code>_context</code>', 'correct' => false],
                ],
            ],

            // Q18 - PHP - File handling fseek/rewind
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:I/O'],
                'title' => 'File handling - fseek equivalent',
                'text' => '<pre><code class="language-php">&lt;?php

$fp = fopen(\'file.txt\', \'r\');

$string1 = fgets($fp, 512);

fseek($fp, 0);</code></pre>
<p>Which of the following functions will give the same output as that given by the <code>fseek()</code> function in the above script?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The rewind() function is equivalent to fseek($fp, 0). It repositions the file pointer to the beginning.',
                'resourceUrl' => 'https://www.php.net/filesystem',
                'answers' => [
                    ['text' => 'rewind()', 'correct' => true],
                    ['text' => 'fgets()', 'correct' => false],
                    ['text' => 'fgetss()', 'correct' => false],
                    ['text' => 'file()', 'correct' => false],
                ],
            ],

            // Q19 - PHP - Basic operations with octal
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'title' => 'PHP basic operations with octal',
                'text' => 'What is the output ?
<pre><code class="language-php">&lt;?php
echo "4" + 05 + 011 + ord(\'a\');
?&gt;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => '"4" = 4, 05 (octal) = 5, 011 (octal) = 9, ord(\'a\') = 97. Total: 4 + 5 + 9 + 97 = 115.',
                'resourceUrl' => 'http://php.net/operators',
                'answers' => [
                    ['text' => '115', 'correct' => true],
                    ['text' => '14', 'correct' => false],
                    ['text' => '18', 'correct' => false],
                    ['text' => '117', 'correct' => false],
                ],
            ],

            // Q20 - Security - Custom request matcher
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'title' => 'Custom request matcher usage',
                'text' => 'Is the following code valid?
<pre><code class="language-yaml"># config/packages/security.yaml

security:

# ...

    firewalls:
        secured_area:
            request_matcher: app.firewall.secured_area.request_matcher

            # ...</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, the request_matcher option allows using a custom service to match requests for the firewall.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/security/firewall_restriction.html#restricting-by-service',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q21 - Console - Verbosity levels
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'title' => 'Console Verbosity levels',
                'text' => 'What are the console verbosity levels?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The console verbosity levels are: VERBOSITY_QUIET, VERBOSITY_NORMAL, VERBOSITY_VERBOSE, VERBOSITY_VERY_VERBOSE, and VERBOSITY_DEBUG.',
                'resourceUrl' => 'http://symfony.com/doc/current/console/verbosity.html',
                'answers' => [
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_QUIET</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_NORMAL</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_VERBOSE</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_VERY_VERBOSE</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_DEBUG</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_NONE</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_VERY_VERY_VERBOSE</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">OutputInterface::VERBOSITY_NO_DEBUG</code></pre>', 'correct' => false],
                ],
            ],

            // Q22 - Twig - Loaders definition
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig loaders definition',
                'text' => 'What are Twig loaders responsible for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Loaders are responsible for loading templates from a resource name (filesystem, database, etc.).',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/api.html#loaders',
                'answers' => [
                    ['text' => 'Loaders are responsible for loading templates from a resource name.', 'correct' => true],
                    ['text' => 'Loaders are responsible for loading extensions.', 'correct' => false],
                    ['text' => 'Loaders are responsible for loading environments such as Twig_Evironment.', 'correct' => false],
                    ['text' => 'Loaders are responsible for loading token parsers.', 'correct' => false],
                ],
            ],

            // Q23 - HttpFoundation - Response::create() since 6.0
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'Response::create() since 6.0',
                'text' => 'Since <code>6.0</code>, could a response be created via <code>Response::create()</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'No, Response::create() was deprecated in 5.4 and removed in 6.0. Use new Response() instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/HttpFoundation/Response.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q24 - PHP - Throwable interface
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Exceptions'],
                'title' => 'PHP Throwable interface',
                'text' => 'Is the following exception class valid ?
<pre><code class="language-php">class MyException implements Throwable
{
  private $message;
  private $code;
  private $file;
  private $line;
  private $trace;
  private $previous;

  public function __construct($message, $code, $file, $line, array $trace, Throwable $previous)
  {
    $this->message = $message;
    $this->code = $code;
    $this->file = $file;
    $this->line = $line;
    $this->trace = $trace;
    $this->throwable = $throwable;
  }

  public function getMessage()
  {
    return $this->message;
  }    

  public function getCode()
  {
    return $this->code;
  }

  public function getFile()
  {
    return $this->file;
  }

  public function getLine()
  {
    return $this->line;
  }

  public function getTrace()
  {
    return $this->trace;
  }

  public function getTraceAsString()
  {
    return serialize($this->trace);
  }

  public function getPrevious()
  {
    return $this->previous;
  }

  public function __toString()
  {
      return sprintf(\'%d: %s\', $this->code, $this->message);
  }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Throwable is an internal interface that cannot be implemented directly by userland classes. You must extend Exception or Error instead.',
                'resourceUrl' => 'https://www.php.net/manual/en/class.throwable.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q25 - Twig - Custom escaper
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'title' => 'Twig custom escaper',
                'text' => 'Can we create a custom escaper for Twig ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, Twig allows creating custom escapers using the setEscaper() method.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/escape.html#custom-escapers',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q26 - Routing - XML route matching
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'title' => 'XML route matching with regex',
                'text' => 'Consider the following XML route definition:
<pre><code class="language-xml">&lt;!-- app/config/routing.xml --&gt;
&lt;?xml version="1.0" encoding="UTF-8" ?&gt;
&lt;routes xmlns="http://symfony.com/schema/routing"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/routing
        http://symfony.com/schema/routing/routing-1.0.xsd"&gt;
    &lt;route id="app_agenda_event" path="/agenda/{date}" methods="GET"&gt;
        &lt;default key="_controller"&gt;AppBundle:Agenda:event&lt;/default&gt;
        &lt;requirement key="date"&gt;(?:20\d{2})-(?:(0?[1-9]|1[1-2]))-(?:(0?|[1-2])\d|3[0-1])&lt;/requirement&gt;
    &lt;/route&gt;
&lt;/routes&gt;</code></pre>
<p>Which of the following URL patterns will match the <code>app_agenda_event</code> route?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The regex requires year starting with 20 (2000-2099), month 01-12 (with optional leading zero), and day 01-31.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/components/routing.html',
                'answers' => [
                    ['text' => '<code>http://localhost/agenda/2011-1-01</code>', 'correct' => true],
                    ['text' => '<code>http://localhost/agenda/2020-2-30</code>', 'correct' => true],
                    ['text' => '<code>http://localhost/agenda/2018-12-12</code>', 'correct' => true],
                    ['text' => '<code>http://localhost/agenda/2008-04-06</code>', 'correct' => true],
                    ['text' => '<code>http://localhost/agenda/2018-14-30</code>', 'correct' => false],
                    ['text' => '<code>http://localhost/agenda/2150-12-31</code>', 'correct' => false],
                ],
            ],

            // Q27 - Validator - Constraints on properties
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'title' => 'Constraints usage on properties',
                'text' => 'A constraint can be applied on',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Constraints can be applied on public, protected, and private properties.',
                'resourceUrl' => 'https://symfony.com/doc/current/book/validation.html#properties',
                'answers' => [
                    ['text' => 'public property', 'correct' => true],
                    ['text' => 'protected property', 'correct' => true],
                    ['text' => 'private property', 'correct' => true],
                ],
            ],

            // Q28 - PHP Arrays - array_diff_assoc
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'title' => 'Array functions - array_diff_assoc',
                'text' => 'Which of the following functions compares array1 against array2 and returns the difference by checking array keys in addition?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_diff_assoc() computes the difference of arrays with additional index check.',
                'resourceUrl' => 'https://php.net/manual/en/function.array-diff-assoc.php',
                'answers' => [
                    ['text' => '<code>array_diff_assoc</code>', 'correct' => true],
                    ['text' => '<code>array_diff_uassoc</code>', 'correct' => false],
                    ['text' => '<code>array_diff_key</code>', 'correct' => false],
                    ['text' => '<code>array_diff_ukey</code>', 'correct' => false],
                ],
            ],

            // Q29 - Messenger - Sender responsibility
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'title' => 'Messenger - Sender responsibility',
                'text' => 'What is the responsibility of a Sender?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'A Sender is responsible for serializing and sending messages to message brokers or third party services.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/messenger.html#concepts',
                'answers' => [
                    ['text' => 'It serialize and send messages to message brokers/third party services', 'correct' => true],
                    ['text' => 'It wrap the message in order to define metadata', 'correct' => false],
                    ['text' => 'It retrieve and deserialize messages', 'correct' => false],
                ],
            ],

            // Q30 - HttpKernel - Controller with Closure
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'title' => 'Controller definition with Closure',
                'text' => 'Could a controller be defined using <code>\Closure</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, controllers can be defined as closures in Symfony.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/controller.html#a-simple-controller',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q31 - DI - ReverseContainer
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'ReverseContainer usage',
                'text' => 'Could a service identifier be returned from a <code>ReverseContainer</code> if the service is not tagged as <code>container.reversible</code> but defined as <code>public</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Yes, ReverseContainer can return identifiers for public services even without the container.reversible tag.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/DependencyInjection/ReverseContainer.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q32 - Filesystem - Path class
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'title' => 'Filesystem Path class',
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

use Symfony\Component\Filesystem\Path;

$path = new Path(\'/srv/app/var/cache\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Path class is a final class with only static methods. It cannot be instantiated.',
                'resourceUrl' => 'https://github.com/symfony/filesystem/blob/5.4/Path.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q33 - DI - ParameterBag frozen
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'title' => 'ParameterBag frozen',
                'text' => 'Could a <code>ParameterBag</code> be frozen?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, the FrozenParameterBag class allows creating an immutable parameter bag.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q34 - HttpFoundation - $_COOKIE access
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'title' => 'Accessing $_COOKIE data',
                'text' => 'How to access <code>$_COOKIE</code> data when using a <code>Symfony\Component\HttpFoundation\Request</code> <code>$request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The cookies property of the Request object provides access to $_COOKIE data.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#accessing-request-data',
                'answers' => [
                    ['text' => '<pre><code>$request->cookies</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>$request->cookie</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getCookie()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getCookieData()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getCookies()</code></pre>', 'correct' => false],
                ],
            ],

            // Q35 - Asset - VersionStrategyInterface
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Assets'],
                'title' => 'VersionStrategyInterface version access',
                'text' => 'Could the version of a asset be accessed from within a class implementing the <code>VersionStrategyInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, VersionStrategyInterface provides getVersion() method to access the asset version.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.7/src/Symfony/Component/Asset/VersionStrategy/VersionStrategyInterface.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q36 - Validator - Validation constraints elements
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'title' => 'Validation constraints elements',
                'text' => 'Which of the following elements can contain validation constraints?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Validation constraints can be applied to public properties, private/protected properties, classes, and public getters/issers.',
                'resourceUrl' => 'https://symfony.com/doc/current/validation.html#index-6',
                'answers' => [
                    ['text' => 'Public properties', 'correct' => true],
                    ['text' => 'Private and protected properties', 'correct' => true],
                    ['text' => 'Classes', 'correct' => true],
                    ['text' => 'Public getters/issers', 'correct' => true],
                    ['text' => 'Private and protected getters/issers', 'correct' => false],
                ],
            ],

            // Q37 - Validator - Constraint class purpose
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'title' => 'Constraint class purpose',
                'text' => 'In validation, what is the purpose of the Constraint classes ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Constraint classes define the rules to validate. The actual validation logic is in the ConstraintValidator classes.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/validation/custom_constraint.html',
                'answers' => [
                    ['text' => 'To define the rules to validate.', 'correct' => true],
                    ['text' => 'To define the validation logic.', 'correct' => false],
                    ['text' => 'To define the validation groups.', 'correct' => false],
                ],
            ],
        ];
    }
}
