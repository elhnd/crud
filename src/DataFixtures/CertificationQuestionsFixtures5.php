<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 5
 */
class CertificationQuestionsFixtures5 extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures4::class];
    }

    public function load(ObjectManager $manager): void
    {
        $symfonyRepo = $manager->getRepository(Category::class);
        $symfony = $symfonyRepo->findOneBy(['name' => 'Symfony']);
        $php = $symfonyRepo->findOneBy(['name' => 'PHP']);

        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        $subcategories = [];

        // Load existing subcategories
        foreach ($subcategoryRepo->findAll() as $sub) {
            $key = $sub->getCategory()->getName() . ':' . $sub->getName();
            $subcategories[$key] = $sub;
        }

        // Create new subcategories if needed
        $newSubcategories = [
            ['HttpClient', $symfony],
            ['OptionsResolver', $symfony],
            ['VarDumper', $symfony],
            ['Asset', $symfony],
            ['Console', $symfony],
            ['PasswordHasher', $symfony],
            ['Runtime', $symfony],
            ['CssSelector', $symfony],
            ['Config', $symfony],
            ['PSR', $php],
            ['PHP Basics', $php],
        ];

        foreach ($newSubcategories as [$name, $category]) {
            $key = $category->getName() . ':' . $name;
            if (!isset($subcategories[$key])) {
                $sub = new Subcategory();
                $sub->setName($name);
                $sub->setCategory($category);
                $manager->persist($sub);
                $subcategories[$key] = $sub;
            }
        }

        $manager->flush();

        $questions = [
            // MockHttpClient request count
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'Could the amount of requests proceeded by <code>MockHttpClient</code> be accessed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, MockHttpClient provides a getRequestsCount() method to track how many requests were made.',
                'resourceUrl' => 'https://symfony.com/doc/5.1/http_client.html#testing-http-clients-and-responses',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // PHP Object assignment
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'What will be the output?<pre><code class="language-php">&lt;?php

class SimpleClass
{

}

$instance = new SimpleClass();

$assigned   =  $instance;
$reference  =&amp; $instance;

$instance-&gt;var = \'$assigned will have this value\';

$instance = null;

var_dump($instance);
var_dump($reference);
var_dump($assigned);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => '$assigned holds a copy of the object identifier (pointing to the same object), while $reference is a reference to $instance. When $instance is set to null, $reference also becomes null, but $assigned still points to the original object.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.basic.php',
                'answers' => [
                    ['text' => '<pre><code>NULL
NULL
object(SimpleClass)#1 (1) {
  ["var"]=>
  string(30) "$assigned will have this value"
}</code></pre>', 'correct' => true],
                    ['text' => 'An error', 'correct' => false],
                ],
            ],
            // OptionsResolver setDefined
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:OptionsResolver'],
                'text' => 'How can you add an option named <code>my_option</code> without setting a default value?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'setDefined() declares an option without requiring it or setting a default value.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/options_resolver.html#options-without-default-values',
                'answers' => [
                    ['text' => '<code>$resolver->setDefined(\'my_option\');</code>', 'correct' => true],
                    ['text' => '<code>$resolver->setDefault(\'my_option\');</code>', 'correct' => false],
                    ['text' => '<code>$resolver->setNotRequired(\'my_option\');</code>', 'correct' => false],
                    ['text' => '<code>$resolver->setDefault(\'my_option\', null);</code>', 'correct' => false],
                ],
            ],
            // VarDumper Dumpers purpose
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarDumper'],
                'text' => 'What is a <strong>Dumper</strong> in VarDumper component?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'A dumper is responsible for outputting a string representation of a PHP variable as provided by a Cloner.',
                'resourceUrl' => 'https://symfony.com/doc/2.6/components/var_dumper/advanced.html#dumpers',
                'answers' => [
                    ['text' => 'A dumper is responsible for outputting a string representation of a PHP variable.', 'correct' => true],
                    ['text' => 'A dumper is responsible for creating a <code>var_export</code> of any PHP variable.', 'correct' => false],
                    ['text' => 'A dumper is responsible for creating a <code>var_dump</code> of any PHP variable.', 'correct' => false],
                    ['text' => 'A dumper is responsible for getting each property values of PHP object.', 'correct' => false],
                ],
            ],
            // CIDR validation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Could a CIDR notation be validated in Symfony?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, Symfony 5.4+ includes a Cidr constraint for validating CIDR notation.',
                'resourceUrl' => 'https://symfony.com/doc/5.4/reference/constraints/Cidr.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // PHP signature compatibility
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Will the following code raise an error or warning?<pre><code class="language-php">&lt;?php

class A
{
    public function foo($arg1) {
        // ...
    }
}

class B extends A
{
    public function foo($arg1, $arg2) {
        // ...
    }
}

$b = new B();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The child class method signature must be compatible with the parent. Adding a required parameter violates the Liskov Substitution Principle.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.basic.php#language.oop.lsp',
                'answers' => [
                    ['text' => 'Yes, the declaration of <code>B::foo($arg1, $arg2)</code> must be compatible with <code>A::foo($arg1)</code>', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes, <code>class B</code> cannot inherit from <code>class A</code>', 'correct' => false],
                ],
            ],
            // VarCloner result
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarDumper'],
                'text' => 'What will be in <code>$result</code> with the following code?<pre><code class="language-php">&lt;?php

use Symfony\Component\VarDumper\Cloner\VarCloner;

$myVar = /*...*/;

$cloner = new VarCloner();
$result = $cloner->cloneVar($myVar);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'VarCloner::cloneVar() returns a Data object containing the cloned variable data.',
                'resourceUrl' => 'https://symfony.com/doc/2.6/components/var_dumper/advanced.html#cloners',
                'answers' => [
                    ['text' => 'A <code>Symfony\\Component\\VarDumper\\Cloner\\Data</code> object', 'correct' => true],
                    ['text' => 'A <code>Symfony\\Component\\VarDumper\\Data</code> object', 'correct' => false],
                    ['text' => 'A <code>Symfony\\Component\\VarDumper\\Cloner\\CloneData</code> object', 'correct' => false],
                    ['text' => 'An object of the same class of <code>$myVar</code>.', 'correct' => false],
                    ['text' => 'An array.', 'correct' => false],
                ],
            ],
            // AbstractSessionListener properties
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP Kernel'],
                'text' => 'Which sentences about <code>AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER</code> are true?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'This header controls the public/private cache-control property and is used as a response header.',
                'resourceUrl' => 'https://github.com/symfony/http-kernel/blob/4.1/EventListener/AbstractSessionListener.php',
                'answers' => [
                    ['text' => 'It\'s related to the <code>public/private</code> property of <code>cache-control</code>', 'correct' => true],
                    ['text' => 'It\'s designed to be used as a response header', 'correct' => true],
                    ['text' => 'It\'s designed to be used as a <code>SessionInterface</code> option', 'correct' => false],
                    ['text' => 'It\'s related to session storage strategy', 'correct' => false],
                    ['text' => 'It\'s designed to be used as a request header', 'correct' => false],
                ],
            ],
            // Asset version access
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Asset'],
                'text' => 'Could the version of an asset be accessed from within a class implementing the <code>VersionStrategyInterface</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, VersionStrategyInterface includes a getVersion() method.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.7/src/Symfony/Component/Asset/VersionStrategy/VersionStrategyInterface.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Translation domain argument
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'When you want to use a translation in another domain than the default domain, you must specify the domain as which argument of <code>trans()</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The domain is the third argument of the trans() method: trans($id, $parameters, $domain, $locale).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/0baa58d4e4bb006c4ae68f75833b586bd3cb6e6f/src/Symfony/Component/Translation/Translator.php#L169',
                'answers' => [
                    ['text' => 'third argument of <code>trans()</code>', 'correct' => true],
                    ['text' => 'first argument of <code>trans()</code>', 'correct' => false],
                    ['text' => 'second argument of <code>trans()</code>', 'correct' => false],
                    ['text' => 'fourth argument of <code>trans()</code>', 'correct' => false],
                ],
            ],
            // Route parameter escaping
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Assuming there is <code>locale: en</code> in <code>translation.yaml</code> and given the following route path: <code>/%%locale%%</code>, what URLs will be matched?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The first % escapes the parameter notation, so %%locale%% becomes the literal string %locale%.',
                'resourceUrl' => 'http://symfony.com/doc/current/routing/service_container_parameters.html',
                'answers' => [
                    ['text' => '<code>/%locale%</code>', 'correct' => true],
                    ['text' => '<code>/en</code>', 'correct' => false],
                    ['text' => '<code>/%en%</code>', 'correct' => false],
                    ['text' => '<code>/locale</code>', 'correct' => false],
                ],
            ],
            // OptionsResolver setAllowedTypes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:OptionsResolver'],
                'text' => 'Which of the following are valid types to use in <code>setAllowedTypes</code> method of <code>OptionsResolver</code> to validate a boolean value?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Both "bool" and "boolean" are valid type strings for setAllowedTypes(). There are no constants like OptionsResolver::BOOL.',
                'resourceUrl' => 'https://symfony.com/doc/3.0/components/options_resolver.html#type-validation',
                'answers' => [
                    ['text' => '<code>"bool"</code>', 'correct' => true],
                    ['text' => '<code>"boolean"</code>', 'correct' => true],
                    ['text' => '<code>OptionsResolver::BOOL</code>', 'correct' => false],
                    ['text' => '<code>OptionsResolver::BOOLEAN</code>', 'correct' => false],
                ],
            ],
            // Get current route in Twig
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'How to get the current route name from Twig?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The route name is stored in the _route request attribute.',
                'resourceUrl' => 'http://symfony.com/doc/current/templating/app_variable.html',
                'answers' => [
                    ['text' => '<code>{{ app.request.attributes.get(\'_route\') }}</code>', 'correct' => true],
                    ['text' => '<code>{{ app.request.attributes._routeName }}</code>', 'correct' => false],
                    ['text' => '<code>{{ app.request.attributes.get(\'route\') }}</code>', 'correct' => false],
                    ['text' => '<code>{{ app.request.route }}</code>', 'correct' => false],
                    ['text' => '<code>{{ app.routing.route }}</code>', 'correct' => false],
                ],
            ],
            // Console InputArgument IS_ARRAY
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'What type of argument would you use to accept more than one input parameter? For example, <code>php app/console hello Fabien Martin Jessica</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'InputArgument::IS_ARRAY allows accepting multiple values for a single argument.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/console/input.html',
                'answers' => [
                    ['text' => '<code>InputArgument::IS_ARRAY</code>', 'correct' => true],
                    ['text' => '<code>InputArgument::MULTIPLE</code>', 'correct' => false],
                    ['text' => '<code>InputArgument::OPTIONAL</code>', 'correct' => false],
                    ['text' => '<code>InputArgument::NONE</code>', 'correct' => false],
                ],
            ],
            // Command::setHidden default
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'What is the default value when calling <code>Command::setHidden()</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'setHidden() defaults to true when called without arguments.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Console/Command/Command.php#L496',
                'answers' => [
                    ['text' => '<code>true</code>', 'correct' => true],
                    ['text' => '<code>false</code>', 'correct' => false],
                ],
            ],
            // Twig Lexer
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following Twig internal objects is responsible for tokenizing the template source code into smaller pieces for easier processing?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Lexer is responsible for tokenizing the template source code into tokens.',
                'resourceUrl' => 'http://twig.symfony.com/doc/internals.html',
                'answers' => [
                    ['text' => 'The Lexer', 'correct' => true],
                    ['text' => 'The Compiler', 'correct' => false],
                    ['text' => 'The Environment', 'correct' => false],
                    ['text' => 'The Parser', 'correct' => false],
                ],
            ],
            // PasswordHasher migrate_from
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PasswordHasher'],
                'text' => 'Is the following password hasher configuration valid?<pre><code class="language-yaml">security:
    password_hashers:
      legacy:
        algorithm: sha256
        encode_as_base64: false
        iterations: 1

      App\Entity\User:
        algorithm: sodium
        migrate_from:
          - legacy</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, this is valid. The migrate_from option allows migrating from a legacy hasher to a new one.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/security/passwords.html#configure-a-new-hasher-using-migrate-from',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // PSR-3
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PSR'],
                'text' => 'What is PSR-3?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'PSR-3 defines a common logger interface (LoggerInterface).',
                'resourceUrl' => 'http://www.php-fig.org/psr/psr-3/',
                'answers' => [
                    ['text' => 'A common logger interface.', 'correct' => true],
                    ['text' => 'A coding style guide.', 'correct' => false],
                    ['text' => 'A utility to convert non-namespaced PHP classes into namespaced ones', 'correct' => false],
                    ['text' => 'A standard way to convert fully qualified names into file paths.', 'correct' => false],
                ],
            ],
            // Event Dispatcher same priority
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'What happens if two listeners have the same priority?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When listeners have the same priority, they are executed in the order they were added to the dispatcher.',
                'resourceUrl' => 'http://symfony.com/doc/2.3/components/event_dispatcher/introduction.html#connecting-listeners',
                'answers' => [
                    ['text' => 'They are executed in the order that they were added to the dispatcher.', 'correct' => true],
                    ['text' => 'They are executed in the alphanumeric order.', 'correct' => false],
                    ['text' => 'It throws an <code>InvalidPriorityException</code>.', 'correct' => false],
                ],
            ],
            // Yaml custom tags
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'],
                'text' => 'Is the following code valid?<pre><code class="language-php">&lt;?php

$parsed = Yaml::parse("!custom_tag { foo: bar }", Yaml::PARSE_CUSTOM_TAGS);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, Yaml::PARSE_CUSTOM_TAGS flag allows parsing custom tags.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/components/yaml.html#parsing-and-dumping-custom-tags',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // DI shared option
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is the way to always get a new instance of a service?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Setting shared: false ensures a new instance is created each time the service is requested.',
                'resourceUrl' => 'https://symfony.com/doc/current/cookbook/service_container/shared.html',
                'answers' => [
                    ['text' => 'Setting the option <code>shared</code> to false.', 'correct' => true],
                    ['text' => 'Setting the option <code>singleton</code> to false.', 'correct' => false],
                    ['text' => 'Setting the option <code>scope</code> to <code>prototype</code>.', 'correct' => false],
                    ['text' => 'Setting the option <code>scope</code> to <code>request</code>.', 'correct' => false],
                ],
            ],
            // Compiler passes registration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'How can you register a new compiler pass?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Compiler passes are registered using addCompilerPass() on ContainerBuilder.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/compiler_passes.html',
                'answers' => [
                    ['text' => 'By passing an instance of the <code>CompilerPass</code> to the <code>addCompilerPass</code> of a <code>ContainerBuilder</code>.', 'correct' => true],
                    ['text' => 'By passing an instance of the <code>CompilerPass</code> to the <code>registerCompilerPass</code> of a <code>ContainerBuilder</code>.', 'correct' => false],
                    ['text' => 'By creating a new service with the tag <code>compiler.pass</code>.', 'correct' => false],
                    ['text' => 'By passing an instance of the <code>CompilerPass</code> to the <code>pushCompilerPass</code> of a <code>ContainerBuilder</code>.', 'correct' => false],
                ],
            ],
            // Serializer context in attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Is it possible to specify the serialization context in an attribute/annotation?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 5.3 you can use #[Context] attribute to specify serialization context.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-3-inlined-serialization-context',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Request cookies access
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'How to access <code>$_COOKIE</code> data when using a <code>Symfony\\Component\\HttpFoundation\\Request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Cookie data is accessed via the cookies property.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#accessing-request-data',
                'answers' => [
                    ['text' => '<code>$request->cookies</code>', 'correct' => true],
                    ['text' => '<code>$request->getCookie()</code>', 'correct' => false],
                    ['text' => '<code>$request->getCookieData()</code>', 'correct' => false],
                    ['text' => '<code>$request->cookie</code>', 'correct' => false],
                    ['text' => '<code>$request->getCookies()</code>', 'correct' => false],
                ],
            ],
            // MockClock from datetime
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'Could a <code>MockClock</code> be created from an existing datetime?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, MockClock constructor accepts a DateTimeImmutable to initialize the clock.',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/MockClock.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Route compilation exception
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Which exception is thrown when a <code>Route</code> defined with <code>/page/{_fragment}</code> cannot be compiled?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'RouteCompiler throws an InvalidArgumentException when routes cannot be compiled.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/Routing/RouteCompiler.php#L39',
                'answers' => [
                    ['text' => '<code>InvalidArgumentException</code>', 'correct' => true],
                    ['text' => '<code>InvalidRouteCompilationContextException</code>', 'correct' => false],
                    ['text' => '<code>RouteCompilationException</code>', 'correct' => false],
                    ['text' => '<code>LogicException</code>', 'correct' => false],
                    ['text' => '<code>RuntimeException</code>', 'correct' => false],
                ],
            ],
            // PSR-1 compliance
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PSR'],
                'text' => 'Is this content PSR-1 compliant?<pre><code class="language-php">&lt;?php
ini_set(\'error_reporting\', E_ALL);

const APP_ENV = \'dev\';
const APP_DEBUG = 1;</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, PSR-1 states that declarations and side effects should not be mixed in the same file. ini_set() is a side effect.',
                'resourceUrl' => 'https://www.php-fig.org/psr/psr-1/',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // FormTypeGuesserInterface methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What are the methods defined in <code>Symfony\\Component\\Form\\FormTypeGuesserInterface</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'FormTypeGuesserInterface defines guessType, guessRequired, guessMaxLength, and guessPattern.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Form/FormTypeGuesserInterface.php',
                'answers' => [
                    ['text' => 'guessType', 'correct' => true],
                    ['text' => 'guessRequired', 'correct' => true],
                    ['text' => 'guessMaxLength', 'correct' => true],
                    ['text' => 'guessPattern', 'correct' => true],
                    ['text' => 'guessValid', 'correct' => false],
                    ['text' => 'guess', 'correct' => false],
                    ['text' => 'guessAttribute', 'correct' => false],
                    ['text' => 'guessLength', 'correct' => false],
                ],
            ],
            // LanguageType choices
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Could the choices of <code>LanguageType</code> be overridden?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, LanguageType has a choices option that can be overridden.',
                'resourceUrl' => 'https://symfony.com/doc/2.0/reference/forms/types/language.html#choices',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // ParameterBag append method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is the following code valid?<pre><code class="language-php">&lt;?php

use Symfony\\Component\\DependencyInjection\\ParameterBag;

$bag = new ParameterBag();
$bag->append(\'foo\', \'bar\');</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, ParameterBag does not have an append() method. Use set() instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/DependencyInjection/ParameterBag/ParameterBag.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Twig strict_variables off
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What will be the result of <code>The {{ color }} car!</code> without passing a <code>color</code> variable when <code>strict_variables</code> is off?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'With strict_variables off, undefined variables evaluate to empty string.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/api.html#environment-options',
                'answers' => [
                    ['text' => 'The template will be successfully evaluated and the string <code>The  car!</code> will be displayed.', 'correct' => true],
                    ['text' => 'The template will be partially evaluated and the string <code>The</code> will be displayed.', 'correct' => false],
                    ['text' => 'Twig will raise a <code>Twig_Error_Runtime</code> exception.', 'correct' => false],
                    ['text' => 'The template will be successfully evaluated and the string <code>The empty car!</code> will be displayed.', 'correct' => false],
                ],
            ],
            // Serializer denormalize
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'What will be displayed by the following code?<pre><code class="language-php">class ValueObject
{
  private $foo;

  public function __construct($bar)
  {
    $this->foo = $bar;
  }

  public function getFoo()
  {
    return $this->foo;
  }
}

$normalizer = new GetSetMethodNormalizer();
$vo = $normalizer->denormalize([\'bar\' => \'symfony\'], ValueObject::class);

echo $vo->getFoo();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'GetSetMethodNormalizer uses constructor parameter names to match data keys during denormalization.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8c778cbaa30db02fee9d972badcac834b51da0fa/src/Symfony/Component/Serializer/Normalizer/AbstractNormalizer.php#L369',
                'answers' => [
                    ['text' => '"symfony"', 'correct' => true],
                    ['text' => 'nothing, an exception will be thrown', 'correct' => false],
                    ['text' => 'an empty string', 'correct' => false],
                ],
            ],
            // PHP HTTP_USER_AGENT
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Which of the following statements allows to retrieve the value of the <code>User-Agent</code> HTTP request header field?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'HTTP headers are available in $_SERVER with HTTP_ prefix and underscores.',
                'resourceUrl' => 'http://php.net/manual/en/reserved.variables.server.php',
                'answers' => [
                    ['text' => '<code>$_SERVER[\'HTTP_USER_AGENT\'];</code>', 'correct' => true],
                    ['text' => '<code>$HTTP_HEADERS_VARS[\'USER_AGENT\'];</code>', 'correct' => false],
                    ['text' => '<code>http_get_request_header(\'User-Agent\');</code>', 'correct' => false],
                    ['text' => 'It is not possible to read an HTTP request header field value with PHP.', 'correct' => false],
                ],
            ],
            // VarDumper HtmlDumper theme
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarDumper'],
                'text' => 'Is the following code valid?<pre><code class="language-php">&lt;?php

use Symfony\\Component\\VarDumper\\Dumper\\HtmlDumper;

$dumper = new HtmlDumper();
$dumper->setTheme(\'light\');</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, HtmlDumper supports setTheme() with "light" or "dark" options.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/components/var_dumper/advanced.html#dumpers',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Console setCode method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'What can be said about <code>$event->getCommand()->setCode(232)</code> in a ConsoleEvents::TERMINATE listener?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'setCode() expects a callable, not an integer. This code will result in an error.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Console/Command/Command.php#L252',
                'answers' => [
                    ['text' => 'The code above will result in an error', 'correct' => true],
                    ['text' => 'The code above will change the command exit status code to 232', 'correct' => false],
                    ['text' => 'This method doesn\'t exist', 'correct' => false],
                ],
            ],
            // PHP sessions without cookies
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Can PHP sessions work without cookies?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, PHP can pass the session ID via URL query parameters instead of cookies.',
                'resourceUrl' => 'https://www.php.net/manual/en/session.idpassing.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Service alias
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'With the following service definition, how is it possible to access the mailer service?<pre><code class="language-yaml">services:
    app.mailer.one:
        class: App\\OneMailer
        arguments: [sendmail]
        public: false

    app.mailer:
        alias: app.mailer.one
        public: true</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The private service can only be accessed through its public alias.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/dependency_injection/advanced.html#aliasing',
                'answers' => [
                    ['text' => '<code>$container->get(\'app.mailer\');</code>', 'correct' => true],
                    ['text' => '<code>$container->get(\'app.mailer.one\');</code>', 'correct' => false],
                    ['text' => 'It is not possible.', 'correct' => false],
                ],
            ],
            // Response setCache options
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Which options are available in the <code>Response::setCache(array $options)</code> method?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'setCache accepts: etag, last_modified, max_age, s_maxage, private, public, immutable.',
                'resourceUrl' => 'http://symfony.com/doc/current/http_cache.html#more-response-methods',
                'answers' => [
                    ['text' => 'etag', 'correct' => true],
                    ['text' => 'max_age', 'correct' => true],
                    ['text' => 'private', 'correct' => true],
                    ['text' => 'public', 'correct' => true],
                    ['text' => 'last_modified', 'correct' => true],
                    ['text' => 's_maxage', 'correct' => true],
                    ['text' => 'not_modified', 'correct' => false],
                ],
            ],
            // EventDispatcher dispatch return
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'What is returned by <code>$dispatcher->dispatch($event, OrderPlacedEvent::NAME)</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The dispatch method returns the event object.',
                'resourceUrl' => 'https://github.com/symfony/event-dispatcher/blob/5.0/EventDispatcher.php',
                'answers' => [
                    ['text' => '<code>$event</code>', 'correct' => true],
                    ['text' => 'Nothing', 'correct' => false],
                    ['text' => '<code>true</code>', 'correct' => false],
                    ['text' => '<code>$dispatcher</code>', 'correct' => false],
                ],
            ],
            // Runtime void application
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'],
                'text' => 'Could an application be executed without returning something (aka <code>void</code>)?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, the Runtime component supports void as a resolvable application type.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/runtime.html#resolvable-applications',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Twig template inheritance error
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following templates will throw an error <code>A template that extends another one cannot have a body</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Templates extending another cannot have content outside blocks. {{ \'f\' ~ \'oo\' }} outputs content directly.',
                'resourceUrl' => 'http://twig.symfony.com/doc/tags/extends.html',
                'answers' => [
                    ['text' => '<pre><code class="language-twig">{% extends \'base.html.twig\' %}\n\n{{ \'f\' ~ \'oo\' }}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-twig">{% extends \'base.html.twig\' %}\n\n{% block body \'foo\' %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% extends \'base.html.twig\' %}\n\n{% block body %}\n    foo\n{% endblock %}</code></pre>', 'correct' => false],
                ],
            ],
            // Filesystem mkdir return
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'What is returned by the <code>Filesystem::mkdir</code> method if the directory has been successfully created?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'mkdir() is a void method; it returns nothing on success and throws an exception on failure.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/components/filesystem.html#mkdir',
                'answers' => [
                    ['text' => 'Nothing', 'correct' => true],
                    ['text' => 'The <code>FileSystem</code> object', 'correct' => false],
                    ['text' => 'A string with the directory path', 'correct' => false],
                    ['text' => '<code>true</code> or <code>false</code>', 'correct' => false],
                ],
            ],
            // Routing request attributes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'How can you retrieve a route default parameter <code>title</code> from the Request object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Route parameters and defaults are stored in request attributes.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/routing.html#extra-parameters',
                'answers' => [
                    ['text' => '<code>$title = $request->attributes->get(\'title\');</code>', 'correct' => true],
                    ['text' => '<code>$title = $request->attributes[\'title\'];</code>', 'correct' => false],
                    ['text' => '<code>$title = $request->getAttributes()[\'title\'];</code>', 'correct' => false],
                    ['text' => '<code>$title = $request->getAttributes()->get(\'title\');</code>', 'correct' => false],
                ],
            ],
            // DI injection types supported
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which injection types are supported by Symfony\'s Dependency Injection Container?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony supports constructor, setter, and property injection. Getter injection is not supported.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/injection_types.html',
                'answers' => [
                    ['text' => 'constructor', 'correct' => true],
                    ['text' => 'setter', 'correct' => true],
                    ['text' => 'property', 'correct' => true],
                    ['text' => 'getter', 'correct' => false],
                ],
            ],
            // CssSelector :where pseudo-class
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:CssSelector'],
                'text' => 'Could the <code>*:where</code> selector be used in CssSelector component?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 7.1 the :where pseudo-class is supported.',
                'resourceUrl' => 'https://symfony.com/doc/7.1/components/css_selector.html#limitations-of-the-cssselector-component',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Config array prototype
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Config'],
                'text' => 'Is the following YAML configuration valid with this array prototype definition?<pre><code class="language-php">$rootNode
    ->children()
        ->arrayNode(\'connections\')
            ->prototype(\'array\')
                ->children()
                    ->scalarNode(\'driver\')->end()
                    ->scalarNode(\'host\')->end()
                -&gt;end()
            ->end()
        ->end()
    ->end()
;</code></pre><pre><code class="language-yml">connections:
    driver: pdo_mysql
    host: mysql</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'No, with prototype(\'array\') the connections should be an array of arrays, not direct key-value pairs.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/config/definition.html#array-nodes',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $q) {
            $question = new Question();
            $question->setText($q['text']);
            $question->setTypeEnum($q['type']);
            $question->setDifficulty($q['difficulty']);
            $question->setExplanation($q['explanation']);
            $question->setResourceUrl($q['resourceUrl']);
            $question->setCategory($q['category']);
            $question->setSubcategory($q['subcategory']);

            foreach ($q['answers'] as $a) {
                $answer = new Answer();
                $answer->setText($a['text']);
                $answer->setIsCorrect($a['correct']);
                $question->addAnswer($answer);
            }

            $manager->persist($question);
        }

        $manager->flush();
    }
}
