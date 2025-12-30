<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 14
 */
class CertificationQuestionsFixtures14 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures13::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found. Run AppFixtures first.');
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
            // Q1 - DI - Compiler Passes priority
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'] ?? $subcategories['Symfony:Services'],
                'text' => 'Could a priority be set when adding a new compiler pass?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 4.1, vous pouvez définir une priorité lors de l\'ajout d\'un compiler pass via la méthode addCompilerPass(). Plus la priorité est élevée, plus tôt le pass sera exécuté.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/components/dependency_injection/compilation.html#controlling-the-pass-ordering, https://github.com/symfony/symfony/blob/4.1/src/Symfony/Component/DependencyInjection/Compiler/PassConfig.php#L113',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q2 - PHP - Invalid callable syntax
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Which syntax is not a valid callable syntax?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La syntaxe "foo->bar" n\'est pas un callable valide. Les syntaxes valides incluent: les closures, les noms de fonctions en string, les méthodes statiques "Foo::bar", et les tableaux [\'foo\', \'bar\'] ou [$object, \'method\'].',
                'resourceUrl' => 'http://php.net/manual/en/language.types.callable.php',
                'answers' => [
                    ['text' => '<code>function () { }</code>', 'correct' => false],
                    ['text' => '<code>"foo"</code>', 'correct' => false],
                    ['text' => '<code>"Foo::bar"</code>', 'correct' => false],
                    ['text' => '<code>"foo->bar"</code>', 'correct' => true],
                    ['text' => '<code>[\'foo\', \'bar\']</code>', 'correct' => false],
                ],
            ],

            // Q4 - Event Dispatcher - Debug partial names
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Could the listeners be debugged via partial event names?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 5.3, la commande debug:event-dispatcher accepte des noms d\'événements partiels pour filtrer les listeners affichés.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/event_dispatcher.html#debugging-event-listeners',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - Cache - saveDeferred
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'] ?? $subcategories['Symfony:Services'],
                'text' => 'Sometimes you may prefer to not save the objects immediately in order to increase the application performance. Which method would you call to mark cache items as "ready to be persisted" and then call to <code>commit()</code> method when you are ready to persist them all?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode saveDeferred() marque un item de cache comme "prêt à être persisté" sans l\'enregistrer immédiatement. Ensuite, commit() persiste tous les items différés en une seule opération.',
                'resourceUrl' => 'https://symfony.com/doc/3.x/components/cache/cache_pools.html#saving-cache-items, https://github.com/symfony/cache/blob/3.1/Adapter/AbstractAdapter.php#L278',
                'answers' => [
                    ['text' => '<code>defer()</code>', 'correct' => false],
                    ['text' => '<code>save()</code>', 'correct' => false],
                    ['text' => '<code>saveDeferred()</code>', 'correct' => true],
                    ['text' => '<code>persistDeferred()</code>', 'correct' => false],
                ],
            ],

            // Q7 - DI - Service Alias
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'] ?? $subcategories['Symfony:Services'],
                'text' => 'With the following service definition how is it possible to create an alias of the <code>foo</code> service?
<pre><code class="language-yaml">services:
    foo:
        class: Example\Foo</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour créer un alias d\'un service, vous pouvez soit utiliser la syntaxe raccourcie bar: \'@foo\', soit définir un nouveau service avec la clé alias pointant vers le service original.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/service_container/alias_private.html#aliasing',
                'answers' => [
                    ['text' => '<pre><code class="language-yaml">services:
    foo:
        class: Example\Foo
        alias: bar</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-yaml">services:
    foo:
        class: Example\Foo
        alias: [bar]</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-yaml">services:
    foo:
        class: Example\Foo
    bar: \'@foo\'</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-yaml">services:
    foo:
        class: Example\Foo
    bar:
        alias: foo</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-yaml">services:
    foo:
        class: Example\Foo
        alias:
            - bar</code></pre>', 'correct' => false],
                ],
            ],

            // Q8 - Translation - LocaleSwitcher
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'] ?? $subcategories['Symfony:Services'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

use Symfony\Component\Translation\LocaleSwitcher;

class SomeClass
{
    public function __construct(
        private LocaleSwitcher $localeSwitcher,
    ) {}

    public function someMethod(): void
    {
        $this->localeSwitcher->runWithLocale(\'es\', function() {
            // ...
        });

        // ...
    }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 6.1, LocaleSwitcher permet d\'exécuter du code dans un contexte de locale temporaire via la méthode runWithLocale(). La locale est restaurée automatiquement après l\'exécution du callback.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-1-locale-switcher',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q9 - ErrorHandler
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:ErrorHandler'] ?? $subcategories['Symfony:Services'],
                'text' => 'What is the purpose of <code>Symfony\Component\ErrorHandler\ErrorHandler</code> class?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ErrorHandler intercepte les erreurs PHP et les convertit en exceptions ErrorException. Il ne gère pas directement les réponses HTTP - c\'est le rôle de ExceptionHandler.',
                'resourceUrl' => 'https://github.com/symfony/error-handler/blob/5.4/ErrorHandler.php',
                'answers' => [
                    ['text' => 'Catches PHP errors and converts them to exceptions', 'correct' => true],
                    ['text' => 'Catches PHP errors and converts them to a nice PHP response', 'correct' => false],
                    ['text' => 'Catches PHP errors and exceptions and converts them to a nice PHP response', 'correct' => false],
                    ['text' => 'Catches PHP errors and pass them to a custom function', 'correct' => false],
                ],
            ],

            // Q10 - Finder - Methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Finder'] ?? $subcategories['Symfony:Services'],
                'text' => 'Which methods belong to <code>Symfony\Component\Finder\Finder</code> class?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La classe Finder possède les méthodes: name(), notName(), path(), notPath(), size() (mais pas notSize()), et d\'autres. La méthode owner() n\'existe pas dans Finder.',
                'resourceUrl' => 'https://symfony.com/doc/2.3/components/finder.html, https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Finder/Finder.php',
                'answers' => [
                    ['text' => '<code>notName</code>', 'correct' => true],
                    ['text' => '<code>notPath</code>', 'correct' => true],
                    ['text' => '<code>size</code>', 'correct' => true],
                    ['text' => '<code>notSize</code>', 'correct' => false],
                    ['text' => '<code>owner</code>', 'correct' => false],
                    ['text' => '<code>type</code>', 'correct' => false],
                    ['text' => '<code>path</code>', 'correct' => true],
                    ['text' => '<code>name</code>', 'correct' => true],
                ],
            ],

            // Q11 - HttpKernel - ESI
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'] ?? $subcategories['Symfony:Services'],
                'text' => 'Which sentence about page fragments caching methods is true?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les ESI (Edge Side Includes) ne sont que partiellement implémentés par Symfony. Seul le tag <esi:include> est supporté parmi tous les tags définis dans la spécification ESI.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/http_cache/esi.html, https://symfony.com/doc/4.3/http_cache/ssi.html',
                'answers' => [
                    ['text' => '<code>Edge Side Includes</code> are partially implemented by Symfony', 'correct' => true],
                    ['text' => '<code>Server Side Includes</code> are more performant than <code>Edge Side Includes</code> and are recommanded by Symfony', 'correct' => false],
                    ['text' => '<code>Server Side Includes</code> don\'t exist', 'correct' => false],
                    ['text' => '<code>Server Side Includes</code> are not implemented by Symfony', 'correct' => false],
                ],
            ],

            // Q12 - Finder - SplFileInfo getMTime
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Finder'] ?? $subcategories['Symfony:Services'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder->files()->in(__DIR__);

foreach ($finder as $file) {
    echo $file->getMTime();
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, ce code est valide. Le Finder itère sur des objets SplFileInfo qui possèdent la méthode getMTime() héritée de la classe PHP native SplFileInfo.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/finder.html#usage, https://www.php.net/manual/en/class.splfileinfo.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q13 - Security - CSRF token in template
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Could a CSRF token be generated in the template rather than in the form?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Oui, la fonction Twig csrf_token() permet de générer un token CSRF directement dans le template. C\'est utile pour les formulaires créés manuellement en HTML.',
                'resourceUrl' => 'https://symfony.com/doc/2.1/reference/twig_reference.html#functions',
                'answers' => [
                    ['text' => 'Yes, using <code>generate_csrf_token()</code>', 'correct' => false],
                    ['text' => 'It\'s not possible', 'correct' => false],
                    ['text' => 'Yes, using <code>generate_token</code>', 'correct' => false],
                    ['text' => 'Yes, using <code>csrf_token()</code>', 'correct' => true],
                    ['text' => 'Yes, using <code>csrf_create_token()</code>', 'correct' => false],
                ],
            ],

            // Q14 - Routing - URL generation with FQCN
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Given the following controller:
<pre><code class="language-php">namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class BlogController extends AbstractController
{
    #[Route(\'/blog\')]
    public function index(): Response
    {
        // ...
    }
}</code></pre>
<p>How to generate an URL for the <code>BlogController::index()</code> action?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Depuis Symfony 6.4, vous pouvez utiliser le FQCN du contrôleur avec la méthode comme alias automatique de route. La syntaxe app_blog_index est le nom de route auto-généré, et BlogController::class.\'::index\' utilise l\'alias FQCN.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#generating-urls, https://symfony.com/blog/new-in-symfony-6-4-fqcn-based-routes',
                'answers' => [
                    ['text' => '<code>$url = $urlGenerator->generate(\'app_blog_index\');</code>', 'correct' => true],
                    ['text' => '<code>$url = $urlGenerator->generate(\'/blog\');</code>', 'correct' => false],
                    ['text' => '<code>$url = $urlGenerator->generate(BlogController::class, \'index\');</code>', 'correct' => false],
                    ['text' => '<code>$url = $urlGenerator->generate(BlogController::class.\'::index\');</code>', 'correct' => true],
                    ['text' => '<code>$url = $urlGenerator->generate(\'_blog_index\');</code>', 'correct' => false],
                ],
            ],
        ];
    }
}
