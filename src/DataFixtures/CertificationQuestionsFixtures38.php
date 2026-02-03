<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 38
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures38 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures37::class];
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
            // Q1 - HttpClient - RetryableHttpClient
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'What is the default retries allowed in <code>RetryableHttpClient</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le RetryableHttpClient permet par défaut 3 tentatives de retry.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.2/src/Symfony/Component/HttpClient/RetryableHttpClient.php#L38',
                'answers' => [
                    ['text' => '3', 'correct' => true],
                    ['text' => '2', 'correct' => false],
                    ['text' => '4', 'correct' => false],
                    ['text' => '1', 'correct' => false],
                ],
            ],

            // Q2 - DI - Container preloading
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could the container be preloaded using Opcache preloading?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, Symfony supporte le preloading Opcache pour le container depuis la version 4.4.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/performance.html#use-the-opcache-class-preloading',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q3 - FrameworkBundle - DI Tags cache clearer
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the tag to use to register your service to be called during the cache clearing process?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le tag kernel.cache_clearer permet d\'enregistrer un service appelé lors du vidage du cache.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/dic_tags.html#kernel-cache-clearer',
                'answers' => [
                    ['text' => '<code>kernel.cache_clearer</code>', 'correct' => true],
                    ['text' => '<code>kernel.cache</code>', 'correct' => false],
                    ['text' => '<code>cache_clearer</code>', 'correct' => false],
                    ['text' => '<code>cache.clearer</code>', 'correct' => false],
                    ['text' => '<code>command.cache_clearer</code>', 'correct' => false],
                ],
            ],

            // Q4 - Console - Input
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'What type of argument would you use to accept more than one input parameter? For example, <code>php bin/console hello Fabien Martin Jessica</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'InputArgument::IS_ARRAY permet d\'accepter plusieurs valeurs pour un même argument.',
                'resourceUrl' => 'https://symfony.com/doc/current/console/input.html',
                'answers' => [
                    ['text' => '<code>InputArgument::IS_ARRAY</code>', 'correct' => true],
                    ['text' => '<code>InputArgument::MULTIPLE</code>', 'correct' => false],
                    ['text' => '<code>InputArgument::NONE</code>', 'correct' => false],
                    ['text' => '<code>InputArgument::OPTIONAL</code>', 'correct' => false],
                ],
            ],

            // Q5 - DI - FrozenParameterBag usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which exception is thrown when removing a parameter from a <code>FrozenParameterBag</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'FrozenParameterBag lance une LogicException car les paramètres sont gelés après compilation.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php',
                'answers' => [
                    ['text' => '<code>LogicException</code>', 'correct' => true],
                    ['text' => '<code>RuntimeException</code>', 'correct' => false],
                    ['text' => '<code>InvalidArgumentException</code>', 'correct' => false],
                    ['text' => '<code>BadMethodCallException</code>', 'correct' => false],
                ],
            ],

            // Q6 - PHP - Global variables
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">$a = 20;

function my_function($b)
{
    $a = 30;
    global $a, $c;

    return $c = ($b + $a);
}

print my_function(40) + $c;</code></pre>
<p>What does this script output when it\'s executed with PHP?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Après "global $a", $a devient 20 (pas 30). $c = 40 + 20 = 60. my_function() retourne 60, et $c global vaut 60. Donc 60 + 60 = 120.',
                'resourceUrl' => 'http://php.net/manual/en/language.variables.scope.php',
                'answers' => [
                    ['text' => '<code>120</code>', 'correct' => true],
                    ['text' => '<code>70</code>', 'correct' => false],
                    ['text' => 'An error saying something like <code>Undefined variable: ...</code>.', 'correct' => false],
                    ['text' => '<code>110</code>', 'correct' => false],
                    ['text' => '<code>60</code>', 'correct' => false],
                ],
            ],

            // Q7 - PHP - SensitiveParameter
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is the goal of the <code>#[SensitiveParameter]</code> attribute?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'attribut #[SensitiveParameter] permet de masquer la valeur d\'un paramètre dans les stack traces.',
                'resourceUrl' => 'https://www.php.net/manual/en/class.sensitive-parameter.php',
                'answers' => [
                    ['text' => 'To hide a function parameter value in the back trace of an exception, for example', 'correct' => true],
                    ['text' => 'This attribute doesn\'t exist natively in PHP', 'correct' => false],
                    ['text' => 'To tell PHP to automatically encrypt a function parameter', 'correct' => false],
                    ['text' => 'To enable further validation on the parameter value at runtime, before interacting with it', 'correct' => false],
                ],
            ],

            // Q8 - Event Dispatcher - Event aliases
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

namespace App;

use App\Event\MyCustomEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\AddEventAliasesPass;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
  protected function build(ContainerBuilder $container)
  {
    $container-&gt;addCompilerPass(new AddEventAliasesPass([
      MyCustomEvent::class =&gt; \'my_custom_event\',
    ]));
  }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, AddEventAliasesPass permet de définir des alias pour les événements.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/event_dispatcher.html#event-aliases',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q9 - DI - Compiler Passes registration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'How can you register a new compiler pass?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un compiler pass s\'enregistre via la méthode addCompilerPass() du ContainerBuilder.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/compiler_passes.html',
                'answers' => [
                    ['text' => 'By passing an instance of the <code>CompilerPass</code> to the <code>addCompilerPass</code> of a <code>ContainerBuilder</code>.', 'correct' => true],
                    ['text' => 'By creating a new service with the tag <code>compiler_pass</code>.', 'correct' => false],
                    ['text' => 'By creating a new service with the tag <code>compiler.pass</code>.', 'correct' => false],
                    ['text' => 'By passing an instance of the <code>CompilerPass</code> to the <code>pushCompilerPass</code> of a <code>ContainerBuilder</code>.', 'correct' => false],
                    ['text' => 'By passing an instance of the <code>CompilerPass</code> to the <code>registerCompilerPass</code> of a <code>ContainerBuilder</code>.', 'correct' => false],
                ],
            ],

            // Q10 - Validator - NotBlank constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Will the following snippet throw an <code>InvalidArgumentException</code> ?
<pre><code class="language-php">use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\NotBlank;

$expectedNotBlank = "0";

$validator = Validation::createValidator();
$violations = $validator-&gt;validate($expectedNotBlank, [new NotBlank()]);

if (0 !== count($violations)) {
    throw new InvalidArgumentException(\'The value is blank !\');
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, la chaîne "0" n\'est pas considérée comme vide par NotBlank. Elle passe la validation.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/NotBlank.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q11 - FrameworkBundle - Validator initializer tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the tag to register a service that initializes objects before validation?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le tag validator.initializer permet d\'enregistrer un service d\'initialisation avant validation.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/dic_tags.html#validator-initializer',
                'answers' => [
                    ['text' => '<code>validator.initializer</code>', 'correct' => true],
                    ['text' => '<code>validator_initializer</code>', 'correct' => false],
                    ['text' => '<code>validation_initializer</code>', 'correct' => false],
                    ['text' => '<code>validation.initializer</code>', 'correct' => false],
                ],
            ],

            // Q12 - DI - Service tags
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which mechanism allows to aggregate services by domain in the service container?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Les tags permettent de regrouper des services par domaine fonctionnel.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/dependency_injection/tags.html',
                'answers' => [
                    ['text' => 'Tag', 'correct' => true],
                    ['text' => 'Scope', 'correct' => false],
                    ['text' => 'Listener', 'correct' => false],
                    ['text' => 'Abstraction', 'correct' => false],
                ],
            ],

            // Q13 - HttpKernel - MapQueryParameter usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Given a <code>GET</code> query <code>/dashboard?startingDate=2022-01-01</code> and the following code, will the <code>startingDate</code> parameter be correctly resolved?
<pre><code class="language-php">&lt;?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route(\'/dashboard\', methods: [\'GET\'])]
    public function changeUserPicture(#[MapQueryParameter] string $startingDate): Response
    {
        // ...
    }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, #[MapQueryParameter] permet de mapper automatiquement les paramètres de query string.',
                'resourceUrl' => 'https://symfony.com/doc/6.3/controller.html#mapping-query-parameters-individually',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q14 - PHP - shuffle function
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'Which of the following statements best describes the <code>shuffle()</code> function? This function accepts an array as its first argument.',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'shuffle() modifie le tableau original, mélange les éléments et réassigne de nouvelles clés numériques.',
                'resourceUrl' => 'https://php.net/manual/en/function.shuffle.php',
                'answers' => [
                    ['text' => 'The original array is modified so elements are now in a random order. New keys are assigned to elements.', 'correct' => true],
                    ['text' => 'A new array is returned with elements in a random order. Each value retains its original key.', 'correct' => false],
                    ['text' => 'A new array is returned with elements in a random order. New keys are assigned to elements.', 'correct' => false],
                    ['text' => 'The original array is modified so elements are now in a random order. Each value retains its original key.', 'correct' => false],
                ],
            ],

            // Q15 - Twig - Twig Internals Lexer
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following Twig internal objects is responsible for tokenizing the template source code into smaller pieces for easier processing?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le Lexer est responsable de la tokenisation du code source en tokens.',
                'resourceUrl' => 'http://twig.symfony.com/doc/internals.html',
                'answers' => [
                    ['text' => 'The Lexer', 'correct' => true],
                    ['text' => 'The Environment', 'correct' => false],
                    ['text' => 'The Parser', 'correct' => false],
                    ['text' => 'The Compiler', 'correct' => false],
                ],
            ],

            // Q16 - Twig - Environment::render()
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which method is used to render the desired template?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La méthode Environment::render() est utilisée pour rendre un template.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/api.html#rendering-templates',
                'answers' => [
                    ['text' => '<code>Environment::render()</code>', 'correct' => true],
                    ['text' => '<code>Environment::display()</code>', 'correct' => false],
                    ['text' => '<code>Environment::resolveTemplate()</code>', 'correct' => false],
                    ['text' => '<code>Environment::showTemplate()</code>', 'correct' => false],
                ],
            ],

            // Q17 - HTTP - Safe directive
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Is the following code valid when using the <code>safe</code> directive?
<pre><code class="language-text">GET /foo.html HTTP/1.1
Host: www.example.org
User-Agent: ExampleBrowser/1.0
Prefer: safe</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le header Prefer: safe est valide selon RFC 8674.',
                'resourceUrl' => 'https://datatracker.ietf.org/doc/html/rfc8674#section-2',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q18 - HTTP - Media-Type format
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'What is the format of the Media-Type value inside <code>Content-Type</code> and <code>Accept</code> headers?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le format Media-Type est type "/" subtype suivi optionnellement de paramètres.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc7231#section-3.1.1.1',
                'answers' => [
                    ['text' => '<code>type "/" subtype *( OWS ";" OWS parameter )</code>', 'correct' => true],
                    ['text' => '<code>type ** subtype *( OWS ";" OWS parameter )</code>', 'correct' => false],
                    ['text' => '<code>type + subtype *( OWS ";" OWS parameter )</code>', 'correct' => false],
                ],
            ],

            // Q19 - Doctrine - Orphan removal
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'Considering the following code:
<pre><code class="language-php">namespace AcmeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Content
{
    /** 
     * @ORM\Id 
     * @ORM\Column(type="integer") 
     * @ORM\GeneratedValue 
     */
    private $id;

    /** 
     * @ORM\OneToMany(targetEntity="Tag", orphanRemoval=true) 
     */
    private $tags;

    public function __construct()
    {
        $this-&gt;tags = new ArrayCollection();
    }

    public function removeTag(Tag $tag)
    {
        $this-&gt;tags-&gt;remove($tag);
    }
}</code></pre>
<p>Will the <code>$tag</code> entity be deleted when running the following code</p>
<pre><code class="language-php">$content-&gt;removeTag($tag);
$em-&gt;persist($content);
$em-&gt;flush();</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, avec orphanRemoval=true, l\'entité Tag sera supprimée de la base de données.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/working-with-associations.html#orphan-removal',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q20 - Validator - Constraints usage (duplicate)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'A constraint can be applied on which types of properties?',
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

            // Q21 - Security - HTTP Basic Authentication
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'When using HTTP basic, how does the server starts the authentication process?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le serveur envoie un header WWW-Authenticate avec le code 401 pour initier l\'authentification HTTP Basic.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2617#section-2',
                'answers' => [
                    ['text' => 'Sending the WWW-Authenticate HTTP header with the HTTP 401 Not Authorized status code.', 'correct' => true],
                    ['text' => 'Sending the status code HTTP 418 Authentication Required.', 'correct' => false],
                    ['text' => 'Rendering a login form with the fields _user and _password.', 'correct' => false],
                    ['text' => 'Redirecting the request to the port 443.', 'correct' => false],
                ],
            ],

            // Q22 - Translation - addLoader
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'What is the way to add a loader to the translator?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode addLoader() prend le format en premier paramètre et l\'instance du loader en second.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/components/translation/usage.html',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$translator-&gt;addLoader(\'array\', new ArrayLoader());</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$translator-&gt;addLoader(new ArrayLoader());</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$translator-&gt;addArrayLoader(new ArrayLoader());</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$translator-&gt;addLoader(new ArrayLoader(), \'array\');</code></pre>', 'correct' => false],
                ],
            ],

            // Q23 - Filesystem - dumpFile
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'What is the <code>Symfony\Component\Filesystem\Filesystem</code> method to dump contents to a file?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La méthode dumpFile() permet d\'écrire du contenu dans un fichier.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/filesystem.html#dumpfile',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$fs-&gt;dumpFile(\'file.txt\', \'Hello World\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$fs-&gt;dump(\'file.txt\', \'Hello World\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$fs-&gt;file(\'file.txt\', \'Hello World\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$fs-&gt;dumpToFile(\'file.txt\', \'Hello World\');</code></pre>', 'correct' => false],
                ],
            ],

            // Q24 - HttpClient - Scoping client
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'In the fullstack framework, what have you to do to inject a scoping client instance ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour injecter un scoped client, utilisez un nom de variable spécifique typé comme HttpClientInterface.',
                'resourceUrl' => 'https://symfony.com/doc/5.0/components/http_client.html',
                'answers' => [
                    ['text' => 'use a specific variable name type-hinted as <code>HTTPClientInterface</code>', 'correct' => true],
                    ['text' => 'create a factory', 'correct' => false],
                    ['text' => 'create an alias for the scoping client', 'correct' => false],
                ],
            ],

            // Q25 - PHP PSR - PSR-0
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PSR'],
                'text' => 'What is true about the PSR-0: Autoloading Standard?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PSR-0 est déprécié, compatible avec PEAR, et utilise spl_autoload_register(). Les sub-namespaces sont optionnels (MAY, pas MUST).',
                'resourceUrl' => 'https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md',
                'answers' => [
                    ['text' => 'PSR-0 is deprecated.', 'correct' => true],
                    ['text' => 'PSR-0 is compatible with PEAR-style classnames.', 'correct' => true],
                    ['text' => 'The autoloader is registered with the <code>spl_autoload_register()</code> function.', 'correct' => true],
                    ['text' => 'The fully qualified class name MUST have one or more sub-namespace names.', 'correct' => false],
                ],
            ],

            // Q26 - Form - DataTransformer validation error
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Does a <code>Symfony\Component\Form\Exception\TransformationFailedException</code> thrown in a <code>DataTransformer::reverseTransform</code> cause a validation error ?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, une TransformationFailedException dans reverseTransform() génère une erreur de validation.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/form/data_transformers.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q27 - CssSelector - :where selector
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:CssSelector'],
                'text' => 'Could the <code>*:where</code> selector be used?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 7.1, le sélecteur :where est supporté.',
                'resourceUrl' => 'https://symfony.com/doc/7.1/components/css_selector.html#limitations-of-the-cssselector-component',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q28 - PHP - CLI superglobal
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Which PHP superglobal variable contains the command line arguments when the script runs in CLI mode?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '$_SERVER contient les arguments de ligne de commande (dans $_SERVER[\'argv\']).',
                'resourceUrl' => 'http://php.net/manual/en/language.variables.superglobals.php',
                'answers' => [
                    ['text' => '<code>$_SERVER</code>', 'correct' => true],
                    ['text' => '<code>$_ENV</code>', 'correct' => false],
                    ['text' => 'PHP cannot run from the command line interface.', 'correct' => false],
                    ['text' => '<code>$_CLI</code>', 'correct' => false],
                    ['text' => '<code>$_POST</code>', 'correct' => false],
                ],
            ],

            // Q30 - Expression Language - AST dump
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'Could the AST be dumped?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, l\'AST (Abstract Syntax Tree) peut être dumpé dans le composant Expression Language.',
                'resourceUrl' => 'https://symfony.com/doc/3.2/components/expression_language/ast.html#dumping-the-ast',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q31 - Validator - Constraints debug
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Could the constraints of a class be listed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la commande debug:validator permet de lister les contraintes d\'une classe.',
                'resourceUrl' => 'https://symfony.com/doc/5.2/validation.html#debugging-the-constraints',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q32 - Mime - DIC tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mime'],
                'text' => 'Which DIC tag is used by MimeType?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le tag mime_types est utilisé pour enregistrer des MimeTypeGuesser.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/Mime/DependencyInjection/AddMimeTypeGuesserPass.php#L30',
                'answers' => [
                    ['text' => '<code>mime_types</code>', 'correct' => true],
                    ['text' => '<code>mime_type</code>', 'correct' => false],
                    ['text' => '<code>mime.types</code>', 'correct' => false],
                ],
            ],

            // Q33 - Serializer - Enumerations
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Could enumerations be serialized?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 5.4, les enums PHP peuvent être sérialisées.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-4-php-enumerations-support#php-enums-support-in-symfony-serializer',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q34 - Validator - Url constraint protocols
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'When using the <code>Symfony\Component\Validator\Constraints\Url</code> validation constraint, what protocols are allowed by default ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Par défaut, la contrainte Url n\'autorise que HTTP et HTTPS.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/Url.html',
                'answers' => [
                    ['text' => 'HTTP and HTTPS.', 'correct' => true],
                    ['text' => 'Any protocol.', 'correct' => false],
                    ['text' => 'HTTP, HTTPS, FTP and SMTP.', 'correct' => false],
                    ['text' => 'Only HTTP.', 'correct' => false],
                ],
            ],

            // Q35 - Twig - Escaping strategies
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which escape strategies are valid for HTML documents ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les stratégies valides sont : html, html_attr, css, url, js. "asset" n\'existe pas.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/escape.html',
                'answers' => [
                    ['text' => '<code>html</code>', 'correct' => true],
                    ['text' => '<code>html_attr</code>', 'correct' => true],
                    ['text' => '<code>css</code>', 'correct' => true],
                    ['text' => '<code>url</code>', 'correct' => true],
                    ['text' => '<code>js</code>', 'correct' => true],
                    ['text' => '<code>asset</code>', 'correct' => false],
                ],
            ],

            // Q36 - Filesystem - Path longest common base
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Could the longest common base path between multiple files be found?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la classe Path introduite en Symfony 5.4 permet de trouver le plus long chemin commun.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-4-filesystem-path-class',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q37 - Twig - Twig Internals Compiler
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following Twig internal objects is responsible for transforming an AST (Abstract Syntax Tree) into PHP code?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le Compiler est responsable de la transformation de l\'AST en code PHP.',
                'resourceUrl' => 'http://twig.symfony.com/doc/internals.html',
                'answers' => [
                    ['text' => 'The Compiler', 'correct' => true],
                    ['text' => 'The Parser', 'correct' => false],
                    ['text' => 'The Environment', 'correct' => false],
                    ['text' => 'The Lexer', 'correct' => false],
                ],
            ],
        ];
    }
}
