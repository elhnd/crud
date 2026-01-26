<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 37
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures37 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures36::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Required categories not found. Please load AppFixtures first.');
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
            // Q1 - Validator - Constraints usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'A constraint can be applied on',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les contraintes de validation peuvent être appliquées sur des propriétés publiques, privées ou protégées.',
                'resourceUrl' => 'https://symfony.com/doc/current/book/validation.html#properties',
                'answers' => [
                    ['text' => 'public property', 'correct' => true],
                    ['text' => 'private property', 'correct' => true],
                    ['text' => 'protected property', 'correct' => true],
                ],
            ],

            // Q2 - Best Practices - Form buttons
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Given the context where a form needs to contain multiple buttons, where <strong>shouldn\'t</strong> the buttons be defined?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Selon les bonnes pratiques Symfony, les boutons de formulaire devraient être définis dans le template et non dans le form type ou le controller.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/form/multiple_buttons.html',
                'answers' => [
                    ['text' => 'In the template', 'correct' => true],
                    ['text' => 'In the form type', 'correct' => false],
                    ['text' => 'In the controller', 'correct' => false],
                ],
            ],

            // Q3 - FrameworkBundle - Base Controller Class
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'Which of the following are valid methods of the Symfony base controller class?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les méthodes isCsrfTokenValid(), redirectToRoute() et denyAccessUnlessGranted() sont des méthodes valides de AbstractController. generatePath() et createXmlResponse() n\'existent pas.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.0/src/Symfony/Bundle/FrameworkBundle/Controller/AbstractController.php',
                'answers' => [
                    ['text' => '<code>isCsrfTokenValid()</code>', 'correct' => true],
                    ['text' => '<code>redirectToRoute()</code>', 'correct' => true],
                    ['text' => '<code>denyAccessUnlessGranted()</code>', 'correct' => true],
                    ['text' => '<code>generatePath()</code>', 'correct' => false],
                    ['text' => '<code>createXmlResponse()</code>', 'correct' => false],
                ],
            ],

            // Q4 - Form - FormRegistry usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which type is returned by <code>FormRegistry::getType()</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'FormRegistry::getType() retourne une instance de ResolvedFormTypeInterface.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Form/FormRegistry.php#L60',
                'answers' => [
                    ['text' => '<code>ResolvedFormTypeInterface</code>', 'correct' => true],
                    ['text' => '<code>FormInterface</code>', 'correct' => false],
                    ['text' => '<code>ResolvedForm</code>', 'correct' => false],
                    ['text' => '<code>GuessedType</code>', 'correct' => false],
                ],
            ],

            // Q5 - FrameworkBundle - Translation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'Is the translation activated by default?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Non, la traduction n\'est pas activée par défaut dans Symfony. Elle doit être configurée explicitement.',
                'resourceUrl' => 'http://symfony.com/doc/current/book/translation.html#configuration',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q6 - Filesystem - Copy method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Which exception is thrown when the origin file does not exist when you use the <code>Symfony\Component\Filesystem\Filesystem::copy</code> method ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode copy() lance une FileNotFoundException lorsque le fichier source n\'existe pas.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/filesystem.html#copy',
                'answers' => [
                    ['text' => '<code>FileNotFoundException</code>', 'correct' => true],
                    ['text' => '<code>FilesystemException</code>', 'correct' => false],
                    ['text' => '<code>FileException</code>', 'correct' => false],
                    ['text' => '<code>FileNotExistException</code>', 'correct' => false],
                    ['text' => '<code>FileErrorException</code>', 'correct' => false],
                ],
            ],

            // Q7 - HttpKernel - HttpKernelInterface
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the third argument of the <code>handle</code> method of <code>Symfony\Component\HttpKernel\HttpKernelInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le troisième argument de la méthode handle() est $catch qui détermine si les exceptions doivent être capturées ou non.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/HttpKernel/HttpKernelInterface.php#L42',
                'answers' => [
                    ['text' => 'Whether to catch exceptions or not.', 'correct' => true],
                    ['text' => 'The type of the request', 'correct' => false],
                    ['text' => 'A Request instance', 'correct' => false],
                    ['text' => 'Whether to activate the debug or not', 'correct' => false],
                    ['text' => 'The name of the environment', 'correct' => false],
                ],
            ],

            // Q8 - BrowserKit - Client lifecycle
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:BrowserKit'],
                'text' => 'Once started, could the <code>Client</code> be restarted?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, la méthode restart() permet de redémarrer le Client du composant BrowserKit.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/BrowserKit/Client.php#L534',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q9 - Twig - Checking if blocks are defined
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In a Twig template that extends other templates, how can you check if any of the parent templates define some block called <code>sidebar</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le test "is defined" peut être utilisé avec la fonction block() pour vérifier si un bloc existe.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/functions/block.html',
                'answers' => [
                    ['text' => '<pre><code class="language-twig">{% if block(\'sidebar\') is defined %}
  ...
{% endif %}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-twig">{% if defined(block(\'sidebar\')) %}
  ...
{% endif %}</code></pre>', 'correct' => false],
                    ['text' => 'You can\'t check if a Twig block exists.', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% if block(\'sidebar\') ?? block(\'\') %}
  ...
{% endif %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% if block(\'sidebar\')|defined %}
  ...
{% endif %}</code></pre>', 'correct' => false],
                ],
            ],

            // Q10 - FrameworkBundle - Symfony and Databases
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'What is the default ORM that integrates with Symfony ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Doctrine est l\'ORM par défaut qui s\'intègre avec Symfony.',
                'resourceUrl' => 'https://symfony.com/doc/current/doctrine.html',
                'answers' => [
                    ['text' => 'Doctrine.', 'correct' => true],
                    ['text' => 'Symfony ORM.', 'correct' => false],
                    ['text' => 'Propel.', 'correct' => false],
                    ['text' => 'Hibernate.', 'correct' => false],
                ],
            ],

            // Q11 - DI - Autowiring alias
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could the <code>autowiring</code> alias of a service be defined using an attribute?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, l\'attribut #[Target] permet de définir un alias d\'autowiring pour un service.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/DependencyInjection/Attribute/Target.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q12 - Filesystem - Mirror usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

# ...

$fs = new Filesystem();
$fs-&gt;mirror(\'/srv/app\', \'/srv/bar\', null, [\'override\' =&gt; true]);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ce code est valide. La méthode mirror() accepte un tableau d\'options incluant "override".',
                'resourceUrl' => 'https://symfony.com/doc/2.1/components/filesystem.html#mirror',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q13 - Twig - Use usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Could the template reference used in <code>use</code> be an expression?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, le tag "use" en Twig ne supporte pas les expressions comme référence de template. Seuls les noms de template littéraux sont acceptés.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/use.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q14 - HTTP - Status code
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'What is the status code for <strong>Too Many Requests</strong>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le code de statut HTTP 429 correspond à "Too Many Requests" (RFC 6585).',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc6585#section-4',
                'answers' => [
                    ['text' => '429', 'correct' => true],
                    ['text' => '503', 'correct' => false],
                    ['text' => '420', 'correct' => false],
                    ['text' => '431', 'correct' => false],
                    ['text' => '502', 'correct' => false],
                ],
            ],

            // Q15 - PHP - Global variables usage
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Does the following code valid?
<pre><code class="language-php">&lt;?php

$GLOBALS = [];
$GLOBALS += [];</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, depuis PHP 8.1, $GLOBALS est en lecture seule et ne peut pas être réaffecté ou modifié de cette manière.',
                'resourceUrl' => 'https://www.php.net/manual/en/reserved.variables.globals',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q16 - PHP - Intersection types
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Data Format & Types'],
                'text' => 'Given the following code:
<pre><code class="language-php">function foo((Iterator&amp;Countable)|ArrayAccess $value) {
    // ...
}</code></pre>
<p>What must be the type of <code>$value</code>?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Les types DNF (Disjunctive Normal Form) permettent de combiner types d\'intersection et d\'union. Ici, $value doit implémenter (Iterator ET Countable) OU ArrayAccess.',
                'resourceUrl' => 'https://wiki.php.net/rfc/dnf_types',
                'answers' => [
                    ['text' => 'A class implementing <code>Iterator</code> and <code>Countable</code>, or implementing <code>ArrayAccess</code>', 'correct' => true],
                    ['text' => 'A class implementing <code>Iterator</code>, but also <code>Countable</code> <strong>or</strong> <code>ArrayAccess</code>', 'correct' => false],
                    ['text' => 'An <code>Iterator</code>, or a class implementing <code>Countable</code> <strong>and</strong> <code>ArrayAccess</code>', 'correct' => false],
                    ['text' => 'One of <code>Iterator</code>, <code>Countable</code> or <code>ArrayAccess</code>', 'correct' => false],
                ],
            ],

            // Q17 - PHP - SPL
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:SPL'],
                'text' => 'What is the SPL?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'SPL signifie Standard PHP Library. C\'est une collection de classes et interfaces fournissant des structures de données, itérateurs, exceptions et fonctions d\'autoload.',
                'resourceUrl' => 'https://www.php.net/manual/en/book.spl.php',
                'answers' => [
                    ['text' => 'The Standard PHP Library, a collection of classes and interfaces which provides common data structures, iterators, exceptions and classes to manipulate files. It also provides functions to handle and configure autoload.', 'correct' => true],
                    ['text' => 'The Service Priority List, a set of functions to register classes and objects as services and define their loading priority, for performance optimization purposes.', 'correct' => false],
                    ['text' => 'The Session Provider Library, a collection of functions and internals to provide more advanced ways of dealing with sessions in PHP. It provides means to get more user-agent information, store sessions on various persistence platforms, and tracing options.', 'correct' => false],
                    ['text' => 'The Sandbox Process Library, an environment designed to run PHP in a completely isolated way to test dangerous or potentially problematic code without risking compromising the rest of the server. I/O operations are completely disabled among other features.', 'correct' => false],
                ],
            ],

            // Q18 - HTTP - Stale response
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Could a stale response be marked as reusable when an origin server responds with and error (<code>500</code>, <code>502</code>, <code>503</code> or <code>504</code>)?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, avec la directive Cache-Control stale-if-error, une réponse périmée peut être réutilisée en cas d\'erreur du serveur d\'origine.',
                'resourceUrl' => 'https://datatracker.ietf.org/doc/html/rfc5861#section-4',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q19 - Doctrine DBAL - Connection URL
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'Which of the following are valid database URLs that can be used in the <code>dbal.url</code> option in Symfony applications?
<pre><code class="language-yaml"># app/config/config.yml
doctrine:
    dbal:
        url: ...</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les URLs valides incluent sqlite:///:memory: et mysql://localhost:4486/foo?charset=UTF-8. Le format mysql://localhost/mydb@user:secret est incorrect (user:password avant l\'hôte).',
                'resourceUrl' => 'https://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html',
                'answers' => [
                    ['text' => '<code>sqlite:///:memory:</code>', 'correct' => true],
                    ['text' => '<code>mysql://localhost:4486/foo?charset=UTF-8</code>', 'correct' => true],
                    ['text' => '<code>sqlite:///data.db</code>', 'correct' => false],
                    ['text' => '<code>pgsql://localhost:5432</code>', 'correct' => false],
                    ['text' => '<code>mysql://localhost/mydb@user:secret</code>', 'correct' => false],
                ],
            ],

            // Q20 - HttpFoundation - Request attributes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Does using <code>$request-&gt;get(\'key\')</code> still a recommended approach when fetching input data?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, l\'utilisation de $request->get() est dépréciée. Il est recommandé d\'utiliser les sources explicites comme $request->query, $request->request, etc.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/HttpFoundation/Request.php#L694',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q21 - HttpFoundation - RequestStack usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Does the <code>RequestStack::getMasterRequest()</code> method still available?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, getMasterRequest() a été supprimée dans Symfony 6.0. Utilisez getMainRequest() à la place.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/UPGRADE-6.0.md#httpfoundation',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q22 - Clock - Clock usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

use Symfony\Component\Clock\NativeClock;
use Symfony\Component\Clock\ClockInterface;

class MyClockSensitiveClass
{
    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function doSomething(): void
    {
        $now = $this-&gt;clock-&gt;now();

        $this-&gt;clock-&gt;sleep(2.5);
    }
}

$clock = new NativeClock();
$service = new MyClockSensitiveClass($clock);
$service-&gt;doSomething();</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ce code est valide. ClockInterface fournit les méthodes now() et sleep().',
                'resourceUrl' => 'https://github.com/symfony/clock',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q23 - Console - Application events
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'The Application class of the Console component allows you to optionally hook into the lifecycle of a console application via events.

How many events are dispatched?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le composant Console dispatche 4 événements : ConsoleCommandEvent, ConsoleTerminateEvent, ConsoleErrorEvent et ConsoleSignalEvent.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/components/console/events.html',
                'answers' => [
                    ['text' => '4', 'correct' => true],
                    ['text' => '5', 'correct' => false],
                    ['text' => '3', 'correct' => false],
                    ['text' => '2', 'correct' => false],
                    ['text' => '6', 'correct' => false],
                    ['text' => '1', 'correct' => false],
                ],
            ],

            // Q24 - Runtime - Configuration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'],
                'text' => 'Is it possible to set the path used to load the <code>.env</code> files?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le composant Runtime permet de configurer le chemin des fichiers .env via les options.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/components/runtime.html#using-options',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q25 - DI - ReverseContainer usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could a service identifier be returned from a <code>ReverseContainer</code> if the service is not tagged as <code>container.reversible</code> and defined as <code>private</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, un service privé doit être tagué avec container.reversible pour que son identifiant puisse être récupéré depuis ReverseContainer.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/DependencyInjection/ReverseContainer.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q26 - Expression Language - Logical Operators
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'What will be displayed by the following code?
<pre><code class="language-php">var_dump($language-&gt;evaluate(
    \'life &lt; universe or life &lt; everything\',
    array(
        \'life\' =&gt; 10,
        \'universe\' =&gt; 10,
        \'everything\' =&gt; 22,
    )
));</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'L\'expression évalue à true car life (10) < everything (22), même si life (10) n\'est pas < universe (10).',
                'resourceUrl' => 'http://symfony.com/doc/current/components/expression_language/syntax.html#logical-operators',
                'answers' => [
                    ['text' => 'true', 'correct' => true],
                    ['text' => 'false', 'correct' => false],
                ],
            ],

            // Q27 - Security - Voters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the signature of the <code>vote()</code> method from <code>VoterInterface</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La signature correcte est vote(TokenInterface $token, $subject, array $attributes). L\'ordre des paramètres est token, subject, attributes.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/Security/Core/Authorization/Voter/VoterInterface.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, $subject, array $attributes)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, array $attributes, $subject)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, array $attributes, $object)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">public function vote(TokenInterface $token, $object, array $attributes)</code></pre>', 'correct' => false],
                ],
            ],

            // Q28 - DI - Compiler Passes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could a priority be set when adding a new compiler pass?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 4.1, on peut définir une priorité lors de l\'ajout d\'un compiler pass.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/components/dependency_injection/compilation.html#controlling-the-pass-ordering',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q29 - Translation - Loading Message Catalogs
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'Which of the followings are part of the built-in message catalogs loaders?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les loaders intégrés incluent : MoFileLoader, CsvFileLoader, PoFileLoader, XliffFileLoader, YamlFileLoader, PhpFileLoader, IniFileLoader, JsonFileLoader, IcuDatFileLoader, IcuResFileLoader.',
                'resourceUrl' => 'https://github.com/symfony/translation/tree/2.3/Loader',
                'answers' => [
                    ['text' => 'MoFileLoader', 'correct' => true],
                    ['text' => 'CsvFileLoader', 'correct' => true],
                    ['text' => 'PoFileLoader', 'correct' => true],
                    ['text' => 'XliffFileLoader', 'correct' => true],
                    ['text' => 'YamlFileLoader', 'correct' => true],
                    ['text' => 'JsonLoader', 'correct' => false],
                    ['text' => 'PhpLoader', 'correct' => false],
                    ['text' => 'IcuFileLoader', 'correct' => false],
                    ['text' => 'IniFileLoader', 'correct' => false],
                ],
            ],

            // Q30 - HttpFoundation - Session usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Session'],
                'text' => 'Which method can be used to add a new value into <code>Session</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La méthode set() est utilisée pour ajouter une nouvelle valeur dans la Session.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/HttpFoundation/Session/Session.php#L93',
                'answers' => [
                    ['text' => '<code>$session-&gt;set()</code>', 'correct' => true],
                    ['text' => '<code>$session-&gt;init()</code>', 'correct' => false],
                    ['text' => '<code>$session-&gt;append()</code>', 'correct' => false],
                    ['text' => '<code>$session-&gt;add()</code>', 'correct' => false],
                    ['text' => '<code>$session-&gt;insert()</code>', 'correct' => false],
                ],
            ],

            // Q31 - Form - DataTransformer
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'How to customize the validation error message of the validation error caused by a <code>TransformationFailedException</code> ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'option invalid_message permet de personnaliser le message d\'erreur de validation causé par une TransformationFailedException.',
                'resourceUrl' => 'http://symfony.com/doc/current/form/data_transformers.html#creating-the-transformer',
                'answers' => [
                    ['text' => 'By using the <code>invalid_message</code> option', 'correct' => true],
                    ['text' => 'The exception message will be used as the validation error message', 'correct' => false],
                    ['text' => 'It\'s not possible', 'correct' => false],
                ],
            ],

            // Q32 - Console - Console table
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
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
     $table-&gt;setRows([[\'foo1\', \'foo2\']]);
     $table-&gt;render();
     $table-&gt;appendRow([\'bar1\', \'bar2\']);

     return 0;
  }
}</code></pre>
<p>What will happen ?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode appendRow() permet d\'ajouter une ligne après le rendu initial. Le tableau aura donc deux lignes.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/components/console/helpers/table.html',
                'answers' => [
                    ['text' => 'The table will have two rows with two values each', 'correct' => true],
                    ['text' => 'The table will have only one row with two values', 'correct' => false],
                    ['text' => 'An exception will be thrown', 'correct' => false],
                ],
            ],

            // Q33 - Config - Normalization
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'What is the purpose of <code>Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition::fixXmlConfig</code> ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'fixXmlConfig() normalise les noms d\'éléments XML (pluralisation) et s\'assure que les éléments XML uniques sont convertis en tableau.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/config/definition.html#normalization',
                'answers' => [
                    ['text' => 'It ensures that single XML elements are turned into an array', 'correct' => true],
                    ['text' => 'It normalizes XML element name (e.g. pluralizing the key used in XML)', 'correct' => true],
                    ['text' => 'It always applies a custom function to an XML element', 'correct' => false],
                    ['text' => 'It applies a custom function to an XML element if an error occurs', 'correct' => false],
                ],
            ],

            // Q34 - PHP Arrays - sort function
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'What is the output of the following PHP script?
<pre><code class="language-php">&lt;?php

$values = [37, 5, \'09\'];

$sorted = sort($values);

foreach ($sorted as $v) {
    echo $v;
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La fonction sort() retourne un booléen (true/false), pas le tableau trié. Le foreach sur un booléen provoque une erreur.',
                'resourceUrl' => 'https://php.net/manual/en/function.sort.php',
                'answers' => [
                    ['text' => 'An error', 'correct' => true],
                    ['text' => '50937', 'correct' => false],
                    ['text' => '375509', 'correct' => false],
                    ['text' => '09537', 'correct' => false],
                ],
            ],

            // Q35 - PHP I/O - __FILE__ constant
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:I/O'],
                'text' => 'Which of the following statements is true about <code>__FILE__</code> constant?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '__FILE__ contient le chemin complet et le nom du fichier actuel, avec les liens symboliques résolus.',
                'resourceUrl' => 'http://www.php.net/constants.predefined',
                'answers' => [
                    ['text' => 'It contains the name of the file and full path.', 'correct' => true],
                    ['text' => 'It contains the current line number of the file.', 'correct' => false],
                    ['text' => 'It contains name of the directory having file and full path of the directory.', 'correct' => false],
                    ['text' => 'It contains the path of the main script.', 'correct' => false],
                ],
            ],

            // Q36 - Twig - Escaping
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given <code>var</code> and <code>bar</code> are existing variables, among the following, which expressions are escaped?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le filtre raw doit être le dernier appliqué pour désactiver l\'échappement. {{ var|raw|upper }} et {{ var|raw~bar }} sont échappés car raw n\'est pas le dernier filtre.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/raw.html',
                'answers' => [
                    ['text' => '<code>{{ var|raw|upper }}</code>', 'correct' => true],
                    ['text' => '<code>{{ var|raw~bar }}</code>', 'correct' => true],
                    ['text' => '<code>{{ var|upper|raw }}</code>', 'correct' => false],
                ],
            ],

            // Q37 - Twig - The "with" tag
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
Will the Twig template work as expected?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le tag "with" crée une portée isolée. Les variables définies à l\'intérieur ne sont pas accessibles à l\'extérieur.',
                'resourceUrl' => 'http://twig.symfony.com/doc/tags/with.html',
                'answers' => [
                    ['text' => 'No. The template will display an error because the <code>maxItems</code> variable is not defined outside the <code>with</code> tag.', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No. The template won\'t iterate from <code>1</code> to <code>7</code>. It will execute the <code>for</code> loop just one time (where <code>i</code> is <code>1</code>).', 'correct' => false],
                    ['text' => 'No. The template will display an error because the <code>with</code> tag is not defined.', 'correct' => false],
                ],
            ],

            // Q38 - PHP Arrays - array_map (duplicate but different context)
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'Consider the following PHP script.
<pre><code class="language-php">&lt;?php

function square($val)
{
    return $val ** 2;
}

$arr = [1, 2, 3, 4];

/** line **/

$i = 0;
foreach ($squares as $value) {
    if ($i++ &gt; 0) {
        echo ".";
    }

    echo $value;
}</code></pre>
What <code>/** line **/</code> should be used to apply a callback function to every element of an array?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_map() applique un callback à chaque élément et retourne un nouveau tableau. array_walk() modifie le tableau en place et retourne un booléen.',
                'resourceUrl' => 'https://php.net/manual/en/function.array-map.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$squares = array_map(\'square\', $arr);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$squares = array_walk($arr, \'square\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$squares = call_user_func_array($arr, \'square\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$squares = call_user_func_array(\'square\', $arr);</code></pre>', 'correct' => false],
                ],
            ],

            // Q39 - Routing - Route matching
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Given the following routes, what controller will be executed for <code>/book/123</code> ?
<pre><code class="language-yml"># config/routes.yaml
book_list:
    path:       /books
    controller: \'App\Controller\BookController::list\'
book_detail:
    path:       /books/{slug}
    controller: \'App\Controller\BookController::detail\'
book_download:
    path:       /books/{slug}/download
    controller: \'App\Controller\BookController::download\'</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'URL /book/123 ne correspond à aucune route car toutes les routes commencent par /books (avec un s).',
                'resourceUrl' => 'https://symfony.com/doc/2.x/routing.html',
                'answers' => [
                    ['text' => 'Error: No route found', 'correct' => true],
                    ['text' => 'App\Controller\BookController::list', 'correct' => false],
                    ['text' => 'App\Controller\BookController::detail', 'correct' => false],
                    ['text' => 'App\Controller\BookController::download', 'correct' => false],
                ],
            ],

            // Q40 - HttpFoundation - Generator in response
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What will be the result of the following code?
<pre><code class="language-php">use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController
{
    #[Route(\'/\', name: \'default\')]
    public function default(Request $request)
    {
        return new JsonResponse([
            \'data\' =&gt; $this-&gt;getData(),
        ]);
    }

    private function getData(): \Generator
    {
        yield \'foo\';
        yield \'bar\';
        yield \'baz\';
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un Generator n\'est pas directement sérialisable en JSON. JsonResponse retournera {"data":{}} car le Generator est traité comme un objet vide.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-dx-improvements-part-2#streamed-json-responses',
                'answers' => [
                    ['text' => 'It will return <code>{"data":{}}</code>', 'correct' => true],
                    ['text' => 'It will return <code>{"data":["foo","bar","baz"]}</code>', 'correct' => false],
                    ['text' => 'It will throw an <code>\InvalidArgumentException</code>', 'correct' => false],
                ],
            ],
        ];
    }
}
