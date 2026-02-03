<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 17
 */
class CertificationQuestionsFixtures17 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures16::class];
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
            // Q1 - Expression Language - ExpressionLanguage usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

$expressionLanguage = new ExpressionLanguage();

$expressionLanguage->evaluate(\'product.price <= .99\', [\'product\' => new class() {
    public $price = 1.99;

    # ...
}]);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le code est valide. ExpressionLanguage peut évaluer des expressions utilisant des propriétés d\'objets et des nombres décimaux commençant par un point (.99 est équivalent à 0.99).',
                'resourceUrl' => 'https://symfony.com/doc/6.1/components/expression_language.html#expression-syntax',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q2 - PropertyAccess - Magic __call() Method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'What is the way to enable magic __call method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour activer la méthode magique __call, il faut utiliser le PropertyAccessorBuilder avec enableMagicCall() puis récupérer l\'accessor avec getPropertyAccessor().',
                'resourceUrl' => 'http://symfony.com/doc/current/components/property_access/introduction.html#magic-call-method',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$accessor = PropertyAccess::createPropertyAccessorBuilder()
    ->enableMagicCall()
    ->getPropertyAccessor()
;</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$accessor = PropertyAccess::createPropertyAccessorBuilder()
    ->getPropertyAccessor(true)
;</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$accessor = PropertyAccess::createPropertyAccessor(true);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$accessor = PropertyAccess::createPropertyAccessor()
    ->enableMagicCall()
;</code></pre>', 'correct' => false],
                ],
            ],

            // Q3 - Filesystem - Path utilities
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Given the following code, what will be displayed?
<pre><code class="language-php">&lt;?php

use Symfony\Component\Filesystem\Path;

echo Path::getRoot("C:\Programs\Apache\Config");</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Path::getRoot() retourne la racine du chemin, c\'est-à-dire "C:/" pour un chemin Windows. La méthode normalise également les séparateurs en utilisant des slashes.',
                'resourceUrl' => 'https://symfony.com/doc/5.4/components/filesystem.html#finding-directories-root-directories, https://github.com/symfony/filesystem/blob/5.4/Path.php#L207',
                'answers' => [
                    ['text' => '<code>C:/Programs/Apache/</code>', 'correct' => false],
                    ['text' => '<code>C:/Programs/Apache</code>', 'correct' => false],
                    ['text' => '<code>C:/Programs/Apache/Config</code>', 'correct' => false],
                    ['text' => '<code>C:/</code>', 'correct' => true],
                    ['text' => '<code>C:/Programs</code>', 'correct' => false],
                    ['text' => '<code>C:/Programs/</code>', 'correct' => false],
                ],
            ],

            // Q4 - PHP - Autoload
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What will be the output of the following code?
<pre><code class="language-php">spl_autoload_register(function ($a) {
    echo \'I am at the top of the auto-loaders stack\';
}, true, true);

if (class_exists(\'\Exception\', true)) {
    echo \'Loaded\';
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Exception est une classe interne PHP qui n\'a pas besoin d\'autoloader. class_exists avec true tente d\'autoloader mais Exception étant déjà chargée, aucun autoloader n\'est appelé. Affiche uniquement "Loaded".',
                'resourceUrl' => 'http://php.net/manual/en/intro.spl.php, http://php.net/manual/en/class.exception.php, http://php.net/manual/en/function.spl-autoload-register.php, http://php.net/manual/en/function.class-exists.php',
                'answers' => [
                    ['text' => 'Loaded', 'correct' => true],
                    ['text' => 'I am at the top of the auto-loaders stackLoaded', 'correct' => false],
                    ['text' => 'PHP Fatal error: Uncaught Error: Class \'Exception\' not found', 'correct' => false],
                    ['text' => 'I am at the top of the auto-loaders stack', 'correct' => false],
                    ['text' => 'Nothing', 'correct' => false],
                ],
            ],

            // Q5 - Event Dispatcher - ImmutableEventDispatcher usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Which exception is throw when trying to add a subscriber into an <code>ImmutableEventDispatcher</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ImmutableEventDispatcher lance une BadMethodCallException lorsqu\'on tente d\'ajouter ou supprimer des listeners/subscribers, car il est en lecture seule.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/EventDispatcher/ImmutableEventDispatcher.php',
                'answers' => [
                    ['text' => '<code>FrozenEventDispatcherException</code>', 'correct' => false],
                    ['text' => '<code>InvalidArgumentException</code>', 'correct' => false],
                    ['text' => '<code>BadMethodCallException</code>', 'correct' => true],
                    ['text' => '<code>LogicException</code>', 'correct' => false],
                    ['text' => '<code>RuntimeException</code>', 'correct' => false],
                ],
            ],

            // Q12 - DI - Compiler passes usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is the default priority used when adding a new compiler pass?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La priorité par défaut d\'un compiler pass est 0. Les priorités plus élevées sont exécutées en premier.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/components/dependency_injection/compilation.html#controlling-the-pass-ordering, https://github.com/symfony/symfony/blob/4.1/src/Symfony/Component/DependencyInjection/Compiler/PassConfig.php#L113',
                'answers' => [
                    ['text' => '1000', 'correct' => false],
                    ['text' => '-255', 'correct' => false],
                    ['text' => '0', 'correct' => true],
                    ['text' => '10', 'correct' => false],
                    ['text' => '100', 'correct' => false],
                ],
            ],

            // Q13 - HttpFoundation - RedirectResponse
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Is it possible to create a <code>Symfony\Component\HttpFoundation\RedirectResponse</code> with the <code>201</code> status code?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, bien que peu conventionnel, RedirectResponse accepte n\'importe quel code de statut HTTP valide, y compris 201. Le constructeur ne valide pas que c\'est un code de redirection 3xx.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc7231#section-6.3.2, https://github.com/symfony/symfony/blob/2c14c5fca7182f11abf0a692e471326f14119c29/src/Symfony/Component/HttpFoundation/RedirectResponse.php#L41, https://github.com/symfony/symfony/blob/2c14c5fca7182f11abf0a692e471326f14119c29/src/Symfony/Component/HttpFoundation/Response.php#L1177',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q14 - Event Dispatcher - Events debug
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Is the following code valid?
<pre><code class="language-bash">php bin/console debug:event-dispatcher kernel</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la commande debug:event-dispatcher accepte un argument optionnel pour filtrer les événements. "kernel" affichera tous les événements dont le nom contient "kernel".',
                'resourceUrl' => 'https://symfony.com/doc/5.3/event_dispatcher.html#debugging-event-listeners',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q15 - Filesystem - Default directory mode
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'What is the default value of the directory mode argument of the <code>Symfony\Component\Filesystem\Filesystem::mkdir</code> method ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le mode par défaut pour mkdir() est 0777 (permissions complètes). Attention, le umask du système peut modifier les permissions effectives.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/filesystem.html#mkdir',
                'answers' => [
                    ['text' => '0755', 'correct' => false],
                    ['text' => '0700', 'correct' => false],
                    ['text' => '0777', 'correct' => true],
                ],
            ],

            // Q16 - Twig - Twig delimiters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What is true about twig delimiters ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 1,
                'explanation' => '{{ ... }} est utilisé pour afficher le résultat d\'une expression, tandis que {% ... %} est utilisé pour exécuter des instructions comme les boucles for. Exception: include() peut utiliser les deux syntaxes.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/templates.html#synopsis',
                'answers' => [
                    ['text' => '<code>{% ... %}</code> is used to output the result of an expression', 'correct' => false],
                    ['text' => '<code>{% ... %}</code> is used to execute statements such as for-loops', 'correct' => true],
                    ['text' => '<code>{{ ... }}</code> used to execute statements such as for-loops', 'correct' => false],
                    ['text' => '<code>{{ ... }}</code> is used to output the result of an expression', 'correct' => true],
                ],
            ],

            // Q17 - Twig - Twig template
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given a template where an object <code>foo</code> is passed and the following call <code>{{ foo.bar }}</code>, what is the first test done by Twig?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Twig teste d\'abord si foo est un tableau et bar un élément valide, avant de tester si c\'est un objet avec une propriété, méthode getter, isser, etc.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/templates.html#variables',
                'answers' => [
                    ['text' => 'If <code>foo</code> is an array and <code>bar</code> a valid element', 'correct' => true],
                    ['text' => 'If <code>foo</code> is an object and <code>bar</code> is an isser (<code>isBar</code>)', 'correct' => false],
                    ['text' => 'If <code>foo</code> is an object and <code>bar</code> is a valid method (or the constructor)', 'correct' => false],
                    ['text' => 'If <code>foo</code> is an object and <code>bar</code> is a property', 'correct' => false],
                ],
            ],
        ];
    }
}
