<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 16
 */
class CertificationQuestionsFixtures16 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures15::class];
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
            // Q1 - Serializer - Handling Arrays
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'] ?? $subcategories['Symfony:Services'],
                'text' => 'With the following code:
<pre><code class="language-php">use Acme\Person;

$person1 = new Person();
$person1->setName(\'foo\');
$person1->setAge(99);
$person1->setSportsman(false);

$person2 = new Person();
$person2->setName(\'bar\');
$person2->setAge(33);
$person2->setSportsman(true);

$persons = array($person1, $person2);
$data = $serializer->serialize($persons, \'json\');</code></pre>
<p>What is the correct way to deserialize the <code>$data</code> into an <code>$persons</code> array of <code>Acme\Person</code> objects?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour désérialiser un tableau d\'objets, il faut utiliser la notation avec les crochets [] après le nom de la classe. ArrayDenormalizer gère cette syntaxe et crée un tableau d\'objets Person.',
                'resourceUrl' => 'http://symfony.com/doc/3.0/components/serializer.html#handling-arrays, https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/Serializer/Serializer.php#L107',
                'answers' => [
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

$serializer = new Serializer(
    array(new GetSetMethodNormalizer(), new ArrayDenormalizer()),
    array(new JsonEncoder())
);

$persons = $serializer->deserialize($data, \'Acme\Person[]\', \'json\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

$serializer = new Serializer(
    array(new GetSetMethodNormalizer(), new ArrayDenormalizer()),
    array(new JsonEncoder())
);

$persons = array();
$persons = $serializer->deserialize($data, \'Acme\Person\', \'json\', $persons);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

$serializer = new Serializer(
    array(new GetSetMethodNormalizer(), new ArrayDenormalizer()),
    array(new JsonEncoder())
);

$persons = $serializer->deserialize($data, \'Acme\Person\', \'json\', true);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

$serializer = new Serializer(
    array(new GetSetMethodNormalizer(), new ArrayDenormalizer()),
    array(new JsonEncoder())
);

$persons = $serializer->deserialize($data, \'Acme\Person[]\', \'json\', \'array\');</code></pre>', 'correct' => false],
                ],
            ],

            // Q2 - Routing - Scheme in Routing
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Considering the following in a template:
<pre><code class="language-twig">{{ path(\'home\') }}</code></pre>
<p>And the following declaration of route:</p>
<pre><code class="language-xml">&lt;routes&gt;
    &lt;route id="home" path="/" schemes="https"&gt;
        &lt;default key="_controller"&gt;AppBundle:Main:home&lt;/default&gt;
    &lt;/route&gt;
&lt;/routes&gt;</code></pre>
<p>What will be displayed if the current scheme is http?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Quand une route spécifie un scheme (https) et que le scheme actuel est différent (http), la fonction path() génère une URL absolue avec le scheme requis pour forcer la redirection.',
                'resourceUrl' => 'https://symfony.com/doc/3.4/routing/scheme.html',
                'answers' => [
                    ['text' => '<code>https://domain.name/</code>', 'correct' => true],
                    ['text' => '<code>http://domain.name/</code>', 'correct' => false],
                    ['text' => 'A fatal error will occur.', 'correct' => false],
                    ['text' => '<code>/</code>', 'correct' => false],
                ],
            ],
            // Q4 - HttpFoundation - FlashBag usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could a <code>FlashBag</code> be cleared?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, FlashBag possède une méthode clear() qui permet de supprimer tous les messages flash stockés.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/HttpFoundation/Session/Flash/FlashBag.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q5 - Runtime - Composer plugin
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'] ?? $subcategories['Symfony:Services'],
                'text' => 'Which event is listened by the Composer plugin of the Runtime component?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le plugin Composer du composant Runtime écoute l\'événement POST_AUTOLOAD_DUMP pour générer le fichier autoload_runtime.php après le dump de l\'autoloader.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/Runtime/Internal/ComposerPlugin.php',
                'answers' => [
                    ['text' => '<code>POST_AUTOLOAD_DUMP</code>', 'correct' => true],
                    ['text' => '<code>PRE_INSTALL</code>', 'correct' => false],
                    ['text' => '<code>POST_INSTALL</code>', 'correct' => false],
                    ['text' => '<code>PRE_AUTOLOAD_DUMP</code>', 'correct' => false],
                ],
            ],

            // Q6 - Inflector - Usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Inflector'] ?? $subcategories['Symfony:Services'],
                'text' => 'Is the <code>Inflector</code> component still available in Symfony 6.0?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, le composant Inflector a été supprimé dans Symfony 6.0. Il faut utiliser EnglishInflector du composant String à la place.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/UPGRADE-6.0.md#inflector',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q7 - HttpFoundation - Cache validation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'How to check if the Response validators (ETag, Last-Modified) match a conditional value specified in the client Request?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode isNotModified($request) compare les validateurs de la Response (ETag, Last-Modified) avec les conditions du Request (If-None-Match, If-Modified-Since) et retourne true si le contenu n\'a pas changé.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#managing-the-http-cache',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$response->isNotModified($request);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response->isOk();</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$response->isCacheable();</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$response->isModified($request);</code></pre>', 'correct' => false],
                ],
            ],

            // Q8 - PHP - PHP and HTTP PUT
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'How would you access the data sent to your PHP server using the PUT HTTP method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP ne fournit pas de superglobale $_PUT. Les données envoyées via PUT doivent être lues depuis le flux php://input. Ce flux est en lecture seule et permet d\'accéder aux données brutes du corps de la requête.',
                'resourceUrl' => 'http://php.net/manual/en/features.file-upload.put-method.php',
                'answers' => [
                    ['text' => 'Using the <code>php://input</code> stream', 'correct' => true],
                    ['text' => 'Using <code>$HTTP_PUT_VARS</code>', 'correct' => false],
                    ['text' => 'It is not possible', 'correct' => false],
                    ['text' => 'Using <code>$_PUT</code>', 'correct' => false],
                    ['text' => 'Using <code>$_POST</code>', 'correct' => false],
                ],
            ],

            // Q9 - PHP OOP - Instanceof Operator
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'What would be the output of this code?
<pre><code class="language-php">class Foo extends Bar implements Baz
{
}

$foo = new Foo();
if ($foo instanceof Foo) { echo \'Foo \'; }
if ($foo instanceof Bar) { echo \'Bar \'; }
if ($foo instanceof Baz) { echo \'Baz \'; }</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'L\'opérateur instanceof vérifie si un objet est une instance d\'une classe, d\'une classe parente ou implémente une interface. Foo est une instance de Foo, hérite de Bar et implémente Baz, donc les trois conditions sont vraies.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.operators.type.php',
                'answers' => [
                    ['text' => 'Foo Bar Baz', 'correct' => true],
                    ['text' => 'Foo', 'correct' => false],
                    ['text' => 'Foo Bar', 'correct' => false],
                    ['text' => 'Bar', 'correct' => false],
                ],
            ],
            // Q11 - Intl - Emojis
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Intl'] ?? $subcategories['Symfony:Services'],
                'text' => 'What is true about the emoji stripping functionality in the Intl component?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'La fonctionnalité de suppression d\'emojis utilise une locale artificielle appelée "strip" et des règles de remplacement basées sur le catalogue complet des emojis Unicode. Elle n\'utilise pas de regex et ne dépend pas du composant Emoji.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-emoji-improvements',
                'answers' => [
                    ['text' => 'It uses an artificial locale called <code>strip</code>', 'correct' => true],
                    ['text' => 'It uses replace rules based on the entire unicode emojis catalogue', 'correct' => true],
                    ['text' => 'It leverages the <code>Emoji</code> component', 'correct' => false],
                    ['text' => 'It uses regex to match emojis', 'correct' => false],
                ],
            ],

            // Q12 - Cache - Key / value store
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'] ?? $subcategories['Symfony:Services'],
                'text' => 'How does a cache differ from a key/value store?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un cache peut être supprimé sans faire crasher l\'application (les données seront régénérées) et ne doit pas être utilisé pour persister des données importantes. Un key/value store est conçu pour la persistance sécurisée des données.',
                'resourceUrl' => 'http://www.aerospike.com/what-is-a-key-value-store/',
                'answers' => [
                    ['text' => 'It can be deleted without making the application crash.', 'correct' => true],
                    ['text' => 'It should not be used to persist data.', 'correct' => true],
                    ['text' => 'It is safe to store data in it.', 'correct' => false],
                ],
            ],

            // Q14 - HttpFoundation - Safe response
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could a response be marked as safe?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, depuis Symfony 5.1, une Response peut être marquée comme "safe" via les méthodes setContentSafe() et isContentSafe(). Cela indique que le contenu est sûr et ne nécessite pas d\'échappement supplémentaire.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.1/src/Symfony/Component/HttpFoundation/Response.php#L1242',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q15 - Yaml - Dumping Multi-line Literal Blocks
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'] ?? $subcategories['Symfony:Services'],
                'text' => 'What will be the output of the following code?
<pre><code class="language-php">$string = array("string" => "Multiple
Line
String");
$yaml = Yaml::dump($string);
echo $yaml;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Par défaut, Yaml::dump() convertit les chaînes multi-lignes en utilisant la notation avec \\n échappés entre guillemets doubles, sauf si on utilise des flags spécifiques pour le format littéral.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/yaml.html#dumping-multi-line-literal-blocks',
                'answers' => [
                    ['text' => '<pre><code>string: "Multiple\nLine\nString"</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>string: Multiple\nLine\nString</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>string: "Multiple
Line
String"</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>string: |
     Multiple
     Line
     String</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>Multiple\nLine\nString</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>"Multiple\nLine\nString"</code></pre>', 'correct' => false],
                ],
            ],

            // Q16 - Serializer - Serialization Groups
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'] ?? $subcategories['Symfony:Services'],
                'text' => 'Given the following code:
<pre><code class="language-php">// src/Dto/Product.php
class Product 
{
    #[Groups([\'light\'])]
    public ?string $name = null;

    #[Groups([\'show\'])]
    #[SerializedName(\'description\')]
    public ?string $desc = null;

    #[Ignore]
    public ?string $internalStatus = null;

    #[Groups([\'show\'])]
    public function getStatus() {
        return $this->internalStatus;
    }
}

// src/Controller/MyController.php
$product = new Product();
$product->name = "Rubber";
$product->desc = "Basic rubber";
$product->internalStatus = "available";

$data = $serializer->serialize($product, \'json\', [
    \'groups\' => [\'light\', \'show\'],
])</code></pre>
<p>What will <code>$data</code> contain?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Avec les groupes "light" et "show", name (groupe light), desc renommé en description (groupe show) et getStatus() (groupe show) sont sérialisés. internalStatus est ignoré via #[Ignore] mais getStatus() retourne sa valeur.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/serializer.html',
                'answers' => [
                    ['text' => '<pre><code class="language-json">{
    "name": "Rubber",
    "description": "Basic rubber",
    "status": "available"
}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-json">{
    "name": "Rubber",
    "desc": "Basic rubber",
    "internalStatus": "available",
    "status": "available"
}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-json">{
    "name": "Rubber",
    "desc": "Basic rubber"
}</code></pre>', 'correct' => false],
                ],
            ],
        ];
    }
}
