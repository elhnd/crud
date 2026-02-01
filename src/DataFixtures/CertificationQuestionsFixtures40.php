<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 40
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures40 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures39::class];
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
            // Q1 - Console - Verbosity
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'What are the console verbosity levels?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The valid verbosity levels are: VERBOSITY_QUIET, VERBOSITY_NORMAL, VERBOSITY_VERBOSE, VERBOSITY_VERY_VERBOSE, and VERBOSITY_DEBUG.',
                'resourceUrl' => 'http://symfony.com/doc/current/console/verbosity.html',
                'answers' => [
                    ['text' => 'OutputInterface::VERBOSITY_DEBUG', 'correct' => true],
                    ['text' => 'OutputInterface::VERBOSITY_VERY_VERBOSE', 'correct' => true],
                    ['text' => 'OutputInterface::VERBOSITY_NO_DEBUG', 'correct' => false],
                    ['text' => 'OutputInterface::VERBOSITY_QUIET', 'correct' => true],
                    ['text' => 'OutputInterface::VERBOSITY_NORMAL', 'correct' => true],
                    ['text' => 'OutputInterface::VERBOSITY_NONE', 'correct' => false],
                    ['text' => 'OutputInterface::VERBOSITY_VERY_VERY_VERBOSE', 'correct' => false],
                    ['text' => 'OutputInterface::VERBOSITY_VERBOSE', 'correct' => true],
                ],
            ],

            // Q2 - Twig - Loaders definition
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What are Twig loaders responsible for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Loaders are responsible for loading templates from a resource name.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/api.html#loaders',
                'answers' => [
                    ['text' => 'Loaders are responsible for loading token parsers.', 'correct' => false],
                    ['text' => 'Loaders are responsible for loading templates from a resource name.', 'correct' => true],
                    ['text' => 'Loaders are responsible for loading environments such as Twig_Evironment.', 'correct' => false],
                    ['text' => 'Loaders are responsible for loading extensions.', 'correct' => false],
                ],
            ],

            // Q3 - HttpFoundation - Response::create()
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Since <code>6.0</code>, could a response be created via <code>Response::create()</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Response::create() method was deprecated in Symfony 5.1 and removed in Symfony 6.0. Use the constructor instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/HttpFoundation/Response.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q4 - PHP Basics - Throwable interface
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Is the following exception class valid?
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
    $this-&gt;message = $message;
    $this-&gt;code = $code;
    $this-&gt;file = $file;
    $this-&gt;line = $line;
    $this-&gt;trace = $trace;
    $this-&gt;throwable = $throwable;
  }

  public function getMessage()
  {
    return $this-&gt;message;
  }    

  public function getCode()
  {
    return $this-&gt;code;
  }

  public function getFile()
  {
    return $this-&gt;file;
  }

  public function getLine()
  {
    return $this-&gt;line;
  }

  public function getTrace()
  {
    return $this-&gt;trace;
  }

  public function getTraceAsString()
  {
    return serialize($this-&gt;trace);
  }

  public function getPrevious()
  {
    return $this-&gt;previous;
  }

  public function __toString()
  {
      return sprintf(\'%d: %s\', $this-&gt;code, $this-&gt;message);
  }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Throwable is an internal interface that cannot be implemented directly by userland classes. You must extend Exception or Error instead.',
                'resourceUrl' => 'https://www.php.net/manual/en/class.throwable.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q5 - Twig - Custom escaper
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Can we create a custom escaper for Twig?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, you can define custom escapers by calling the setEscaper() method on the core extension instance.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/escape.html#custom-escapers',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - Routing - XML route matching
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
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
                'explanation' => 'The regex requires year starting with 20, month 01-12 (with optional leading zero), and day 01-31.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/components/routing.html',
                'answers' => [
                    ['text' => 'http://localhost/agenda/2018-14-30', 'correct' => false],
                    ['text' => 'http://localhost/agenda/2150-12-31', 'correct' => false],
                    ['text' => 'http://localhost/agenda/2011-1-01', 'correct' => true],
                    ['text' => 'http://localhost/agenda/2008-04-06', 'correct' => true],
                    ['text' => 'http://localhost/agenda/2018-12-12', 'correct' => true],
                    ['text' => 'http://localhost/agenda/2020-2-30', 'correct' => true],
                ],
            ],

            // Q7 - Validator - Constraints on properties
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'A constraint can be applied on',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Constraints can be applied on public, protected, and private properties.',
                'resourceUrl' => 'https://symfony.com/doc/current/book/validation.html#properties',
                'answers' => [
                    ['text' => 'protected property', 'correct' => true],
                    ['text' => 'public property', 'correct' => true],
                    ['text' => 'private property', 'correct' => true],
                ],
            ],

            // Q8 - PHP Arrays - array_diff_assoc
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'Which of the following functions compares array1 against array2 and returns the difference by checking array keys in addition?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_diff_assoc() computes the difference of arrays with additional index check.',
                'resourceUrl' => 'https://php.net/manual/en/function.array-diff-assoc.php',
                'answers' => [
                    ['text' => 'array_diff_ukey', 'correct' => false],
                    ['text' => 'array_diff_uassoc', 'correct' => false],
                    ['text' => 'array_diff_assoc', 'correct' => true],
                    ['text' => 'array_diff_key', 'correct' => false],
                ],
            ],

            // Q9 - Messenger - Sender responsibility
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'What is the responsibility of a Sender?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'A Sender is responsible for serializing and sending messages to message brokers or third-party services.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/messenger.html#concepts',
                'answers' => [
                    ['text' => 'It wrap the message in order to define metadata', 'correct' => false],
                    ['text' => 'It serialize and send messages to message brokers/third party services', 'correct' => true],
                    ['text' => 'It retrieve and deserialize messages', 'correct' => false],
                ],
            ],

            // Q10 - HttpKernel - Controller with Closure
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Could a controller be defined using <code>\Closure</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, a controller can be a Closure. The routing component supports closures as controllers.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/controller.html#a-simple-controller',
                'answers' => [
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes', 'correct' => true],
                ],
            ],

            // Q11 - DI - ReverseContainer
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could a service identifier be returned from a <code>ReverseContainer</code> if the service is not tagged as <code>container.reversible</code> but defined as <code>public</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, ReverseContainer can return service identifiers for public services even without the container.reversible tag.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/DependencyInjection/ReverseContainer.php',
                'answers' => [
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes', 'correct' => true],
                ],
            ],

            // Q12 - Filesystem - Path class
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
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

            // Q13 - DI - ParameterBag frozen
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could a <code>ParameterBag</code> be frozen?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, FrozenParameterBag exists to provide an immutable ParameterBag.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q14 - HttpFoundation - $_COOKIE access
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'How to access <code>$_COOKIE</code> data when using a <code>Symfony\Component\HttpFoundation\Request</code> <code>$request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The $request->cookies property (a ParameterBag) provides access to $_COOKIE values.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#accessing-request-data',
                'answers' => [
                    ['text' => '<pre><code>$request-&gt;getCookieData()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request-&gt;getCookies()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request-&gt;cookies</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>$request-&gt;cookie</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request-&gt;getCookie()</code></pre>', 'correct' => false],
                ],
            ],

            // Q15 - Asset - VersionStrategyInterface
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Assets'],
                'text' => 'Could the version of a asset be accessed from within a class implementing the <code>VersionStrategyInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, the VersionStrategyInterface defines a getVersion() method that returns the asset version.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.7/src/Symfony/Component/Asset/VersionStrategy/VersionStrategyInterface.php',
                'answers' => [
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes', 'correct' => true],
                ],
            ],

            // Q16 - Validator - Validation constraints elements
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Which of the following elements can contain validation constraints?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Validation constraints can be applied to classes, properties (public/private/protected), and getter methods.',
                'resourceUrl' => 'https://symfony.com/doc/current/validation.html#index-6',
                'answers' => [
                    ['text' => 'Classes', 'correct' => true],
                    ['text' => 'Private and protected getters/issers', 'correct' => true],
                    ['text' => 'Public getters/issers', 'correct' => true],
                    ['text' => 'Public properties', 'correct' => true],
                    ['text' => 'Private and protected properties', 'correct' => true],
                ],
            ],

            // Q17 - Validator - Constraint class purpose
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'In validation, what is the purpose of the Constraint classes?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Constraint classes define the rules to validate. The ConstraintValidator classes contain the actual validation logic.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/validation/custom_constraint.html',
                'answers' => [
                    ['text' => 'To define the rules to validate.', 'correct' => true],
                    ['text' => 'To define the validation groups.', 'correct' => false],
                    ['text' => 'To define the validation logic.', 'correct' => false],
                ],
            ],

            // Q18 - Form - TelType
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which of the following sentences are true?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'TelType renders an HTML5 tel input field. It does not perform any validation - that is left to the browser or constraints.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/reference/forms/types/tel.html',
                'answers' => [
                    ['text' => 'PhoneType form field allows to use HTML5 input type <code>tel</code> and trigger some basic validation on the entered phone number', 'correct' => false],
                    ['text' => 'TelType form field only allows to use HTML5 input type <code>tel</code>', 'correct' => true],
                    ['text' => 'PhoneType form field only allows to use HTML5 input type <code>tel</code>', 'correct' => false],
                    ['text' => 'TelType form field allows to use HTML5 input type <code>tel</code> and trigger some basic validation on the entered phone number', 'correct' => false],
                ],
            ],

            // Q19 - Form - ChoiceType choice_attr
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which types are allowed for the <code>choice_attr</code> option of the <code>Symfony\Component\Form\Extension\Core\Type\ChoiceType</code> form type?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The choice_attr option accepts an array, a callable, or a string (property path).',
                'resourceUrl' => 'https://symfony.com/doc/2.7/reference/forms/types/choice.html#choice-attr',
                'answers' => [
                    ['text' => '<code>string</code>', 'correct' => true],
                    ['text' => '<code>array</code>', 'correct' => true],
                    ['text' => '<code>integer</code>', 'correct' => false],
                    ['text' => '<code>boolean</code>', 'correct' => false],
                    ['text' => '<code>callable</code>', 'correct' => true],
                ],
            ],

            // Q20 - Security - VoterInterface::vote() signature
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the signature of the <code>vote()</code> method from <code>VoterInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The VoterInterface::vote() method signature is: vote(TokenInterface $token, $subject, array $attributes)',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/Security/Core/Authorization/Voter/VoterInterface.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, array $attributes, $subject)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, array $attributes, $object)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, $subject, array $attributes)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, $object, array $attributes)</code></pre>', 'correct' => false],
                ],
            ],

            // Q21 - DI - ContainerConfigurator imports
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could imports be configured using <code>ContainerConfigurator</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, ContainerConfigurator has an import() method to import other configuration files.',
                'resourceUrl' => 'https://github.com/symfony/dependency-injection/blob/3.4/Loader/Configurator/ContainerConfigurator.php#L54',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q22 - Routing - Multiple routes same path different methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'What happens if multiple routes have the same path but different HTTP methods?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony will match the route based on the HTTP method of the request.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/routing.html#matching-http-methods',
                'answers' => [
                    ['text' => 'Symfony merges all routes', 'correct' => false],
                    ['text' => 'Symfony will throw an exception', 'correct' => false],
                    ['text' => 'Symfony will match based on the HTTP method', 'correct' => true],
                    ['text' => 'Symfony ignores all but the first route', 'correct' => false],
                ],
            ],

            // Q23 - Routing - Route matching with requirements
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'According to the following definition of route, which ones are matching?
<pre><code class="language-yaml">blog_page:
    path: /blog/{page}
    requirements:
        page: \d*
    defaults:
        page: 1</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The requirement \d* matches zero or more digits. /blog, /blog/, and /blog/1 all match.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/routing.html#adding-requirements',
                'answers' => [
                    ['text' => '/blog', 'correct' => true],
                    ['text' => '/blog/page', 'correct' => false],
                    ['text' => '/blog/page1', 'correct' => false],
                    ['text' => '/blog/1', 'correct' => true],
                    ['text' => '/blog/', 'correct' => true],
                    ['text' => '/blog/page-1', 'correct' => false],
                ],
            ],

            // Q24 - BrowserKit - Cookie assertions
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:BrowserKit'],
                'text' => 'Could assertions be performed on the fact that the current browser has a certain cookie?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, BrowserHasCookie constraint can be used to assert that the browser has a certain cookie.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/BrowserKit/Test/Constraint/BrowserHasCookie.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q25 - DI - ContainerBuilder --no-dev
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could the fact that a class is available and will remain available in the <code>--no-dev</code> mode of Composer be obtained when using <code>ContainerBuilder</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, ContainerBuilder has a method to check if a class is available for the runtime (willBeAvailable).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/DependencyInjection/ContainerBuilder.php#L1454',
                'answers' => [
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes', 'correct' => true],
                ],
            ],

            // Q26 - PSR - PSR-6 Cache
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PSR'],
                'text' => 'Which Symfony component has been created to provide a PSR-6 implementation?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Symfony Cache component provides a PSR-6 compliant caching implementation.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/cache.html',
                'answers' => [
                    ['text' => 'Filesystem', 'correct' => false],
                    ['text' => 'Cache', 'correct' => true],
                    ['text' => 'Inflector', 'correct' => false],
                    ['text' => 'PropertyAccess', 'correct' => false],
                ],
            ],

            // Q27 - HTTP - Vary header
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the followings are valid usage of the <code>Vary</code> header?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'All of these are valid Vary header values. Vary indicates which request headers affect the response.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Vary',
                'answers' => [
                    ['text' => '<code>Vary: Cookie</code>', 'correct' => true],
                    ['text' => '<code>Vary: *</code>', 'correct' => true],
                    ['text' => '<code>Vary: Referer</code>', 'correct' => true],
                    ['text' => '<code>Vary: Accept-Encoding</code>', 'correct' => true],
                    ['text' => '<code>Vary: User-Agent</code>', 'correct' => true],
                ],
            ],

            // Q28 - Security - AccessDecisionManager strategy
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which of the following is the <code>AccessDecisionManager</code> default strategy?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The default strategy for AccessDecisionManager is "affirmative" - access is granted if any voter grants access.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Security/Core/Authorization/AccessDecisionManager.php#L30',
                'answers' => [
                    ['text' => '<code>affirmative</code>', 'correct' => true],
                    ['text' => '<code>unanimous</code>', 'correct' => false],
                    ['text' => '<code>consensus</code>', 'correct' => false],
                ],
            ],

            // Q29 - HTTP - 502 status code
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'What does the 502 HTTP status code stand for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'HTTP 502 is "Bad Gateway" - the server received an invalid response from an upstream server.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/502',
                'answers' => [
                    ['text' => 'Gateway timeout', 'correct' => false],
                    ['text' => 'Not implemented', 'correct' => false],
                    ['text' => 'Service Unavailable', 'correct' => false],
                    ['text' => 'Bad Gateway', 'correct' => true],
                ],
            ],

            // Q30 - DI - Parameters imports
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is this code valid?
<pre><code class="language-yaml"># app/config/config.yml
imports:
    - { resource: "%kernel.root_dir%/parameters.yml" }</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Parameters cannot be used in the imports section because they are not yet resolved at that point.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/configuration/configuration_organization.html#different-directories-per-environment',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q31 - Event Dispatcher - __invoke() listener
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Could an event listener be registered while using the <code>__invoke()</code> method to listen to an event?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, event listeners can use the __invoke() method. This is the default method called if no method is specified.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/event_dispatcher.html#creating-an-event-listener',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q32 - FrameworkBundle - Redirection query parameters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'Given the context where the user is redirected to another page, could the original query parameters be maintained?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, the redirectToRoute method accepts a keepQueryParams option to maintain query parameters.',
                'resourceUrl' => 'https://symfony.com/doc/5.1/controller.html#redirecting',
                'answers' => [
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes', 'correct' => true],
                ],
            ],

            // Q33 - PHP Arrays - array_filter
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
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
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '5', 'correct' => false],
                    ['text' => '4', 'correct' => false],
                    ['text' => '1', 'correct' => true],
                    ['text' => '3', 'correct' => false],
                ],
            ],

            // Q34 - PHP OOP - Magic methods
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Which of the following are a magic method?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'All of these are PHP magic methods: __serialize(), __set_state(), __get(), __invoke(), and __wakeup().',
                'resourceUrl' => 'http://php.net/manual/en/language.oop5.magic.php',
                'answers' => [
                    ['text' => '<code>__serialize()</code>', 'correct' => true],
                    ['text' => '<code>__set_state()</code>', 'correct' => true],
                    ['text' => '<code>__get()</code>', 'correct' => true],
                    ['text' => '<code>__invoke()</code>', 'correct' => true],
                    ['text' => '<code>__wakeup()</code>', 'correct' => true],
                ],
            ],

            // Q35 - DomCrawler - attr() method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:DomCrawler'],
                'text' => 'Given the following HTML code:
<pre><code class="language-html">&lt;!-- ... --&gt;

&lt;a class=\'home-link\' href=\'/home\'&gt;Go to home&lt;/a&gt;
</code></pre>
<p>And the following code using the <code>DomCrawler</code>:</p>
<pre><code class="language-php">$value = $crawler-&gt;filter(\'a.home-link\')-&gt;attr(\'data\', \'href\');</code></pre>
<p>What will be the content of <code>$value</code>?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Since Symfony 6.4, attr() accepts a second parameter as default value. Since "data" attribute doesn\'t exist, it returns "href".',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-4-dx-improvements-part-2#default-crawler-attributes',
                'answers' => [
                    ['text' => 'An empty string', 'correct' => false],
                    ['text' => 'Nothing, an exception is thrown', 'correct' => false],
                    ['text' => '<code>href</code>', 'correct' => true],
                    ['text' => '<code>/home</code>', 'correct' => false],
                ],
            ],

            // Q36 - FrameworkBundle - fragment_renderer tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the tag to add a new HTTP content rendering strategy?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The kernel.fragment_renderer tag is used to add a new HTTP content rendering strategy.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/dic_tags.html#kernel-fragment-renderer',
                'answers' => [
                    ['text' => '<code>renderer</code>', 'correct' => false],
                    ['text' => '<code>fragment_renderer</code>', 'correct' => false],
                    ['text' => '<code>content_renderer</code>', 'correct' => false],
                    ['text' => '<code>kernel.renderer</code>', 'correct' => false],
                    ['text' => '<code>kernel.content_renderer</code>', 'correct' => false],
                    ['text' => '<code>kernel.fragment_renderer</code>', 'correct' => true],
                ],
            ],

            // Q37 - Twig - Functions/filters at runtime
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Could functions and filters be defined at runtime without any overhead?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, Twig allows defining undefined functions and filters on the fly using callbacks.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/recipes.html#defining-undefined-functions-and-filters-on-the-fly',
                'answers' => [
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes', 'correct' => true],
                ],
            ],

            // Q38 - Twig - with tag scope
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
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
                'difficulty' => 2,
                'explanation' => 'Variables defined inside a with tag are not available outside of it. With strict_variables, this throws an error.',
                'resourceUrl' => 'http://twig.symfony.com/doc/tags/with.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No. The template will display an error because the <code>maxItems</code> variable is not defined outside the <code>with</code> tag.', 'correct' => true],
                    ['text' => 'No. The template won\'t iterate from <code>1</code> to <code>7</code>. It will execute the <code>for</code> loop just one time (where <code>i</code> is <code>1</code>).', 'correct' => false],
                    ['text' => 'No. The template will display an error because the <code>with</code> tag is not defined.', 'correct' => false],
                ],
            ],

            // Q39 - Doctrine DBAL - URL
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'Which of the following are valid database URLs that can be used in the <code>dbal.url</code> option in Symfony applications?
<pre><code class="language-yaml"># app/config/config.yml
doctrine:
    dbal:
        url: ...</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Valid SQLite URLs include sqlite:///data.db and sqlite:///:memory:. MySQL URLs need proper format with user:pass@host.',
                'resourceUrl' => 'https://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html',
                'answers' => [
                    ['text' => '<code>pgsql://localhost:5432</code>', 'correct' => false],
                    ['text' => '<code>sqlite:///data.db</code>', 'correct' => true],
                    ['text' => '<code>mysql://localhost/mydb@user:secret</code>', 'correct' => false],
                    ['text' => '<code>sqlite:///:memory:</code>', 'correct' => true],
                    ['text' => '<code>mysql://localhost:4486/foo?charset=UTF-8</code>', 'correct' => false],
                ],
            ],

            // Q40 - Routing - Duplicate parameter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Is the following definition of route correct?
<pre><code class="language-php">use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection-&gt;add(\'blog_show\', new Route(\'/blog/{page}/category/{slug}/{page}\', array(
  \'_controller\' =&gt; \'AppBundle:Blog:show\',
)));

return $collection;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Route parameters must be unique. The {page} parameter appears twice, which is invalid.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#routing-examples',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],
        ];
    }
}
