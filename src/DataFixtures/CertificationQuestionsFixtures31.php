<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 31
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures31 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures30::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found. Please run AppFixtures first.');
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

            // Q4 - PHP Arrays - array_map usage
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'Could <code>array_map</code> be applied to multiple arrays at the same time?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, array_map() peut être appliqué à plusieurs tableaux simultanément. La fonction callback recevra alors autant d\'arguments qu\'il y a de tableaux.',
                'resourceUrl' => 'https://www.php.net/manual/en/function.array-map',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q5 - PHP I/O - File deletion function
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:I/O'],
                'text' => 'Which of the following functions is used to delete a file?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La fonction unlink() est utilisée pour supprimer un fichier en PHP. rmdir() supprime un répertoire, delete() n\'existe pas, et unset() libère une variable.',
                'resourceUrl' => 'http://php.net/filesystem',
                'answers' => [
                    ['text' => '<code>unlink()</code>', 'correct' => true],
                    ['text' => '<code>delete()</code>', 'correct' => false],
                    ['text' => '<code>rmdir()</code>', 'correct' => false],
                    ['text' => '<code>unset()</code>', 'correct' => false],
                ],
            ],

            // Q6 - PHP Basics - Enum implementing interface
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Can an enumeration (whether it\'s Pure or Backed) implement an interface?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, les énumérations PHP (Pure ou Backed) peuvent implémenter des interfaces et définir des méthodes.',
                'resourceUrl' => 'https://www.php.net/manual/fr/language.enumerations.methods.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q7 - Validator - Validation groups count
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Given the following class and constraints:
<pre><code class="language-php">&lt;?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class User implements UserInterface
{
    /**
     * @Assert\Email(groups={"registration"})
     */
    private $email;

    /**
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(min=7, groups={"registration"})
     */
    private $password;

    /**
     * @Assert\Length(min=2)
     */
    private $city;
}</code></pre>
<p>How many validation groups does this class contain?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Cette classe contient 2 groupes de validation : "registration" (explicite) et "Default" (implicite pour la contrainte sur city qui n\'a pas de groupe spécifié).',
                'resourceUrl' => 'https://symfony.com/doc/current/validation/groups.html',
                'answers' => [
                    ['text' => '2', 'correct' => true],
                    ['text' => '1', 'correct' => false],
                    ['text' => '3', 'correct' => false],
                    ['text' => '4', 'correct' => false],
                    ['text' => '5', 'correct' => false],
                    ['text' => '0', 'correct' => false],
                ],
            ],

            // Q8 - HttpKernel - KernelEvent methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What are the methods available in <code>Symfony\Component\HttpKernel\Event\KernelEvent</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'KernelEvent fournit les méthodes getKernel(), getRequest(), getRequestType() et isMainRequest(). Les méthodes liées à Response et Exception sont dans des sous-classes spécifiques.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/HttpKernel/Event/KernelEvent.php',
                'answers' => [
                    ['text' => '<code>getKernel</code>', 'correct' => true],
                    ['text' => '<code>getRequest</code>', 'correct' => true],
                    ['text' => '<code>getRequestType</code>', 'correct' => true],
                    ['text' => '<code>isMainRequest</code>', 'correct' => true],
                    ['text' => '<code>hasResponse</code>', 'correct' => false],
                    ['text' => '<code>getResponse</code>', 'correct' => false],
                    ['text' => '<code>hasRequest</code>', 'correct' => false],
                    ['text' => '<code>hasException</code>', 'correct' => false],
                    ['text' => '<code>getException</code>', 'correct' => false],
                ],
            ],

            // Q11 - Doctrine - Persisting entities
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'What should you use to persist an entity to the database?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'L\'EntityManager est utilisé pour persister les entités dans la base de données via persist() et flush().',
                'resourceUrl' => 'https://symfony.com/doc/current/doctrine.html',
                'answers' => [
                    ['text' => 'The EntityManager.', 'correct' => true],
                    ['text' => 'The EntityRepository.', 'correct' => false],
                    ['text' => 'The EntityInterface.', 'correct' => false],
                ],
            ],

            // Q12 - Runtime - Environment variables override
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'],
                'text' => 'Is it possible to set the environment variable name that stores the name of the configuration environment to use when running the application?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, il est possible de configurer le nom de la variable d\'environnement qui stocke l\'environnement de configuration via les options du composant Runtime.',
                'resourceUrl' => 'https://symfony.com/doc/5.4/components/runtime.html#using-options, https://symfony.com/doc/5.4/configuration.html#configuration-environments',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q14 - Forms - Form handling with GET method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Can this form (with default configuration) be submitted?
<pre><code class="language-php">class FooController extends AbstractController
{
    #[Route(\'/foo\', name: \'foo\', methods: [\'GET\'])]
    public function foo(Request $request)
    {
        $form = $this-&gt;createForm(FooType::class);
        $form-&gt;handleRequest($request);

        if ($form-&gt;isSubmitted() &amp;&amp; $form-&gt;isValid()) {
            // ...
        }
    }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, par défaut les formulaires Symfony n\'acceptent que les requêtes POST. Avec une route qui n\'accepte que GET, le formulaire ne pourra pas être soumis.',
                'resourceUrl' => 'https://symfony.com/doc/current/forms.html#rendering-forms',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q15 - HTTP - Must-understand usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which information is used to store a response that uses the <code>must-understand</code> directive?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'La directive must-understand indique que le cache ne doit stocker la réponse que s\'il comprend le code de statut HTTP.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control#must-understand',
                'answers' => [
                    ['text' => 'The status code', 'correct' => true],
                    ['text' => 'The value of the <code>Etag</code> directive', 'correct' => false],
                    ['text' => 'The value of the <code>Last-Modified</code> directive', 'correct' => false],
                    ['text' => 'The value of the <code>Expires</code> directive', 'correct' => false],
                ],
            ],

            // Q16 - Twig - Inheritance with parent()
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'All the pages of a website must have a <code>common.css</code> stylesheet. In addition, the homepage needs to have an extra stylesheet <code>home.css</code>. How to achieve that?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'En Twig, la fonction parent() permet d\'inclure le contenu du bloc parent. C\'est la façon correcte d\'étendre un bloc tout en conservant son contenu original.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/tags/extends.html',
                'answers' => [
                    ['text' => 'base.html.twig
<pre><code class="language-twig">&lt;html&gt;
&lt;head&gt;
{% block stylesheet %}
    &lt;link rel="stylesheet" href="common.css"&gt;
{% endblock %}    
{# ... #}</code></pre>
home.html.twig
<pre><code class="language-twig">{% extends \'base.html.twig\' %}

{% block stylesheet %}
    {{ parent() }}
    &lt;link rel="stylesheet" href="home.css"&gt;
{% endblock %}    
{# ... #}</code></pre>', 'correct' => true],
                    ['text' => 'base.html.twig
<pre><code class="language-twig">&lt;html&gt;
&lt;head&gt;
{% block stylesheet %}
    &lt;link rel="stylesheet" href="common.css"&gt;
{% endblock %}    
{# ... #}</code></pre>
home.html.twig
<pre><code class="language-twig">{% extends \'base.html.twig\' %}

{% block stylesheet %}
    {{ parent_block(\'stylesheet\') }}
    &lt;link rel="stylesheet" href="home.css"&gt;
{% endblock %}    
{# ... #}</code></pre>', 'correct' => false],
                    ['text' => 'base.html.twig
<pre><code class="language-twig">&lt;html&gt;
&lt;head&gt;
{% block stylesheet %}
    &lt;link rel="stylesheet" href="common.css"&gt;
{% endblock %}    
{# ... #}</code></pre>
home.html.twig
<pre><code class="language-twig">{% extends \'base.html.twig\' %}

{% block stylesheet %}
    {{ parent(\'stylesheet\') }}
    &lt;link rel="stylesheet" href="home.css"&gt;
{% endblock %}    
{# ... #}</code></pre>', 'correct' => false],
                ],
            ],

            // Q17 - HTTP - Safe HTTP verbs
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which HTTP verbs are safe?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les méthodes HTTP sûres sont celles qui ne modifient pas l\'état du serveur : GET, HEAD, OPTIONS et TRACE.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc7231#section-4.2.1, https://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol#Safe_methods',
                'answers' => [
                    ['text' => '<code>GET</code>', 'correct' => true],
                    ['text' => '<code>HEAD</code>', 'correct' => true],
                    ['text' => '<code>OPTIONS</code>', 'correct' => true],
                    ['text' => '<code>TRACE</code>', 'correct' => true],
                    ['text' => '<code>POST</code>', 'correct' => false],
                    ['text' => '<code>PUT</code>', 'correct' => false],
                    ['text' => '<code>DELETE</code>', 'correct' => false],
                    ['text' => '<code>PATCH</code>', 'correct' => false],
                    ['text' => '<code>COPY</code>', 'correct' => false],
                    ['text' => '<code>LINK</code>', 'correct' => false],
                ],
            ],

            // Q23 - Twig - Use tag expression
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Could the template reference used in <code>use</code> be an expression?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, le tag use ne peut utiliser qu\'une chaîne littérale comme référence de template, pas une expression.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/use.html',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q24 - Runtime - DotEnv putenv
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'],
                'text' => 'Given the context where an application runs under the <code>prod</code> environment and an external library that use <code>getenv()</code> to access environment variables, is it possible to force <code>DotEnv</code> to use <code>putenv()</code> instead of defining environment variables at the machine level?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, il est possible de forcer DotEnv à utiliser putenv() via les options de configuration du composant Runtime.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/components/runtime.html#using-options',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q26 - Validator - Constraint classes purpose
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'In validation, what is the purpose of the Constraint classes?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les classes Constraint définissent les règles de validation. La logique de validation est implémentée dans les classes Validator correspondantes.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/validation/custom_constraint.html, https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/Validator/Constraint.php',
                'answers' => [
                    ['text' => 'To define the rules to validate.', 'correct' => true],
                    ['text' => 'To define the validation logic.', 'correct' => false],
                    ['text' => 'To define the validation groups.', 'correct' => false],
                ],
            ],

            // Q27 - HttpClient - getHeaders signature
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'What is the signature of the <code>Symfony\Contracts\HttpClient\ResponseInterface::getHeaders</code> method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La signature est public function getHeaders(bool $throw = true): array. Par défaut, une exception est lancée si la réponse a un code d\'erreur.',
                'resourceUrl' => 'https://symfony.com/doc/6.0/http_client.html#handling-exceptions',
                'answers' => [
                    ['text' => '<code>public function getHeaders(bool $throw = true): array</code>', 'correct' => true],
                    ['text' => '<code>public function getHeaders(bool $throw = false): array</code>', 'correct' => false],
                    ['text' => '<code>public function getHeaders(): array</code>', 'correct' => false],
                ],
            ],

            // Q28 - OptionsResolver - setAllowedTypes boolean
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:OptionsResolver'],
                'text' => 'Which of the following are valid types to use in <code>setAllowedTypes</code> method of <code>Symfony\Component\OptionsResolver\OptionsResolver</code> to validate a boolean value?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les deux formes "bool" et "boolean" sont valides pour valider un booléen avec setAllowedTypes().',
                'resourceUrl' => 'https://symfony.com/doc/3.0/components/options_resolver.html#type-validation, https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/OptionsResolver/OptionsResolver.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">"bool"</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">"boolean"</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">OptionsResolver::BOOL</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">OptionsResolver::BOOLEAN</code></pre>', 'correct' => false],
                ],
            ],

            // Q29 - PHP OOP - Nested anonymous class
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'What is the output of the following script?
<pre><code class="language-php">&lt;?php

class Foo
{
  protected $property;

  public function __construct($property = \'property\') {
    $this-&gt;property = $property;
  }

  public function setProperty($property) {
    $this-&gt;property = $property;
  }

  public function getAnonymousClass() {
    return new class($this-&gt;property) extends Foo {
      public function getProperty() {
        return $this-&gt;property;
      }
    };
  }
}

$foo = new Foo();
$foo-&gt;setProperty(\'bar\');
$anonymousClass = $foo-&gt;getAnonymousClass();

echo $anonymousClass-&gt;getProperty();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'La classe anonyme reçoit la valeur "bar" via son constructeur (hérité de Foo), donc getProperty() retourne "bar".',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.anonymous.php',
                'answers' => [
                    ['text' => '<code>bar</code>', 'correct' => true],
                    ['text' => '<code>property</code>', 'correct' => false],
                    ['text' => 'A notice or a warning is raised', 'correct' => false],
                ],
            ],

            // Q30 - HttpKernel - Controller with Closure
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Could a controller be defined using <code>\Closure</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, un contrôleur peut être défini comme une Closure, ce qui est utile pour des routes simples.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/controller.html#a-simple-controller',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q31 - PHP Basics - Spaceship operator
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What will be the output of the following code?
<pre><code class="language-php">$a = (object) ["a" =&gt; "b"]; 
$b = (object) ["a" =&gt; "c"]; 
echo $a &lt;=&gt; $b;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'opérateur spaceship compare les valeurs des propriétés. "b" < "c" donc le résultat est -1.',
                'resourceUrl' => 'http://php.net/manual/en/language.operators.comparison.php',
                'answers' => [
                    ['text' => '<code>-1</code>', 'correct' => true],
                    ['text' => '<code>0</code>', 'correct' => false],
                    ['text' => '<code>1</code>', 'correct' => false],
                ],
            ],

            // Q32 - PropertyAccess - Magic __call method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'Is the <code>__call</code> feature enabled by default?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, la fonctionnalité __call n\'est pas activée par défaut dans PropertyAccess. Elle doit être explicitement activée.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/property_access/introduction.html#magic-call-method',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q33 - Console - Terminal helpers with COLUMNS env var
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Given the context where <code>COLUMNS</code> is set as an environment variable with the value of <code>120</code>, what will be the value returned using the following code?
<pre><code class="language-php">&lt;?php

use Symfony\Component\Console\Terminal;

echo (new Terminal())-&gt;getWidth();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'La classe Terminal utilise la variable d\'environnement COLUMNS si elle est définie, donc elle retournera 120.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/Console/Terminal.php',
                'answers' => [
                    ['text' => '<code>120</code>', 'correct' => true],
                    ['text' => 'The width of the actual terminal if used in the context of a terminal', 'correct' => false],
                    ['text' => '<code>80</code>', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                    ['text' => '<code>0</code>', 'correct' => false],
                    ['text' => 'Nothing', 'correct' => false],
                    ['text' => '<code>200</code>', 'correct' => false],
                ],
            ],

            // Q36 - Console - Command::INVALID status code
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

// ...

class FooCommand extends Command
{
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    // ...

    return Command::INVALID;
  }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, Command::INVALID est une constante valide depuis Symfony 5.3 pour indiquer une utilisation incorrecte de la commande (code de retour 2).',
                'resourceUrl' => 'https://symfony.com/doc/5.3/console.html#creating-a-command',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
        ];
    }
}
