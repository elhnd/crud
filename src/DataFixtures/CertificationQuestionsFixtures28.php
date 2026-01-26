<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 28
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures28 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures27::class];
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
            // Q1 - PHP Basics - Type hinting
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Is it possible to type hint variable in function definition?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, depuis PHP 7.0, il est possible de typer les arguments de fonctions avec des types scalaires (int, string, float, bool) et depuis PHP 5.0 avec des classes/interfaces.',
                'resourceUrl' => 'http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q2 - Filesystem - Mirror usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

# ...

$fs = new Filesystem();
$fs-&gt;mirror(\'/srv/app\', \'/srv/bar\', null, [\'copy_on_windows\' =&gt; true]);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ce code est valide. La méthode mirror() du composant Filesystem accepte un quatrième paramètre $options depuis Symfony 2.3, qui peut contenir l\'option copy_on_windows.',
                'resourceUrl' => 'https://symfony.com/doc/2.1/components/filesystem.html#mirror, https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/Filesystem/Filesystem.php#L338',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q3 - Expression Language - ExpressionLanguage usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'What will be stored in <code>$result</code>?
<pre><code class="language-php">&lt;?php

# ...

$result = $expressionLanguage-&gt;parse(\'1 + 2\', []);</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode parse() retourne une instance de ParsedExpression qui implémente l\'interface Expression. Donc les deux réponses sont techniquement correctes.',
                'resourceUrl' => 'https://symfony.com/doc/7.1/components/expression_language.html#parsing-and-linting-expressions',
                'answers' => [
                    ['text' => 'An instance of <code>ParsedExpression</code>', 'correct' => true],
                    ['text' => 'An instance of <code>Expression</code>', 'correct' => true],
                    ['text' => 'An instance of <code>CachedExpression</code>', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                ],
            ],

            // Q4 - Event Dispatcher - ImmutableEventDispatcher
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Could the event dispatcher be transformed into a read-only proxy?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la classe ImmutableEventDispatcher permet de créer un proxy en lecture seule d\'un dispatcher existant, empêchant l\'ajout ou la suppression de listeners.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/EventDispatcher/ImmutableEventDispatcher.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - Event Dispatcher - Connecting Listeners
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Is it possible to make the same listener object listen to multiple events?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, un même listener peut être enregistré pour écouter plusieurs événements différents en l\'ajoutant plusieurs fois avec différents noms d\'événements.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/event_dispatcher/introduction.html#connecting-listeners',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q7 - DI - Service decoration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could the priority of a decorator service be configured using the <code>AsDecorator</code> attribute?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, l\'attribut AsDecorator accepte un paramètre priority pour définir l\'ordre d\'exécution des décorateurs.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-1-service-decoration-attributes',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q8 - PropertyAccess - Reading from Arrays
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'What will be the result of the following code?
<pre><code class="language-php">use Symfony\Component\PropertyAccess\PropertyAccess;

$accessor = PropertyAccess::createPropertyAccessorBuilder()
    -&gt;enableExceptionOnInvalidIndex()
    -&gt;getPropertyAccessor()
;

$person = array(
    \'first_name\' =&gt; \'Wouter\',
);

$age = $accessor-&gt;getValue($person, \'[age]\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Avec enableExceptionOnInvalidIndex() activé, une NoSuchIndexException sera lancée lorsqu\'on tente d\'accéder à une clé inexistante dans un tableau.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/property_access/introduction.html#reading-from-arrays, http://symfony.com/doc/current/components/property_access/introduction.html#enable-other-features, https://github.com/symfony/symfony/blob/master/src/Symfony/Component/PropertyAccess/PropertyAccessorBuilder.php#L71, http://api.symfony.com/3.1/Symfony/Component/PropertyAccess/PropertyAccessorBuilder.html#method_enableExceptionOnInvalidIndex',
                'answers' => [
                    ['text' => 'A <code>Symfony\Component\PropertyAccess\Exception\NoSuchIndexException</code> will be thrown.', 'correct' => true],
                    ['text' => 'The value of <code>$age</code> will be <code>null</code>.', 'correct' => false],
                    ['text' => 'A <code>Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException</code> will be thrown.', 'correct' => false],
                    ['text' => 'The value of <code>$age</code> will be <code>0</code>.', 'correct' => false],
                ],
            ],

            // Q11 - PHP Functions - Closure bindTo
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Functions'],
                'text' => 'What will be the result of the following code ?
<pre><code class="language-php">class Foo
{
  protected $val = \'bar\';
}

$f = function() {
  echo $this-&gt;val;
};

$binded = $f-&gt;bindTo(new Foo());
$binded();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'bindTo() sans spécifier la classe de scope (2e paramètre) ne donne pas accès aux propriétés protégées. Il faut utiliser bindTo(new Foo(), Foo::class) pour y accéder. Sans cela, une erreur sera générée.',
                'resourceUrl' => 'https://www.php.net/manual/en/closure.bindto.php',
                'answers' => [
                    ['text' => 'An error', 'correct' => true],
                    ['text' => 'displays <code>bar</code>', 'correct' => false],
                ],
            ],

            // Q14 - Form - Events
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'If you need to modify the data given during pre-population or modify a form depending on the pre-populated data (adding or removing fields dynamically), to which event your code should be hooked?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'PRE_SET_DATA est l\'événement approprié pour modifier un formulaire en fonction des données de pré-population, car il est déclenché avant que les données ne soient définies sur le formulaire.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/form/form_events.html',
                'answers' => [
                    ['text' => '<code>FormEvents::PRE_SET_DATA</code>', 'correct' => true],
                    ['text' => '<code>FormEvents::POST_SET_DATA</code>', 'correct' => false],
                    ['text' => '<code>FormEvents::PRE_SUBMIT</code>', 'correct' => false],
                    ['text' => '<code>FormEvents::SUBMIT</code>', 'correct' => false],
                    ['text' => '<code>FormEvents::POST_SUBMIT</code>', 'correct' => false],
                ],
            ],

            // Q15 - DI - Service Priority
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'When configuring tags with priority, what service will come first when getting tagged items ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les services avec la priorité la plus élevée (valeur numérique la plus grande) sont traités en premier.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/service_container/tags.html#tagged-services-with-priority',
                'answers' => [
                    ['text' => 'The service with the highest priority.', 'correct' => true],
                    ['text' => 'The service with the lowest priority.', 'correct' => false],
                    ['text' => 'The service with the priority closest to 0.', 'correct' => false],
                ],
            ],

            // Q18 - FrameworkBundle - Dependency Injection Tags (console.command)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What is the tag to use to add a command?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le tag console.command est utilisé pour enregistrer des commandes dans Symfony.',
                'resourceUrl' => 'https://symfony.com/doc/2.4/reference/dic_tags.html#console-command',
                'answers' => [
                    ['text' => '<code>console.command</code>', 'correct' => true],
                    ['text' => '<code>command</code>', 'correct' => false],
                    ['text' => '<code>console_command</code>', 'correct' => false],
                    ['text' => '<code>console</code>', 'correct' => false],
                ],
            ],

            // Q19 - Inflector - Usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Inflector'],
                'text' => 'Which of this methods are available?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le composant Inflector fournit les méthodes pluralize() et singularize() pour transformer des mots entre singulier et pluriel.',
                'resourceUrl' => 'https://symfony.com/doc/master/components/inflector.html#usage',
                'answers' => [
                    ['text' => '<code>Inflector::pluralize()</code>', 'correct' => true],
                    ['text' => '<code>Inflector::singularize()</code>', 'correct' => true],
                    ['text' => '<code>Inflector::denormalize()</code>', 'correct' => false],
                    ['text' => '<code>Inflector::normalize()</code>', 'correct' => false],
                ],
            ],

            // Q20 - Serializer - The Serializer Context
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'What is a serializer context?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le contexte du sérialiseur est un tableau passé lors de la (dé)sérialisation qui permet de contrôler diverses fonctionnalités du sérialiseur.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/serializer.html#serializer_serializer-context',
                'answers' => [
                    ['text' => 'An array passed on (de)serialization to control serializer features', 'correct' => true],
                    ['text' => 'The initial data to serialize/deserialize', 'correct' => false],
                    ['text' => 'The object to update during deserialization', 'correct' => false],
                    ['text' => 'The global configuration of the serializer component', 'correct' => false],
                ],
            ],

            // Q21 - FrameworkBundle - Controller::redirect()
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'What returns the <code>Symfony\Bundle\FrameworkBundle\Controller\Controller::redirect()</code> method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode redirect() retourne une instance de RedirectResponse du composant HttpFoundation.',
                'resourceUrl' => 'http://symfony.com/doc/2.8/controller.html#redirecting',
                'answers' => [
                    ['text' => 'A <code>Symfony\Component\HttpFoundation\RedirectResponse</code>.', 'correct' => true],
                    ['text' => 'A <code>Symfony\Component\HttpFoundation\RedirectionResponse</code>.', 'correct' => false],
                    ['text' => 'A <code>Symfony\Component\HttpKernel\RedirectionResponse</code>.', 'correct' => false],
                    ['text' => 'A <code>Symfony\Component\HttpKernel\RedirectResponse</code>.', 'correct' => false],
                ],
            ],

            // Q22 - PHP Basics - PHP operators
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is the output ?
<pre><code class="language-php">&lt;?php
$a = 4 &lt;&lt; 2 + 1;
echo $a;
?&gt;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'L\'opérateur + a une priorité plus élevée que l\'opérateur de décalage <<. Donc 2 + 1 = 3 est calculé d\'abord, puis 4 << 3 = 4 * 2^3 = 32.',
                'resourceUrl' => 'http://php.net/operators.precedence',
                'answers' => [
                    ['text' => '32', 'correct' => true],
                    ['text' => '9', 'correct' => false],
                    ['text' => '16', 'correct' => false],
                    ['text' => '1', 'correct' => false],
                    ['text' => '17', 'correct' => false],
                ],
            ],

            // Q23 - Yaml - Write
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'],
                'text' => 'What is the method to transform a PHP array into a YAML representation?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La méthode Yaml::dump() transforme un tableau PHP en représentation YAML.',
                'resourceUrl' => 'https://symfony.com/doc/2.3/components/yaml/introduction.html#writing-yaml-files',
                'answers' => [
                    ['text' => '<code>Symfony\Component\Yaml\Yaml::dump</code>', 'correct' => true],
                    ['text' => '<code>Symfony\Component\Yaml\Yaml::yaml</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Yaml\Yaml::toYaml</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Yaml\Yaml::write</code>', 'correct' => false],
                ],
            ],

            // Q25 - PHP Arrays - Array functions
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'What is the output of the following PHP code?
<pre><code class="language-php">&lt;?php
$myArray = [
    0,
    NULL,
    \'\',
    \'0\',
    -1
];

echo count(
    array_filter($myArray)
);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_filter() sans callback supprime les valeurs considérées comme false : 0, NULL, \'\', \'0\' sont supprimés. Seul -1 reste (valeur truthy). Le résultat est donc 1.',
                'resourceUrl' => 'https://php.net/array, https://php.net/manual/en/function.array-filter.php, https://php.net/manual/en/function.count.php',
                'answers' => [
                    ['text' => '1', 'correct' => true],
                    ['text' => '4', 'correct' => false],
                    ['text' => '3', 'correct' => false],
                    ['text' => '5', 'correct' => false],
                    ['text' => 'An error', 'correct' => false],
                ],
            ],

            // Q26 - PHP Basics - Function arguments redefinition
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following PHP code snippet:
<pre><code class="language-php">&lt;?php

function foo($x, $x = 1, $x = 2)
{
    return $x;
}

echo foo(1, 2, 3);</code></pre>
<p>What will be the outcome of executing this script with any PHP 7 versions?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'En PHP 7, il n\'est pas possible de redéfinir le même paramètre plusieurs fois. Cela cause une erreur fatale "Cannot redeclare parameter $x".',
                'resourceUrl' => 'http://php.net/manual/en/functions.arguments.php',
                'answers' => [
                    ['text' => 'This script will cause a PHP fatal error because parameter <code>$x</code> cannot be redefined.', 'correct' => true],
                    ['text' => '\'x\'', 'correct' => false],
                    ['text' => '2', 'correct' => false],
                    ['text' => '3', 'correct' => false],
                ],
            ],

            // Q27 - Console - Question usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Could a response to a question be hidden?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la méthode setHidden() de la classe Question permet de masquer la réponse de l\'utilisateur (utile pour les mots de passe).',
                'resourceUrl' => 'https://symfony.com/doc/2.5/components/console/helpers/questionhelper.html#hiding-the-user-s-response',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q28 - HttpFoundation - Accessing Request Data
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'How to access <code>$_GET</code> data when using a <code>Symfony\Component\HttpFoundation\Request</code> <code>$request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Les données GET sont accessibles via la propriété $request->query qui est un ParameterBag.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#accessing-request-data',
                'answers' => [
                    ['text' => '<pre><code>$request-&gt;query</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>$request-&gt;getQueryData()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request-&gt;getGetData()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request-&gt;getData()</code></pre>', 'correct' => false],
                ],
            ],

            // Q30 - Process - Output-disabled process
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'],
                'text' => 'What happens if you try to get the output of process that has its output disabled?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Une LogicException est lancée lorsqu\'on tente d\'accéder à l\'output d\'un processus dont la sortie a été désactivée.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/Process/Process.php#L565-L578',
                'answers' => [
                    ['text' => 'A <code>LogicException</code> is thrown', 'correct' => true],
                    ['text' => '<code>null</code> is returned', 'correct' => false],
                    ['text' => 'An <code>InvalidArgumentException</code> is thrown', 'correct' => false],
                    ['text' => 'An empty string is returned', 'correct' => false],
                ],
            ],

            // Q31 - Event Dispatcher - Security events debug
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Is the following code valid?
<pre><code class="language-bash">php bin/console debug:event-dispatcher --dispatcher=security.event_dispatcher.main</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la commande debug:event-dispatcher accepte l\'option --dispatcher pour spécifier un dispatcher spécifique à déboguer, comme celui de la sécurité.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/event_dispatcher.html#debugging-event-listeners',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            
            // Q34 - HTTP - Redirection cache
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Can a response with a <code>308</code> status code be cached?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le code de statut 308 (Permanent Redirect) est cacheable par défaut selon la RFC 7538, tout comme le 301.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc7538#section-3',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q36 - Doctrine ORM - The QueryBuilder
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'Which of these helper methods are available in the <code>QueryBuilder</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le QueryBuilder de Doctrine fournit les méthodes join(), innerJoin() et leftJoin(). Il n\'existe pas de méthode rightJoin() dans Doctrine ORM.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/query-builder.html#working-with-querybuilder',
                'answers' => [
                    ['text' => '<code>join</code>', 'correct' => true],
                    ['text' => '<code>innerJoin</code>', 'correct' => true],
                    ['text' => '<code>leftJoin</code>', 'correct' => true],
                    ['text' => '<code>rightJoin</code>', 'correct' => false],
                ],
            ],
        ];
    }
}
