<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 39
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures39 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures38::class];
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
            // Q1 - PHP I/O - File handling rewind
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:I/O'],
                'text' => 'Consider the following code:
<pre><code class="language-php">&lt;?php

$fp = fopen(\'file.txt\', \'r\');

$string1 = fgets($fp, 512);

fseek($fp, 0);</code></pre>
<p>Which of the following functions will give the same output as that given by the <code>fseek()</code> function in the above script?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La fonction rewind() est équivalente à fseek($fp, 0). Elle repositionne le pointeur de fichier au début.',
                'resourceUrl' => 'https://www.php.net/filesystem',
                'answers' => [
                    ['text' => 'rewind()', 'correct' => true],
                    ['text' => 'fgetss()', 'correct' => false],
                    ['text' => 'file()', 'correct' => false],
                    ['text' => 'fgets()', 'correct' => false],
                ],
            ],

            // Q2 - PHP Basics - Basic operations
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
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
                    ['text' => '117', 'correct' => false],
                    ['text' => '14', 'correct' => false],
                    ['text' => '18', 'correct' => false],
                ],
            ],

            // Q3 - Security - Custom request matcher
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Is the following code valid?
<pre><code class="language-yaml"># config/packages/security.yaml

security:

# ...

    firewalls:
        secured_area:
            request_matcher: app.firewall.secured_area.request_matcher

            # ...</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, on peut utiliser un service personnalisé comme request_matcher dans la configuration du firewall.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/security/firewall_restriction.html#restricting-by-service',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q4 - Routing - Parameters in route
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Given the following definition of the <code>book_list</code> route, what will be the value of the variable <code>$url</code>?
<pre><code class="language-yaml"># config/routes.yaml
book_list:
    path:     /books
    controller: \'App\Controller\DefaultController::list\'
    methods: [POST]</code></pre>
<pre><code class="language-php">&lt;?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController 
{
    public function index() 
    {
        $url = $this-&gt;generateUrl(\'book_list\', [\'page\' =&gt; 1]);
        // ...
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les paramètres non définis dans la route sont ajoutés comme query string. Donc /books?page=1.',
                'resourceUrl' => 'https://symfony.com/doc/4.0/routing.html#generating-urls-with-query-strings',
                'answers' => [
                    ['text' => '/books?page=1', 'correct' => true],
                    ['text' => 'https://example.com/books?_page=1', 'correct' => false],
                    ['text' => 'Error: Parameter "page" is not defined.', 'correct' => false],
                    ['text' => 'https://example.com/books?page=1', 'correct' => false],
                    ['text' => '/books?_page=1', 'correct' => false],
                ],
            ],

            // Q5 - HttpFoundation - Accessing Session
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What is the way to access the session from the <code>$request</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La méthode getSession() de l\'objet Request permet d\'accéder à la session.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/components/http_foundation.html#accessing-the-session',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$request-&gt;getSession()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$request-&gt;getPhpSession()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$request-&gt;session</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$request-&gt;fetchSession()</code></pre>', 'correct' => false],
                ],
            ],

            // Q6 - Console - setCode error
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Given the following console event subscriber:
<pre><code class="language-php">&lt;?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\ConsoleEvents;

class ConsoleListener implements EventSubscriberInterface
{
  private AppChecker $appChecker;

  public function __construct(AppChecker $appChecker)
  {
    $this-&gt;appChecker = $appChecker;
  }

  public static function getSubscribedEvents()
  {
    return [
        ConsoleEvents::TERMINATE =&gt; \'setCode\'
    ];
  }

  public function setCode(ConsoleEvent $event): void
  {
    if (!$this-&gt;appChecker-&gt;isOk()) {
      $event-&gt;getCommand()-&gt;setCode(232);
    }
  }
}</code></pre>
<p>What will be the result of <code>setCode</code> method call?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'setCode() attend un callable, pas un entier. Passer un entier déclenchera une erreur.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.2/src/Symfony/Component/Console/Command/Command.php#L248',
                'answers' => [
                    ['text' => 'It will trigger an error', 'correct' => true],
                    ['text' => 'It will change the command exit status code to 232', 'correct' => false],
                ],
            ],

            // Q7 - Event Dispatcher - Security events debug
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Could the listeners be debugged per firewall?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, on peut déboguer les listeners par firewall avec debug:event-dispatcher.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/event_dispatcher.html#debugging-event-listeners',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q8 - Security - User roles
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'I am correctly logged in, but I am not fully authenticated, what is the main cause?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un utilisateur sans rôle n\'est pas considéré comme pleinement authentifié.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/security.html#roles',
                'answers' => [
                    ['text' => 'My User have no roles', 'correct' => true],
                    ['text' => 'I\'m connected with an API Token', 'correct' => false],
                    ['text' => 'I didn\'t tick the "Remember me" checkbox', 'correct' => false],
                    ['text' => 'We\'re always fully autenticated', 'correct' => false],
                ],
            ],

            // Q9 - Security - Security events
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which sentences are true about security events?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'security.authentication.success est dispatché à chaque page pour un utilisateur authentifié par session. security.authentication.failure est lancé lors d\'un échec d\'authentification.',
                'resourceUrl' => 'https://symfony.com/doc/4.x/components/security/authentication.html#authentication-events',
                'answers' => [
                    ['text' => 'On a session-based authentication, the <code>security.authentication.success</code> is dispatched on each page when the user is authenticated', 'correct' => true],
                    ['text' => '<code>security.authentication.failure</code> is launched when an authentication attempt fails', 'correct' => true],
                    ['text' => 'When you log in via an http basic header, a <code>security.interactive_login</code> event is triggered', 'correct' => false],
                    ['text' => '<code>security.logout_on_change</code> is triggered when the user use the logout feature of the firewall', 'correct' => false],
                ],
            ],

            // Q10 - Validator - Validation groups count
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Given the following class and constraints:
<pre><code class="language-php">&lt;?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class User implements UserInterface
{
    /**
     * @Assert\Email(groups={"registration"})
     */
    private $email;

    /**
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(min=7, groups={"registration"})
     */
    private $password;

    /**
     * @Assert\Length(min=2)
     */
    private $city;
}</code></pre>
<p>How many validation groups does this class contain?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '3 groupes: "registration", "Default" (implicite pour city), et "User" (nom de la classe).',
                'resourceUrl' => 'https://symfony.com/doc/current/validation/groups.html',
                'answers' => [
                    ['text' => '3', 'correct' => true],
                    ['text' => '0', 'correct' => false],
                    ['text' => '2', 'correct' => false],
                    ['text' => '5', 'correct' => false],
                    ['text' => '4', 'correct' => false],
                    ['text' => '1', 'correct' => false],
                ],
            ],

            // Q11 - HttpFoundation - Vary header
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Which are the valid ways of caching a <code>Response</code> based not only on the URI but also the value of the <code>Accept-Encoding</code> and <code>User-Agent</code> request headers?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'setVary() avec un tableau, ou plusieurs appels avec replace=false, ou via headers->set() avec tableau ou string séparé par virgules.',
                'resourceUrl' => 'http://symfony.com/doc/current/http_cache/cache_vary.html',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$response-&gt;setVary([\'Accept-Encoding\', \'User-Agent\']);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response-&gt;setVary(\'Accept-Encoding, User-Agent\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response-&gt;setVary(\'Accept-Encoding\');
$response-&gt;setVary(\'User-Agent\', false);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response-&gt;headers-&gt;set(\'Vary\', [\'Accept-Encoding\', \'User-Agent\']);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response-&gt;headers-&gt;set(\'Vary\', \'Accept-Encoding, User-Agent\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response-&gt;headers-&gt;set(\'Vary\', \'Accept-Encoding\');
$response-&gt;headers-&gt;set(\'Vary\', \'User-Agent\', false);</code></pre>', 'correct' => true],
                    ['text' => 'This is the default behavior', 'correct' => false],
                    ['text' => 'This is not possible without calling a reverse proxy', 'correct' => false],
                ],
            ],

            // Q12 - Finder - VCS Files
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Finder'],
                'text' => 'By default, the Finder ignores popular VCS files, what is the method to use them ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode ignoreVCS(false) permet d\'inclure les fichiers VCS.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/components/finder.html#files-or-directories',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$finder-&gt;ignoreVCS(false);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$finder-&gt;enableVCSFiles();</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$finder-&gt;useTypes(array(\'vcs\' =&gt; true);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$finder-&gt;ignoreFiles(array(\'vcs\' =&gt; false));</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$finder-&gt;enableVCS();</code></pre>', 'correct' => false],
                ],
            ],

            // Q13 - Filesystem - Lazy implementation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Is the Filesystem component based on a lazy or eager implementation?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le composant Filesystem utilise une implémentation lazy (les opérations sont exécutées immédiatement, pas en différé).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/Filesystem/Filesystem.php',
                'answers' => [
                    ['text' => 'Lazy', 'correct' => true],
                    ['text' => 'Eager', 'correct' => false],
                ],
            ],

            // Q14 - Serializer - NameConverterInterface methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'What are the methods of <code>Symfony\Component\Serializer\NameConverter\NameConverterInterface</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'NameConverterInterface définit deux méthodes: normalize() et denormalize().',
                'resourceUrl' => 'https://symfony.com/doc/2.7/components/serializer.html#converting-property-names-when-serializing-and-deserializing',
                'answers' => [
                    ['text' => '<pre><code class="language-php">public function normalize($propertyName);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">public function denormalize($propertyName);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">public function reverse($propertyName);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function getName();</code></pre>', 'correct' => false],
                ],
            ],

            // Q15 - Twig - Cache tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Is the following code valid?
<pre><code class="language-twig">{% cache \'_foo_template\' ttl(45) %}
  Cached
{% endcache %}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la syntaxe du tag cache avec ttl() est valide dans Twig 3.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/tags/cache.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q16 - Twig - PHP objects in templates
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Is it possible to pass PHP objects to a Twig template?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, on peut passer des objets PHP aux templates Twig.',
                'resourceUrl' => 'http://twig.symfony.com/doc/templates.html#variables',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q17 - Form - TelType
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which of the following sentences are true about TelType form field?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'TelType utilise uniquement le type HTML5 tel, sans validation côté serveur.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/reference/forms/types/tel.html',
                'answers' => [
                    ['text' => 'TelType form field only allows to use HTML5 input type <code>tel</code>', 'correct' => true],
                    ['text' => 'PhoneType form field allows to use HTML5 input type <code>tel</code> and trigger some basic validation on the entered phone number', 'correct' => false],
                    ['text' => 'TelType form field allows to use HTML5 input type <code>tel</code> and trigger some basic validation on the entered phone number', 'correct' => false],
                    ['text' => 'PhoneType form field only allows to use HTML5 input type <code>tel</code>', 'correct' => false],
                ],
            ],

            // Q18 - Form - ChoiceType choice_attr
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which types are allowed for the <code>choice_attr</code> option of the <code>Symfony\Component\Form\Extension\Core\Type\ChoiceType</code> form type ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'choice_attr accepte array, string ou callable.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/reference/forms/types/choice.html#choice-attr',
                'answers' => [
                    ['text' => '<code>array</code>', 'correct' => true],
                    ['text' => '<code>string</code>', 'correct' => true],
                    ['text' => '<code>callable</code>', 'correct' => true],
                    ['text' => '<code>integer</code>', 'correct' => false],
                    ['text' => '<code>boolean</code>', 'correct' => false],
                ],
            ],

            // Q19 - Security - VoterInterface::vote signature
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the signature of the <code>vote()</code> method from <code>VoterInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La signature est vote(TokenInterface $token, $subject, array $attributes).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/Security/Core/Authorization/Voter/VoterInterface.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, $subject, array $attributes)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, array $attributes, $subject)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, $object, array $attributes)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, array $attributes, $object)</code></pre>', 'correct' => false],
                ],
            ],

            // Q20 - DI - Parameters imports
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is this code valid ?
<pre><code class="language-yaml"># app/config/config.yml
imports:
    - { resource: "%kernel.root_dir%/parameters.yml" }</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, on ne peut pas utiliser de paramètres dans les imports. Depuis Symfony 3.4, il faut utiliser %kernel.project_dir%.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/configuration/configuration_organization.html#different-directories-per-environment',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q21 - HttpClient - RetryableHttpClient base_uri type
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'What is the expected type of the <code>base_uri</code> option in <code>RetryableHttpClient</code> ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Depuis Symfony 6.3, base_uri peut être un tableau pour le retry sur plusieurs URIs.',
                'resourceUrl' => 'https://symfony.com/doc/6.3/http_client.html#retry-over-several-base-uris',
                'answers' => [
                    ['text' => '<code>array</code>', 'correct' => true],
                    ['text' => '<code>string</code>', 'correct' => false],
                    ['text' => '<code>Closure</code>', 'correct' => false],
                ],
            ],

            // Q22 - Translation - Built-in loaders
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'Which of the followings are part of the built-in message catalogs loaders?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les loaders intégrés sont: PhpFileLoader, YamlFileLoader, XliffFileLoader, PoFileLoader, MoFileLoader, CsvFileLoader, IcuResFileLoader, IcuDatFileLoader, JsonFileLoader.',
                'resourceUrl' => 'https://github.com/symfony/translation/tree/2.3/Loader',
                'answers' => [
                    ['text' => 'PoFileLoader', 'correct' => true],
                    ['text' => 'MoFileLoader', 'correct' => true],
                    ['text' => 'XliffFileLoader', 'correct' => true],
                    ['text' => 'YamlFileLoader', 'correct' => true],
                    ['text' => 'JsonLoader', 'correct' => false],
                    ['text' => 'PhpLoader', 'correct' => false],
                    ['text' => 'IcuFileLoader', 'correct' => false],
                    ['text' => 'CsvLoader', 'correct' => false],
                    ['text' => 'IniFileLoader', 'correct' => false],
                ],
            ],

            // Q23 - Expression Language - evaluate
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'What will be displayed by the following code?
<pre><code class="language-php">use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$language = new ExpressionLanguage();

echo $language-&gt;evaluate(\'1 + 2\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'evaluate() évalue l\'expression et retourne le résultat: 1 + 2 = 3.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/expression_language.html#usage',
                'answers' => [
                    ['text' => '<code>3</code>', 'correct' => true],
                    ['text' => '<code>true</code>', 'correct' => false],
                    ['text' => '<code>integer</code>', 'correct' => false],
                    ['text' => '<code>false</code>', 'correct' => false],
                    ['text' => '<code>1 + 2</code>', 'correct' => false],
                ],
            ],

            // Q24 - PHP - Null coalescing operator
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
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
                'explanation' => '$x n\'existe pas, donc on passe à $y. Premier appel: $y=null, donc $z=\'bar\'. Deuxième appel: $y=\'foo\'. Résultat: barfoo.',
                'resourceUrl' => 'http://php.net/manual/en/language.operators.comparison.php',
                'answers' => [
                    ['text' => 'barfoo', 'correct' => true],
                    ['text' => 'foobar', 'correct' => false],
                    ['text' => 'foo', 'correct' => false],
                    ['text' => 'A <em>Fatal error: syntax error</em> will be thrown.', 'correct' => false],
                    ['text' => 'barbar', 'correct' => false],
                ],
            ],

            // Q25 - Security - Voter methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What methods MUST be implemented in a custom voter extending <code>Voter</code> ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'En étendant Voter, vous devez implémenter supports() et voteOnAttribute(). vote() est déjà implémentée dans la classe abstraite.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/security/voters.html',
                'answers' => [
                    ['text' => '<code>voteOnAttribute()</code>', 'correct' => true],
                    ['text' => '<code>supports()</code>', 'correct' => true],
                    ['text' => '<code>vote()</code>', 'correct' => false],
                    ['text' => '<code>voteOnAccess()</code>', 'correct' => false],
                    ['text' => '<code>supportsSubject()</code>', 'correct' => false],
                    ['text' => '<code>supportsAccess()</code>', 'correct' => false],
                    ['text' => '<code>supportsAttribute()</code>', 'correct' => false],
                ],
            ],

            // Q26 - DI - AsDecorator attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is the following code valid when decorating a service?
<pre><code class="language-php">&lt;?php

namespace App\Mailer;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: Mailer::class)]
class LoggingMailer
{
    // ...
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, l\'attribut #[AsDecorator] permet de décorer un service via PHP 8 attributes.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-1-service-decoration-attributes',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q27 - PHP - Static arrow functions
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'We sometimes come across arrow functions declared as static, like in the code below:
<pre><code class="language-php">class Foo
{
    public function bar(): iterable
    {
        $array = \range(1, 10);

        return array_map(static fn($x) =&gt; $x*$x, $array);
    }
}</code></pre>
<p>Which of the following choices is true</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Avec static, $this n\'est pas lié à la fonction fléchée, ce qui améliore légèrement les performances car le binding n\'a pas besoin d\'être fait.',
                'resourceUrl' => 'https://wiki.php.net/rfc/arrow_functions_v2',
                'answers' => [
                    ['text' => 'By using the <code>static</code> modifier, <code>$this</code> (representing the current instance of <code>Foo</code>) won\'t be bound and "injected" in the arrow function. It will result in a faster execution, as this binding doesn\'t have to be made', 'correct' => true],
                    ['text' => 'Nothing actually changes', 'correct' => false],
                    ['text' => 'It allows the arrow function to be able to access to <code>$this</code>, representing the current instance of <code>Foo</code> calling this method', 'correct' => false],
                ],
            ],

            // Q28 - HttpKernel - KernelEvent methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What are the methods available in <code>Symfony\Component\HttpKernel\Event\KernelEvent</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'KernelEvent a: getKernel(), getRequest(), getRequestType() et isMainRequest().',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/HttpKernel/Event/KernelEvent.php',
                'answers' => [
                    ['text' => '<code>getKernel</code>', 'correct' => true],
                    ['text' => '<code>getRequest</code>', 'correct' => true],
                    ['text' => '<code>getRequestType</code>', 'correct' => true],
                    ['text' => '<code>isMainRequest</code>', 'correct' => true],
                    ['text' => '<code>getException</code>', 'correct' => false],
                    ['text' => '<code>hasRequest</code>', 'correct' => false],
                    ['text' => '<code>hasResponse</code>', 'correct' => false],
                    ['text' => '<code>hasException</code>', 'correct' => false],
                    ['text' => '<code>getResponse</code>', 'correct' => false],
                ],
            ],

            // Q29 - HttpFoundation - isEmpty
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What is returned by the <code>isEmpty</code> method of <code>Symfony\Component\HttpFoundation\Response</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'isEmpty() retourne true si le status code est 204 (No Content) ou 304 (Not Modified).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/0baa58d4e4bb006c4ae68f75833b586bd3cb6e6f/src/Symfony/Component/HttpFoundation/Response.php#L1090',
                'answers' => [
                    ['text' => '<code>true</code> if the response status code are 204 or 304', 'correct' => true],
                    ['text' => '<code>true</code> if there is a server error', 'correct' => false],
                    ['text' => '<code>true</code> if the response has no headers', 'correct' => false],
                    ['text' => '<code>true</code> if the response content is <code>null</code>', 'correct' => false],
                ],
            ],

            // Q30 - Form - DataTransformer invalid_message
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'How to customize the validation error message of the validation error caused by a <code>TransformationFailedException</code> ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'option invalid_message permet de personnaliser le message d\'erreur lors d\'une TransformationFailedException.',
                'resourceUrl' => 'http://symfony.com/doc/current/form/data_transformers.html#creating-the-transformer',
                'answers' => [
                    ['text' => 'By using the <code>invalid_message</code> option', 'correct' => true],
                    ['text' => 'The exception message will be used as the validation error message', 'correct' => false],
                    ['text' => 'It\'s not possible', 'correct' => false],
                ],
            ],

            // Q31 - Mime - Custom type guesser re-registration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mime'],
                'text' => 'Could a custom type guesser be registered again when already registered?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, un guesser peut être enregistré plusieurs fois.',
                'resourceUrl' => 'https://symfony.com/doc/4.x/components/mime.html#adding-a-mime-type-guesser',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q32 - Twig - Valid block names
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following are valid block names ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les noms de blocs valides suivent les règles des identifiants PHP: lettres, chiffres, underscore, ne peut pas commencer par un chiffre.',
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

            // Q33 - Twig - Global variables standalone
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'When twig is used as a standalone library, which global variables are always available in templates?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'En standalone, seule _self est toujours disponible. _charset et _context n\'existent pas, app est ajouté par Symfony.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/templates.html#global-variables',
                'answers' => [
                    ['text' => '<code>_self</code>', 'correct' => true],
                    ['text' => '<code>_charset</code>', 'correct' => false],
                    ['text' => '<code>_context</code>', 'correct' => false],
                    ['text' => '<code>app</code>', 'correct' => false],
                ],
            ],
        ];
    }
}
