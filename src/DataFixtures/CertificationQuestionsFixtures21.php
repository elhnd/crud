<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Subcategory;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 21
 */
class CertificationQuestionsFixtures21 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['certification', 'questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures20::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found. Run AppFixtures first.');
        }

        $subcategories = $this->getSubcategories($manager, $symfony, $php);
        $questions = $this->getCertificationQuestions($symfony, $php, $subcategories);

        foreach ($questions as $q) {
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }

    private function getSubcategories(ObjectManager $manager, Category $symfony, Category $php): array
    {
        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        $subcategories = [];

        foreach ($subcategoryRepo->findAll() as $sub) {
            $subcategories[$sub->getCategory()->getName() . ':' . $sub->getName()] = $sub;
        }

        $additional = [
            'Symfony' => [
                'Lock' => 'Lock component for resource locking',
                'ErrorHandler' => 'ErrorHandler component for error handling',
                'Process' => 'Process component for executing system commands',
                'HttpClient' => 'HttpClient component for HTTP requests',
            ],
            'PHP' => [
                'PSR' => 'PHP Standards Recommendations',
                'Data Format & Types' => 'PHP data formats and types',
            ],
        ];

        foreach ($additional as $catName => $subs) {
            $category = $catName === 'Symfony' ? $symfony : $php;
            foreach ($subs as $name => $description) {
                $key = $catName . ':' . $name;
                if (!isset($subcategories[$key])) {
                    $sub = new Subcategory();
                    $sub->setName($name);
                    $sub->setDescription($description);
                    $sub->setCategory($category);
                    $manager->persist($sub);
                    $subcategories[$key] = $sub;
                }
            }
        }

        $manager->flush();
        return $subcategories;
    }

    private function getCertificationQuestions(Category $symfony, Category $php, array $subcategories): array
    {
        return [
            // Q1 - HttpFoundation - isXmlHttpRequest
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What will be the result of invoking the <code>isXmlHttpRequest()</code> method on a <code>Symfony\Component\HttpFoundation\Request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode isXmlHttpRequest() retourne true si l\'en-tête X-Requested-With est défini à XMLHttpRequest, ce qui est le standard pour les requêtes AJAX.',
                'resourceUrl' => 'https://github.com/symfony/http-foundation/blob/5.3/Request.php#L1763',
                'answers' => [
                    ['text' => '<code>true</code> if the request has the <code>X-Requested-With</code> header set to <code>XMLHttpRequest</code>.', 'correct' => true],
                    ['text' => '<code>true</code> if the request contains XML content.', 'correct' => false],
                    ['text' => '<code>true</code> if the request must generate an XML response.', 'correct' => false],
                    ['text' => '<code>true</code> if the request has the <code>Content-Type</code> header set to <code>application/xml</code>.', 'correct' => false],
                ],
            ],

            // Q2 - Expression Language - Registering Functions
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'] ?? $subcategories['Symfony:Services'],
                'text' => 'What are the arguments of the <code>register()</code> method used to register a function?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode register() prend trois arguments: le nom de la fonction (string), un callable pour la compilation, et un callable pour l\'évaluation.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/expression_language/extending.html#registering-functions',
                'answers' => [
                    ['text' => '<pre><code>string   $name      The function name
callable $compiler  A callable able to compile the function
callable $evaluator A callable able to evaluate the function</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>callable $compiler  A callable able to compile the function
callable $evaluator A callable able to evaluate the function</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>string             $name      The function name
CompilerInterface  $compiler  A compiler able to compile the function
EvaluatorInterface $evaluator An evaluator able to evaluate the function</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>CompilerInterface  $compiler  A compiler able to compile the function
EvaluatorInterface $evaluator An evaluator able to evaluate the function</code></pre>', 'correct' => false],
                ],
            ],

            // Q3 - Security - Authentication
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Authentication is the process that makes sure that a user is who he claims to be?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Vrai. L\'authentification vérifie l\'identité d\'un utilisateur (qui il prétend être), tandis que l\'autorisation détermine ce qu\'il peut faire.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/security/authentication.html',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // Q4 - Form - UrlType default_protocol
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What is the use of the <code>default_protocol</code> option of the <code>Symfony\Component\Form\Extension\Core\Type\UrlType</code> form type?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'option default_protocol ajoute automatiquement un schéma URI (comme http://) à la valeur soumise si elle n\'en contient pas déjà un.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/forms/types/url.html',
                'answers' => [
                    ['text' => 'To prepend the submitted value with an URI scheme (eg. <code>http://</code>) if it does not begin with one.', 'correct' => true],
                    ['text' => 'To render the input with the <code>placeholder</code> property containing the value of the option.', 'correct' => false],
                    ['text' => 'To force the submitted value to begin with a given URI scheme (eg. <code>http://</code>).', 'correct' => false],
                ],
            ],

            // Q5 - PHP - PSR Coding style
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PSR'] ?? $subcategories['PHP:PHP Basics'],
                'text' => 'Which PSRs define coding guidelines to keep consistent coding styles across projects?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PSR-1 définit les standards de codage de base et PSR-12 étend PSR-1 avec des règles de style de code plus détaillées. PSR-0/4 concernent l\'autoloading, PSR-3 concerne le logging.',
                'resourceUrl' => 'http://www.php-fig.org/psr/',
                'answers' => [
                    ['text' => 'PSR-1', 'correct' => true],
                    ['text' => 'PSR-12', 'correct' => true],
                    ['text' => 'PSR-0', 'correct' => false],
                    ['text' => 'PSR-4', 'correct' => false],
                    ['text' => 'PSR-3', 'correct' => false],
                ],
            ],

            // Q6 - HttpKernel - ValueResolver
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'How to make sure a service tagged as <code>controller.argument_value_resolver</code> will not be called on every argument?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'attribut #[AsTargetedValueResolver] permet de cibler un resolver spécifique pour un argument, évitant que le resolver soit appelé pour chaque argument.',
                'resourceUrl' => 'https://symfony.com/doc/6.3/controller/value_resolver.html#controller-targeted-value-resolver',
                'answers' => [
                    ['text' => 'By adding the attribute <code>#[AsTargetedValueResolver]</code>', 'correct' => true],
                    ['text' => 'By adding the attribute <code>#[AsValueResolver(target: \'App\MyCustomInterface\')]</code>', 'correct' => false],
                ],
            ],

            // Q7 - PHP - echo print
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">echo print(\'hello\');</code></pre>
<p>What will be the output when running this script?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'print() affiche "hello" et retourne 1, puis echo affiche ce 1. Résultat: "hello1".',
                'resourceUrl' => 'http://php.net/print',
                'answers' => [
                    ['text' => '<pre><code>hello1</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>hello</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>hello5</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>hello0</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>hellotrue</code></pre>', 'correct' => false],
                ],
            ],

            // Q8 - Form - ChoiceType choice_loader
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which interface should be implemented when you want to set the <code>choice_loader</code> option of the <code>Symfony\Component\Form\Extension\Core\Type\ChoiceType</code> form type?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'interface ChoiceLoaderInterface se trouve dans le namespace Symfony\Component\Form\ChoiceList\Loader.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/forms/types/choice.html#choice-loader',
                'answers' => [
                    ['text' => '<code>Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface</code>', 'correct' => true],
                    ['text' => '<code>Symfony\Component\Form\ChoiceLoaderInterface</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Form\Extension\Core\ChoiceLoaderInterface</code>', 'correct' => false],
                ],
            ],

            // Q9 - Form - ChoiceType choice_attr
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which types are allowed for the <code>choice_attr</code> option of the <code>Symfony\Component\Form\Extension\Core\Type\ChoiceType</code> form type?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'option choice_attr accepte un tableau (array) ou un callable. Les types string, boolean, integer ne sont pas valides.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/reference/forms/types/choice.html#choice-attr',
                'answers' => [
                    ['text' => '<code>array</code>', 'correct' => true],
                    ['text' => '<code>callable</code>', 'correct' => true],
                    ['text' => '<code>string</code>', 'correct' => false],
                    ['text' => '<code>boolean</code>', 'correct' => false],
                    ['text' => '<code>integer</code>', 'correct' => false],
                ],
            ],

            // Q10 - HttpClient - cookies support
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'] ?? $subcategories['Symfony:Services'],
                'text' => 'How to configure the HTTP Client provided by the Symfony <code>HttpClient</code> component to save a Cookie between requests?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le composant HttpClient de Symfony ne gère pas automatiquement les cookies. Il faut utiliser BrowserKit ou gérer manuellement les headers.',
                'resourceUrl' => 'https://symfony.com/doc/6.0/http_client.html#cookies',
                'answers' => [
                    ['text' => 'The HTTP Client provided by the Symfony <code>HttpClient</code> component does not handle cookies.', 'correct' => true],
                    ['text' => 'Use <code>$client->cookies->set(new Cookie(...))</code>', 'correct' => false],
                    ['text' => 'Use <code>$client->getCookieJar()->set(new Cookie(...))</code>', 'correct' => false],
                    ['text' => 'Use <code>$client->setCookie(new Cookie(...))</code>', 'correct' => false],
                ],
            ],

            // Q11 - Twig - Operator precedence highest
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In Twig, which of the following operators has the <strong>highest</strong> precedence?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'opérateur pipe (|) pour les filtres a la plus haute précédence parmi les options listées.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/templates.html#expressions',
                'answers' => [
                    ['text' => '<code>|</code> (filters)', 'correct' => true],
                    ['text' => '<code>or</code>', 'correct' => false],
                    ['text' => '<code>==</code>', 'correct' => false],
                    ['text' => '<code><=></code>', 'correct' => false],
                    ['text' => '<code>in</code>', 'correct' => false],
                    ['text' => '<code>and</code>', 'correct' => false],
                ],
            ],

            // Q12 - Process - PhpProcess
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'] ?? $subcategories['Symfony:Services'],
                'text' => 'It is possible to run a PHP script in an independent process?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La classe PhpProcess permet d\'exécuter du code PHP dans un processus indépendant.',
                'resourceUrl' => 'https://symfony.com/doc/2.0/components/process.html',
                'answers' => [
                    ['text' => 'Yes, with the <code>Symfony\Component\Process\PhpProcess</code> class', 'correct' => true],
                    ['text' => 'Yes, with the <code>Symfony\Component\Process\PhpRunner</code> class', 'correct' => false],
                    ['text' => 'No, it\'s not possible.', 'correct' => false],
                    ['text' => 'Yes, with the <code>Symfony\Component\Process\PhpExec</code> class', 'correct' => false],
                ],
            ],

            // Q13 - Lock - TTL refresh
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Lock'] ?? $subcategories['Symfony:Services'],
                'text' => 'Given a lock set to be released after 60 seconds, is there any way to refresh this TTL?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la méthode refresh() permet de prolonger la durée de vie d\'un lock.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/components/lock.html#expiring-locks',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q14 - DI - Autowiring exception
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Assuming both transformers below implement <code>Acme\Transformer\TransformerInterface</code>, will the following code throws an exception?
<pre><code class="language-yaml">services:
    transformer_html:
        class:    Acme\Transformer\HtmlTransformer
    transformer_text:
        class:    Acme\Transformer\TextTransformer
    twitter_client:
        class:    Acme\Model\TwitterClient
        autowire: true</code></pre>
<pre><code class="language-php">namespace Acme\Model;

use Acme\Transformer\TransformerInterface;

class TwitterClient
{
    private $transformer;

    public function __construct(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }
    // ...
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, une exception sera lancée car l\'autowiring ne peut pas déterminer quelle implémentation de TransformerInterface utiliser quand il y en a plusieurs.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/dependency_injection/autowiring.html#dealing-with-multiple-implementations-of-the-same-type',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q15 - HttpFoundation - Session migration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could the session be migrated?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la méthode migrate() permet de régénérer l\'ID de session tout en conservant les données.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/HttpFoundation/Session/Session.php#L171',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q16 - ErrorHandler
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:ErrorHandler'] ?? $subcategories['Symfony:Services'],
                'text' => 'Given the following code in a controller:
<pre><code class="language-php">use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ErrorHandler\ErrorHandler;

class MyController
{
  public function displayContent(): Response
  {
    $content = ErrorHandler::call(\'file_get_content\', \'/my-inexistent-file.txt\');

    return new Response($content);
  }
}</code></pre>
<p>What will be displayed if <code>/my-inexistent-file.txt</code> doesn\'t exist?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ErrorHandler::call() convertit les erreurs PHP en exceptions. Si le fichier n\'existe pas, une exception sera lancée.',
                'resourceUrl' => 'https://github.com/symfony/error-handler/blob/dc432104fe98d79edcdd305312e4494956ce47ad/ErrorHandler.php#L159',
                'answers' => [
                    ['text' => 'An exception will be thrown', 'correct' => true],
                    ['text' => 'An empty page will be displayed and an exception will be visible in the profiler', 'correct' => false],
                    ['text' => 'An empty page will be displayed', 'correct' => false],
                ],
            ],

            // Q17 - Twig - with tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given that Twig is configured with "strict_variables" set to true.
<p>Consider the following Twig snippet:</p>
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
                'explanation' => 'Non, le tag with crée une portée locale. Les variables définies à l\'intérieur ne sont pas accessibles à l\'extérieur. maxItems sera indéfini dans la boucle for.',
                'resourceUrl' => 'http://twig.symfony.com/doc/tags/with.html',
                'answers' => [
                    ['text' => 'No. The template will display an error because the <code>maxItems</code> variable is not defined outside the <code>with</code> tag.', 'correct' => true],
                    ['text' => 'No. The template will display an error because the <code>with</code> tag is not defined.', 'correct' => false],
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No. The template won\'t iterate from <code>1</code> to <code>7</code>. It will execute the <code>for</code> loop just one time (where <code>i</code> is <code>1</code>).', 'correct' => false],
                ],
            ],

            // Q18 - DI - Tags
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which mechanism allows to aggregate services by domain in the service container?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Les tags permettent de regrouper des services par domaine fonctionnel et de les récupérer ensemble via un compiler pass.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/dependency_injection/tags.html',
                'answers' => [
                    ['text' => 'Tag', 'correct' => true],
                    ['text' => 'Scope', 'correct' => false],
                    ['text' => 'Abstraction', 'correct' => false],
                    ['text' => 'Listener', 'correct' => false],
                ],
            ],

            // Q19 - HttpFoundation - FlashBag clear
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could a <code>FlashBag</code> be cleared?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, la méthode clear() permet de vider tous les messages du FlashBag.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/HttpFoundation/Session/Flash/FlashBag.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q20 - HttpFoundation - getPathInfo
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'For a request to <code>http://example.com/blog/index.php/post/hello-world</code>, what will be the value of <code>$pathInfo</code> in the following code?
<pre><code class="language-php">$pathInfo = $request->getPathInfo();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'getPathInfo() retourne la partie du chemin après le front controller (index.php), donc /post/hello-world.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#identifying-a-request',
                'answers' => [
                    ['text' => '<code>/post/hello-world</code>', 'correct' => true],
                    ['text' => '<code>/blog/index.php/post/hello-world</code>', 'correct' => false],
                    ['text' => '<code>/index.php/post/hello-world</code>', 'correct' => false],
                    ['text' => '<code>example.com/blog/index.php/post/hello-world</code>', 'correct' => false],
                ],
            ],

            // Q21 - Validator - Constraint errorNames
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Does using <code>Constraint::$errorNames</code> considered as a best practice?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, depuis Symfony 6.1, $errorNames est déprécié. Il faut utiliser ERROR_NAMES à la place.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.1.md#validator',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q22 - DI - ContainerBuilder willBeAvailable
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could the fact that a class is available and will remain available in the <code>--no-dev</code> mode of Composer be obtained when using <code>ContainerBuilder</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ContainerBuilder::willBeAvailable() permet de vérifier si une classe sera disponible même en mode --no-dev.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/DependencyInjection/ContainerBuilder.php#L1454',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q23 - PHP - PHP_FLOAT_EPSILON
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Data Format & Types'] ?? $subcategories['PHP:PHP Basics'],
                'text' => 'How is called the PHP constant representing the smallest possible number <code>n</code>, so that <code>1.0 + n != 1.0</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP_FLOAT_EPSILON représente la plus petite valeur positive x telle que x + 1.0 != 1.0.',
                'resourceUrl' => 'https://www.php.net/manual/en/reserved.constants.php',
                'answers' => [
                    ['text' => '<code>PHP_FLOAT_EPSILON</code>', 'correct' => true],
                    ['text' => '<code>PHP_FLOAT_MIN</code>', 'correct' => false],
                    ['text' => '<code>PHP_FLOAT_SMALLEST</code>', 'correct' => false],
                    ['text' => '<code>PHP_FLOAT_DIG</code>', 'correct' => false],
                ],
            ],

            // Q24 - Twig - FilesystemCache
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given the case where the opcache/APC cache for template need to be invalidated, is the following code valid?
<pre><code class="language-php">&lt;?php

// ...

$twig = new Environment($loader, [
    \'cache\' => new FilesystemCache(\'/some/cache/path\', 1),
    // ...
]);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le second paramètre de FilesystemCache permet de forcer l\'invalidation du cache opcache/APC.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/recipes.html#refreshing-modified-templates-when-opcache-or-apc-is-enabled',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q25 - HttpFoundation - getPayload
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What can we say about the <code>Symfony\Component\HttpFoundation\Request::getPayload</code> method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'getPayload() est une alternative à $request->request pour accéder aux données soumises, mais fonctionne avec JSON et form data.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-request-payload',
                'answers' => [
                    ['text' => 'It\'s an alternative method to <code>$request->request</code>', 'correct' => true],
                    ['text' => 'It returns the request\'s body', 'correct' => false],
                    ['text' => 'It\'s a shortcut for <code>$request->query->all()</code>', 'correct' => false],
                ],
            ],

            // Q26 - Form - DataTransformer inherit_data
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Are Data Transformers applied on a form field which has the <code>inherit_data</code> option set?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, les Data Transformers ne sont pas appliqués sur les champs avec inherit_data car ces champs n\'ont pas leurs propres données.',
                'resourceUrl' => 'http://symfony.com/doc/current/form/data_transformers.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q27 - Routing - Extra parameters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Given the following definition of the <code>book_list</code> route, what will be the value of the variable <code>$url</code>?
<pre><code class="language-yaml"># app/config/routing.yml
book_list:
    path:     /books
    defaults: { _controller: AppBundle:Default:list }
    methods:  [POST]</code></pre>
<pre><code class="language-php"> // src/AppBundle/Controller/HomeController.php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller {

    public function indexAction() {
        $url = $this->generateUrl(\'book_list\', [\'page\' => 1]);
        // ...
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les paramètres supplémentaires non définis dans la route sont ajoutés comme query string.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/routing.html#generating-urls-with-query-strings',
                'answers' => [
                    ['text' => '/books?page=1', 'correct' => true],
                    ['text' => 'https://example.com/books?_page=1', 'correct' => false],
                    ['text' => 'Error: Parameter "page" is not defined.', 'correct' => false],
                    ['text' => '/books?_page=1', 'correct' => false],
                    ['text' => 'https://example.com/books?page=1', 'correct' => false],
                ],
            ],

            // Q28 - Form - RepeatedType
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What solution can you use to ask the user to type his password twice in a form?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'RepeatedType crée deux champs qui doivent avoir la même valeur, parfait pour la confirmation de mot de passe.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/forms/types/repeated.html',
                'answers' => [
                    ['text' => 'Use the <code>RepeatedType</code> form type.', 'correct' => true],
                    ['text' => 'Use the <strong>Validation</strong> plugin of <strong>jQuery</strong>.', 'correct' => false],
                    ['text' => 'Call the <code>render_widget</code> twig function twice on the password form type.', 'correct' => false],
                    ['text' => 'Use the <code>ask_confirmation</code> option on the <code>PasswordType</code> form type.', 'correct' => false],
                ],
            ],

            // Q29 - VarDumper - Cloners
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarDumper'] ?? $subcategories['Symfony:Services'],
                'text' => 'What is a <strong>Cloner</strong>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un Cloner crée une représentation intermédiaire de n\'importe quelle variable PHP qui peut ensuite être affichée par un Dumper.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/var_dumper/advanced.html#cloners',
                'answers' => [
                    ['text' => 'A cloner is used to create an intermediate representation of any PHP variable.', 'correct' => true],
                    ['text' => 'A cloner is used to create a <code>var_dump</code> of any PHP variable.', 'correct' => false],
                    ['text' => 'A cloner is used to create a <code>var_export</code> of any PHP variable.', 'correct' => false],
                    ['text' => 'A cloner is used to create an ReflectionClass of any PHP object.', 'correct' => false],
                    ['text' => 'A cloner is used to create an clone of any PHP object.', 'correct' => false],
                ],
            ],

            // Q30 - HttpFoundation - FlashBag setAll
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could all <code>FlashBag</code> messages be overridden?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, la méthode setAll() permet de remplacer tous les messages du FlashBag.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/HttpFoundation/Session/Flash/FlashBag.php#L127',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q31 - PHP - STDIN STDOUT STDERR
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'When writing <em>CLI</em> scripts, how can you access the standard input/output/error streams?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'En mode CLI, PHP définit automatiquement les constantes STDIN, STDOUT et STDERR.',
                'resourceUrl' => 'http://php.net/manual/en/features.commandline.php',
                'answers' => [
                    ['text' => 'Use <code>STDIN</code>, <code>STDOUT</code> and <code>STDERR</code> constants', 'correct' => true],
                    ['text' => 'use <code>FD_0</code>, <code>FD_1</code> and <code>FD_2</code> constants', 'correct' => false],
                    ['text' => 'Use <code>php::STDIN</code>, <code>php::STDOUT</code>, <code>php::STDERR</code> class constants', 'correct' => false],
                    ['text' => 'Use <code>stdin()</code>, <code>stdout()</code> and <code>stderr()</code> functions', 'correct' => false],
                ],
            ],

            // Q32 - Twig - Operator precedence lowest
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In Twig, which of the following operators has the <em>lowest</em> precedence?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'opérateur "and" a une précédence plus basse que les opérateurs de comparaison (==, !=, <=).',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/templates.html#expressions',
                'answers' => [
                    ['text' => '<code>and</code>', 'correct' => true],
                    ['text' => '<code>!=</code>', 'correct' => false],
                    ['text' => '<code>==</code>', 'correct' => false],
                    ['text' => '<code><=</code>', 'correct' => false],
                ],
            ],
        ];
    }
}
