<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 20
 */
class CertificationQuestionsFixtures20 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures19::class];
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

            // Q2 - Twig - Variables
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Is it possible to pass PHP objects to a Twig template?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, Twig peut recevoir des objets PHP comme variables. Twig accède aux propriétés et méthodes des objets de manière transparente.',
                'resourceUrl' => 'http://twig.symfony.com/doc/templates.html#variables',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q3 - Security - Passport validation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Could a <code>Passport</code> be self-validated?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, Symfony fournit SelfValidatingPassport qui permet de créer un passport sans nécessiter de validation supplémentaire des credentials.',
                'resourceUrl' => 'https://symfony.com/doc/5.4/security/custom_authenticator.html#self-validating-passport',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q4 - Validator - Blank constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Will the following snippet throw an <code>InvalidArgumentException</code>?
<pre><code class="language-php">&lt;?php

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Blank;

$expectedBlank = [];

$validator = Validation::createValidator();
$violations = $validator-&gt;validate($expectedBlank, [new Blank()]);

if (0 !== count($violations)) {
    throw new InvalidArgumentException(\'The value is not blank !\');
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, car la contrainte Blank vérifie si une valeur est vide (chaîne vide ou null). Un tableau vide [] n\'est pas considéré comme "blank" par cette contrainte, donc il y aura des violations et l\'exception sera lancée.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/Blank.html',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // Q5 - PHP - Namespaces
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following context:
<ol>
<li>The application code runs in the <code>myapp</code> namespace.</li>
<li>The <code>world()</code> function is defined in the <code>myapp\utils\hello</code> namespace.</li>
</ol>
<p>What is the correct way to import the <code>hello</code> namespace so that you can use the <code>world()</code> function?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour importer une fonction d\'un namespace, il faut utiliser "use function" suivi du chemin complet vers la fonction.',
                'resourceUrl' => 'http://php.net/manual/en/language.namespaces.importing.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">use function myapp\utils\hello\world;</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">use world;</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use function utils\hello\world;</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use utils\hello;</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use myapp\utils\hello\world;</code></pre>', 'correct' => false],
                ],
            ],

            // Q6 - HttpFoundation - CHIPS cookies
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could CHIPS cookies be defined?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 6.4, il est possible de définir des cookies CHIPS (Cookies Having Independent Partitioned State) via l\'attribut Partitioned de la classe Cookie.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-4-chips-cookies',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q7 - PHP OOP - Class visibility
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Is it possible for a PHP class to be declared <code>private</code> or <code>protected</code> in order to limit its scope to the current namespace only?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Non, en PHP les classes ne peuvent pas avoir de modificateurs de visibilité (private, protected). Seules les propriétés, méthodes et constantes de classe peuvent avoir ces modificateurs.',
                'resourceUrl' => 'http://php.net/manual/en/language.oop5.visibility.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q9 - Filesystem - Behaviour
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Is the Filesystem component based on a lazy or eager implementation?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le composant Filesystem utilise une implémentation "lazy" - les opérations sont exécutées au moment où elles sont appelées, sans pré-chargement.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/Filesystem/Filesystem.php',
                'answers' => [
                    ['text' => 'Lazy', 'correct' => true],
                    ['text' => 'Eager', 'correct' => false],
                ],
            ],

            // Q10 - Event Dispatcher - No listeners
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'What happens if an event has no listeners?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Si un événement n\'a pas de listeners, il est simplement dispatché sans aucun effet. Aucune exception n\'est levée et l\'application continue normalement.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/event_dispatcher.html',
                'answers' => [
                    ['text' => 'The event is dispatched but nothing happens', 'correct' => true],
                    ['text' => 'The application halts', 'correct' => false],
                    ['text' => 'Symfony throws a logic exception', 'correct' => false],
                    ['text' => 'It is skipped at compile time', 'correct' => false],
                ],
            ],

            // Q11 - DI - Abstract services
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Should a parent service be declared as <code>abstract</code> if no class is set in its service definition?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, si un service parent n\'a pas de classe définie, il doit être déclaré comme abstract pour éviter que Symfony tente de l\'instancier.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/service_container/parent_services.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q12 - DI - Autowiring
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Will Autowiring automatically inject dependencies of an autowired service?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'autowiring injectera automatiquement les dépendances si elles sont également déclarées comme services autowirés ou configurées manuellement dans le conteneur.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/service_container/autowiring.html',
                'answers' => [
                    ['text' => 'Yes, if dependencies are explicitly declared as autowired or manually configured.', 'correct' => true],
                    ['text' => 'Yes, autowiring will find and register all dependencies', 'correct' => false],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q15 - Event Dispatcher - ImmutableEventDispatcher
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Could subscribers be removed from an <code>ImmutableEventDispatcher</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, ImmutableEventDispatcher est en lecture seule. Toute tentative d\'ajouter ou supprimer des listeners/subscribers lance une BadMethodCallException.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/EventDispatcher/ImmutableEventDispatcher.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q19 - BrowserKit - HttpBrowser lifecycle
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:BrowserKit'] ?? $subcategories['Symfony:Services'],
                'text' => 'Once started, could the <code>HttpBrowser</code> be restarted?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, HttpBrowser peut être redémarré via la méthode restart() qui réinitialise l\'état du navigateur (cookies, historique, etc.).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/BrowserKit/HttpBrowser.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q20 - DI - ReverseContainer public
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could a service identifier be returned from a <code>ReverseContainer</code> if the service is not tagged as <code>container.reversible</code> but defined as <code>public</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ReverseContainer peut retourner l\'identifiant d\'un service public même sans le tag container.reversible.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/DependencyInjection/ReverseContainer.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q21 - HttpKernel - ValidateRequestListener exception
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Which exception is thrown if <code>ValidateRequestListener::onKernelRequest()</code> detect that ips are not consistent?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Lorsque ValidateRequestListener détecte des IPs inconsistantes (par exemple entre X-Forwarded-For et Client-Ip), il lance une ConflictingHeadersException.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.7/src/Symfony/Component/HttpKernel/EventListener/ValidateRequestListener.php',
                'answers' => [
                    ['text' => '<code>ConflictingHeadersException</code>', 'correct' => true],
                    ['text' => '<code>InconsistentIpsException</code>', 'correct' => false],
                    ['text' => '<code>RuntimeException</code>', 'correct' => false],
                    ['text' => '<code>LogicException</code>', 'correct' => false],
                    ['text' => '<code>InvalidArgumentException</code>', 'correct' => false],
                ],
            ],

            // Q23 - HttpFoundation - Status code
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Is it possible to change the status code of a <code>Symfony\Component\HttpFoundation\Response</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Oui, on peut changer le code de statut d\'une Response avec la méthode setStatusCode().',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#response',
                'answers' => [
                    ['text' => 'Yes with the <code>setStatusCode()</code> method', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes with the <code>setCode()</code> method', 'correct' => false],
                    ['text' => 'Yes with the <code>setStatus()</code> method', 'correct' => false],
                ],
            ],

            // Q26 - PHP OOP - Anonymous Functions
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'What would be the output of this code?
<pre><code class="language-php">$myArray = [1, 2, 3];
$closure = function (int $a): int {
    return $myArray[$a];
};
echo $closure(1);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Une erreur sera lancée car $myArray n\'est pas accessible dans la closure. Il faudrait utiliser "use ($myArray)" pour capturer la variable.',
                'resourceUrl' => 'https://www.php.net/manual/en/functions.anonymous.php',
                'answers' => [
                    ['text' => 'An error is thrown', 'correct' => true],
                    ['text' => '2', 'correct' => false],
                    ['text' => '1', 'correct' => false],
                ],
            ],

            // Q27 - Runtime - Closure execution
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'] ?? $subcategories['Symfony:Services'],
                'text' => 'Could a <code>Closure</code> be executed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le composant Runtime de Symfony peut exécuter des Closures via ClosureRunner.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/Runtime/Runner/ClosureRunner.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q29 - Validator - Color constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Could a RGB color be validated?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 5.4, la contrainte CssColor permet de valider les couleurs CSS, y compris les couleurs RGB.',
                'resourceUrl' => 'https://symfony.com/doc/5.4/reference/constraints/CssColor.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q30 - Clock - Usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'] ?? $subcategories['Symfony:Services'],
                'text' => 'Could a <code>ClockInterface</code> implementation specify a timezone as a <code>DateTimeZone</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, les implémentations de ClockInterface peuvent spécifier un fuseau horaire via DateTimeZone.',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/ClockInterface.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q31 - PropertyAccess - Writing to Arrays
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'] ?? $subcategories['Symfony:Services'],
                'text' => 'Is it possible to set values of an array with a <code>PropertyAccessor</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, PropertyAccessor peut écrire des valeurs dans des tableaux en utilisant la méthode setValue().',
                'resourceUrl' => 'http://symfony.com/doc/current/components/property_access/introduction.html#writing-to-arrays',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q32 - HttpKernel - Enumeration usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Given the context where <code>Suit</code> is an existing backed enumeration, is the following code valid?
<pre><code class="language-php">&lt;?php

// ...

class CardController
{
  #[Route(\'/cards/{suit}\')]
  public function list(Suit $suit): Response
  {
    // ...
  }

  // ...
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 6.1, BackedEnumValueResolver permet de résoudre automatiquement les enums backés dans les paramètres de route.',
                'resourceUrl' => 'https://symfony.com/doc/6.1/controller/argument_value_resolver.html#built-in-value-resolvers',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q33 - Twig - Template array access
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Does this syntax perform any check?
<pre><code class="language-twig">{{ foo[\'bar\'] }}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La syntaxe avec crochets en Twig vérifie uniquement si foo est un tableau et si la clé bar existe.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/templates.html#variables',
                'answers' => [
                    ['text' => 'Yes if <code>foo</code> is an array', 'correct' => true],
                    ['text' => 'Yes, if <code>foo</code> is an object then if it\'s an array', 'correct' => false],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
        ];
    }
}
