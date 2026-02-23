<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 47
 * Symfony 7.4 / 8.0 new features - Part 3
 * Topics: PHP 8.4, Symfony Architecture, Testing, Miscellaneous, HTTP
 */
class CertificationQuestionsFixtures47 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures46::class];
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
            // Q1 - PHP 8.4 - Property hooks
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:OOP'),
                'text' => '<p>PHP 8.4 introduces property hooks. What do they allow?</p>
<pre><code class="language-php">class User
{
    public string $name {
        set(string $value) => strtolower($value);
        get => strtoupper($this->name);
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Property hooks in PHP 8.4 allow defining get and set behavior directly on a property. The set hook transforms the value when assigned, and the get hook transforms the value when read. This eliminates the need for explicit getter/setter methods in many cases.',
                'resourceUrl' => 'https://wiki.php.net/rfc/property-hooks',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Defining custom <code>get</code> and <code>set</code> behavior directly on a property, eliminating explicit getter/setter methods', 'correct' => true],
                    ['text' => 'Adding event listeners that trigger when a property value changes', 'correct' => false],
                    ['text' => 'Defining computed properties that are evaluated once and cached', 'correct' => false],
                    ['text' => 'Adding validation constraints to properties at the language level', 'correct' => false],
                ],
            ],

            // Q2 - PHP 8.4 - Asymmetric visibility
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:OOP'),
                'text' => '<p>PHP 8.4 introduces asymmetric visibility for properties. What does the following code do?</p>
<pre><code class="language-php">class BankAccount
{
    public private(set) float $balance;

    public function __construct(float $initial)
    {
        $this->balance = $initial;
    }

    public function deposit(float $amount): void
    {
        $this->balance += $amount;
    }
}</code></pre>
<p>Given <code>$account = new BankAccount(100.0);</code>, which operation is valid?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Asymmetric visibility allows a property to have different visibility for reading and writing. "public private(set)" means the property can be read publicly but can only be set from within the class itself. So $account->balance can be read, but $account->balance = 200 would throw an error.',
                'resourceUrl' => 'https://wiki.php.net/rfc/asymmetric-visibility-v2',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => '<code>echo $account->balance;</code> works, but <code>$account->balance = 200;</code> throws an error', 'correct' => true],
                    ['text' => 'Both reading and writing are allowed because the property is <code>public</code>', 'correct' => false],
                    ['text' => 'Neither reading nor writing is allowed outside the class', 'correct' => false],
                    ['text' => 'Writing is allowed by anyone, but reading is restricted to the class', 'correct' => false],
                ],
            ],

            // Q3 - PHP 8.4 - new without parentheses
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'text' => '<p>PHP 8.4 introduces a syntax change for object instantiation. Which of the following is now valid in PHP 8.4?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP 8.4 allows chaining methods directly on new expressions without wrapping them in parentheses. Previously you had to write (new Foo())->bar(), now you can write new Foo()->bar(). This also works for property access and array access on new objects.',
                'resourceUrl' => 'https://wiki.php.net/rfc/new_without_parentheses',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => '<code>new DateTime()->format(\'Y-m-d\')</code> — chaining directly on <code>new</code> without extra parentheses', 'correct' => true],
                    ['text' => '<code>new static::class()</code> — using <code>static::</code> with the <code>new</code> keyword', 'correct' => false],
                    ['text' => '<code>new $className::method()</code> — calling static methods via <code>new</code>', 'correct' => false],
                    ['text' => '<code>new (expression)()</code> — using any expression with <code>new</code>', 'correct' => false],
                ],
            ],

            // Q4 - PHP 8.4 - array_find
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:Arrays'),
                'text' => '<p>PHP 8.4 introduces new array functions. What does the following code output?</p>
<pre><code class="language-php">&lt;?php
$numbers = [1, 2, 3, 4, 5];
$result = array_find($numbers, fn($n) => $n > 3);
echo $result;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_find() is a new PHP 8.4 function that returns the first element in the array for which the callback returns true. Since 4 is the first element greater than 3, it returns 4.',
                'resourceUrl' => 'https://wiki.php.net/rfc/array_find',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => '<code>4</code>', 'correct' => true],
                    ['text' => '<code>5</code>', 'correct' => false],
                    ['text' => '<code>[4, 5]</code>', 'correct' => false],
                    ['text' => '<code>3</code> (the key)', 'correct' => false],
                ],
            ],

            // Q5 - PHP 8.4 - array_find_key
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:Arrays'),
                'text' => '<p>What is the difference between <code>array_find()</code> and <code>array_find_key()</code> in PHP 8.4?</p>
<pre><code class="language-php">&lt;?php
$data = [\'a\' => 10, \'b\' => 20, \'c\' => 30];

$val = array_find($data, fn($v) => $v > 15);
$key = array_find_key($data, fn($v) => $v > 15);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_find() returns the first VALUE that matches the callback (20 in this case), while array_find_key() returns the first KEY whose value matches the callback (\'b\' in this case). Both stop at the first match.',
                'resourceUrl' => 'https://wiki.php.net/rfc/array_find',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => '<code>array_find()</code> returns the value (<code>20</code>), <code>array_find_key()</code> returns the key (<code>\'b\'</code>)', 'correct' => true],
                    ['text' => '<code>array_find()</code> returns an array, <code>array_find_key()</code> returns a single value', 'correct' => false],
                    ['text' => 'They are aliases for the same function', 'correct' => false],
                    ['text' => '<code>array_find()</code> finds by value, <code>array_find_key()</code> filters by key', 'correct' => false],
                ],
            ],

            // Q6 - PHP 8.4 - array_any / array_all
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:Arrays'),
                'text' => '<p>PHP 8.4 introduces <code>array_any()</code> and <code>array_all()</code>. What does the following code output?</p>
<pre><code class="language-php">&lt;?php
$numbers = [2, 4, 6, 8];

var_dump(array_all($numbers, fn($n) => $n % 2 === 0));
var_dump(array_any($numbers, fn($n) => $n > 10));</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_all() returns true if the callback returns true for ALL elements. Since all elements are even, it returns true. array_any() returns true if the callback returns true for ANY element. Since no element is > 10, it returns false.',
                'resourceUrl' => 'https://wiki.php.net/rfc/array_find',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => '<code>bool(true)</code> then <code>bool(false)</code>', 'correct' => true],
                    ['text' => '<code>bool(true)</code> then <code>bool(true)</code>', 'correct' => false],
                    ['text' => '<code>bool(false)</code> then <code>bool(false)</code>', 'correct' => false],
                    ['text' => '<code>bool(false)</code> then <code>bool(true)</code>', 'correct' => false],
                ],
            ],

            // Q7 - Symfony Architecture - Symfony 8.0 vs 7.4
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'text' => '<p>What is the relationship between Symfony 7.4 and Symfony 8.0?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 8.0 and 7.4 were released at the same time and share the exact same features. The difference is that Symfony 8.0 removes all deprecated features from previous versions and requires PHP 8.4 or higher. Symfony 7.4 is the last minor version in the 7.x series.',
                'resourceUrl' => 'https://symfony.com/releases/8.0',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'They share the same features, but Symfony 8.0 removes all deprecated code and requires PHP 8.4+', 'correct' => true],
                    ['text' => 'Symfony 8.0 is a complete rewrite of the framework with new architecture', 'correct' => false],
                    ['text' => 'Symfony 7.4 adds features that will not be available in 8.0', 'correct' => false],
                    ['text' => 'They are independent releases targeting different PHP versions', 'correct' => false],
                ],
            ],

            // Q8 - Symfony Architecture - PHP 8.4 requirement
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'text' => '<p>What is the minimum PHP version required by Symfony 8.0?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 8.0 requires PHP 8.4 or higher. This allows Symfony to leverage new PHP 8.4 features like property hooks, asymmetric visibility, and new array functions.',
                'resourceUrl' => 'https://symfony.com/releases/8.0',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'PHP 8.4', 'correct' => true],
                    ['text' => 'PHP 8.3', 'correct' => false],
                    ['text' => 'PHP 8.2', 'correct' => false],
                    ['text' => 'PHP 8.1', 'correct' => false],
                ],
            ],

            // Q9 - Console - #[Interact] attribute
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Console'),
                'text' => '<p>In Symfony 7.4, the <code>#[Interact]</code> attribute was introduced for invokable commands. What does it replace?</p>
<pre><code class="language-php">#[AsCommand(\'app:example\')]
class ExampleCommand
{
    #[Interact]
    public function prompt(InputInterface $input, SymfonyStyle $io): void
    {
        if (!$input->getArgument(\'name\')) {
            $input->setArgument(\'name\', $io->ask(\'Enter name\'));
        }
    }

    public function __invoke(#[Argument] string $name): int
    {
        // ...
        return Command::SUCCESS;
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[Interact] attribute replaces the interact() method that commands had to override. Now you can apply it to any non-static public method in your command. The method name is no longer required to be "interact", and you can use a more convenient method signature.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-improved-invokable-commands',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'The <code>interact()</code> method override, allowing any public method to handle interactive prompts', 'correct' => true],
                    ['text' => 'The <code>configure()</code> method by combining interactive setup with runtime configuration', 'correct' => false],
                    ['text' => 'The <code>initialize()</code> method for setting up command state', 'correct' => false],
                    ['text' => 'The <code>afterExecute()</code> hook for post-execution interactions', 'correct' => false],
                ],
            ],

            // Q10 - HTTP - QUERY method
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'text' => '<p>Symfony 7.4 adds support for the HTTP <code>QUERY</code> method. What is the purpose of this method?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The HTTP QUERY method is a proposed standard that acts like GET but allows sending a request body (like POST). This is useful for complex queries that cannot fit in URL query strings. Symfony 7.4 adds support for parsing the body of QUERY requests.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'It acts like <code>GET</code> but allows sending a request body for complex queries', 'correct' => true],
                    ['text' => 'It executes SQL queries directly against the database through HTTP', 'correct' => false],
                    ['text' => 'It returns only the query string parameters without executing the controller', 'correct' => false],
                    ['text' => 'It is an alias for <code>HEAD</code> that also returns response headers', 'correct' => false],
                ],
            ],

            // Q11 - Miscellaneous - Error handling in terminal
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Console'),
                'text' => '<p>In Symfony 7.4, when an exception occurs while running a console command, how are exceptions displayed by default?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 7.4 displays clean, readable exception traces in the terminal instead of verbose HTML dumps. This improvement makes debugging console commands much easier.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-exceptions-in-terminal',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Clean, readable exception traces optimized for terminal display', 'correct' => true],
                    ['text' => 'Full HTML stack traces rendered in the terminal', 'correct' => false],
                    ['text' => 'JSON-formatted error objects for machine parsing', 'correct' => false],
                    ['text' => 'Only the exception message, without any trace information', 'correct' => false],
                ],
            ],

            // Q12 - DI - allowed_http_method_override config
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HttpFoundation'),
                'text' => '<p>In Symfony 7.4, a new configuration option <code>allowed_http_method_override</code> was introduced. What does this configuration control?</p>
<pre><code class="language-yaml"># config/packages/framework.yaml
framework:
    allowed_http_method_override: [\'PUT\', \'DELETE\', \'PATCH\']</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'This configuration defines which HTTP methods can be overridden using the _method field in forms. Since browsers only support GET and POST in HTML forms, Symfony allows overriding to simulate PUT, DELETE, PATCH, etc. This option restricts which methods can be overridden to prevent abuse.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-request-class-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Which HTTP methods can be simulated using the <code>_method</code> form field override', 'correct' => true],
                    ['text' => 'Which HTTP methods are allowed by the application firewall', 'correct' => false],
                    ['text' => 'Which HTTP methods trigger CORS preflight requests', 'correct' => false],
                    ['text' => 'Which HTTP methods are allowed for static asset serving', 'correct' => false],
                ],
            ],

            // Q13 - Serializer - Extending with PHP attributes
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Serializer'),
                'text' => '<p>Symfony 7.4 extends both the Validator and Serializer components with new PHP attributes. What is a common benefit of these new attributes for both components?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Both the Validator and Serializer components received new PHP attributes in Symfony 7.4 that allow you to extend metadata for classes you do not control. This means you can add serialization groups, validation constraints, and other metadata to third-party classes using attributes, without modifying the original code.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-extending-validation-and-serialization-with-php-attributes',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Adding metadata to third-party classes you do not control, without modifying their source', 'correct' => true],
                    ['text' => 'Replacing the entire serialization/validation pipeline with a simpler one', 'correct' => false],
                    ['text' => 'Automatically generating REST API endpoints from these attributes', 'correct' => false],
                    ['text' => 'Enabling runtime validation and serialization without any configuration', 'correct' => false],
                ],
            ],

            // Q14 - PHP 8.4 - #[\Deprecated] attribute
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'text' => '<p>PHP 8.4 introduces a native <code>#[\Deprecated]</code> attribute. What is its effect?</p>
<pre><code class="language-php">#[\Deprecated("Use newMethod() instead")]
function oldMethod(): void
{
    // ...
}

oldMethod(); // What happens?</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[\Deprecated] attribute in PHP 8.4 triggers an E_USER_DEPRECATED error when the annotated function, method, or class constant is called. This is the native PHP equivalent of the @deprecated docblock, but it actually enforces the deprecation at runtime.',
                'resourceUrl' => 'https://wiki.php.net/rfc/deprecated_attribute',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'An <code>E_USER_DEPRECATED</code> error is triggered at runtime when the function is called', 'correct' => true],
                    ['text' => 'A compile-time error is thrown preventing the code from running', 'correct' => false],
                    ['text' => 'The function is removed from the available functions list', 'correct' => false],
                    ['text' => 'Nothing happens at runtime; it is only for IDE and static analysis tools', 'correct' => false],
                ],
            ],

            // Q15 - PHP 8.4 - Lazy objects
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:OOP'),
                'text' => '<p>PHP 8.4 adds native support for lazy objects via the <code>ReflectionClass</code> API. What is the primary benefit of lazy objects?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Lazy objects delay their initialization until they are actually used (a property is accessed or a method is called). This is useful for expensive-to-create objects like database connections, heavy services, etc. PHP 8.4 provides native support through ReflectionClass::newLazyGhost() and ReflectionClass::newLazyProxy().',
                'resourceUrl' => 'https://wiki.php.net/rfc/lazy-objects',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Delaying object initialization until the object is actually used, improving performance', 'correct' => true],
                    ['text' => 'Automatically caching object state between requests', 'correct' => false],
                    ['text' => 'Creating objects that can be serialized without implementing Serializable', 'correct' => false],
                    ['text' => 'Enabling concurrent access to objects from multiple threads', 'correct' => false],
                ],
            ],
        ];
    }
}
