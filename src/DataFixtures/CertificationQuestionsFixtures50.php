<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 50
 * Symfony 7.4 / 8.0 new features - Part 6
 * Topics: Advanced PHP 8.4, Deprecations, Migration, Configuration, Deeper DI & Routing
 */
class CertificationQuestionsFixtures50 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures49::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Required categories not found. Please load AppFixtures first.');
        }

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
            // ── Q1: PHP 8.4 Property Hooks – virtual vs backed ──
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In PHP 8.4, what is the difference between a "virtual" property and a "backed" property when using property hooks?</p>',
                'answers' => [
                    ['text' => 'A virtual property has no storage and only relies on <code>get</code>/<code>set</code> hooks; a backed property has actual storage and hooks optionally modify access to it', 'correct' => true],
                    ['text' => 'A virtual property can only be read-only; a backed property can be both read and written', 'correct' => false],
                    ['text' => 'There is no difference — both terms refer to the same concept', 'correct' => false],
                    ['text' => 'A virtual property exists only at compile time; a backed property exists at runtime', 'correct' => false],
                ],
                'explanation' => 'In PHP 8.4, a virtual property uses only get/set hooks without backing storage. A backed property has actual storage ($this->propertyName) and hooks can optionally modify access.',
                'resourceUrl' => 'https://wiki.php.net/rfc/property-hooks',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q2: PHP 8.4 Asymmetric Visibility – Practical ──
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>With PHP 8.4 asymmetric visibility, how do you declare a property that is publicly readable but only writable from within the class itself?</p>',
                'answers' => [
                    ['text' => '<code>public private(set) string $name</code>', 'correct' => true],
                    ['text' => '<code>readonly public string $name</code>', 'correct' => false],
                    ['text' => '<code>#[ReadOnly] public string $name</code>', 'correct' => false],
                    ['text' => '<code>public string $name { get; private set; }</code>', 'correct' => false],
                ],
                'explanation' => 'PHP 8.4 introduces asymmetric visibility: "public private(set)" means public for reading, private for writing. The set visibility must be equal to or more restrictive than get.',
                'resourceUrl' => 'https://wiki.php.net/rfc/asymmetric-visibility-v2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q3: PHP 8.4 #[\Deprecated] ──
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>What can you mark with the native <code>#[\Deprecated]</code> attribute in PHP 8.4? (Select all that apply)</p>',
                'answers' => [
                    ['text' => 'Functions and methods', 'correct' => true],
                    ['text' => 'Class constants', 'correct' => true],
                    ['text' => 'Properties', 'correct' => false],
                    ['text' => 'Entire classes', 'correct' => false],
                ],
                'explanation' => 'PHP 8.4\'s #[\Deprecated] attribute can be applied to functions, methods, and class constants. It cannot be used on properties or entire classes yet.',
                'resourceUrl' => 'https://wiki.php.net/rfc/deprecated_attribute',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q4: PHP 8.4 Lazy Objects ──
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>PHP 8.4 introduces native lazy objects via the <code>ReflectionClass</code> API. Which two strategies are supported?</p>',
                'answers' => [
                    ['text' => 'Lazy ghost objects and lazy proxy objects', 'correct' => true],
                    ['text' => 'Lazy loading and eager loading', 'correct' => false],
                    ['text' => 'Deferred initialization and immediate initialization', 'correct' => false],
                    ['text' => 'Virtual proxies and value holders', 'correct' => false],
                ],
                'explanation' => 'PHP 8.4 supports two native lazy strategies: ghost objects (which use the same class) and proxy objects (which wrap around the real object). Both are created via ReflectionClass methods.',
                'resourceUrl' => 'https://wiki.php.net/rfc/lazy-objects',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q5: PHP 8.4 array functions ──
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>What does the new <code>array_find()</code> function in PHP 8.4 return?</p>',
                'answers' => [
                    ['text' => 'The first element for which the callback returns true, or <code>null</code> if none found', 'correct' => true],
                    ['text' => 'An array of all elements matching the callback', 'correct' => false],
                    ['text' => 'The index (key) of the first matching element', 'correct' => false],
                    ['text' => 'A boolean indicating if any element matches', 'correct' => false],
                ],
                'explanation' => 'array_find() returns the value of the first element for which the callback returns true, or null if no element matches. array_find_key() returns the key, and array_any()/array_all() return booleans.',
                'resourceUrl' => 'https://wiki.php.net/rfc/array_find',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q6: Symfony 8.0 Deprecation Strategy ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>What is the relationship between deprecations in Symfony 7.4 and Symfony 8.0?</p>',
                'answers' => [
                    ['text' => 'Features deprecated in Symfony 7.4 are removed in Symfony 8.0; fixing all 7.4 deprecations ensures smooth upgrade to 8.0', 'correct' => true],
                    ['text' => 'Symfony 8.0 has its own new deprecations independent of 7.4', 'correct' => false],
                    ['text' => 'Deprecations from 7.4 are kept in 8.0 but generate errors instead of warnings', 'correct' => false],
                    ['text' => 'There is no relationship; Symfony 8.0 is a complete rewrite', 'correct' => false],
                ],
                'explanation' => 'Symfony follows a strict deprecation policy: new deprecations are added in minor versions (7.4), and deprecated code is removed in the next major version (8.0).',
                'resourceUrl' => 'https://symfony.com/doc/current/contributing/community/releases.html',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q7: Symfony Configuration – XML Deprecation ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>What is happening to XML configuration format in Symfony 7.4/8.0?</p>',
                'answers' => [
                    ['text' => 'XML configuration is being deprecated in favor of PHP and YAML configuration', 'correct' => true],
                    ['text' => 'XML configuration is being enhanced with new schema features', 'correct' => false],
                    ['text' => 'XML configuration remains unchanged and fully supported', 'correct' => false],
                    ['text' => 'XML configuration is being replaced by JSON configuration', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 deprecates XML configuration for service definitions and package configuration, encouraging migration to PHP or YAML formats.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q8: Simpler Target – Lock Factory ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Dependency Injection'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, to inject a lock factory named <code>invoice</code> using the simplified <code>#[Target]</code> attribute, which syntax is correct?</p>',
                'answers' => [
                    ['text' => '<code>#[Target(\'invoice\')] private LockFactory $lockFactory</code>', 'correct' => true],
                    ['text' => '<code>#[Target(\'invoice.lock.factory\')] private LockFactory $lockFactory</code>', 'correct' => false],
                    ['text' => '<code>#[Autowire(service: \'invoice.lock\')] private LockFactory $lockFactory</code>', 'correct' => false],
                    ['text' => '<code>#[Target(\'lock.invoice\')] private LockFactory $lockFactory</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 simplifies #[Target] by removing the need for suffixes like .lock.factory. You can now write #[Target(\'invoice\')] directly.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q9: Simpler Target – Asset Package ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Dependency Injection'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Before Symfony 7.4, to target an asset package named <code>foo_package</code>, you had to write <code>#[Target(\'foo_package.package\')]</code>. What is the Symfony 7.4 equivalent?</p>',
                'answers' => [
                    ['text' => '<code>#[Target(\'foo_package\')]</code>', 'correct' => true],
                    ['text' => '<code>#[Target(\'foo_package.asset\')]</code>', 'correct' => false],
                    ['text' => '<code>#[AssetPackage(\'foo_package\')]</code>', 'correct' => false],
                    ['text' => '<code>#[Autowire(\'%foo_package%\')]</code>', 'correct' => false],
                ],
                'explanation' => 'In Symfony 7.4, #[Target] no longer requires type-based suffixes. For an asset package "foo_package", just use #[Target(\'foo_package\')] instead of #[Target(\'foo_package.package\')].',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q10: Messenger – Exclude Receivers Usage ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Messenger'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>How do you exclude multiple receivers when consuming all Messenger transports in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'Use <code>--all</code> with multiple <code>--exclude-receivers</code> options (e.g. <code>--exclude-receivers=queue1 --exclude-receivers=queue2</code>)', 'correct' => true],
                    ['text' => 'Use <code>--all --except=queue1,queue2</code> as a comma-separated list', 'correct' => false],
                    ['text' => 'Use <code>--all</code> and list excluded receivers as positional arguments', 'correct' => false],
                    ['text' => 'Set an <code>exclude_receivers</code> key in the <code>messenger.yaml</code> config file', 'correct' => false],
                ],
                'explanation' => 'The --exclude-receivers option is used together with --all and can be specified multiple times to exclude specific receivers from consumption.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q11: Question Helper Timeout – Method ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Console'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'text' => '<p>How do you set a timeout on a console <code>Question</code> in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => '<code>$question->setTimeout(10)</code> — the user must respond within 10 seconds', 'correct' => true],
                    ['text' => 'Pass a timeout parameter to <code>$helper->ask($input, $output, $question, 10)</code>', 'correct' => false],
                    ['text' => 'Set the <code>--timeout=10</code> option on the command', 'correct' => false],
                    ['text' => 'Use <code>new TimedQuestion(\'Confirm?\', timeout: 10)</code>', 'correct' => false],
                ],
                'explanation' => 'In Symfony 7.4, the Question class has a setTimeout() method. If the user doesn\'t respond within the specified seconds, a MissingInputException is thrown.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q12: Form Accessibility – No Code Changes ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Forms'),
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'text' => '<p>In Symfony 7.4, the automatic addition of <code>aria-invalid</code> and <code>aria-describedby</code> attributes to form fields with errors requires manual configuration changes in your form templates.</p>',
                'answers' => [
                    ['text' => 'False', 'correct' => true],
                    ['text' => 'True', 'correct' => false],
                ],
                'explanation' => 'No code changes are required. Just upgrade to Symfony 7.4, and forms automatically include the correct ARIA attributes for fields with validation errors.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q13: Caching HTTP Client – Tag-aware Requirement ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'text' => '<p>The cache pool used for the new Symfony 7.4 caching HTTP client must be tag-aware (using a tag-aware adapter with <code>tags: true</code>).</p>',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
                'explanation' => 'The cache pool must use a tag-aware adapter (e.g. cache.adapter.redis_tag_aware) and have tags: true enabled in its configuration.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-caching-http-client',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q14: PHP 8.4 – new without parentheses ──
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In PHP 8.4, <code>new MyClass()->method()</code> can be written without wrapping <code>new MyClass()</code> in parentheses. What was required before PHP 8.4?</p>',
                'answers' => [
                    ['text' => '<code>(new MyClass())->method()</code> — parentheses around the <code>new</code> expression were mandatory', 'correct' => true],
                    ['text' => 'It was already supported without parentheses in PHP 8.3', 'correct' => false],
                    ['text' => 'A temporary variable was the only option: <code>$obj = new MyClass(); $obj->method();</code>', 'correct' => false],
                    ['text' => 'You had to use <code>MyClass::create()->method()</code> with a static factory', 'correct' => false],
                ],
                'explanation' => 'Before PHP 8.4, you needed (new MyClass())->method(). PHP 8.4 allows direct member access on new expressions without parentheses: new MyClass()->method().',
                'resourceUrl' => 'https://wiki.php.net/rfc/new_without_parentheses',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q15: Weighted Workflow – Default Weight ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4 workflows, what is the default weight of a transition when no explicit weight is specified?</p>',
                'answers' => [
                    ['text' => '1 — every transition produces or consumes exactly one instance per place', 'correct' => true],
                    ['text' => '0 — the transition has no effect on the place count', 'correct' => false],
                    ['text' => 'There is no default; weight must always be specified explicitly', 'correct' => false],
                    ['text' => '-1 — it removes one instance from the place', 'correct' => false],
                ],
                'explanation' => 'Without weights, every transition produces or consumes exactly one instance per place, which is the traditional workflow behavior. Weights are optional.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-weighted-workflow-transitions',
                'symfonyVersion' => '7.4/8.0',
            ],
        ];
    }
}
