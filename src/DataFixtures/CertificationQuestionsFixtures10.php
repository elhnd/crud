<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 10
 */
class CertificationQuestionsFixtures10 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['certification', 'questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures9::class];
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
                'HttpCache' => 'HTTP Cache component (ESI, SSI)',
                'Clock' => 'Clock component for time management',
                'Process' => 'Process component for running external processes',
                'VarDumper' => 'VarDumper component for debugging',
            ],
            'PHP' => [
                'PHP 8' => 'PHP 8 features and syntax',
                'Arrays' => 'PHP Arrays',
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
            // Q1 - PHP Named arguments as array
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP 8'] ?? $subcategories['PHP:OOP'],
                'text' => 'Is the following code correct?
<pre><code class="language-php">class Foo
{
    public function qux(): void
    {
        $args = [
            \'secondArgument\' => \'arg\',
            \'firstArgument\' => true,
        ];

        $this->bar(...$args);
    }

    private function bar(bool $firstArgument, string $secondArgument): void
    {
        // ...
    }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis PHP 8.0, on peut utiliser le spread operator (...) avec un tableau associatif pour passer des arguments nommés. L\'ordre dans le tableau n\'a pas d\'importance car les clés correspondent aux noms des paramètres.',
                'resourceUrl' => 'https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q2 - PHP list() construct
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'] ?? $subcategories['PHP:OOP'],
                'text' => 'The <code>___________</code> language construct is particularly useful to assign your own variable names to values within an array.',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La construction list() permet d\'assigner des valeurs d\'un tableau à des variables en une seule opération. Exemple: list($a, $b) = [1, 2];',
                'resourceUrl' => 'https://www.php.net/manual/en/function.list.php',
                'answers' => [
                    ['text' => 'import_variables()', 'correct' => false],
                    ['text' => 'list()', 'correct' => true],
                    ['text' => 'array_get_variables()', 'correct' => false],
                    ['text' => 'each()', 'correct' => false],
                    ['text' => 'current()', 'correct' => false],
                ],
            ],

            // Q3 - Service Configurator
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'What is a "Service Configurator" in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un Service Configurator est un callable PHP qui peut être exécuté pour configurer un service Symfony après son instanciation. Utile pour une configuration complexe qui ne peut pas être faite via l\'injection de dépendances.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/configurators.html',
                'answers' => [
                    ['text' => 'There\'s no such a thing in Symfony\'s Dependency Injection component.', 'correct' => false],
                    ['text' => 'It\'s a feature of the Dependency Injection component that allows to apply some configuration logic to all the services that define a specific tag.', 'correct' => false],
                    ['text' => 'It\'s a PHP callable that you can optionally execute to configure a Symfony service after its instantiation.', 'correct' => true],
                    ['text' => 'It\'s a Symfony built-in service that can be obtained as $container->get(\'configurator\')', 'correct' => false],
                ],
            ],

            // Q4 - SSI render_ssi
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpCache'] ?? $subcategories['Symfony:Cache'],
                'text' => 'Is the following code valid when using <code>Server Side Includes</code>?
<pre><code class="language-twig">{{ render_ssi(controller(\'App\\Controller\\ProfileController::gdpr\')) }}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la fonction render_ssi() est valide pour les Server Side Includes (SSI). Elle permet de rendre un fragment de page via SSI.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_cache/ssi.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - Form Extension
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'How to add an extension <code>MyForm</code> to the <code>Form</code> component?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour ajouter une extension au composant Form, on utilise addExtension() sur le FormFactoryBuilder. La méthode prend directement l\'instance de l\'extension sans nom de type.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/form.html',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(\'text\', new MyFormExtension())
    ->getFormFactory();</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$formFactory = Forms::createFormFactoryBuilder()
    ->add(new MyFormExtension())
    ->getFormFactory();</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new MyFormExtension())
    ->getFormFactory();</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$formFactory = Forms::createFormFactoryBuilder()
    ->registerExtension(new MyFormExtension())
    ->getFormFactory();</code></pre>', 'correct' => false],
                ],
            ],

            // Q7 - Container build hash
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP Kernel'] ?? $subcategories['Symfony:Services'],
                'text' => 'Could the build hash of the container be configured?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Le paramètre container.build_hash est exposé mais ne peut pas être configuré. Le hash est obtenu via ContainerBuilder::hash() pendant la phase de compilation/dump du conteneur.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/configuration/kernel.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q8 - Import parameters with kernel.root_dir
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'Is this code valid?
<pre><code class="language-yaml"># app/config/config.yml
imports:
    - { resource: "%kernel.root_dir%/parameters.yml" }</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, kernel.root_dir a été déprécié dans Symfony 4. De plus, les paramètres ne peuvent pas être utilisés dans la section imports car ils ne sont pas encore résolus à ce stade.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q9 - Routing query parameters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Given the following routing configuration:
<pre><code class="language-php">class BlogController
{
    #[Route("/blog/articles", name: "blog_articles")]
    public function listArticles(int $page)
    {
        // ...
    }
}</code></pre>

Does the path <code>/blog/articles?page=1</code> display the page without error?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, les paramètres de query string (?page=1) sont automatiquement injectés dans les paramètres du contrôleur si le type correspond. Symfony convertit "1" en int.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q10 - Serializer denormalize
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Given the following denormalization attempt:
<pre><code class="language-php">class ValueObject
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

echo $vo->getFoo();</code></pre>

What will be displayed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le GetSetMethodNormalizer utilise les paramètres du constructeur pour dénormaliser. Comme le constructeur attend $bar, la valeur "symfony" sera passée et stockée dans $foo.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/serializer.html',
                'answers' => [
                    ['text' => 'Nothing, an exception will be thrown', 'correct' => false],
                    ['text' => '"symfony"', 'correct' => true],
                    ['text' => 'An empty string', 'correct' => false],
                ],
            ],

            // Q11 - Console Cursor
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Could the output be cleaned using the <code>Cursor</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, la classe Cursor du composant Console permet de nettoyer l\'écran avec des méthodes comme clearScreen(), clearLine(), etc.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/console/helpers/cursor.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q12 - Security firewall event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the event a firewall must be registered on?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les listeners du firewall doivent être enregistrés sur l\'événement kernel.request pour intercepter les requêtes avant qu\'elles n\'atteignent le contrôleur.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/security/firewall.html',
                'answers' => [
                    ['text' => 'kernel.exception', 'correct' => false],
                    ['text' => 'kernel.response', 'correct' => false],
                    ['text' => 'kernel.controller', 'correct' => false],
                    ['text' => 'security.interactive_login', 'correct' => false],
                    ['text' => 'security.authentication.success', 'correct' => false],
                    ['text' => 'kernel.request', 'correct' => true],
                ],
            ],

            // Q13 - Form fields mapping callbacks
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Could values be mapped to fields using callbacks?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 5.2, on peut mapper les champs de formulaire en utilisant des callbacks via les options getter et setter.',
                'resourceUrl' => 'https://symfony.com/doc/current/form/data_mappers.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q14 - VarDumper destination
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarDumper'] ?? $subcategories['Symfony:Console'],
                'text' => 'Could the destination of the dump be customized?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, le VarDumper permet de personnaliser la destination du dump via le dump server ou en configurant un handler personnalisé.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/var_dumper.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q15 - Process waitUntil
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'] ?? $subcategories['Symfony:Console'],
                'text' => 'What does the <code>waitUntil</code> method allow you to do in the Process component?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode waitUntil() permet d\'attendre qu\'une condition soit vérifiée avant de continuer l\'exécution du script principal.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/process.html',
                'answers' => [
                    ['text' => 'To wait for a condition to be verified before continuing the main script execution', 'correct' => true],
                    ['text' => 'To wait for a condition to be verified before killing the running async process', 'correct' => false],
                    ['text' => 'To wait for a certain amount of time before killing the running async process', 'correct' => false],
                ],
            ],

            // Q16 - Twig functions at runtime
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Could functions and filters be defined at runtime without any overhead?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, Twig permet de définir des fonctions et filtres à la volée en utilisant registerUndefinedFunctionCallback et registerUndefinedFilterCallback.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/recipes.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q17 - Twig escaping with raw then escape
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which will be the output of the following code?
<pre><code class="language-twig">{% set twig = \'&lt;h1&gt;Hello from Twig&lt;/h1&gt;\' %}

{{ twig|raw|escape(\'html\') }}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le filtre raw annule l\'échappement automatique, mais escape(\'html\') réapplique l\'échappement HTML. Le résultat sera donc les entités HTML échappées: &lt;h1&gt;Hello from Twig&lt;/h1&gt;',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/filters/escape.html',
                'answers' => [
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '&lt;h1&gt;Hello from Twig&lt;/h1&gt; (escaped HTML entities)', 'correct' => true],
                    ['text' => '<h1>Hello from Twig</h1> (rendered as HTML)', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $qData) {
            $this->upsertQuestion($manager, $qData);
        }

        $manager->flush();
    }
}
