<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 22
 */
class CertificationQuestionsFixtures22 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures21::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Required categories not found. Please load CertificationExamFixtures first.');
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

            // Q2 - Serializer - Serialization Context
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">#[Serializer\Context(
    normalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    groups: [\'extended\']
)]
public \DateTime $date;</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 5.3, l\'attribut #[Context] permet de définir le contexte de sérialisation directement sur les propriétés, incluant le format de date et les groupes.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-3-inlined-serialization-context',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q3 - HttpFoundation - Sending the Response
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What is the aim of the <code>prepare()</code> method in <code>Symfony\Component\HttpFoundation\Response</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode prepare() ajuste la Response pour s\'assurer qu\'elle est conforme à la spécification RFC 2616 (HTTP/1.1). Elle ajuste les headers en fonction de la requête.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/components/http_foundation.html#sending-the-response, https://github.com/symfony/symfony/blob/0baa58d4e4bb006c4ae68f75833b586bd3cb6e6f/src/Symfony/Component/HttpFoundation/Response.php#L204',
                'answers' => [
                    ['text' => 'To tweak the Response to ensure that it is compliant with RFC 2616.', 'correct' => true],
                    ['text' => 'To convert the Response to a string that is compatible with the HTTP response message format.', 'correct' => false],
                    ['text' => 'To send the Response to the client.', 'correct' => false],
                ],
            ],

            // Q5 - PHP - Type declaration
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What type-hint would you use in place of the <code>/* ... */</code>?
<pre><code class="language-php">&lt;?php

function bar(/* ... */ $a)
{
     return $a();
}</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour appeler $a(), on peut utiliser "callable". Un objet peut aussi être appelé s\'il implémente __invoke(), mais le type-hint le plus approprié est "callable".',
                'resourceUrl' => 'http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration, https://3v4l.org/Bq4G8',
                'answers' => [
                    ['text' => 'callable', 'correct' => true],
                    ['text' => '<code>traversable</code>', 'correct' => false],
                    ['text' => '<code>object</code>', 'correct' => true],
                    ['text' => '<code>array</code>', 'correct' => true],
                    ['text' => '<code>void</code>', 'correct' => false],
                ],
            ],

            // Q6 - Messenger - Ping webhook handler
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Given the following code:
<pre><code class="language-php">// ..
$bus->dispatch(new PingWebhookMessage(\'GET\', \'https://example.com/status\'));</code></pre>
<p>What happens if the HTTP response code is 3xx/4xx/5xx?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PingWebhookMessageHandler lance une exception si le code de réponse HTTP indique une erreur (3xx/4xx/5xx), car la méthode throwOnError() est appelée sur la réponse.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-4-more-built-in-message-handlers#ping-webhook-handler, https://github.com/symfony/symfony/blob/6.4/src/Symfony/Component/HttpClient/Messenger/PingWebhookMessageHandler.php#L30',
                'answers' => [
                    ['text' => 'An exception is thrown', 'correct' => true],
                    ['text' => 'A <code>Response</code> object is returned with the appropriate status code', 'correct' => false],
                ],
            ],

            // Q7 - Yaml - Booleans elements
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'],
                'text' => 'Which of the following values are available to define a boolean element in YAML?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'En YAML (spécification 1.2 utilisée par Symfony), seuls "true" et "false" sont reconnus comme booléens. Les valeurs numériques 0 et 1 sont interprétées comme des entiers.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/yaml/yaml_format.html#booleans',
                'answers' => [
                    ['text' => '<code>true</code>', 'correct' => true],
                    ['text' => '<code>false</code>', 'correct' => true],
                    ['text' => '<code>1</code>', 'correct' => false],
                    ['text' => '<code>0</code>', 'correct' => false],
                ],
            ],

            // Q8 - Filesystem - Path usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Given the following code, what will be displayed?
<pre><code class="language-php">&lt;?php

use Symfony\Component\Filesystem\Path;

echo Path::getRoot("/etc/apache2/sites-available");</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode Path::getRoot() retourne la racine du chemin. Pour un chemin absolu Unix, c\'est "/" (slash).',
                'resourceUrl' => 'https://symfony.com/doc/5.4/components/filesystem.html#finding-directories-root-directories, https://github.com/symfony/filesystem/blob/5.4/Path.php#L207',
                'answers' => [
                    ['text' => '<code>/</code>', 'correct' => true],
                    ['text' => '<code>/etc/</code>', 'correct' => false],
                    ['text' => '<code>/etc/apache2/</code>', 'correct' => false],
                    ['text' => '<code>/etc/apache2/sites-available</code>', 'correct' => false],
                    ['text' => '<code>/etc</code>', 'correct' => false],
                    ['text' => '<code>/etc/apache2</code>', 'correct' => false],
                ],
            ],

            // Q9 - Security - Security Attributes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What attribute helps enforce access control on your resources?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'L\'attribut #[IsGranted] permet de restreindre l\'accès aux contrôleurs ou méthodes en vérifiant les permissions de l\'utilisateur.',
                'resourceUrl' => 'https://symfony.com/doc/6.2/reference/attributes.html',
                'answers' => [
                    ['text' => '<code>#[IsGranted]</code>', 'correct' => true],
                    ['text' => '<code>#[HasRole]</code>', 'correct' => false],
                    ['text' => '<code>#[HasAccess]</code>', 'correct' => false],
                    ['text' => '<code>#[AccessControl]</code>', 'correct' => false],
                    ['text' => '<code>#[RestrictAccess]</code>', 'correct' => false],
                    ['text' => '<code>#[AccessManager]</code>', 'correct' => false],
                ],
            ],

            // Q10 - PHP - Security SHA-512
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'How would you use the SHA-512 hash algorithm in PHP?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La fonction hash() avec l\'algorithme "sha512" et la fonction crypt() avec le préfixe approprié permettent d\'utiliser SHA-512. Il n\'existe pas de fonction sha512() native en PHP.',
                'resourceUrl' => 'http://php.net/manual/en/function.crypt.php, http://php.net/manual/en/function.hash.php',
                'answers' => [
                    ['text' => 'Using the hash() function', 'correct' => true],
                    ['text' => 'Using the crypt() function', 'correct' => true],
                    ['text' => 'Using the sha512() function', 'correct' => false],
                ],
            ],

            // Q11 - Expression Language - Passing in Variables
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'What is the way to return <code>Honeycrisp</code> with the following code?
<pre><code class="language-php">use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$language = new ExpressionLanguage();

class Apple
{
    public $variety;
}

$apple = new Apple();
$apple->variety = \'Honeycrisp\';</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour passer des variables à ExpressionLanguage, il faut utiliser un tableau associatif où les clés sont les noms des variables utilisées dans l\'expression.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/expression_language.html#passing-in-variables',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$language->evaluate(
    \'fruit.variety\',
    array(
        \'fruit\' => $apple,
    )
);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$language->evaluate(\'apple.variety\', $apple);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$language->evaluate(\'variety\', $apple);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$language->compile(\'apple.variety\', $apple);</code></pre>', 'correct' => false],
                ],
            ],

            // Q12 - FrameworkBundle - Controllers isGranted
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'Up to Symfony 4, where was defined the <code>isGranted()</code> method available for any controller?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Jusqu\'à Symfony 4, la méthode isGranted() était définie dans le trait ControllerTrait, qui était utilisé par AbstractController et Controller.',
                'resourceUrl' => 'https://github.com/symfony/framework-bundle/blob/4.4/Controller/ControllerTrait.php#L175',
                'answers' => [
                    ['text' => '<code>ControllerTrait</code>', 'correct' => true],
                    ['text' => '<code>AbstractController</code>', 'correct' => false],
                    ['text' => '<code>Controller</code>', 'correct' => false],
                    ['text' => '<code>ServiceContainerAware</code>', 'correct' => false],
                ],
            ],

            // Q14 - HttpKernel - ControllerArgumentsEvent usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Could the controller attributes be retrieved from within <code>ControllerArgumentsEvent</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 6.2, ControllerArgumentsEvent permet de récupérer les attributs du contrôleur via la méthode getAttributes().',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/HttpKernel/Event/ControllerArgumentsEvent.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q15 - HTTP - Status code Not Implemented
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What is the HTTP status code for <strong>Not Implemented</strong>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le code HTTP 501 signifie "Not Implemented" - le serveur ne supporte pas la fonctionnalité requise pour traiter la requête.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2616#section-6.1.1, https://tools.ietf.org/html/rfc2616#section-10.5.2',
                'answers' => [
                    ['text' => '501', 'correct' => true],
                    ['text' => '500', 'correct' => false],
                    ['text' => '502', 'correct' => false],
                    ['text' => '503', 'correct' => false],
                    ['text' => '504', 'correct' => false],
                    ['text' => '505', 'correct' => false],
                ],
            ],

            // Q16 - Twig - Strict Variables Mode
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following Twig code snippet:
<pre><code class="language-twig">The {{ color }} car!</code></pre>
<p>What will be the result of evaluating this template without passing it a <code>color</code> variable when the <code>strict_variables</code> global setting is on?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Avec strict_variables activé, Twig lève une exception Twig_Error_Runtime (ou Twig\Error\RuntimeError) si une variable n\'est pas définie.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/templates.html#variables, https://twig.symfony.com/doc/2.x/templates.html#variables, https://twig.symfony.com/doc/2.x/api.html#environment-options',
                'answers' => [
                    ['text' => 'Twig will raise a <code>Twig_Error_Runtime</code> exception preventing the template from being evaluated.', 'correct' => true],
                    ['text' => 'The template will be succesfully evaluated and the string <code>The  car!</code> will be displayed in the web browser.', 'correct' => false],
                    ['text' => 'The template will be succesfully evaluated and the string <code>The empty car!</code> will be displayed in the web browser.', 'correct' => false],
                    ['text' => 'The template will be partially evaluated and the string <code>The</code> will be displayed in the web browser.', 'correct' => false],
                ],
            ],

            // Q17 - PHP Arrays - array_reduce
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays & Collections'],
                'text' => 'What is the output of the following script?
<pre><code class="language-php">&lt;?php

function reducer($total, $elt)
{
    return $elt + $total;
}

$arr = [1, 2, 3, 4, 5];

echo array_reduce($arr, \'reducer\', 1);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_reduce applique la fonction reducer itérativement. Avec une valeur initiale de 1: 1+1=2, 2+2=4, 4+3=7, 7+4=11, 11+5=16.',
                'resourceUrl' => 'https://php.net/array, https://php.net/manual/en/function.array-reduce.php',
                'answers' => [
                    ['text' => '16', 'correct' => true],
                    ['text' => '15', 'correct' => false],
                    ['text' => '4', 'correct' => false],
                    ['text' => '5', 'correct' => false],
                ],
            ],

            // Q18 - HTTP - Status codes for client errors
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What are the HTTP status codes for client errors?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Les codes HTTP 4xx indiquent des erreurs client (400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, etc.).',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2616#section-6.1.1',
                'answers' => [
                    ['text' => '4xx', 'correct' => true],
                    ['text' => '1xx', 'correct' => false],
                    ['text' => '2xx', 'correct' => false],
                    ['text' => '3xx', 'correct' => false],
                    ['text' => '5xx', 'correct' => false],
                ],
            ],

            // Q19 - Doctrine ORM - Bidirectional associations
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which of the following rules are true about bidirectional associations in Doctrine ORM?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Une relation bidirectionnelle a toujours un côté propriétaire (owning side) et un côté inverse (inverse side). Doctrine ne vérifie que le côté propriétaire pour les changements.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/unitofwork-associations.html#association-updates-owning-side-and-inverse-side',
                'answers' => [
                    ['text' => 'A bidirectional relationship has both an owning side and an inverse side.', 'correct' => true],
                    ['text' => 'Doctrine will only check the owning side of an association for changes.', 'correct' => true],
                    ['text' => 'A bidirectional relationship may only have an owning side.', 'correct' => false],
                    ['text' => 'Doctrine will check both the owning side and the inverse side of an association for changes.', 'correct' => false],
                ],
            ],
        ];
    }
}
