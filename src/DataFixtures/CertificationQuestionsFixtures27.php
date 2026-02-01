<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 27
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures27 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures26::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \Exception('Base categories not found. Please load AppFixtures first.');
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
            // Q1 - PHP Arrays - Adding values to array
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'How do you add the value <code>10</code> to an array called <code>$myArray</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_unshift() ajoute un élément au début du tableau. array_merge() peut fusionner des tableaux mais nécessite un tableau comme second argument. array_shift() supprime et retourne le premier élément.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.types.array.php, https://www.php.net/manual/en/function.array-merge, https://www.php.net/manual/en/function.array-shift, https://www.php.net/manual/en/function.array-unshift',
                'answers' => [
                    ['text' => '<pre><code class="language-php">array_unshift($myArray, 10);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">array_shift($myArray, 10);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$myArray = array_merge($myArray, [10]);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$myArray = array_merge($myArray, 10);</code></pre>', 'correct' => false],
                ],
            ],

            // Q6 - Translation - ICU message format
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'How can you have a different translation message for a number in the range -infinity to 2 with ICU (i.e. <code>]-Inf, 2]</code> with the legacy translation system) ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'La fonction ICU "plural" permet de gérer les intervalles de nombres, y compris les intervalles ouverts avec -infinity.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/translation/message_format.html',
                'answers' => [
                    ['text' => 'with the <code>plural</code> ICU function', 'correct' => true],
                    ['text' => 'with the <code>select</code> ICU function', 'correct' => false],
                    ['text' => 'ICU doesn\'t provide a way to do that', 'correct' => false],
                    ['text' => 'with the <code>interval</code> ICU function', 'correct' => false],
                    ['text' => 'with the <code>ordinal</code> ICU function', 'correct' => false],
                ],
            ],

            // Q8 - DI - ContainerConfigurator imports
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could imports be configured using <code>ContainerConfigurator</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ContainerConfigurator fournit une méthode import() pour importer d\'autres fichiers de configuration.',
                'resourceUrl' => 'https://github.com/symfony/dependency-injection/blob/3.4/Loader/Configurator/ContainerConfigurator.php#L54',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q9 - VarDumper - Enumerations support
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarDumper'],
                'text' => 'Could enumerations be dumped?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le composant VarDumper supporte le dump des énumérations PHP 8.1+ depuis Symfony 5.4.',
                'resourceUrl' => 'https://github.com/symfony/symfony/pull/41072',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q12 - Config - Array overwriting
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'How to overwrite a configuration array if the value is also defined in a second configuration array ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode performNoDeepMerging() permet d\'écraser complètement un tableau de configuration au lieu de le fusionner récursivement.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/config/definition.html#merging-options',
                'answers' => [
                    ['text' => 'By using <code>->performNoDeepMerging()</code>', 'correct' => true],
                    ['text' => 'By using <code>->performDeepMerging()</code>', 'correct' => false],
                    ['text' => 'By using <code>->canBeOverwritten()</code>', 'correct' => false],
                    ['text' => 'By using <code>->enableMerging()</code>', 'correct' => false],
                ],
            ],

            // Q14 - PHP Arrays - Variadic functions
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">function sum( ??? )
{
    return array_sum($args);
}

echo 6 === sum(1, 2, 3) ? \'Yes\' : \'No\';</code></pre>
<p>What must the <code>???</code> placeholder be replaced with in order to make the script print the string <code>Yes</code> on the standard output since PHP 5.6?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'opérateur splat ...$args (variadic) permet de capturer un nombre variable d\'arguments dans un tableau depuis PHP 5.6.',
                'resourceUrl' => 'http://php.net/manual/en/functions.arguments.php, http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list',
                'answers' => [
                    ['text' => '<code>...$args</code>', 'correct' => true],
                    ['text' => '<code>array $args</code>', 'correct' => false],
                    ['text' => '<code>$args</code>', 'correct' => false],
                    ['text' => '<code>$args = func_get_args()</code>', 'correct' => false],
                ],
            ],

            // Q15 - DI - FrozenParameterBag exception
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which exception is thrown when clearing parameters from a <code>FrozenParameterBag</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'FrozenParameterBag lance une LogicException lors d\'une tentative de modification car le conteneur est compilé et les paramètres sont gelés.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php',
                'answers' => [
                    ['text' => '<code>LogicException</code>', 'correct' => true],
                    ['text' => '<code>BadMethodCallException</code>', 'correct' => false],
                    ['text' => '<code>InvalidArgumentException</code>', 'correct' => false],
                    ['text' => '<code>RuntimeException</code>', 'correct' => false],
                ],
            ],

            // Q16 - Twig - Block existence check
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In a Twig template that extends other templates, how can you check if any of the parent templates define some block called <code>sidebar</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour vérifier l\'existence d\'un bloc Twig, on utilise le test "is defined" avec la fonction block().',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/functions/block.html',
                'answers' => [
                    ['text' => '<pre><code class="language-twig">{% if block(\'sidebar\') is defined %}
  ...
{% endif %}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-twig">{% if defined(block(\'sidebar\')) %}
  ...
{% endif %}</code></pre>', 'correct' => false],
                    ['text' => 'You can\'t check if a Twig block exists.', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% if block(\'sidebar\')|defined %}
  ...
{% endif %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% if block(\'sidebar\') ?? block(\'\') %}
  ...
{% endif %}</code></pre>', 'correct' => false],
                ],
            ],

            // Q18 - PasswordHasher - Multiple hashers
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PasswordHasher'],
                'text' => 'Can multiple hashers be declared in the <code>security.yaml</code> file?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, il est possible de déclarer plusieurs hashers pour différentes classes d\'utilisateurs dans security.yaml.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/security/passwords.html, https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/PasswordHasher/Hasher/PasswordHasherFactory.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q20 - PHP - Error class
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Exceptions'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">try {
    call_this_undefined_function();
} catch (???? $e) {
    echo \'Oups, there is a serious problem there!\';
}</code></pre>
<p>Since PHP 7.0, which type/class could be used to replace the <code>????</code> placeholder in order to output <code>Oups, there is a serious problem there!</code> ?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Depuis PHP 7.0, les erreurs fatales comme l\'appel d\'une fonction indéfinie lancent une Error, pas une Exception.',
                'resourceUrl' => 'http://php.net/manual/en/language.exceptions.php, http://php.net/manual/en/class.error.php',
                'answers' => [
                    ['text' => '<code>Error</code>', 'correct' => true],
                    ['text' => '<code>Warning</code>', 'correct' => false],
                    ['text' => '<code>Exception</code>', 'correct' => false],
                    ['text' => '<code>BadFunctionCallException</code>', 'correct' => false],
                    ['text' => '<code>ErrorException</code>', 'correct' => false],
                ],
            ],

            // Q23 - FrameworkBundle - sendEarlyHints return type
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the return type of <code>Symfony\Bundle\FrameworkBundle\Controller\AbstractController::sendEarlyHints</code> ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode sendEarlyHints() retourne un objet Response.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.3/src/Symfony/Bundle/FrameworkBundle/Controller/AbstractController.php#L410',
                'answers' => [
                    ['text' => '<code>Response</code>', 'correct' => true],
                    ['text' => '<code>void</code>', 'correct' => false],
                    ['text' => '<code>bool</code>', 'correct' => false],
                    ['text' => '<code>\\Generator</code>', 'correct' => false],
                ],
            ],

            // Q24 - FrameworkBundle - Vault directory override
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'Could the vault directory be overridden?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le répertoire vault peut être configuré dans la configuration framework.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/reference/configuration/framework.html#vault-directory',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q25 - Serializer - Serialize to JSON
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Which of the following is the correct usage for serializing the <code>$person</code> object into <code>json</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La syntaxe correcte est serialize($object, $format).',
                'resourceUrl' => 'http://symfony.com/doc/current/components/serializer.html#serializing-an-object',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$serializer->serialize($person, \'json\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$serializer->serialize($person)->toJson();</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$serializer->toJson($person);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$serializer->serialize(\'json\', $person);</code></pre>', 'correct' => false],
                ],
            ],

            // Q27 - HttpFoundation - Vary header multiple values
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Which are the valid ways of caching a <code>Response</code> based not only on the URI but also the value of the <code>Accept-Encoding</code> and <code>User-Agent</code> request headers?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'setVary() peut être appelé plusieurs fois pour ajouter des headers. On peut aussi passer une string avec valeurs séparées par des virgules, ou utiliser headers->set() avec une string.',
                'resourceUrl' => 'http://symfony.com/doc/current/http_cache/cache_vary.html, https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/HttpFoundation/Response.php#L1042, https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/HttpFoundation/ResponseHeaderBag.php#L98',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$response->setVary(\'Accept-Encoding\');
$response->setVary(\'User-Agent\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response->setVary(\'Accept-Encoding, User-Agent\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response->headers->set(\'Vary\', \'Accept-Encoding, User-Agent\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response->setVary([\'Accept-Encoding\', \'User-Agent\']);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response->headers->set(\'Vary\', [\'Accept-Encoding\', \'User-Agent\']);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$response->headers->set(\'Vary\', \'Accept-Encoding\');
$response->headers->set(\'Vary\', \'User-Agent\');</code></pre>', 'correct' => false],
                    ['text' => 'This is not possible without calling a reverse proxy', 'correct' => false],
                    ['text' => 'This is the default behavior', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$response->setVary(\'Accept-Encoding\');
$response->setVary(\'User-Agent\', false);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$response->headers->set(\'Vary\', \'Accept-Encoding\');
$response->headers->set(\'Vary\', \'User-Agent\', false);</code></pre>', 'correct' => false],
                ],
            ],

            // Q28 - FrameworkBundle - data_collector tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the tag to use to create a class that collects custom data for the profiler?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le tag data_collector est utilisé pour enregistrer des collecteurs de données personnalisés pour le profiler.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/dic_tags.html#data-collector',
                'answers' => [
                    ['text' => '<code>data_collector</code>', 'correct' => true],
                    ['text' => '<code>kernel.data_collector</code>', 'correct' => false],
                    ['text' => '<code>debug.data_collector</code>', 'correct' => false],
                    ['text' => '<code>profiler.data_collector</code>', 'correct' => false],
                ],
            ],

            // Q31 - HttpFoundation - Query string parameter names
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Regarding this URI : <code>/example?tags.id=2</code>
<p>What will be the content of <code>$request->query->all()</code> ?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'PHP convertit les points dans les noms de paramètres en tableaux. tags.id devient [\'tags\' => [\'id\' => 2]].',
                'resourceUrl' => 'http://www.php.net/manual/en/language.variables.basics.php, http://www.php.net/manual/en/language.variables.external.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">[ 
    \'tags\' => [\'id\' => 2] 
]
</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">[ 
    \'tags_id\' => 2 
]
</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">[ 
    \'tags.id\' => 2 
]
</code></pre>', 'correct' => false],
                ],
            ],

            // Q32 - PHP - PDO extension
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'You want to fully overload all PDO features with OOP. What do you need to accomplish that?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Pour surcharger complètement PDO, il faut étendre la classe PDO, étendre PDOStatement et utiliser le paramètre ATTR_STATEMENT_CLASS pour spécifier la classe de statement personnalisée.',
                'resourceUrl' => 'http://www.php.net/pdo',
                'answers' => [
                    ['text' => 'Extend the <code>PDO</code> class', 'correct' => true],
                    ['text' => 'Extend the <code>PDOStatement</code> class', 'correct' => true],
                    ['text' => 'Use the PDO parameter
<pre><code class="language-php">PDO::ATTR_STATEMENT_CLASS</code></pre>', 'correct' => true],
                    ['text' => 'Call 
<pre><code class="language-php">PDO::setStatementClass()</code></pre>', 'correct' => false],
                    ['text' => 'Use the PDO parameter
<pre><code class="language-php">PDO::ATTR_USE_CLASS</code></pre>', 'correct' => false],
                ],
            ],

            // Q33 - Doctrine - Owning side
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'] ?? null,
                'text' => 'What side of the relation is the owning side ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le côté ManyToOne est toujours le côté propriétaire (owning side) dans une relation bidirectionnelle.',
                'resourceUrl' => 'https://www.doctrine-project.org/projects/doctrine-orm/en/3.1/reference/unitofwork-associations.html',
                'answers' => [
                    ['text' => '<code>ManyToOne</code>', 'correct' => true],
                    ['text' => '<code>OneToMany</code>', 'correct' => false],
                ],
            ],

            // Q35 - HTTP - must-understand with no-store
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Should the <code>must-understand</code> directive be coupled with <code>no-store</code> for fallback behavior?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'Oui, must-understand doit être couplé avec no-store pour assurer un comportement de fallback sécurisé.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control#directives',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
        ];
    }
}
