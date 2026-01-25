<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 30
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures30 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures29::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Symfony or PHP category not found. Please load AppFixtures first.');
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

            // Q2 - PHP Basics - Cryptographically secure random integer
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Which native function should you use to generate a cryptographically secure random integer?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'random_int() génère un entier aléatoire cryptographiquement sécurisé. mt_rand(), rand() et lcg_value() ne sont pas cryptographiquement sécurisés.',
                'resourceUrl' => 'https://www.php.net/manual/en/ref.random.php',
                'answers' => [
                    ['text' => '<code>random_int()</code>', 'correct' => true],
                    ['text' => '<code>mt_rand()</code>', 'correct' => false],
                    ['text' => '<code>lcg_value()</code>', 'correct' => false],
                    ['text' => '<code>rand()</code>', 'correct' => false],
                ],
            ],

            // Q5 - FrameworkBundle - FormExtension usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the prerequisite to create a <code>FormTypeExtension</code>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour créer une FormTypeExtension, il faut créer un service avec le tag form.type_extension. L\'extension peut implémenter FormTypeExtensionInterface ou étendre AbstractTypeExtension.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/form/create_form_type_extension.html#defining-the-form-type-extension, https://symfony.com/doc/4.2/form/create_form_type_extension.html#registering-your-form-type-extension-as-a-service',
                'answers' => [
                    ['text' => 'Creating a service with the <code>form.type_extension</code> tag', 'correct' => true],
                    ['text' => 'Extending the <code>Symfony\Component\Form\AbstractTypeExtension</code>', 'correct' => false],
                    ['text' => 'Implementing the interface <code>Symfony\Component\Form\FormTypeExtensionInterface</code>', 'correct' => false],
                    ['text' => 'Putting the new <code>MyFormExtension</code> class in the <code>Form\Extension</code> namespace', 'correct' => false],
                ],
            ],

            // Q6 - HttpCache - Server Side Includes usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpCache'],
                'text' => 'Could <code>Server Side Includes</code> be used in Symfony?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, Symfony supporte les Server Side Includes (SSI) pour améliorer les performances du cache HTTP.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/http_cache/ssi.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q8 - SensioFrameworkExtraBundle - ParamConverterListener
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'Which event does the <code>ParamConverterListener</code> listen to ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le ParamConverterListener écoute l\'événement kernel.controller pour convertir les paramètres de route en objets.',
                'resourceUrl' => 'https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/2.3/EventListener/ParamConverterListener.php',
                'answers' => [
                    ['text' => 'kernel.controller', 'correct' => true],
                    ['text' => 'kernel.request', 'correct' => false],
                    ['text' => 'kernel.controller_arguments', 'correct' => false],
                ],
            ],

            // Q9 - HTTP - Validation model
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which HTTP headers can be used with the validation model?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le modèle de validation HTTP utilise les en-têtes ETag et Last-Modified pour vérifier si le contenu a changé.',
                'resourceUrl' => 'https://symfony.com/doc/6.0/http_cache/validation.html',
                'answers' => [
                    ['text' => '<code>ETag</code>', 'correct' => true],
                    ['text' => '<code>Last-Modified</code>', 'correct' => true],
                    ['text' => '<code>Expires</code>', 'correct' => false],
                    ['text' => '<code>Cache-Control</code>', 'correct' => false],
                ],
            ],

            // Q10 - DI - Service decoration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could services be decorated using an attribute?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 6.1, il est possible de décorer des services en utilisant l\'attribut #[AsDecorator].',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-1-service-decoration-attributes',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q11 - HTTP - Cache-Control directives
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which are the directives that can be found in the <code>Cache-Control</code> header?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les directives no-cache, no-store et max-age sont des directives valides de l\'en-tête Cache-Control.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2616#section-14.9',
                'answers' => [
                    ['text' => '<code>no-cache</code>', 'correct' => true],
                    ['text' => '<code>no-store</code>', 'correct' => true],
                    ['text' => '<code>max-age</code>', 'correct' => true],
                    ['text' => '<code>no-rule</code>', 'correct' => false],
                    ['text' => '<code>no-limit</code>', 'correct' => false],
                ],
            ],

            // Q12 - HttpFoundation - Status code
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What is the default status code of a <code>Symfony\Component\HttpFoundation\Response</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le code de statut par défaut d\'un objet Response est 200 (OK).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/HttpFoundation/Response.php#L127',
                'answers' => [
                    ['text' => '200', 'correct' => true],
                    ['text' => '201', 'correct' => false],
                    ['text' => '202', 'correct' => false],
                    ['text' => '204', 'correct' => false],
                    ['text' => '400', 'correct' => false],
                ],
            ],

            // Q13 - Cache - Pools debug
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Could the available pools list be displayed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la liste des pools disponibles peut être affichée avec la commande "php bin/console cache:pool:list".',
                'resourceUrl' => 'https://symfony.com/doc/4.3/cache.html#clearing-the-cache',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q16 - FrameworkBundle - EarlyHints
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'Will the resources sent as early hints be loaded faster for the current page ?
<pre><code class="language-php">namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\WebLink\Link;

class HomepageController extends AbstractController
{
    #[Route("/", name: "homepage")]
    public function index(): Response
    {
        $this->sendEarlyHints([
            new Link(rel: \'preconnect\', href: \'https://fonts.google.com\'),
            (new Link(href: \'/main.css\'))->withAttribute(\'as\', \'stylesheet\'),
            (new Link(href: \'/app.js\'))->withAttribute(\'as\', \'script\'),
        ]);

        // prepare the contents of the response...

        return $this->render(\'homepage/index.html.twig\');
    }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'Non, car le résultat de sendEarlyHints() (un objet Response) doit être passé à la méthode render() pour que les hints soient effectivement envoyés.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-early-hints',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q17 - Doctrine DBAL - API
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'In which of these cases will <code>$result</code> contain the number of affected rows?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'executeUpdate() et delete() retournent le nombre de lignes affectées. executeQuery() retourne un ResultStatement et execute() sur un Statement retourne true/false.',
                'resourceUrl' => 'https://github.com/doctrine/dbal/blob/71140662c0a954602e81271667b6e03d9f53ea34/lib/Doctrine/DBAL/Connection.php#L975, https://github.com/doctrine/dbal/blob/71140662c0a954602e81271667b6e03d9f53ea34/lib/Doctrine/DBAL/Connection.php#L577, https://github.com/doctrine/dbal/blob/71140662c0a954602e81271667b6e03d9f53ea34/lib/Doctrine/DBAL/Connection.php#L810, https://github.com/doctrine/dbal/blob/71140662c0a954602e81271667b6e03d9f53ea34/lib/Doctrine/DBAL/Connection.php#L778',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$result = $conn->executeUpdate(\'DELETE FROM user WHERE id = 1\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$result = $conn->delete(\'user\', [\'id\' => 1]);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$result = $conn->executeQuery(\'DELETE FROM user WHERE id = 1\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$result = $conn->prepare(\'DELETE FROM user WHERE id = 1\')->execute();</code></pre>', 'correct' => false],
                ],
            ],

            // Q18 - DI - ContainerConfigurator usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could an <code>Expression</code> be configured using <code>ContainerConfigurator</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ContainerConfigurator fournit une méthode expr() pour créer des expressions.',
                'resourceUrl' => 'https://github.com/symfony/dependency-injection/blob/3.4/Loader/Configurator/ContainerConfigurator.php#L125',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q19 - VarExporter - Lazy object usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarExporter'],
                'text' => 'Could attributes be skipped when initializing a lazy object?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le LazyGhostTrait permet de spécifier des propriétés à ignorer lors de l\'initialisation avec le paramètre $skippedProperties.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/VarExporter/LazyGhostTrait.php#L23',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q20 - Filesystem - Directory copy
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Which method can be used to copy a whole directory to a new one?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode mirror() du composant Filesystem permet de copier un répertoire entier vers un nouveau répertoire.',
                'resourceUrl' => 'https://symfony.com/doc/2.1/components/filesystem.html#mirror',
                'answers' => [
                    ['text' => '<code>mirror</code>', 'correct' => true],
                    ['text' => '<code>copy</code>', 'correct' => false],
                    ['text' => '<code>symlink</code>', 'correct' => false],
                    ['text' => 'It\'s not possible', 'correct' => false],
                ],
            ],

            // Q21 - PHP Functions - String modification
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Functions'],
                'text' => 'Given the following code, what will be displayed?
<pre><code class="language-php">&lt;?php

$values = strip_tags(\'&lt;section&gt;&lt;a href="foo.html"&gt;Bar&lt;/a&gt;&lt;/section&gt;\', [\'&lt;a&gt;\', \'&lt;/a&gt;\']);

echo $values;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'strip_tags() avec le second paramètre accepte un tableau de tags autorisés depuis PHP 7.4. Seul le tag <a> sera conservé.',
                'resourceUrl' => 'https://www.php.net/manual/en/function.strip-tags.php',
                'answers' => [
                    ['text' => '<code>&lt;a href="foo.html"&gt;Bar&lt;/a&gt;</code>', 'correct' => true],
                    ['text' => '<code>&lt;section&gt;&lt;/section&gt;</code>', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                    ['text' => 'An empty string', 'correct' => false],
                ],
            ],

            // Q23 - DI - Attributes usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could custom attributes be registered for autoconfiguring annotated classes?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ContainerBuilder::registerAttributeForAutoconfiguration() permet d\'enregistrer des attributs personnalisés.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/DependencyInjection/ContainerBuilder.php#L1309',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q24 - Process - Process stop
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'],
                'text' => 'Given <code>$process</code> is a <code>Process</code> object that runs a command asynchronously; calling <code>$process->stop(3)</code> will immediately send a <code>SIGKILL</code> signal to the running command.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Faux. stop() avec un timeout envoie d\'abord SIGTERM, puis attend le timeout avant d\'envoyer SIGKILL si le processus ne s\'est pas arrêté.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Process/Process.php#L636',
                'answers' => [
                    ['text' => 'False', 'correct' => true],
                    ['text' => 'True', 'correct' => false],
                ],
            ],

            // Q26 - Twig - Assertions
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given an object <code>Foo</code> which implements <code>\Countable</code> and the method <code>count()</code> which return <code>1</code>, what will be displayed?
<pre><code class="language-twig">{% if foo is empty %}
    {{ foo.get(\'name\') }}
{% endif %}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le test "empty" en Twig utilise count() pour les objets Countable. Comme count() retourne 1, l\'objet n\'est pas vide et rien ne sera affiché.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tests/empty.html',
                'answers' => [
                    ['text' => 'Nothing', 'correct' => true],
                    ['text' => 'The value of <code>foo.get(\'name\')</code>', 'correct' => false],
                ],
            ],

            // Q27 - HttpFoundation - IP address
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could an IP address be anonymized?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la classe IpUtils fournit une méthode anonymize() pour anonymiser les adresses IP.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/components/http_foundation.html#anonymizing-ip-addresses, https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/HttpFoundation/IpUtils.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q28 - DI - Public or Private
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'By default, registered services are private?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Vrai. Depuis Symfony 4.0, les services sont privés par défaut.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.0/src/Symfony/Component/DependencyInjection/Definition.php',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // Q30 - Config - Validation Rules
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'Which of the following are validation rules provided by <code>Symfony\Component\Config\Definition\Builder\ExprBuilder</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ExprBuilder fournit les règles de validation: always(), ifTrue(), ifString(), ifNull(), ifEmpty(), ifArray(), ifInArray(), ifNotInArray().',
                'resourceUrl' => 'https://symfony.com/doc/2.3/components/config/definition.html#validation-rules',
                'answers' => [
                    ['text' => '<pre><code class="language-php">ifTrue()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">ifString()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">always()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">ifInArray()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">ifNotInArray()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">ifNull()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">ifArray()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">never()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">ifNotNull()</code></pre>', 'correct' => false],
                ],
            ],

            // Q32 - Templating - Global variables
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Is a service defined as a global variable lazy-loaded?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, les services définis comme variables globales ne sont pas chargés paresseusement. Ils sont instanciés à chaque fois qu\'ils sont utilisés dans un template.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/templating/global_variables.html#referencing-services',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q34 - Console - Console events
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'What are the console events?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les événements de console sont: COMMAND, TERMINATE, ERROR et SIGNAL (ajouté en Symfony 5.2).',
                'resourceUrl' => 'https://symfony.com/doc/5.3/components/console/events.html, https://github.com/symfony/symfony/blob/5.2/src/Symfony/Component/Console/ConsoleEvents.php',
                'answers' => [
                    ['text' => '<code>ConsoleEvents::COMMAND</code>', 'correct' => true],
                    ['text' => '<code>ConsoleEvents::TERMINATE</code>', 'correct' => true],
                    ['text' => '<code>ConsoleEvents::ERROR</code>', 'correct' => true],
                    ['text' => '<code>ConsoleEvents::SIGNAL</code>', 'correct' => true],
                    ['text' => '<code>ConsoleEvents::START</code>', 'correct' => false],
                    ['text' => '<code>ConsoleEvents::EXCEPTION</code>', 'correct' => false],
                    ['text' => '<code>ConsoleEvents::FINISH</code>', 'correct' => false],
                ],
            ],
        ];
    }
}
