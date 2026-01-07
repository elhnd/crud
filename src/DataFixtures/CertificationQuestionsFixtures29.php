<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 29
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures29 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures28::class];
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

            // Q2 - DI - Autowiring
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What does autowiring do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'autowiring permet l\'injection automatique de services basée uniquement sur les type hints, sans avoir besoin de configuration explicite.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/autowiring.html',
                'answers' => [
                    ['text' => 'It allows automatic injection of services based on type hints alone', 'correct' => true],
                    ['text' => 'It removes the need for service configuration', 'correct' => false],
                    ['text' => 'It forces services to be publicly accessible directly from the container', 'correct' => false],
                    ['text' => 'It registers all services in the container', 'correct' => false],
                ],
            ],

            // Q5 - BrowserKit - HttpClient usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:BrowserKit'],
                'text' => 'Could <code>HttpClient</code> be used to perform requests?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 4.3, HttpClient peut être utilisé avec BrowserKit pour effectuer des requêtes.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/BrowserKit/CHANGELOG.md#430',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - Clock - DatePoint purpose
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'What is the purpose of the <code>Symfony\Component\Clock\DatePoint</code> class?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'DatePoint est un remplacement drop-in des classes PHP date/time pour fournir une intégration complète avec le composant Clock.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-4-datepoint, https://github.com/symfony/clock/blob/6.4/DatePoint.php',
                'answers' => [
                    ['text' => 'It\'s a drop-in replacement of PHP date/time classes to provide full integration with the <code>Clock</code> component', 'correct' => true],
                    ['text' => 'It adds a convenient widget for <code>DateTimeImmutable</code> data in forms', 'correct' => false],
                    ['text' => 'It\'s a wrapper to better handle PHP date/time objects in statistic and probability computing', 'correct' => false],
                ],
            ],

            // Q8 - Security - Access Decision Manager strategies
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which of the following are built-in voting strategies that can be configured in the <code>AccessDecisionManager</code> object?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les trois stratégies de vote intégrées sont : affirmative (au moins un vote positif), consensus (majorité), et unanimous (tous positifs).',
                'resourceUrl' => 'https://symfony.com/doc/5.1/security/voters.html#changing-the-access-decision-strategy',
                'answers' => [
                    ['text' => '<code>affirmative</code>', 'correct' => true],
                    ['text' => '<code>consensus</code>', 'correct' => true],
                    ['text' => '<code>unanimous</code>', 'correct' => true],
                    ['text' => '<code>veto</code>', 'correct' => false],
                    ['text' => '<code>neutral</code>', 'correct' => false],
                    ['text' => '<code>null</code>', 'correct' => false],
                    ['text' => '<code>positive</code>', 'correct' => false],
                    ['text' => '<code>priority</code>', 'correct' => false],
                ],
            ],

            // Q10 - PHP OOP - Valid class names
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Which of the following are valid PHP class names ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Les noms de classe PHP peuvent commencer par un underscore ou une lettre (minuscule ou majuscule), mais pas par un chiffre ou un tiret.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.basic.php',
                'answers' => [
                    ['text' => '_MyClass', 'correct' => true],
                    ['text' => 'myClass', 'correct' => true],
                    ['text' => '-MyClass', 'correct' => false],
                    ['text' => '123MyClass', 'correct' => false],
                ],
            ],

            // Q11 - Form - handleRequest
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What triggers the form processing in controllers ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La méthode handleRequest() déclenche le traitement du formulaire dans les contrôleurs.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/form.html',
                'answers' => [
                    ['text' => 'With <code>$form->handleRequest()</code>.', 'correct' => true],
                    ['text' => 'With <code>$form->isSubmitted()</code>.', 'correct' => false],
                    ['text' => 'With <code>$form->isValid()</code>.', 'correct' => false],
                    ['text' => 'With <code>$form->process()</code>.', 'correct' => false],
                ],
            ],

            // Q12 - Yaml - Null elements
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'],
                'text' => 'Which of the following values are available to define a null element in YAML ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'En YAML, ~ et null sont les deux valeurs valides pour définir un élément null.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/yaml/yaml_format.html#nulls',
                'answers' => [
                    ['text' => '<code>~</code>', 'correct' => true],
                    ['text' => '<code>null</code>', 'correct' => true],
                    ['text' => '<code>-</code>', 'correct' => false],
                    ['text' => '<code>false</code>', 'correct' => false],
                ],
            ],

            // Q14 - Validator - JSR 303
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'The Symfony validator is based on…?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le validateur Symfony est basé sur la spécification JSR 303 Bean Validation.',
                'resourceUrl' => 'http://symfony.com/doc/current/book/validation.html',
                'answers' => [
                    ['text' => 'JSR 303', 'correct' => true],
                    ['text' => 'CVE-2015-2308', 'correct' => false],
                    ['text' => 'PSR-2', 'correct' => false],
                    ['text' => 'RFC 2616', 'correct' => false],
                ],
            ],

            // Q18 - Finder - File type
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Finder'],
                'text' => 'What is the kind of value of <code>$file</code> in the following code?
<pre><code class="language-php">&lt;?php

use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder->files()->in(__DIR__);

foreach ($finder as $file) {
    // ...
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le Finder retourne des instances de Symfony\Component\Finder\SplFileInfo, qui étend SplFileInfo de PHP.',
                'resourceUrl' => 'https://symfony.com/doc/2.0/components/finder.html',
                'answers' => [
                    ['text' => 'An instance of <code>Symfony\Component\Finder\SplFileInfo</code>.', 'correct' => true],
                    ['text' => 'An instance of <code>Symfony\Component\Finder\FileInfo</code>.', 'correct' => false],
                    ['text' => 'An instance of <code>Symfony\Component\Finder\File</code>.', 'correct' => false],
                    ['text' => 'An instance of <code>Symfony\Component\Finder\File\SplFile</code>.', 'correct' => false],
                    ['text' => 'An instance of <code>Symfony\Component\Finder\File\File</code>.', 'correct' => false],
                    ['text' => 'An instance of <code>Symfony\Component\Finder\File\SplFileInfo</code>.', 'correct' => false],
                ],
            ],

            // Q19 - DI - Private services
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Let\'s assume, we have a private service <code>my_private_service</code>.
<pre><code class="language-php">$container->get(\'my_private_service\');</code></pre>
<p>Will it work?</p>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, les services privés ne peuvent pas être récupérés directement depuis le container avec get(). Ils ne sont accessibles que par injection de dépendances.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/alias_private.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q21 - HttpClient - RetryableHttpClient default retries
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'What is the default retries allowed in <code>RetryableHttpClient</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Par défaut, RetryableHttpClient autorise 3 tentatives de retry.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.2/src/Symfony/Component/HttpClient/RetryableHttpClient.php#L38',
                'answers' => [
                    ['text' => '3', 'correct' => true],
                    ['text' => '4', 'correct' => false],
                    ['text' => '1', 'correct' => false],
                    ['text' => '2', 'correct' => false],
                ],
            ],

            // Q22 - PHP - STDIN constant
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'The <strong>???</strong> constant in a CLI script is an automatically provided file resource representing standard input of the terminal.',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La constante STDIN représente l\'entrée standard du terminal dans un script CLI PHP.',
                'resourceUrl' => 'http://php.net/manual/en/features.commandline.io-streams.php',
                'answers' => [
                    ['text' => '<code>STDIN</code>', 'correct' => true],
                    ['text' => '<code>PHP::STDIO</code>', 'correct' => false],
                    ['text' => '<code>__STDIN__</code>', 'correct' => false],
                    ['text' => '<code>STD_IN</code>', 'correct' => false],
                    ['text' => '<code>STDIO</code>', 'correct' => false],
                ],
            ],

            // Q24 - PHP - Attributes advantages
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is the main advantages of attributes over annotations?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les attributs sont une fonctionnalité native de PHP, alors que les annotations nécessitent une bibliothèque supplémentaire pour être lues et utilisées.',
                'resourceUrl' => 'https://php.watch/articles/php-attributes#intro-naming, https://www.php.net/manual/en/language.attributes.overview.php',
                'answers' => [
                    ['text' => 'Attributes are a native PHP feature, where annotations require an additional library to be read and used', 'correct' => true],
                    ['text' => 'Attributes allow more possibilities and features than annotations', 'correct' => false],
                    ['text' => 'Both are equivalent, it is just a different syntax', 'correct' => false],
                ],
            ],

            // Q25 - VarExporter - Instantiate exported variable
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarExporter'],
                'text' => 'How do you instantiate an object/variable that has been exported by VarExporter?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour instancier une variable exportée, il faut écrire le code exporté dans un fichier PHP et le require ensuite.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/components/var_exporter.html#exporting-serializing-variables',
                'answers' => [
                    ['text' => '<pre><code class="language-php">&lt;?php

$exported = VarExporter::export($someVariable);
$data = file_put_contents(\'exported.php\', \'&lt;?php return \'.$exported.\';\');

$regeneratedVariable = require \'exported.php\';</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">&lt;?php

$exported = VarExporter::export($someVariable);

// ...

$regeneratedVariable = VarExporter::instantiate($exported);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">&lt;?php

$exported = VarExporter::export($someVariable);
$data = file_put_contents(\'exported.php\', \'&lt;?php return \'.$exported.\';\');

$regeneratedVariable = VarExporter::import(\'exported.php\');</code></pre>', 'correct' => false],
                ],
            ],

            // Q26 - Form - CountryType choices
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'By default, which function provides the choices of the <code>Symfony\Component\Form\Extension\Core\Type\CountryType</code> form type?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Depuis Symfony 5.0, les choix du CountryType sont fournis par Symfony\Component\Intl\Countries::getNames().',
                'resourceUrl' => 'https://symfony.com/doc/5.0/reference/forms/types/country.html#choices',
                'answers' => [
                    ['text' => '<code>Symfony\Component\Intl\Countries::getNames()</code>', 'correct' => true],
                    ['text' => '<code>Symfony\Component\Form\Extension\Core\Type\CountryType::getChoices()</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Locale\getDisplayCountries()</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Intl\Intl::getRegionBundle()->getCountryNames()</code>', 'correct' => false],
                    ['text' => '<code>Intl::getCountries()</code>', 'correct' => false],
                ],
            ],

            // Q28 - HTTP - Pragma header
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Does the Pragma HTTP response header allow to efficiently transmit cache instructions in HTTP/1.1 ?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, Pragma est un header HTTP/1.0 obsolète. Pour HTTP/1.1, il faut utiliser Cache-Control.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Pragma',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q29 - Event Dispatcher - ImmutableEventDispatcher
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Could listeners be removed from an <code>ImmutableEventDispatcher</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, comme son nom l\'indique, un ImmutableEventDispatcher est immuable et les listeners ne peuvent pas être ajoutés ou supprimés.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/EventDispatcher/ImmutableEventDispatcher.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q31 - DomCrawler - form method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:DomCrawler'],
                'text' => 'Which sentences are true about the DomCrawler <code>form</code> method ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode form() permet de sélectionner un formulaire. Elle ne permet pas de le soumettre, ni de modifier ses valeurs par défaut, méthode ou URL après sélection.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/components/dom_crawler.html, https://symfony.com/doc/2.x/components/dom_crawler.html#forms',
                'answers' => [
                    ['text' => 'it allows to select a form', 'correct' => true],
                    ['text' => 'it allows to override form url action', 'correct' => false],
                    ['text' => 'it allows to override form field default values', 'correct' => false],
                    ['text' => 'it allows to submit a form', 'correct' => false],
                    ['text' => 'it allows to override form method', 'correct' => false],
                ],
            ],

            // Q32 - Filesystem - mirror usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'When using <code>mirror(...)</code>, could files that are not present in the source directory be deleted?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 2.8, mirror() peut supprimer les fichiers qui ne sont pas présents dans le répertoire source avec l\'option delete.',
                'resourceUrl' => 'https://symfony.com/doc/2.2/components/filesystem.html#mirror, https://github.com/symfony/symfony/blob/2.2/src/Symfony/Component/Filesystem/Filesystem.php#L338',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q34 - HTTP - Expires header
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Is the following header value valid?
<pre><code class="language-text">Expires: Sun, 06 Nov 1994 08:49:37 GMT</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, cette valeur Expires est valide et respecte le format HTTP-date spécifié dans RFC 7231.',
                'resourceUrl' => 'https://datatracker.ietf.org/doc/html/rfc7234#section-5.3, https://datatracker.ietf.org/doc/html/rfc7231#section-7.1.1.1, https://developer.mozilla.org/en/docs/Web/HTTP/Headers/Expires',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q35 - Twig - Empty test
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following statements will display <code>bar</code> ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'En Twig, les valeurs considérées comme "falsy" sont : false, 0, "", null, et []. Le test "is not empty" retourne false pour ces valeurs. Donc toutes les expressions listées affichent "bar".',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/if.html, https://github.com/twigphp/Twig/blob/1.x/src/Extension/CoreExtension.php',
                'answers' => [
                    ['text' => '<code>{{ 0 ? \'foo\' : \'bar\' }}</code>', 'correct' => true],
                    ['text' => '<code>{{ \'\' is not empty ? \'foo\' : \'bar\' }}</code>', 'correct' => true],
                    ['text' => '<code>{{ [] ? \'foo\' : \'bar\' }}</code>', 'correct' => true],
                    ['text' => '<code>{{ 0 is not empty ? \'foo\' : \'bar\' }}</code>', 'correct' => true],
                    ['text' => '<code>{{ \'\' ? \'foo\' : \'bar\' }}</code>', 'correct' => true],
                    ['text' => '<code>{{ [] is not empty ? \'foo\' : \'bar\' }}</code>', 'correct' => true],
                    ['text' => '<code>{{ \'0\' ? \'foo\' : \'bar\' }}</code>', 'correct' => false],
                    ['text' => '<code>{{ \'0\' is not empty ? \'foo\' : \'bar\' }}</code>', 'correct' => false],
                ],
            ],

            // Q36 - Doctrine - OrderBy annotation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'How to order a <code>To-Many</code> association ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour ordonner une association To-Many, on utilise l\'annotation @OrderBy sur l\'association.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/ordered-associations.html',
                'answers' => [
                    ['text' => 'By adding the <code>@OrderBy({"fieldName" = "ASC"})</code> annotation on the association', 'correct' => true],
                    ['text' => 'By adding the <code>@findBy({"repositoryClass" = "Acme\Repository\AcmeRepository", method= "findOrdered"})</code> annotation', 'correct' => false],
                    ['text' => 'It\'s not possible', 'correct' => false],
                    ['text' => 'By implementing the <code>OrederableInterface</code> on the entity', 'correct' => false],
                ],
            ],

            // Q37 - PHP - PHP Birth
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'When was PHP first released by Rasmus Lerdorf?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'PHP a été publié pour la première fois par Rasmus Lerdorf en 1995.',
                'resourceUrl' => 'http://php.net/manual/en/history.php.php',
                'answers' => [
                    ['text' => '1995', 'correct' => true],
                    ['text' => '2005', 'correct' => false],
                    ['text' => '2000', 'correct' => false],
                    ['text' => '1987', 'correct' => false],
                ],
            ],

            // Q38 - PHP - Enum tryFrom
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

enum Suit: string
{
    case Hearts = \'H\';
    case Diamonds = \'D\';
    case Clubs = \'C\';
    case Spades = \'S\';
}

$h = Suit::tryFrom(\'E\') ?? Suit::from(\'H\');</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ce code est valide. tryFrom() retourne null si la valeur n\'existe pas, et l\'opérateur ?? permet de fallback sur Suit::from(\'H\').',
                'resourceUrl' => 'https://www.php.net/manual/en/backedenum.tryfrom.php, https://www.php.net/manual/en/backedenum.from.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q39 - Filesystem - readLink
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Given the following code and an existing path set to <code>/srv/app</code> which is a symbolic link to <code>srv/sf</code>, what will be stored in <code>$value</code>?
<pre><code class="language-php">&lt;?php

$fs = new Filesystem();
$value = $fs->readLink(\'/srv/app\', false);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode readLink() avec le second paramètre à false retourne la cible du lien symbolique, donc /srv/sf.',
                'resourceUrl' => 'https://symfony.com/doc/3.2/components/filesystem.html#readlink',
                'answers' => [
                    ['text' => '<code>/srv/sf</code>', 'correct' => true],
                    ['text' => '<code>false</code>', 'correct' => false],
                    ['text' => '<code>true</code>', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '<code>/srv/app</code>', 'correct' => false],
                ],
            ],

            // Q41 - Mailer - Markdown content
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mailer'],
                'text' => 'Could markdown be used as content for mails?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, le composant Mailer supporte le contenu Markdown pour les emails.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/mailer.html#rendering-markdown-content, https://github.com/twigphp/markdown-extra',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q46 - Validator - Url constraint protocols
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'When using the <code>Symfony\Component\Validator\Constraints\Url</code> validation constraint, what protocols are allowed by default ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Par défaut, la contrainte Url accepte les protocoles HTTP et HTTPS.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/Url.html',
                'answers' => [
                    ['text' => 'HTTP and HTTPS.', 'correct' => true],
                    ['text' => 'Only HTTP.', 'correct' => false],
                    ['text' => 'HTTP, HTTPS, FTP and SMTP.', 'correct' => false],
                    ['text' => 'Any protocol.', 'correct' => false],
                ],
            ],

            // Q47 - Mailer - Email encryption
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mailer'],
                'text' => 'Could an email be encrypted?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, les emails peuvent être chiffrés avec le composant Mailer de Symfony.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/mailer.html#encrypting-messages',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q48 - OptionsResolver - setAllowedTypes float
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:OptionsResolver'],
                'text' => 'Which of the following are valid types to use in <code>setAllowedTypes</code> method of <code>Symfony\Component\OptionsResolver\OptionsResolver</code> to validate a float value?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les types valides pour float dans setAllowedTypes sont "float" et "double" (alias de float en PHP).',
                'resourceUrl' => 'https://symfony.com/doc/3.0/components/options_resolver.html#type-validation, https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/OptionsResolver/OptionsResolver.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">"float"</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">"double"</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OptionsResolver::FLOAT</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">OptionsResolver::DOUBLE</code></pre>', 'correct' => false],
                ],
            ],

            // Q49 - Clock - Clock sleep
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'Could a <code>Clock</code> sleep?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, l\'interface ClockInterface fournit une méthode sleep() pour mettre en pause l\'exécution.',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/ClockInterface.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
        ];
    }
}
