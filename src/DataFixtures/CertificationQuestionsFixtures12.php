<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 12
 */
class CertificationQuestionsFixtures12 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures11::class];
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
            // Q1 - DI - Container dump
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could the container be dumped into a single file?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 4.4, le conteneur peut être dumpé dans un seul fichier pour améliorer les performances en réduisant le nombre de fichiers à charger.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/performance.html#dump-the-service-container-into-a-single-file, https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/DependencyInjection/Dumper/PhpDumper.php#L117',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q2 - PHP - fopen for HTTP
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Which native function can be used to get the HTML content of Google.com\'s homepage?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La fonction fopen() peut ouvrir des URLs si allow_url_fopen est activé dans php.ini. Elle peut être utilisée avec fread() ou file_get_contents() pour récupérer le contenu HTML.',
                'resourceUrl' => 'http://php.net/manual/en/function.fopen.php, http://php.net/manual/en/function.http-build-query.php',
                'answers' => [
                    ['text' => '<code>http_build_query()</code>', 'correct' => false],
                    ['text' => '<code>url_open()</code>', 'correct' => false],
                    ['text' => '<code>fopen()</code>', 'correct' => true],
                    ['text' => 'This is not possible.', 'correct' => false],
                    ['text' => '<code>get_web_adress()</code>', 'correct' => false],
                ],
            ],

            // Q3 - DI - Environment variables file processor
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is the following code valid?
<pre><code class="language-yaml"># config/packages/framework.yaml
parameters:
  env(AUTH_FILE): \'../config/auth.json\'

google:
  auth: \'%env(file:AUTH_FILE)%\'</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le processeur d\'environnement file: lit le contenu du fichier dont le chemin est stocké dans la variable d\'environnement. C\'est valide depuis Symfony 4.2.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/configuration/env_var_processors.html#built-in-environment-variable-processors',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q4 - Twig - Template Inheritance error
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following Twig code snippet:
<pre><code class="language-twig">{% extends \'layout.html.twig\' %}

{% block title \'Hello World!\' %}

My name is Amanda.</code></pre>
<p>What will be the result of evaluating this Twig template?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Quand un template étend un autre template, il ne peut contenir que des blocs. Tout contenu en dehors des blocs (comme "My name is Amanda.") provoque une Twig_Error_Syntax.',
                'resourceUrl' => 'http://twig.symfony.com/doc/tags/extends.html',
                'answers' => [
                    ['text' => 'Twig will raise a <code>Twig_Error_Syntax</code> exception preventing the template from being evaluated.', 'correct' => true],
                    ['text' => 'The template is successfully evaluated and the string <em>My name is Amanda</em> will be displayed in the web browser.', 'correct' => false],
                ],
            ],

            // Q5 - PHP Arrays - range function
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays & Collections'],
                'text' => 'What PHP function is used to create a new array pre-filled with a sequential series of values?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La fonction range() crée un tableau contenant une plage d\'éléments. Par exemple, range(1, 5) retourne [1, 2, 3, 4, 5].',
                'resourceUrl' => 'https://php.net/array, https://php.net/manual/en/function.range.php',
                'answers' => [
                    ['text' => '<code>array_construct</code>', 'correct' => false],
                    ['text' => '<code>range</code>', 'correct' => true],
                    ['text' => '<code>array_fill</code>', 'correct' => false],
                    ['text' => '<code>array_combine</code>', 'correct' => false],
                ],
            ],

            // Q6 - HttpFoundation - $request->get deprecated
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Does using <code>$request->get(\'key\')</code> still a recommended approach when fetching input data?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, l\'utilisation de $request->get() est dépréciée. Il est recommandé d\'utiliser des sources d\'entrée explicites comme $request->query->get(), $request->request->get(), ou $request->attributes->get().',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/HttpFoundation/Request.php#L694',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q7 - Validator - Constraints scopes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Which of the following are valid targets when adding new validation constraints to a PHP object?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les contraintes de validation peuvent être appliquées sur: les propriétés (publiques, privées ou protégées), certaines méthodes publiques (getters), et la classe elle-même.',
                'resourceUrl' => 'http://symfony.com/doc/current/validation.html#validator-constraint-targets',
                'answers' => [
                    ['text' => 'On any <strong>public</strong>, <strong>private</strong> or <strong>protected</strong> properties.', 'correct' => true],
                    ['text' => 'On certain <strong>public</strong> methods of the class.', 'correct' => true],
                    ['text' => 'On any <strong>public static</strong> methods of the class only.', 'correct' => false],
                    ['text' => 'On any <strong>public</strong> properties only.', 'correct' => false],
                    ['text' => 'On the class itself.', 'correct' => true],
                ],
            ],

            // Q8 - Console - Table setColumnWidth
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'What will be the output of the following command?
<pre><code class="language-php">// ...

class SomeCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table
            ->setHeaders([\'ISBN\', \'Title\', \'Author\'])
            ->setRows([
                [\'99921-58-10-7\', \'Divine Comedy\', \'Dante Alighieri\'],
                [\'9971-5-0210-0\', \'A Tale of Two Cities\', \'Charles Dickens\'],
                new TableSeparator(),
                [\'960-425-059-0\', \'The Lord of the Rings\', \'J. R. R. Tolkien\'],
                [\'80-902734-1-6\', \'And Then There Were None\', \'Agatha Christie\'],
            ]);
        $table->setColumnWidth(0, 8);
        $table->setColumnWidth(2, 30);
        $table->render();
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'setColumnWidth() définit la largeur MINIMALE d\'une colonne. Si le contenu est plus large, la colonne s\'adapte. Donc les ISBN complets sont affichés car ils dépassent 8 caractères.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/console/helpers/table.html',
                'answers' => [
                    ['text' => '<pre><code class="language-txt">+-------------+--------------------------+--------------------------------+
| ISBN        | Title                    | Author                         |
+-------------+--------------------------+--------------------------------+
| 99921-58... | Divine Comedy            | Dante Alighieri                |
| 9971-5-0... | A Tale of Two Cities     | Charles Dickens                |
+-------------+--------------------------+--------------------------------+
| 960-425-... | The Lord of the Rings    | J. R. R. Tolkien               |
| 80-90273... | And Then There Were None | Agatha Christie                |
+-------------+--------------------------+--------------------------------+</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-txt">+---------------+--------------------------+--------------------------------+
| ISBN          | Title                    | Author                         |
+---------------+--------------------------+--------------------------------+
| 99921-58-10-7 | Divine Comedy            | Dante Alighieri                |
| 9971-5-0210-0 | A Tale of Two Cities     | Charles Dickens                |
+---------------+--------------------------+--------------------------------+
| 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien               |
| 80-902734-1-6 | And Then There Were None | Agatha Christie                |
+---------------+--------------------------+--------------------------------+</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-txt">+----------+--------------------------+--------------------------------+
| ISBN     | Title                    | Author                         |
+----------+--------------------------+--------------------------------+
| 99921-58 | Divine Comedy            | Dante Alighieri                |
| 9971-5-0 | A Tale of Two Cities     | Charles Dickens                |
+----------+--------------------------+--------------------------------+
| 960-425- | The Lord of the Rings    | J. R. R. Tolkien               |
| 80-90273 | And Then There Were None | Agatha Christie                |
+----------+--------------------------+--------------------------------+</code></pre>', 'correct' => false],
                ],
            ],

            // Q9 - HttpFoundation - FlashBag has
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could the existence of a <code>FlashBag</code> message be checked?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, la méthode has() du FlashBag permet de vérifier si un type de message flash existe sans le supprimer.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/HttpFoundation/Session/Flash/FlashBag.php#L135',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q11 - Routing - generateUrl with query string
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Given the following definition of the <code>book_list</code> route, what will be the value of the variable <code>$url</code>?
<pre><code class="language-yaml"># app/config/routing.yml
book_list:
    path:     /books
    defaults: { _controller: AppBundle:Default:list }
    methods:  [POST]</code></pre>
<pre><code class="language-php">// src/AppBundle/Controller/HomeController.php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller {

    public function indexAction() {
        $url = $this->generateUrl(\'book_list\', [\'page\' => 1]);
        // ...
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les paramètres qui ne correspondent pas à des placeholders de la route sont ajoutés comme query string. Comme "page" n\'est pas dans le path, il devient ?page=1.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/routing.html#generating-urls-with-query-strings',
                'answers' => [
                    ['text' => '/books?_page=1', 'correct' => false],
                    ['text' => 'Error: Parameter "page" is not defined.', 'correct' => false],
                    ['text' => 'https://example.com/books?page=1', 'correct' => false],
                    ['text' => 'https://example.com/books?_page=1', 'correct' => false],
                    ['text' => '/books?page=1', 'correct' => true],
                ],
            ],

            // Q12 - Runtime - HttpFoundation integration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__).\'/vendor/autoload_runtime.php\';

return function (): Response {
    return new Response(\'Hello world\');
};</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le composant Runtime supporte le retour direct d\'une Response depuis une closure. Le runtime se charge automatiquement d\'envoyer la réponse au client.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/components/runtime.html#using-the-runtime',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q13 - Console - CommandTester events
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Are console events dispatched when testing commands using <code>CommandTester</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, par défaut CommandTester n\'utilise pas l\'Application donc les événements console ne sont pas dispatchés. Il faut utiliser ApplicationTester ou configurer explicitement le dispatcher.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/console.html#testing-commands, https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Console/Tester/CommandTester.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q14 - Cache - key/value store
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'How does a cache differ from a key/value store?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un cache ne doit pas être utilisé pour persister des données car il peut être vidé à tout moment sans impacter le fonctionnement de l\'application.',
                'resourceUrl' => 'http://www.aerospike.com/what-is-a-key-value-store/',
                'answers' => [
                    ['text' => 'It should not be used to persist data.', 'correct' => true],
                    ['text' => 'It is safe to store data in it.', 'correct' => false],
                    ['text' => 'It can be deleted without making the application crash.', 'correct' => true],
                ],
            ],

            // Q15 - Intl - languages support
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Intl'],
                'text' => 'What do you have to do to enjoy the full power of the Intl component, in many dozens of languages?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Pour profiter pleinement du composant Intl avec tous les langages supportés, il faut installer l\'extension php-intl qui fournit les données ICU complètes.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/intl.html',
                'answers' => [
                    ['text' => 'Nothing, Intl offers all features out-of-the-box, without any requirement', 'correct' => false],
                    ['text' => 'Install the <code>php-intl</code> extension', 'correct' => true],
                    ['text' => 'Install a ZIP package on your server containing necessary informations for it to fully work', 'correct' => false],
                ],
            ],

            // Q16 - Security - isGranted from Security class
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Could the authorization to access a resource be checked from within <code>Symfony\Component\Security\Core\Security</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, la classe Security fournit la méthode isGranted() qui permet de vérifier les autorisations depuis n\'importe quel service.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/security.html#securing-other-services, https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/Security/Core/Security.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q17 - Twig - Filter is_safe
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following <code>$options</code> allow a <code>Twig_Filter</code> decide how to escape data by itself?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'L\'option is_safe doit être un tableau contenant les contextes d\'échappement (comme \'html\', \'js\'). [\'is_safe\' => [\'html\']] indique que le filtre produit une sortie sûre pour le contexte HTML.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping',
                'answers' => [
                    ['text' => '<pre><code class="language-php">[\'is_safe\']</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">[\'is_safe\' => [\'html\']]</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">[\'is_safe\' => \'html\']</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">[\'is_safe\' => true]</code></pre>', 'correct' => false],
                ],
            ],
        ];
    }
}
