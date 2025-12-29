<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions extracted from SymfonyInsight quizzes
 */
class CertificationQuestionsFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;
    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [AppFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // Get existing categories
        $symfonyRepo = $manager->getRepository(Category::class);
        $symfony = $symfonyRepo->findOneBy(['name' => 'Symfony']);
        $php = $symfonyRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found. Run AppFixtures first.');
        }

        // Create additional subcategories needed
        $additionalSymfonySubcategories = [
            'Process' => 'Process component for executing system commands',
            'Clock' => 'Clock component for time handling',
            'Serializer' => 'Serializer component for data transformation',
            'Messenger' => 'Messenger component for async messaging',
            'Yaml' => 'YAML parsing and dumping',
        ];

        $additionalPhpSubcategories = [
            'PSR' => 'PHP Standard Recommendations',
            'SPL' => 'Standard PHP Library data structures',
        ];

        $subcategories = [];

        // Get existing subcategories
        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        foreach ($subcategoryRepo->findAll() as $sub) {
            $subcategories[$sub->getCategory()->getName() . ':' . $sub->getName()] = $sub;
        }

        // Create new Symfony subcategories
        foreach ($additionalSymfonySubcategories as $name => $description) {
            $key = 'Symfony:' . $name;
            if (!isset($subcategories[$key])) {
                $sub = new Subcategory();
                $sub->setName($name);
                $sub->setDescription($description);
                $sub->setCategory($symfony);
                $manager->persist($sub);
                $subcategories[$key] = $sub;
            }
        }

        // Create new PHP subcategories
        foreach ($additionalPhpSubcategories as $name => $description) {
            $key = 'PHP:' . $name;
            if (!isset($subcategories[$key])) {
                $sub = new Subcategory();
                $sub->setName($name);
                $sub->setDescription($description);
                $sub->setCategory($php);
                $manager->persist($sub);
                $subcategories[$key] = $sub;
            }
        }

        $manager->flush();

        // Define all extracted questions
        $questions = [
            // Question 1: Routing - Route generation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Given the following definition of the <code>book_list</code> route, what will be the value of the generated URL when calling <code>$router->generate(\'book_list\', [\'page\' => 2]);</code>?<pre><code class="language-yaml"># config/routes.yaml
book_list:
    path:     /books
    controller: \'App\Controller\DefaultController::list\'
    methods: [POST]</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The generate() method creates a relative path by default. Extra parameters not in the route path are added as query string. The correct answer is /books?page=2',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.0/src/Symfony/Component/Routing/Generator/UrlGeneratorInterface.php',
                'answers' => [
                    ['text' => '<code>/books?page=2</code>', 'correct' => true],
                    ['text' => '<code>https://example.com/books?page=2</code>', 'correct' => false],
                    ['text' => '<code>https://example.com/books?_page=2</code>', 'correct' => false],
                    ['text' => 'An error will be thrown', 'correct' => false],
                    ['text' => '<code>/books?_page=1</code>', 'correct' => false],
                ],
            ],
            // Question 2: Console - Locked command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Could you prevent a command from running multiple times on a single server?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, you can use the LockableTrait to prevent a command from running concurrently.',
                'resourceUrl' => 'https://symfony.com/doc/current/console/lockable_trait.html',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 3: Process - Timeout
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'],
                'text' => 'Is it possible to limit the amount of time a process takes to complete?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, you can use the setTimeout() method to limit process execution time.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/process.html#process-timeout',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 4: Routing - Route compilation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Could a <code>Route</code> defined with <code>/page/{_fragment}</code> path be compiled?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'explanation' => 'No, _fragment is a reserved variable and cannot be used as a route parameter.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/Routing/RouteCompiler.php#L39',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Question 5: Forms - ChoiceType rendering
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'If the <code>expanded</code> and <code>multiple</code> options are set to <strong>true</strong> on a <code>Symfony\Component\Form\Extension\Core\Type\ChoiceType</code> form type, what is displayed when rendering the form?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When expanded=true and multiple=true, checkboxes are displayed. Radio buttons are shown when expanded=true and multiple=false.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/forms/types/choice.html',
                'answers' => [
                    ['text' => 'Checkboxes.', 'correct' => true],
                    ['text' => 'Select tag.', 'correct' => false],
                    ['text' => 'Radio buttons.', 'correct' => false],
                    ['text' => 'Select tag (with <em>multiple</em> attribute).', 'correct' => false],
                ],
            ],
            // Question 6: PHP PSR - Monolog
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PSR'],
                'text' => 'Does Monolog implement the PSR-3 interface?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, Monolog implements PSR-3 LoggerInterface.',
                'resourceUrl' => 'https://www.php-fig.org/psr/psr-3/',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 7: Config - Normalization
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'Is the following configuration valid?<pre><code class="language-php">$rootNode
    ->children()
        ->arrayNode(\'connections\')
            ->children()
                ->scalarNode(\'my_custom_parameter\')->end()
            ->end()
        ->end()
    ->end()
;</code></pre><pre><code class="language-yml">connections:
    my-custom-parameter: value</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, Symfony normalizes underscores and dashes in configuration keys.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/config/definition.html#normalization',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 8: Clock - NativeClock
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'Can you extend the <code>NativeClock</code> class?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, NativeClock is a final class and cannot be extended.',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/NativeClock.php',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Question 9: Validator - Blank constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Which of the following values don\'t trigger a violation when the <code>Blank</code> constraint is applied to them?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Blank constraint validates that a value is blank (null or empty string).',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/Blank.html',
                'answers' => [
                    ['text' => '<code>null</code>', 'correct' => true],
                    ['text' => 'empty string', 'correct' => true],
                    ['text' => 'false', 'correct' => false],
                    ['text' => 'empty array', 'correct' => false],
                ],
            ],
            // Question 10: PHP SPL - Data structures
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:SPL'],
                'text' => 'Which of these classes are NOT SPL data structures?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'SplArrayStorage and SplLinkedList do not exist. The correct SPL classes are SplDoublyLinkedList, SplStack, SplQueue, SplHeap, SplPriorityQueue, etc.',
                'resourceUrl' => 'https://www.php.net/book.spl',
                'answers' => [
                    ['text' => 'SplArrayStorage', 'correct' => true],
                    ['text' => 'SplLinkedList', 'correct' => true],
                    ['text' => 'SplPriorityQueue', 'correct' => false],
                    ['text' => 'SplQueue', 'correct' => false],
                    ['text' => 'SplStack', 'correct' => false],
                ],
            ],
            // Question 11: Security - Authentication Flow
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'When are credentials checked during authentication?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Credentials are validated after the passport is created and before the token is created.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/Security/Http/Authentication/AuthenticatorManager.php#L174',
                'answers' => [
                    ['text' => 'After the passport is created and before the token is created', 'correct' => true],
                    ['text' => 'Before the passport is created', 'correct' => false],
                    ['text' => 'After the token is created', 'correct' => false],
                ],
            ],
            // Question 12: DI - Service Configurator
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is a "Service Configurator" in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'A Service Configurator is a PHP callable that you can execute to configure a service after its instantiation.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/configurators.html',
                'answers' => [
                    ['text' => 'It\'s a PHP callable that you can optionally execute to configure a Symfony service after its instantiation.', 'correct' => true],
                    ['text' => 'It\'s a feature of the Dependency Injection component that allows to apply some configuration logic to all the services that define a specific tag.', 'correct' => false],
                    ['text' => 'It\'s a Symfony built-in service that can be obtained as <code>$container->get(\'configurator\')</code>', 'correct' => false],
                    ['text' => 'There\'s no such a thing in Symfony\'s Dependency Injection component.', 'correct' => false],
                ],
            ],
            // Question 13: Serializer - Context
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'How to specify the date format for a date attribute in a serialization context?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'You can use the #[Context] attribute or @Context annotation to specify serialization context on a property.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-3-inlined-serialization-context',
                'answers' => [
                    ['text' => '#[Serializer\Context([DateTimeNormalizer::FORMAT_KEY => \'Y-m-d\'])]', 'correct' => true],
                    ['text' => '@Serializer\Context({ DateTimeNormalizer::FORMAT_KEY = \'Y-m-d\' })', 'correct' => true],
                    ['text' => '@Serializer\DateFormat(\'Y-m-d\')', 'correct' => false],
                    ['text' => 'It\'s not possible', 'correct' => false],
                ],
            ],
            // Question 14: Messenger - Worker metadata
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Could information like the transport name and so on be retrieved from the worker?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, WorkerMetadata class provides access to worker information including transport names.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-4-messenger-improvements#worker-metadata',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Question 15: Routing - Special parameters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Which attributes are reserved special routing parameters?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony reserves _controller, _format, _fragment, and _locale as special routing parameters.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html',
                'answers' => [
                    ['text' => '<code>_controller</code>', 'correct' => true],
                    ['text' => '<code>_format</code>', 'correct' => true],
                    ['text' => '<code>_locale</code>', 'correct' => true],
                    ['text' => '<code>_response</code>', 'correct' => false],
                    ['text' => '<code>_type</code>', 'correct' => false],
                ],
            ],
            // Question 16: Twig - Apply tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What\'s the aim of the <code>apply</code> tag in Twig?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The apply tag allows you to apply one or multiple Twig filters on a block of template data.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/tags/apply.html',
                'answers' => [
                    ['text' => 'Apply one or multiple filters on a block', 'correct' => true],
                    ['text' => 'Apply one and only one filter on a block', 'correct' => false],
                    ['text' => 'Apply a camelCase transformation to a text', 'correct' => false],
                    ['text' => 'Define a new tag', 'correct' => false],
                ],
            ],
            // Question 18: Console - Events with CommandTester
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Are console events dispatched when testing commands using <code>CommandTester</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'No, CommandTester does not dispatch console events by default.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html#testing-commands',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Question 19: Console - Table helper column width
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'What will be the output when using setColumnWidth() with a value smaller than the content? Does it truncate the content?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'setColumnWidth() sets a minimum width, not a maximum. Content larger than the specified width will not be truncated.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/console/helpers/table.html',
                'answers' => [
                    ['text' => 'The column expands to fit the content (minimum width only)', 'correct' => true],
                    ['text' => 'Content is truncated with "..."', 'correct' => false],
                    ['text' => 'Content is truncated without ellipsis', 'correct' => false],
                ],
            ],
            // Question 20: Yaml - Exception
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Yaml'],
                'text' => 'What is the exception class used when an error occurs during parsing with <code>Symfony\Component\Yaml\Yaml::parse</code> method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Yaml component throws Symfony\Component\Yaml\Exception\ParseException on parsing errors.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/yaml.html',
                'answers' => [
                    ['text' => '<code>Symfony\Component\Yaml\Exception\ParseException</code>', 'correct' => true],
                    ['text' => '<code>Symfony\Component\Yaml\ParseException</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Yaml\Exception\ParsingException</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Yaml\ParsingException</code>', 'correct' => false],
                ],
            ],
        ];

        // Persist all questions using upsert
        foreach ($questions as $q) {
            $q['isCertification'] = false; // These are training questions
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }
}
