<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 7
 */
class CertificationQuestionsFixtures7 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures6::class];
    }

    public function load(ObjectManager $manager): void
    {
        $symfonyRepo = $manager->getRepository(Category::class);
        $symfony = $symfonyRepo->findOneBy(['name' => 'Symfony']);
        $php = $symfonyRepo->findOneBy(['name' => 'PHP']);

        // Load existing subcategories from AppFixtures
        $subcategories = $this->loadSubcategories($manager);

        $questions = [
            // Twig - map/join usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which expression can be applied to <code>???</code> in order to display the name of each people along with his name and age?<pre><code class="language-twig">{% set people = [
    {firstname: "Bob", lastname: "Smith", age: 12},
    {firstname: "Alice", lastname: "Dupond", age: 13},
] %}

{{ ??? }} {# Must display "Bob Smith is 12 years old, Alice Dupond is 13 years old" #}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The map filter transforms each element, and join concatenates them with a separator.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/map.html',
                'answers' => [
                    ['text' => '<pre><code class="language-twig">{{ people|map(p => "#{p.firstname} #{p.lastname} is #{p.age} years old")|join(\', \') }}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-twig">{{ people|join(\', \') }}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{{ people|split(\', \') }}</code></pre>', 'correct' => false],
                    ['text' => 'None of those', 'correct' => false],
                ],
            ],
            // Intl - Polyfill
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Intl'],
                'text' => 'For what does the <code>Intl</code> component act as a polyfill?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Intl component provides a polyfill for the English part of the ICU library, not the whole library.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/intl.html',
                'answers' => [
                    ['text' => 'The English part of the ICU library', 'correct' => true],
                    ['text' => 'The whole ICU library', 'correct' => false],
                    ['text' => 'Intl is not a polyfill', 'correct' => false],
                ],
            ],
            // DI - YAML imports validation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is this code valid?<pre><code class="language-yaml"># app/config/config.yml
imports:
    - { resource: "%kernel.root_dir%/parameters.yml" }</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, since Symfony 3.4, parameters cannot be used in the imports section. Use relative paths instead.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/configuration/configuration_organization.html#different-directories-per-environment',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // PHP Basics - sprintf vs echo/print
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Which of the following language structures and functions does not output anything?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'sprintf() returns a formatted string but does not output it. echo, print, print_r(), and var_dump() all produce output.',
                'resourceUrl' => 'http://php.net/manual/en/function.sprintf.php',
                'answers' => [
                    ['text' => '<code>sprintf()</code>', 'correct' => true],
                    ['text' => '<code>echo</code>', 'correct' => false],
                    ['text' => '<code>print</code>', 'correct' => false],
                    ['text' => '<code>print_r()</code>', 'correct' => false],
                    ['text' => '<code>var_dump()</code>', 'correct' => false],
                ],
            ],
            // DI - Tags
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which mechanism allows to aggregate services by domain in the service container?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Tags allow you to mark services for special processing or to group related services together.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/dependency_injection/tags.html',
                'answers' => [
                    ['text' => 'Tag', 'correct' => true],
                    ['text' => 'Scope', 'correct' => false],
                    ['text' => 'Abstraction', 'correct' => false],
                    ['text' => 'Listener', 'correct' => false],
                ],
            ],
            // Mime - multipart/related
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mime'],
                'text' => 'What is the purpose of the <code>multipart/related</code> MIME message part?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'multipart/related is used to indicate that each message part is a component of an aggregate whole, commonly for embedding images in HTML emails.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/mime#creating-raw-email-messages',
                'answers' => [
                    ['text' => 'Used to indicate that each message part is a component of an aggregate whole. The most common usage is to display images embedded in the message contents', 'correct' => true],
                    ['text' => 'Used when two or more parts are alternatives of the same (or very similar) content. The preferred format must be added last', 'correct' => false],
                    ['text' => 'Used to send different content types in the same message, such as when attaching files', 'correct' => false],
                ],
            ],
            // Twig - FilesystemCache
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given the case where the opcache/APC cache for template need to be invalidated, is the following code valid?<pre><code class="language-php">&lt;?php

// ...

$twig = new Environment($loader, [
    \'cache\' => new FilesystemCache(\'/some/cache/path\', 1),
    // ...
]);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, FilesystemCache with the second argument set to 1 enables opcache invalidation.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/recipes.html#refreshing-modified-templates-when-opcache-or-apc-is-enabled',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // PHP Basics - iterable return type
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is missing in the following code snippet in place of <code>???</code>?<pre><code class="language-php">$f = function (): ??? {
    yield null;
};</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Functions using yield (generators) must declare an iterable return type.',
                'resourceUrl' => 'http://php.net/manual/en/language.types.iterable.php',
                'answers' => [
                    ['text' => '<code>iterable</code>', 'correct' => true],
                    ['text' => '<code>void</code>', 'correct' => false],
                    ['text' => '<code>use (Iterable)</code>', 'correct' => false],
                    ['text' => '<code>array</code>', 'correct' => false],
                ],
            ],
            // Lock - Key contains state
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Lock'],
                'text' => 'Which class contains the lock state?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Key class contains the lock state, including information about whether the lock is acquired.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/Lock/Key.php',
                'answers' => [
                    ['text' => 'The <code>Key</code>', 'correct' => true],
                    ['text' => 'The <code>Lock</code>', 'correct' => false],
                    ['text' => 'The <code>LockFactory</code>', 'correct' => false],
                    ['text' => 'The <code>Store</code>', 'correct' => false],
                ],
            ],
            // Form - Button types
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which type does not correspond to a button?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony Form component has button, submit, and reset button types. There is no "input" button type.',
                'resourceUrl' => 'https://symfony.com/doc/2.3/reference/forms/types.html#buttons',
                'answers' => [
                    ['text' => '<em>input</em>', 'correct' => true],
                    ['text' => '<em>button</em>', 'correct' => false],
                    ['text' => '<em>submit</em>', 'correct' => false],
                    ['text' => '<em>reset</em>', 'correct' => false],
                ],
            ],
            // Form - FormRegistry::getType
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which type is returned by <code>FormRegistry::getType()</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'FormRegistry::getType() returns a ResolvedFormTypeInterface instance.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Form/FormRegistry.php#L60',
                'answers' => [
                    ['text' => '<code>ResolvedFormTypeInterface</code>', 'correct' => true],
                    ['text' => '<code>GuessedType</code>', 'correct' => false],
                    ['text' => '<code>FormInterface</code>', 'correct' => false],
                    ['text' => '<code>ResolvedForm</code>', 'correct' => false],
                ],
            ],
            // Config - FileLocator
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'What is the first argument of the <code>Symfony\\Component\\Config\\FileLocator::locate</code> method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The first argument is the name of the file to look for.',
                'resourceUrl' => 'https://symfony.com/doc/2.0/components/config/resources.html#locating-resources',
                'answers' => [
                    ['text' => 'The name of the file to look for.', 'correct' => true],
                    ['text' => 'The name of the configuration value to look for.', 'correct' => false],
                    ['text' => 'The type of file to look for.', 'correct' => false],
                    ['text' => 'The name of the directory to look for.', 'correct' => false],
                ],
            ],
            // DI - Service configurators
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could service configurators use <code>__invoke()</code> to configure a service?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, service configurators can use __invoke() as an invokable class to configure services.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/service_container/configurators.html#using-the-configurator',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Translation - Escape characters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'Which of this following code is correct to use the percent character <code>%</code> in a translated string?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'To escape a percent character in translations, use double percent (%%).',
                'resourceUrl' => 'http://symfony.com/doc/current/translation.html#twig-templates',
                'answers' => [
                    ['text' => '<pre><code class="language-twig">{% trans %}Percent: %percent%%%{% endtrans %}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-twig">{% trans %}Percent: %percent%\\%{% endtrans %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% trans %}Percent: %percent%[%]{% endtrans %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% trans %}Percent: %percent%{%}{% endtrans %}</code></pre>', 'correct' => false],
                ],
            ],
            // Form - PreSubmitEvent getData
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
                    ['text' => 'The model data of the form', 'correct' => false],
                    ['text' => 'the view data of the form', 'correct' => false],
                    ['text' => 'the norm data of the form', 'correct' => false],
                ],
            ],
            // HttpClient - consuming SSE
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'Could you consume server-sent events?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, HttpClient supports consuming server-sent events (SSE) since Symfony 5.2.',
                'resourceUrl' => 'https://symfony.com/doc/5.2/http_client.html#consuming-server-sent-events',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // HttpFoundation - accessing files
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'How to access <code>$_FILES</code> data when using a <code>Symfony\\Component\\HttpFoundation\\Request</code> <code>$request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'File uploads are accessed via the files property of the Request object.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#accessing-request-data',
                'answers' => [
                    ['text' => '<pre><code>$request->files</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>$request->file</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getFiles()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getFileData()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getFilesData()</code></pre>', 'correct' => false],
                ],
            ],
            // DI - Autowiring with multiple implementations
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Will the following autowiring declaration work?<pre><code class="language-yaml">services:
    rot13:
        class:    Acme\\Transformer\\Rot13Transformer
        arguments: [true]

    rot13_2:
        class:    Acme\\Transformer\\Rot13Transformer
        arguments: [false]

    twitter_client:
        class:    Acme\\TwitterClient
        autowire: true</code></pre><pre><code class="language-php">namespace Acme;

use Acme\\Transformer\\Rot13Transformer;

class TwitterClient
{
    private $transformer;

    public function __construct(Rot13Transformer $transformer)
    {
        $this->transformer = $transformer;
    }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'No, autowiring fails when there are multiple services of the same type. You need to explicitly specify which service to use.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/dependency_injection/autowiring.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Console - constructor issue
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Given <code>1</code> is injected in the <code>$kernelDebug</code> constructor arg and the following code:<pre><code class="language-php">class FooBarCommand extends Command
{
    protected static $defaultName = \'app:foo:bar\';
    private $kernelDebug;

    public function __construct(int $kernelDebug)
    {
        parent::__construct();
        $this->kernelDebug = $kernelDebug;
    }

    protected function configure()
    {
        $this
            ->setDescription(\'My FooBar command\')
            ->addArgument(\'debug\', InputArgument::OPTIONAL, \'debug\', $this->kernelDebug)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(\'debug:\'.$input->getArgument(\'debug\'));
        return 0;
    }
}</code></pre>What will be the behavior of this command when called without arguments?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Since parent::__construct() calls configure(), $this->kernelDebug is still empty (null/0) when configure() is called. The assignment happens after parent::__construct().',
                'resourceUrl' => 'https://symfony.com/doc/3.4/console.html',
                'answers' => [
                    ['text' => 'it will display: <code>debug:</code>', 'correct' => true],
                    ['text' => 'it will display <code>debug:1</code>', 'correct' => false],
                    ['text' => 'an exception will be thrown', 'correct' => false],
                ],
            ],
            // Arrays - array_diff_assoc
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays & Collections'],
                'text' => 'Which of the following functions compares array1 against array2 and returns the difference by checking array keys in addition?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_diff_assoc() computes the difference of arrays with additional index check.',
                'resourceUrl' => 'https://php.net/manual/en/function.array-diff-assoc.php',
                'answers' => [
                    ['text' => '<code>array_diff_assoc</code>', 'correct' => true],
                    ['text' => '<code>array_diff_key</code>', 'correct' => false],
                    ['text' => '<code>array_diff_ukey</code>', 'correct' => false],
                    ['text' => '<code>array_diff_uassoc</code>', 'correct' => false],
                ],
            ],
            // Messenger - exception count
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Can we access the exception count in the <code>Messenger</code> data collector?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, MessengerDataCollector provides access to exception count via getExceptionsCount().',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/Messenger/DataCollector/MessengerDataCollector.php#L126',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // HttpKernel - Request locale
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Is it possible to change directly the locale of a Request from an URL?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, you can use the _locale parameter in routes to set the request locale from the URL.',
                'resourceUrl' => 'http://symfony.com/doc/current/book/routing.html#adding-requirements',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Twig - Lexer EOF token
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which token is used by the <code>Lexer</code> to find the end of a template?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Twig Lexer uses Token::EOF_TYPE to indicate the end of a template.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/internals.html#the-lexer',
                'answers' => [
                    ['text' => '<code>Token::EOF_TYPE</code>', 'correct' => true],
                    ['text' => '<code>Token::EOF</code>', 'correct' => false],
                    ['text' => '<code>Token::END_OF_FILE_TYPE</code>', 'correct' => false],
                    ['text' => '<code>Token::END_OF_FILE</code>', 'correct' => false],
                ],
            ],
            // PHP Basics - echo print behavior
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following code snippet:<pre><code class="language-php">echo print(\'hello\');</code></pre>What will be the output when running this script?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'print() outputs "hello" and returns 1. Then echo outputs that 1. Result: hello1',
                'resourceUrl' => 'http://php.net/print',
                'answers' => [
                    ['text' => '<pre><code>hello1</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>hello</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>hellotrue</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>hello5</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>hello0</code></pre>', 'correct' => false],
                ],
            ],
            // Form - DateIntervalType widget
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'How is a DateIntervalType form field rendered when the <code>widget</code> option is set to <code>single_text</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'With widget set to single_text, DateIntervalType renders as a single text field expecting ISO 8601 format.',
                'resourceUrl' => 'https://symfony.com/doc/3.2/reference/forms/types/dateinterval.html',
                'answers' => [
                    ['text' => 'a single text field', 'correct' => true],
                    ['text' => 'a single html5 date field', 'correct' => false],
                    ['text' => 'two html5 date fields', 'correct' => false],
                    ['text' => 'two text fields', 'correct' => false],
                ],
            ],
            // HttpFoundation - Generator in JsonResponse
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What will be returned by the following code?<pre><code class="language-php">use Symfony\\Component\\HttpFoundation\\JsonResponse;

class DefaultController
{
    public function default()
    {
        return new JsonResponse([
            \'data\' => $this->getData(),
        ]);
    }

    private function getData(): \\Generator
    {
        yield \'foo\';
        yield \'bar\';
        yield \'baz\';
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'JsonResponse cannot properly serialize a Generator. It will output an empty object. Use StreamedJsonResponse for generators.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-dx-improvements-part-2#streamed-json-responses',
                'answers' => [
                    ['text' => 'It will return <code>{"data":{}}</code>', 'correct' => true],
                    ['text' => 'It will return <code>{"data":["foo","bar","baz"]}</code>', 'correct' => false],
                    ['text' => 'It will throw an <code>\\InvalidArgumentException</code>', 'correct' => false],
                ],
            ],
            // DI - Environment variables usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Given the following configuration and the fact that the <code>.env</code> file exist with a key <code>APP_SECRET=bar</code>, which value will be used in <code>framework.secret</code>?<pre><code class="language-yaml"># config/packages/framework.yaml
parameters:
    env(SECRET): \'foo\'

framework:
    secret: \'%env(APP_SECRET)%\'</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The .env file value (bar) takes precedence over the default fallback (foo) because APP_SECRET is set.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/configuration/env_var_processors.html#built-in-environment-variable-processors',
                'answers' => [
                    ['text' => '<code>bar</code>', 'correct' => true],
                    ['text' => '<code>foo</code>', 'correct' => false],
                    ['text' => 'An error will be thrown', 'correct' => false],
                ],
            ],
            // HttpKernel - ResponseListener
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the aim of the <code>Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener::onKernelResponse()</code> listener on <code>kernel.response</code> event?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ResponseListener sets the Response headers based on the Request, particularly charset and content-type.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/HttpKernel/EventListener/ResponseListener.php',
                'answers' => [
                    ['text' => 'Sets the Response headers based on the Request.', 'correct' => true],
                    ['text' => 'Checks if the Response has a body.', 'correct' => false],
                    ['text' => 'Checks if the Response headers match the HTTP RFC requirements.', 'correct' => false],
                ],
            ],
            // ErrorHandler - ErrorHandler::call
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:ErrorHandler'],
                'text' => 'Given the following code in a controller:<pre><code class="language-php">use Symfony\\Component\\HttpFoundation\\Response;
use Symfony\\Component\\ErrorHandler\\ErrorHandler;

class MyController
{
  public function displayContent(): Response
  {
    $content = ErrorHandler::call(\'file_get_content\', \'/my-inexistent-file.txt\');

    return new Response($content);
  }
}</code></pre>What will be displayed if <code>/my-inexistent-file.txt</code> doesn\'t exist?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ErrorHandler::call() converts PHP errors to exceptions. A missing file will throw an exception.',
                'resourceUrl' => 'https://github.com/symfony/error-handler/blob/dc432104fe98d79edcdd305312e4494956ce47ad/ErrorHandler.php#L159',
                'answers' => [
                    ['text' => 'An exception will be thrown', 'correct' => true],
                    ['text' => 'An empty page will be displayed', 'correct' => false],
                    ['text' => 'An empty page will be displayed and an exception will be visible in the profiler', 'correct' => false],
                ],
            ],
            // Routing - condition expression variables
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Which variables can be used in the <code>ExpressionLanguage</code> expression when using the <code>condition</code> option on a Route?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The available variables are: context, request, and params. Since Symfony 6.1, env() function is also available.',
                'resourceUrl' => 'https://symfony.com/doc/6.1/routing.html#matching-expressions',
                'answers' => [
                    ['text' => '<code>context</code>', 'correct' => true],
                    ['text' => '<code>request</code>', 'correct' => true],
                    ['text' => '<code>params</code>', 'correct' => true],
                    ['text' => '<code>service</code>', 'correct' => false],
                    ['text' => '<code>user</code>', 'correct' => false],
                    ['text' => '<code>container</code>', 'correct' => false],
                    ['text' => '<code>this</code>', 'correct' => false],
                    ['text' => '<code>object</code>', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $questionData) {
            $this->upsertQuestion($manager, $questionData);
        }

        $manager->flush();
    }
}
