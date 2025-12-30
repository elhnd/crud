<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 15
 */
class CertificationQuestionsFixtures15 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures14::class];
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
            // Q2 - VarExporter - SplObjectStorage instantiation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarExporter'] ?? $subcategories['Symfony:Services'],
                'text' => 'How can you instantiate a <code>SplObjectStorage</code> with VarExporter?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Pour instancier un SplObjectStorage avec VarExporter, il faut utiliser la clé spéciale "\0" avec un tableau contenant les paires objet/info alternées.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/var_exporter.html',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$theObject = Instantiator::instantiate(SplObjectStorage::class, [
    [$object1, $info1, $object2, $info2...],
]);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$theObject = Instantiator::instantiate(SplObjectStorage::class, [
    "\0" => [$object1, $info1, $object2, $info2...],
]);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$theObject = Instantiator::instantiate(SplObjectStorage::class, [
    [$object1 => $info1, $object2 => $info2...],
]);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$theObject = Instantiator::instantiate(SplObjectStorage::class, [
    [$object1, $object2...],
    [$info1, $info2...],
]);</code></pre>', 'correct' => false],
                ],
            ],

            // Q3 - Expression Language - Logical Operators
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'] ?? $subcategories['Symfony:Services'],
                'text' => 'What will be displayed by the following code?
<pre><code class="language-php">var_dump($language->evaluate(
    \'life < universe or life < everything\',
    array(
        \'life\' => 10,
        \'universe\' => 10,
        \'everything\' => 22,
    )
));</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'expression évalue: (10 < 10) or (10 < 22) = false or true = true. L\'opérateur "or" retourne true si au moins une des conditions est vraie.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/expression_language/syntax.html#logical-operators',
                'answers' => [
                    ['text' => 'true', 'correct' => true],
                    ['text' => 'false', 'correct' => false],
                ],
            ],

            // Q4 - Twig - String concatenation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which is a valid statement to concatenate two strings in Twig?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'En Twig, l\'opérateur de concaténation est ~ (tilde). La syntaxe correcte utilise {% set %} pour définir une variable.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/templates.html',
                'answers' => [
                    ['text' => '<pre><code class="language-twig">{% concatenated = \'foo\' ~ \'bar\' %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% set concatenated = \'foo\' ~ \'bar\' %}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-twig">{% concatenated = \'foo\'.\'bar\' %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% set concatenated = \'foo\'.\'bar\' %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% set concatenated = \'foo\' + \'bar\' %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% concatenated = \'foo\' + \'bar\' %}</code></pre>', 'correct' => false],
                ],
            ],

            // Q5 - PHP - SQL Injection
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What values in $user or $pass will modify the SQL semantics and lead to SQL injection in the code below?
<pre><code class="language-php">$query = "UPDATE users SET password=\'$pass\' WHERE user=\'$user\'";</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La valeur $pass = "foobar\' WHERE user=\'admin\' --" permet une injection SQL. Le guillemet simple ferme la chaîne password, puis modifie la clause WHERE pour cibler l\'admin, et -- commente le reste.',
                'resourceUrl' => 'http://php.net/manual/fr/security.database.sql-injection.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$pass = "foobar\' WHERE user=\'admin\' --";</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$pass = "\"foobar\" WHERE";
$user = "\"admin\"";</code></pre>', 'correct' => false],
                    ['text' => 'None', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$user = "foobar\\\' WHERE user="admin"";</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$user = "foobar\\\' WHERE user=\'admin\'";</code></pre>', 'correct' => false],
                ],
            ],

            // Q7 - PHP - Configuration directives
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'How can you get the value of the current configuration directives (php.ini) of a PHP extension?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ini_get_all() retourne toutes les directives de configuration enregistrées. La Reflection API via ReflectionExtension::getINIEntries() permet également d\'obtenir les entrées INI d\'une extension. get_loaded_extensions() ne retourne que la liste des extensions chargées, pas leurs configurations.',
                'resourceUrl' => 'http://php.net/manual/en/function.ini-get-all.php, http://php.net/manual/en/reflectionextension.getinientries.php, http://php.net/manual/en/function.get-loaded-extensions.php',
                'answers' => [
                    ['text' => 'With <code>php://config</code> stream', 'correct' => false],
                    ['text' => 'With <code>ini_get_all()</code>', 'correct' => true],
                    ['text' => 'With <code>get_loaded_extensions()</code>', 'correct' => false],
                    ['text' => 'With the reflection API', 'correct' => true],
                    ['text' => 'With the <code>$GLOBALS</code> array', 'correct' => false],
                ],
            ],

            // Q8 - DI - ContainerConfigurator Expression
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could an <code>Expression</code> be configured using <code>ContainerConfigurator</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ContainerConfigurator supporte les expressions via la fonction expr(). Cela permet d\'utiliser le langage d\'expression dans la configuration des services PHP.',
                'resourceUrl' => 'https://github.com/symfony/dependency-injection/blob/3.4/Loader/Configurator/ContainerConfigurator.php#L125',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q9 - Expression Language - Arithmetic Operators
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'] ?? $subcategories['Symfony:Services'],
                'text' => 'What will be displayed by the following code?
<pre><code class="language-php">var_dump($expressionLanguage->evaluate(
    \'life + universe * everything\',
    array(
        \'life\' => 10,
        \'universe\' => 10,
        \'everything\' => 22,
    )
));</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Selon la priorité des opérateurs, la multiplication est effectuée avant l\'addition: 10 + (10 * 22) = 10 + 220 = 230.',
                'resourceUrl' => 'http://php.net/manual/en/language.operators.precedence.php, http://symfony.com/doc/current/components/expression_language/syntax.html#arithmetic-operators',
                'answers' => [
                    ['text' => '1', 'correct' => false],
                    ['text' => '42', 'correct' => false],
                    ['text' => '230', 'correct' => true],
                    ['text' => 'true', 'correct' => false],
                    ['text' => '440', 'correct' => false],
                ],
            ],

            // Q11 - Routing - RouteCollection add
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'How do you add a <code>Route</code> to a <code>RouteCollection</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La méthode add() de RouteCollection prend deux arguments: le nom de la route et l\'objet Route. La syntaxe est $routes->add(\'route_name\', $route).',
                'resourceUrl' => 'https://symfony.com/doc/2.3/create_framework/routing.html',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$routes->addRoute(\'route_name\', $route);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$routes->add($route);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$routes->add(\'route_name\', $route);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$routes->routes->add($route);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$routes->addRoute($route);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$routes->routes->add(\'route_name\', $route);</code></pre>', 'correct' => false],
                ],
            ],

            // Q13 - PasswordHasher - needsRehash
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PasswordHasher'] ?? $subcategories['Symfony:Security'],
                'text' => 'Could a hasher determine if a password needs to be rehashed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, l\'interface PasswordHasherInterface définit la méthode needsRehash() qui permet de déterminer si un mot de passe doit être re-hashé (par exemple si les options de hachage ont changé).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/PasswordHasher/PasswordHasherInterface.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q15 - Twig - Checking for blocks
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following Twig snippet:
<pre><code class="language-twig">{% if block(\'footer\', \'common_blocks.html.twig\') is defined %}
    ...
{% endif %}</code></pre>
<p>Which of the following statements are true?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La fonction block() avec un second argument permet de vérifier si un bloc existe dans un template spécifique. Combinée avec "is defined", elle vérifie l\'existence du bloc footer dans common_blocks.html.twig.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/functions/block.html',
                'answers' => [
                    ['text' => 'The code is wrong because the <code>block()</code> function doesn\'t allow to pass a second argument.', 'correct' => false],
                    ['text' => 'The code checks if the <code>common_blocks.html.twig</code> template contains a Twig block called <code>footer</code>', 'correct' => true],
                    ['text' => 'The <code>if</code> condition will be <code>false</code> if the <code>footer</code> block exists in the <code>common_blocks.html.twig</code> template but it\'s empty (it doesn\'t have any content inside).', 'correct' => false],
                    ['text' => 'The code is wrong because the <code>is defined</code> test cannot be used with the <code>block()</code> function.', 'correct' => false],
                ],
            ],

            // Q16 - DI - Expressions as service factories
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could expressions be used as service factories?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 6.1, les expressions peuvent être utilisées comme factories de services. Cela permet d\'utiliser des expressions complexes pour créer des instances de services.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-1-expressions-as-service-factories',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q17 - BrowserKit - Cookies support
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:BrowserKit'] ?? $subcategories['Symfony:HttpFoundation'],
                'text' => 'How to configure the Client provided by the Symfony <code>BrowserKit</code> component to save a Cookie between requests?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour définir un cookie dans BrowserKit, il faut utiliser getCookieJar()->set(). Le CookieJar gère la persistance des cookies entre les requêtes.',
                'resourceUrl' => 'https://symfony.com/doc/6.0/components/browser_kit#setting-cookies',
                'answers' => [
                    ['text' => 'Use <code>$client->cookies->set(new Cookie(...))</code>', 'correct' => false],
                    ['text' => 'The Client provided by the Symfony <code>BrowserKit</code> component does not handle cookies.', 'correct' => false],
                    ['text' => 'Use <code>$client->getCookieJar()->set(new Cookie(...))</code>', 'correct' => true],
                    ['text' => 'Use <code>$client->setCookie(new Cookie(...))</code>', 'correct' => false],
                ],
            ],
        ];
    }
}
