<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 25
 */
class CertificationQuestionsFixtures25 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures24::class];
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

            // Q2 - DI - Services preload
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is the following code valid when trying to add a service into the list of preloaded classes?
<pre><code class="language-yaml">services:
    App\SomeNamespace\SomeService:
        tags:
            - { name: \'container.preload\', class: \'App\SomeClass\' }
            - { name: \'container.preload\', class: \'App\Some\OtherClass\' }

    # ...</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, il est possible d\'ajouter plusieurs tags container.preload à un service pour précharger plusieurs classes.',
                'resourceUrl' => 'https://symfony.com/doc/5.1/reference/dic_tags.html#container-preload',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q3 - Clock - MonotonicClock
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'] ?? null,
                'text' => 'Can you extends <code>MonotonicClock</code> class ?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, la classe MonotonicClock est déclarée comme final et ne peut donc pas être étendue.',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/MonotonicClock.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q4 - Form - PercentType
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What are the valid values of the <code>type</code> option of the <code>Symfony\Component\Form\Extension\Core\Type\PercentType</code> form type.',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les valeurs valides pour l\'option type du PercentType sont "integer" et "fractional". Les options "decimal", "rounded" et "raw" ne sont pas valides.',
                'resourceUrl' => 'https://symfony.com/doc/2.3/reference/forms/types/percent.html#type',
                'answers' => [
                    ['text' => '<code>integer</code>', 'correct' => true],
                    ['text' => '<code>fractional</code>', 'correct' => true],
                    ['text' => '<code>raw</code>', 'correct' => false],
                    ['text' => '<code>decimal</code>', 'correct' => false],
                    ['text' => '<code>rounded</code>', 'correct' => false],
                ],
            ],

            // Q6 - Console - Testing commands with events
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Which <code>Tester</code> class should be used when testing a command that relies on console events (e.g. the <code>ConsoleEvents::TERMINATE</code> event)?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour tester une commande qui s\'appuie sur les événements de console, il faut utiliser ApplicationTester car CommandTester n\'exécute pas le cycle de vie complet avec les événements.',
                'resourceUrl' => 'https://symfony.com/doc/3.x/console.html#command-lifecycle',
                'answers' => [
                    ['text' => '<code>Symfony\Component\Console\Tester\ApplicationTester</code>', 'correct' => true],
                    ['text' => '<code>Symfony\Component\Console\Tester\CommandTester</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Console\Tester\CommandCompletionTester</code>', 'correct' => false],
                ],
            ],

            // Q7 - Console - bin/console format
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'What is the format of the <code>bin/console</code> file?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le fichier bin/console est un script PHP simple avec un shebang qui le rend auto-exécutable sur les systèmes Unix.',
                'resourceUrl' => 'https://github.com/symfony/recipes/blob/main/symfony/console/5.3/bin/console#L1-L2',
                'answers' => [
                    ['text' => 'Plain PHP script', 'correct' => true],
                    ['text' => 'Self-executable compressed file', 'correct' => false],
                    ['text' => 'Binary file', 'correct' => false],
                    ['text' => 'PHAR file', 'correct' => false],
                ],
            ],

            // Q11 - DI - Autowire env vars
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could environment variables be autowired via an attribute?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 6.1, l\'attribut #[Autowire] permet d\'injecter des variables d\'environnement directement.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-1-service-autowiring-attributes, https://github.com/symfony/symfony/blob/6.1/src/Symfony/Component/DependencyInjection/Attribute/Autowire.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q14 - Twig - Set tag with multiple variables
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-twig">{% set foo, bar = \'FOO\' %}

&lt;p&gt;
  Foo is {{ foo }} and Bar is {{ bar }}.
&lt;/p&gt;</code></pre>
<p>What will be the outcome of evaluating this Twig code?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Twig ne supporte pas l\'affectation multiple avec une seule valeur comme en PHP. Cette syntaxe lèvera une exception Twig_Error_Syntax.',
                'resourceUrl' => 'https://twig.symfony.com/doc/tags/set.html',
                'answers' => [
                    ['text' => 'Twig will raise a <code>Twig_Error_Syntax</code> exception.', 'correct' => true],
                    ['text' => 'The output will display the string <code>Foo is  and Bar is .</code>.', 'correct' => false],
                    ['text' => 'The output will display the string <code>Foo is FOO and Bar is .</code>.', 'correct' => false],
                    ['text' => 'The output will display the string <code>Foo is FOO and Bar is FOO.</code>.', 'correct' => false],
                ],
            ],

            // Q15 - PHP OOP - Interface extends
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Can an interface extend another interface?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, en PHP, une interface peut étendre une ou plusieurs autres interfaces en utilisant le mot-clé extends.',
                'resourceUrl' => 'http://php.net/manual/en/language.oop5.interfaces.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q17 - PHP Arrays - array_slice
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'What is the output ?
<pre><code class="language-php">&lt;?php
$array1 = [\'a\', \'b\', \'c\', \'d\', \'e\', \'f\'];
$array2 = array_slice($array1, -3);

foreach ($array2 as $val) {
    print "$val ";
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_slice avec un offset négatif retourne les éléments à partir de la fin. -3 retourne les 3 derniers éléments: d, e, f.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.types.array.php, https://www.php.net/manual/en/function.array-slice',
                'answers' => [
                    ['text' => 'd e f', 'correct' => true],
                    ['text' => 'b c d', 'correct' => false],
                    ['text' => 'a b c', 'correct' => false],
                    ['text' => 'c d e', 'correct' => false],
                ],
            ],

            // Q19 - PHP - Null coalescing operator
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What will be the output of the following code?
<pre><code class="language-php">&lt;?php

$z = \'bar\';
$f = function ($y) use ($z) {
    echo $x ?? $y ?? $z;
};

$f(null);
$f(\'foo\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le premier appel $f(null) affiche "bar" car $x n\'existe pas, $y est null, donc on retombe sur $z. Le second appel $f(\'foo\') affiche "foo" car $x n\'existe pas mais $y vaut "foo".',
                'resourceUrl' => 'http://php.net/manual/en/language.operators.comparison.php',
                'answers' => [
                    ['text' => 'barfoo', 'correct' => true],
                    ['text' => 'foobar', 'correct' => false],
                    ['text' => 'A <em>Fatal error: syntax error</em> will be thrown.', 'correct' => false],
                    ['text' => 'foo', 'correct' => false],
                    ['text' => 'barbar', 'correct' => false],
                ],
            ],

            // Q21 - EventDispatcher - Event aliases
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Could the alias of an event be changed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, les alias d\'événements peuvent être définis et modifiés via la configuration du dispatcher.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/event_dispatcher.html#event-aliases',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q22 - Console - Console helpers
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'What are the console helpers ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les helpers de console incluent Question helper, Formatter helper et Process helper. Le Dialog helper a été déprécié et remplacé par Question helper. Validator helper et Answer helper n\'existent pas.',
                'resourceUrl' => 'https://symfony.com/doc/5.0/components/console/helpers/index.html',
                'answers' => [
                    ['text' => 'Question helper', 'correct' => true],
                    ['text' => 'Formatter helper', 'correct' => true],
                    ['text' => 'Process helper', 'correct' => true],
                    ['text' => 'Dialog helper', 'correct' => false],
                    ['text' => 'Validator helper', 'correct' => false],
                    ['text' => 'Answer helper', 'correct' => false],
                ],
            ],

            // Q23 - DI - Environment variable processor
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is the following code valid?
<pre><code class="language-yaml"># config/packages/framework.yaml
framework:
  router:
    http_port: \'%env(int:HTTP_PORT)%\'</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ce code est valide. Le processeur env(int:) convertit la variable d\'environnement en entier.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/configuration/env_var_processors.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q24 - PHP OOP - Private method override
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Consider the following PHP code snippet:
<pre><code class="language-php">&lt;?php

class A
{
    protected $foo;

    private function foo()
    {
        $this-&gt;foo = \'A-foo\';
    }

    public function bar()
    {
        $this-&gt;foo();

        return $this-&gt;foo;
    }
}

class B extends A
{
    private function foo()
    {
        $this-&gt;foo = \'B-foo\';
    }
}

echo (new B())-&gt;bar();</code></pre>
<p>What is the expected output when executing this script?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Les méthodes privées ne peuvent pas être surchargées. Quand bar() appelle $this->foo(), c\'est toujours la méthode privée de la classe A qui est appelée, donc le résultat est "A-foo".',
                'resourceUrl' => 'https://3v4l.org/InSq9',
                'answers' => [
                    ['text' => '<pre><code>A-foo</code></pre>', 'correct' => true],
                    ['text' => '<pre><code>B-foo</code></pre>', 'correct' => false],
                    ['text' => 'PHP will raise a fatal error because it\'s not allowed to override a private method.', 'correct' => false],
                    ['text' => '<pre><code class="language-php">NULL</code></pre>', 'correct' => false],
                ],
            ],

            // Q26 - Serializer - Enums
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Could enumerations be serialized?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 5.4, le Serializer supporte la sérialisation et la désérialisation des énumérations PHP 8.1.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-4-php-enumerations-support#php-enums-support-in-symfony-serializer',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q27 - DI - AutowireCallable
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => '<pre><code class="language-php">// src/Service/MyService.php
namespace App\Service;

class MyService {

public function doSomething(
  #[AutowireCallable(service: \'app.logger.custom\', method: \'error\')]
  Closure $error
) {
  // ...
  $error(\'My Error\');
  // ...
}</code></pre>
<p>Considering the <code>app.logger.custom</code> is a correctly registered service, when will this service be instantiated ?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Avec AutowireCallable, le service est instancié au moment de la création de la closure $error, pas seulement lors de son appel. C\'est une injection normale, pas lazy.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/autowiring.html#generate-closures-with-autowiring',
                'answers' => [
                    ['text' => 'When creating the $error argument', 'correct' => true],
                    ['text' => 'When calling the $error callable', 'correct' => false],
                ],
            ],

            // Q29 - Expression Language - evaluate
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'What will be displayed by the following code?
<pre><code class="language-php">use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$language = new ExpressionLanguage();

echo $language-&gt;evaluate(\'1 + 2\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'ExpressionLanguage::evaluate() évalue l\'expression et retourne le résultat. 1 + 2 donne 3.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/expression_language.html#usage, https://github.com/symfony/symfony/blob/2.4/src/Symfony/Component/ExpressionLanguage/ExpressionLanguage.php#L53',
                'answers' => [
                    ['text' => '<code>3</code>', 'correct' => true],
                    ['text' => '<code>false</code>', 'correct' => false],
                    ['text' => '<code>true</code>', 'correct' => false],
                    ['text' => '<code>integer</code>', 'correct' => false],
                    ['text' => '<code>1 + 2</code>', 'correct' => false],
                ],
            ],

            // Q32 - DI - ContainerConfigurator
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is true about <code>ContainerConfigurator</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ContainerConfigurator permet de travailler avec les définitions de services lors de la configuration du conteneur. Il ne faut pas le confondre avec les service configurators.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/service_container.html, https://github.com/symfony/dependency-injection/blob/5.3/Loader/Configurator/ContainerConfigurator.php, https://symfony.com/doc/2.x/service_container/configurators.html',
                'answers' => [
                    ['text' => 'it allows to work with service definitions', 'correct' => true],
                    ['text' => 'it\'s an internal class that you should not use in your application', 'correct' => false],
                    ['text' => 'it allows to configure a service after its instanciation', 'correct' => false],
                    ['text' => 'it doesn\'t exist', 'correct' => false],
                ],
            ],

            // Q34 - Twig - Null coalesce
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'If the <code>user</code> variable is not defined, what will be the result of rendering this Twig template?
<pre><code class="language-twig">Hi {{ user.name ?? \'anonymous\' }}!</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'opérateur ?? (null coalesce) en Twig retourne la valeur de droite si la variable n\'est pas définie ou est null. Donc "Hi anonymous!" sera affiché.',
                'resourceUrl' => 'http://twig.symfony.com/doc/templates.html#other-operators',
                'answers' => [
                    ['text' => 'Hi anonymous!', 'correct' => true],
                    ['text' => 'This template will display an error when rendering it.', 'correct' => false],
                    ['text' => 'Hi null!', 'correct' => false],
                    ['text' => 'Hi ??!', 'correct' => false],
                    ['text' => 'Hi!', 'correct' => false],
                ],
            ],
        ];
    }
}
