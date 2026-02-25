<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Symfony 8 Certification Exam Questions (Questions 76-150)
 * Based on official topics: https://certification.symfony.com/exams/symfony.html
 * Sources: https://symfony.com/doc + https://www.php.net/manual
 * Topics: PHP (up to 8.4), HTTP, Symfony Architecture, Controllers, Routing,
 * Twig, Forms, Validation, DI, Security, Messenger, Console, Tests, Miscellaneous
 * Excluded: Doctrine, Symfony UX, AssetMapper, Lock, Uid, Rate Limiter, third-party bridges
 */
class CertificationExamFixtures3 extends Fixture implements FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['exam'];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found. Run AppFixtures first.');
        }

        $subcategories = $this->loadSubcategories($manager);
        $questions = $this->getCertificationQuestions($symfony, $php, $subcategories);

        foreach ($questions as $q) {
            $q['isCertification'] = true;
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }

    private function getCertificationQuestions(Category $symfony, Category $php, array $subcategories): array
    {
        return [

            // =====================================================
            // SECTION 1: PHP 8.1 - 8.4 FEATURES
            // =====================================================

            // QUESTION 76 - PHP 8.1 Enums
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Consider the following PHP 8.1 enum:
<pre><code class="language-php">enum Status: string
{
    case Active = \'active\';
    case Inactive = \'inactive\';

    public function label(): string
    {
        return match($this) {
            self::Active => \'Actif\',
            self::Inactive => \'Inactif\',
        };
    }
}</code></pre>

Which of the following statements are correct about this code?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP 8.1 Backed Enums can have methods. Status::Active->value returns \'active\'. Status::from(\'active\') returns Status::Active. Enums cannot be instantiated with new. Enums can implement interfaces but cannot extend classes.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.enumerations.backed.php',
                'answers' => [
                    ['text' => 'Status::Active->value returns \'active\'.', 'correct' => true],
                    ['text' => 'Status::from(\'active\') returns Status::Active.', 'correct' => true],
                    ['text' => 'You can create a new instance with new Status(\'active\').', 'correct' => false],
                    ['text' => 'Backed enums can have methods like label().', 'correct' => true],
                    ['text' => 'This enum can extend a class.', 'correct' => false],
                ],
            ],

            // QUESTION 77 - PHP 8.1 Readonly properties
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Consider the following PHP 8.1 code:
<pre><code class="language-php">class User
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}
}

$user = new User(\'Alice\', \'alice@example.com\');
$user->name = \'Bob\';</code></pre>

What will happen when this code is executed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Readonly properties in PHP 8.1 can only be initialized once (typically in the constructor). Attempting to modify them afterwards throws an Error.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties',
                'answers' => [
                    ['text' => 'The name property will be changed to \'Bob\'.', 'correct' => false],
                    ['text' => 'A fatal error (Error) will be thrown because readonly properties cannot be modified after initialization.', 'correct' => true],
                    ['text' => 'A warning will be emitted but the value will remain \'Alice\'.', 'correct' => false],
                    ['text' => 'The code is invalid because readonly cannot be used with constructor property promotion.', 'correct' => false],
                ],
            ],

            // QUESTION 78 - PHP 8.1 Intersection Types
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Typing & Strict Types'],
                'text' => 'Which of the following type declarations is a valid intersection type in PHP 8.1?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Intersection types use & to require a value to implement multiple interfaces/classes simultaneously. Countable&Iterator means the parameter must implement BOTH interfaces. You cannot mix union (|) and intersection (&) types without Disjunctive Normal Form (PHP 8.2+).',
                'resourceUrl' => 'https://www.php.net/manual/en/language.types.type-system.php#language.types.type-system.composite.intersection',
                'answers' => [
                    ['text' => 'function foo(Countable|Iterator $param)', 'correct' => false],
                    ['text' => 'function foo(Countable&Iterator $param)', 'correct' => true],
                    ['text' => 'function foo(Countable&string $param)', 'correct' => false],
                    ['text' => 'function foo(int&string $param)', 'correct' => false],
                ],
            ],

            // QUESTION 79 - PHP 8.1 Fibers
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is the main purpose of Fibers introduced in PHP 8.1?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Fibers are lightweight, cooperatively scheduled threads that allow interruptible computations. A Fiber can suspend its execution (Fiber::suspend()) and be resumed later (Fiber->resume()). They are NOT about parallel execution or multi-threading.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.fibers.php',
                'answers' => [
                    ['text' => 'To enable multi-threaded parallel computation in PHP.', 'correct' => false],
                    ['text' => 'To allow interruptible code blocks that can be suspended and resumed cooperatively.', 'correct' => true],
                    ['text' => 'To replace generators (yield) in PHP.', 'correct' => false],
                    ['text' => 'To provide asynchronous I/O operations natively in PHP.', 'correct' => false],
                ],
            ],

            // QUESTION 80 - PHP 8.1 First-class callable syntax
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Functions'],
                'text' => 'Consider the following PHP 8.1 code:
<pre><code class="language-php">$fn = strlen(...);
echo $fn(\'Hello\');</code></pre>

What will be the output?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP 8.1 introduced the first-class callable syntax using the spread operator (...). strlen(...) creates a Closure that wraps the strlen function. Calling $fn(\'Hello\') is equivalent to calling strlen(\'Hello\'), which returns 5.',
                'resourceUrl' => 'https://www.php.net/manual/en/functions.first_class_callable_syntax.php',
                'answers' => [
                    ['text' => '5', 'correct' => true],
                    ['text' => 'An error: strlen is not a closure.', 'correct' => false],
                    ['text' => 'Hello', 'correct' => false],
                    ['text' => 'null', 'correct' => false],
                ],
            ],

            // QUESTION 81 - PHP 8.1 never return type
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Typing & Strict Types'],
                'text' => 'Consider the following PHP 8.1 code:
<pre><code class="language-php">function redirect(string $url): never
{
    header("Location: $url");
    exit();
}</code></pre>

What does the "never" return type mean?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The "never" return type indicates that a function will never return a value — it either throws an exception, calls exit()/die(), or enters an infinite loop. If the function actually returns, a TypeError is thrown.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.types.never.php',
                'answers' => [
                    ['text' => 'The function returns null.', 'correct' => false],
                    ['text' => 'The function never returns — it always throws, exits, or loops forever.', 'correct' => true],
                    ['text' => 'The function returns void.', 'correct' => false],
                    ['text' => 'The function can only return false.', 'correct' => false],
                ],
            ],

            // QUESTION 82 - PHP 8.2 Readonly classes
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Consider the following PHP 8.2 code:
<pre><code class="language-php">readonly class Point
{
    public function __construct(
        public float $x,
        public float $y,
    ) {}
}</code></pre>

Which of the following statements is correct about readonly classes in PHP 8.2?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'A readonly class in PHP 8.2 implicitly makes all declared properties readonly. You cannot declare non-readonly properties inside a readonly class. The class can still have methods, but all properties are automatically readonly.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.readonly',
                'answers' => [
                    ['text' => 'All properties are implicitly readonly, so you don\'t need to declare them as readonly individually.', 'correct' => true],
                    ['text' => 'The class cannot have any methods.', 'correct' => false],
                    ['text' => 'The class cannot be extended.', 'correct' => false],
                    ['text' => 'You can still add non-readonly properties to the class.', 'correct' => false],
                ],
            ],

            // QUESTION 83 - PHP 8.2 Disjunctive Normal Form (DNF) types
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Typing & Strict Types'],
                'text' => 'Which of the following type declarations uses the Disjunctive Normal Form (DNF) syntax introduced in PHP 8.2?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'PHP 8.2 allows combining union and intersection types using DNF. The syntax (A&B)|C means: the value must either implement both A and B, OR be of type C. Parentheses are required around intersection types when combined with union types.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.types.type-system.php#language.types.type-system.composite.dnf',
                'answers' => [
                    ['text' => 'function foo(Countable&Iterator|null $param)', 'correct' => false],
                    ['text' => 'function foo((Countable&Iterator)|null $param)', 'correct' => true],
                    ['text' => 'function foo(Countable|Iterator&null $param)', 'correct' => false],
                    ['text' => 'function foo(Countable&(Iterator|null) $param)', 'correct' => false],
                ],
            ],

            // QUESTION 84 - PHP 8.3 Typed class constants
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Is the following PHP 8.3 code valid?
<pre><code class="language-php">class Config
{
    public const string APP_NAME = \'MyApp\';
    public const int MAX_RETRIES = 3;
    public const array ALLOWED_ROLES = [\'admin\', \'user\'];
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'PHP 8.3 introduced typed class constants. You can now specify a type for class constants. The types string, int, and array are all valid types for constants.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.constants.php',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // QUESTION 85 - PHP 8.3 #[\Override] attribute
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'What is the purpose of the #[\Override] attribute introduced in PHP 8.3?
<pre><code class="language-php">class ParentClass
{
    public function doSomething(): void {}
}

class ChildClass extends ParentClass
{
    #[\Override]
    public function doSomething(): void {}
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[\Override] attribute signals that a method is intended to override a parent method. If the parent class removes or renames the method, PHP will throw an error at compile time, helping detect broken inheritance chains.',
                'resourceUrl' => 'https://www.php.net/manual/en/class.override.php',
                'answers' => [
                    ['text' => 'It forces the method to be final and prevents further overriding.', 'correct' => false],
                    ['text' => 'It verifies at compile time that the method actually overrides a parent/interface method, throwing an error if not.', 'correct' => true],
                    ['text' => 'It makes the method automatically call the parent implementation first.', 'correct' => false],
                    ['text' => 'It prevents the method from being called directly — only the parent version is used.', 'correct' => false],
                ],
            ],

            // QUESTION 86 - PHP 8.3 json_validate()
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:JSON'],
                'text' => 'Consider the following PHP 8.3 code:
<pre><code class="language-php">$result1 = json_validate(\'{"name": "Alice"}\');
$result2 = json_validate(\'not json\');
$result3 = json_validate(\'"hello"\');</code></pre>

What are the values of $result1, $result2, and $result3?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'json_validate() is a new function in PHP 8.3 that validates if a string is valid JSON without decoding it. {"name": "Alice"} is valid JSON (true), "not json" is invalid (false), and "hello" is a valid JSON string value (true).',
                'resourceUrl' => 'https://www.php.net/manual/en/function.json-validate.php',
                'answers' => [
                    ['text' => 'true, false, true', 'correct' => true],
                    ['text' => 'true, false, false', 'correct' => false],
                    ['text' => 'true, true, true', 'correct' => false],
                    ['text' => 'true, false, null', 'correct' => false],
                ],
            ],

            // QUESTION 87 - PHP 8.1 enum in match
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Consider the following PHP 8.1 code:
<pre><code class="language-php">enum Color
{
    case Red;
    case Green;
    case Blue;
}

function describe(Color $color): string
{
    return match($color) {
        Color::Red => \'Warm\',
        Color::Green => \'Cool\',
    };
}

echo describe(Color::Blue);</code></pre>

What will happen when this code is executed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The match() expression in PHP 8.0+ throws an UnhandledMatchError if no arm matches the subject. Since Color::Blue is not handled and there is no default arm, an UnhandledMatchError will be thrown.',
                'resourceUrl' => 'https://www.php.net/manual/en/control-structures.match.php',
                'answers' => [
                    ['text' => 'It prints an empty string.', 'correct' => false],
                    ['text' => 'It prints null.', 'correct' => false],
                    ['text' => 'An UnhandledMatchError is thrown.', 'correct' => true],
                    ['text' => 'A warning is emitted and execution continues.', 'correct' => false],
                ],
            ],

            // QUESTION 88 - PHP 8.2 true/false/null as standalone types
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Typing & Strict Types'],
                'text' => 'In PHP 8.2, which of the following function signatures are valid?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP 8.2 introduced true, false, and null as standalone types. function foo(): true means the function can only return true. function bar(): false means it can only return false. function baz(): null means it can only return null. These are all valid standalone types in PHP 8.2.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.types.literal.php',
                'answers' => [
                    ['text' => 'function foo(): true { return true; }', 'correct' => true],
                    ['text' => 'function bar(): false { return false; }', 'correct' => true],
                    ['text' => 'function baz(): null { return null; }', 'correct' => true],
                    ['text' => 'function qux(): 42 { return 42; }', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 2: SYMFONY 8 - CONTROLLERS & ROUTING
            // =====================================================

            // QUESTION 89 - #[MapQueryParameter] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Controllers'],
                'text' => 'Consider the following Symfony controller:
<pre><code class="language-php">use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class ProductController extends AbstractController
{
    #[Route(\'/products\')]
    public function list(
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] string $sort = \'name\',
    ): Response {
        // ...
    }
}</code></pre>

If a user visits /products?page=3&sort=price, what values will $page and $sort have?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[MapQueryParameter] attribute automatically maps query string parameters to controller method arguments with type coercion. ?page=3 maps to int $page = 3, and ?sort=price maps to string $sort = \'price\'.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller.html#mapping-query-parameters',
                'answers' => [
                    ['text' => '$page = 3 (int), $sort = \'price\' (string)', 'correct' => true],
                    ['text' => '$page = \'3\' (string), $sort = \'price\' (string)', 'correct' => false],
                    ['text' => '$page = 1, $sort = \'name\' (default values always used)', 'correct' => false],
                    ['text' => 'An exception is thrown because query parameters need explicit binding.', 'correct' => false],
                ],
            ],

            // QUESTION 90 - #[MapRequestPayload] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Controllers'],
                'text' => 'Consider the following Symfony controller:
<pre><code class="language-php">use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class ApiController extends AbstractController
{
    #[Route(\'/api/users\', methods: [\'POST\'])]
    public function create(
        #[MapRequestPayload] UserDto $userDto,
    ): JsonResponse {
        // ...
    }
}</code></pre>

What does the #[MapRequestPayload] attribute do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '#[MapRequestPayload] automatically deserializes the request body (JSON, XML, or form data) into the specified DTO object using the Symfony Serializer. It also validates the object if Validator is available.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller.html#mapping-the-request-payload',
                'answers' => [
                    ['text' => 'It extracts query string parameters and maps them to the DTO.', 'correct' => false],
                    ['text' => 'It deserializes the request body (JSON/XML) into the DTO object and optionally validates it.', 'correct' => true],
                    ['text' => 'It parses the URL path segments into the DTO properties.', 'correct' => false],
                    ['text' => 'It only works with HTML form submissions, not JSON requests.', 'correct' => false],
                ],
            ],

            // QUESTION 91 - #[MapQueryString] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Controllers'],
                'text' => 'What is the difference between #[MapQueryParameter] and #[MapQueryString] in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '#[MapQueryParameter] maps individual query parameters to scalar arguments. #[MapQueryString] maps the entire query string to a DTO object, allowing multiple parameters to be grouped into a single typed object.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller.html#mapping-the-whole-query-string',
                'answers' => [
                    ['text' => '#[MapQueryParameter] maps individual query params to scalar arguments; #[MapQueryString] maps the whole query string to a DTO object.', 'correct' => true],
                    ['text' => 'They are aliases and do exactly the same thing.', 'correct' => false],
                    ['text' => '#[MapQueryString] only works with GET requests, while #[MapQueryParameter] works with all HTTP methods.', 'correct' => false],
                    ['text' => '#[MapQueryParameter] is deprecated in favor of #[MapQueryString].', 'correct' => false],
                ],
            ],

            // QUESTION 92 - Enum type in route parameters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Consider the following code:
<pre><code class="language-php">enum StatusEnum: string
{
    case Published = \'published\';
    case Draft = \'draft\';
}

class ArticleController extends AbstractController
{
    #[Route(\'/articles/{status}\')]
    public function listByStatus(StatusEnum $status): Response
    {
        // ...
    }
}</code></pre>

What happens when a user visits /articles/published?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony supports PHP 8.1 backed enums as route parameters. The string \'published\' is automatically converted to StatusEnum::Published. If the value doesn\'t match any enum case, a 404 is returned.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#enum-route-parameters',
                'answers' => [
                    ['text' => 'Symfony automatically converts \'published\' to StatusEnum::Published.', 'correct' => true],
                    ['text' => '$status will be the string \'published\', not the enum case.', 'correct' => false],
                    ['text' => 'An error is thrown because enums cannot be used as route parameters.', 'correct' => false],
                    ['text' => 'You need a ParamConverter to convert the string to an enum.', 'correct' => false],
                ],
            ],

            // QUESTION 93 - #[IsGranted] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Consider the following controller:
<pre><code class="language-php">use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    #[IsGranted(\'ROLE_ADMIN\')]
    #[Route(\'/admin/dashboard\')]
    public function dashboard(): Response
    {
        // ...
    }
}</code></pre>

What happens if a user without ROLE_ADMIN accesses /admin/dashboard?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[IsGranted] attribute checks the expression/role at the controller level. If the user doesn\'t have the required role, an AccessDeniedException is thrown, which typically results in a 403 Forbidden response.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html#securing-controllers-and-other-code',
                'answers' => [
                    ['text' => 'An AccessDeniedException is thrown (HTTP 403).', 'correct' => true],
                    ['text' => 'The user is redirected to the login page.', 'correct' => false],
                    ['text' => 'A 404 Not Found response is returned.', 'correct' => false],
                    ['text' => 'The method executes normally but returns an empty response.', 'correct' => false],
                ],
            ],

            // QUESTION 94 - #[IsGranted] with subject
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Consider the following controller:
<pre><code class="language-php">use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends AbstractController
{
    #[IsGranted(\'POST_EDIT\', subject: \'post\')]
    #[Route(\'/post/{id}/edit\')]
    public function edit(Post $post): Response
    {
        // ...
    }
}</code></pre>

What does the "subject" parameter do in the #[IsGranted] attribute?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The "subject" parameter specifies which controller argument should be passed to the Voter as the subject of the authorization check. Here, the $post object is passed to the Voter alongside the \'POST_EDIT\' attribute.',
                'resourceUrl' => 'https://symfony.com/doc/current/security/voters.html',
                'answers' => [
                    ['text' => 'It passes the $post argument to the security Voter as the subject for the authorization decision.', 'correct' => true],
                    ['text' => 'It sets the subject header of the HTTP response.', 'correct' => false],
                    ['text' => 'It specifies the name of the route parameter to check.', 'correct' => false],
                    ['text' => 'It defines the error message displayed when access is denied.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 3: SYMFONY 8 - DEPENDENCY INJECTION
            // =====================================================

            // QUESTION 95 - #[AsEventListener] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Consider the following class:
<pre><code class="language-php">use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(event: RequestEvent::class, priority: 10)]
class MyRequestListener
{
    public function __invoke(RequestEvent $event): void
    {
        // ...
    }
}</code></pre>

Which statement is correct about the #[AsEventListener] attribute?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[AsEventListener] attribute registers a class or method as an event listener without any YAML/XML configuration. The attribute is detected by autoconfiguration. The event and priority parameters are equivalent to manual tag configuration.',
                'resourceUrl' => 'https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener',
                'answers' => [
                    ['text' => 'It registers the class as an event listener automatically through autoconfiguration — no YAML/XML config needed.', 'correct' => true],
                    ['text' => 'You still need to register the service in services.yaml with the kernel.event_listener tag.', 'correct' => false],
                    ['text' => 'The class must extend EventSubscriberInterface to use this attribute.', 'correct' => false],
                    ['text' => 'The attribute only works with kernel events, not custom events.', 'correct' => false],
                ],
            ],

            // QUESTION 96 - #[AutowireIterator] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Consider the following code:
<pre><code class="language-php">use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class HandlerManager
{
    public function __construct(
        #[AutowireIterator(\'app.handler\')] private iterable $handlers,
    ) {}
}</code></pre>

What does the #[AutowireIterator] attribute do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '#[AutowireIterator] injects all services tagged with the specified tag as an iterable. This replaces the need for !tagged_iterator in YAML config. It provides lazy iteration over the tagged services.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/tags.html#autoconfigurable-tags-with-attributes',
                'answers' => [
                    ['text' => 'It injects all services tagged with \'app.handler\' as a lazy iterable.', 'correct' => true],
                    ['text' => 'It creates an array of all service IDs matching the tag.', 'correct' => false],
                    ['text' => 'It only works with services implementing IteratorInterface.', 'correct' => false],
                    ['text' => 'It injects a single random service with the specified tag.', 'correct' => false],
                ],
            ],

            // QUESTION 97 - #[AutowireLocator] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is the difference between #[AutowireIterator] and #[AutowireLocator]?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => '#[AutowireIterator] provides an iterable to loop over all tagged services (lazy instantiation on iteration). #[AutowireLocator] provides a ServiceLocator (PSR-11 ContainerInterface) that allows fetching specific services by key on demand, without instantiating all of them.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/service_subscribers_locators.html',
                'answers' => [
                    ['text' => '#[AutowireIterator] provides an iterable for looping; #[AutowireLocator] provides a service locator for on-demand access by key.', 'correct' => true],
                    ['text' => 'They are identical in behavior, only the name differs.', 'correct' => false],
                    ['text' => '#[AutowireLocator] only works with Doctrine entities.', 'correct' => false],
                    ['text' => '#[AutowireIterator] is for production, #[AutowireLocator] is for development only.', 'correct' => false],
                ],
            ],

            // QUESTION 98 - #[Target] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Consider the following code:
<pre><code class="language-php">use Symfony\Component\DependencyInjection\Attribute\Target;

class OrderService
{
    public function __construct(
        #[Target(\'payment.logger\')] private LoggerInterface $logger,
    ) {}
}</code></pre>

What does the #[Target] attribute do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[Target] attribute is used to specify which named autowiring alias should be injected. It allows disambiguating between multiple implementations of the same interface without referencing the concrete service ID. It works with named aliases defined via #[AsAlias] or configuration.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/autowiring.html#dealing-with-multiple-implementations-of-the-same-type',
                'answers' => [
                    ['text' => 'It specifies which named autowiring alias to inject when multiple services implement the same interface.', 'correct' => true],
                    ['text' => 'It creates a new service with the ID \'payment.logger\'.', 'correct' => false],
                    ['text' => 'It tags the service with \'payment.logger\' for later retrieval.', 'correct' => false],
                    ['text' => 'It is equivalent to #[Autowire(service: \'payment.logger\')].', 'correct' => false],
                ],
            ],

            // QUESTION 99 - #[Autoconfigure] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Consider the following interface:
<pre><code class="language-php">use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: [\'app.handler\'])]
interface HandlerInterface
{
    public function handle(): void;
}</code></pre>

What happens when a class implements HandlerInterface?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[Autoconfigure] attribute on an interface or parent class automatically applies configuration (tags, method calls, etc.) to all services that implement/extend it. Here, any class implementing HandlerInterface is automatically tagged with \'app.handler\'.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html#autoconfiguring-tags-attributes-and-calls',
                'answers' => [
                    ['text' => 'The class is automatically tagged with \'app.handler\' through autoconfiguration.', 'correct' => true],
                    ['text' => 'The class must manually add the tag via attributes.', 'correct' => false],
                    ['text' => 'The #[Autoconfigure] attribute only works on concrete classes, not interfaces.', 'correct' => false],
                    ['text' => 'Nothing happens — #[Autoconfigure] is only for YAML configuration.', 'correct' => false],
                ],
            ],

            // QUESTION 100 - #[Lazy] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Consider the following service:
<pre><code class="language-php">use Symfony\Component\DependencyInjection\Attribute\Lazy;

#[Lazy]
class HeavyService
{
    public function __construct()
    {
        // Very expensive initialization...
    }

    public function process(): string
    {
        return \'result\';
    }
}</code></pre>

What does the #[Lazy] attribute do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[Lazy] attribute marks a service for lazy loading. A ghost proxy object is injected instead of the real service. The actual service is only instantiated when one of its methods is first called. This avoids expensive initialization when the service might not be used.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/lazy_services.html',
                'answers' => [
                    ['text' => 'A proxy object is injected; the real service is only instantiated when a method is actually called on it.', 'correct' => true],
                    ['text' => 'The service is created in a background process.', 'correct' => false],
                    ['text' => 'The service becomes a singleton shared across all requests.', 'correct' => false],
                    ['text' => 'The service is only available in the dev environment.', 'correct' => false],
                ],
            ],

            // QUESTION 101 - #[When] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Consider the following code:
<pre><code class="language-php">use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: \'dev\')]
class DebugMailer implements MailerInterface
{
    public function send(Email $email): void
    {
        // Log email instead of sending
    }
}</code></pre>

What does the #[When] attribute do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[When] attribute conditionally registers a service based on the current environment. Here, DebugMailer is only registered as a service in the \'dev\' environment. In other environments (prod, test), this class is completely ignored by the container.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html#limiting-services-to-a-specific-symfony-environment',
                'answers' => [
                    ['text' => 'The service is only registered in the container when the environment matches (here: dev).', 'correct' => true],
                    ['text' => 'The service is available in all environments but throws an exception if called in non-dev.', 'correct' => false],
                    ['text' => 'The service is lazy-loaded only in the specified environment.', 'correct' => false],
                    ['text' => 'The attribute has no effect — environment filtering requires YAML config only.', 'correct' => false],
                ],
            ],

            // QUESTION 102 - #[AsAlias] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'What is the purpose of the #[AsAlias] attribute in Symfony?
<pre><code class="language-php">use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: \'app.mailer\')]
class SmtpMailer implements MailerInterface
{
    // ...
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '#[AsAlias] creates a service alias. Here, the service ID \'app.mailer\' becomes an alias for the SmtpMailer service. This allows other services to reference it by the alias ID, and also enables #[Target(\'app.mailer\')] to resolve to this service.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/autowiring.html',
                'answers' => [
                    ['text' => 'It creates a service alias so the service can be referenced by the given ID name.', 'correct' => true],
                    ['text' => 'It renames the service class at runtime.', 'correct' => false],
                    ['text' => 'It makes the service public so it can be fetched from the container.', 'correct' => false],
                    ['text' => 'It is used to tag the service for event dispatching.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 4: SYMFONY 8 - MESSENGER
            // =====================================================

            // QUESTION 103 - Messenger sync vs async dispatch
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'In Symfony Messenger, what happens when a message is dispatched and no transport routing is configured for that message class?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When no transport routing is defined for a message class, Symfony Messenger handles the message synchronously — the handler is called immediately within the same request/process. To handle messages asynchronously, you must configure routing in messenger.yaml to send the message to a transport.',
                'resourceUrl' => 'https://symfony.com/doc/current/messenger.html#transports-async-queued-messages',
                'answers' => [
                    ['text' => 'The message is handled synchronously — the handler is called immediately in the same process.', 'correct' => true],
                    ['text' => 'An exception is thrown because no transport is configured.', 'correct' => false],
                    ['text' => 'The message is silently discarded.', 'correct' => false],
                    ['text' => 'The message is queued in a default in-memory transport.', 'correct' => false],
                ],
            ],

            // QUESTION 104 - Messenger Stamp
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'In Symfony Messenger, what is the purpose of "Stamps"?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Stamps are metadata objects attached to an Envelope in Symfony Messenger. They provide additional context about the message (transport, delay, retry count, etc.). Examples: DelayStamp, TransportMessageIdStamp, SentStamp.',
                'resourceUrl' => 'https://symfony.com/doc/current/messenger.html#envelopes-stamps',
                'answers' => [
                    ['text' => 'They are metadata objects attached to a message envelope to add context or modify behavior.', 'correct' => true],
                    ['text' => 'They are used to validate message contents before dispatch.', 'correct' => false],
                    ['text' => 'They are serialization markers to convert messages to JSON.', 'correct' => false],
                    ['text' => 'They define which handlers should process the message.', 'correct' => false],
                ],
            ],

            // QUESTION 105 - Messenger handler return
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Consider the following Messenger handler:
<pre><code class="language-php">use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailHandler
{
    public function __invoke(SendEmailMessage $message): void
    {
        // send email...
    }
}</code></pre>

Which attribute registers this class as a Messenger handler?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The #[AsMessageHandler] attribute is the modern way to register a Messenger handler. It replaces the manual tagging with messenger.message_handler. With autoconfiguration, implementing MessageHandlerInterface also works, but the attribute is the recommended approach.',
                'resourceUrl' => 'https://symfony.com/doc/current/messenger.html#creating-a-message-handler',
                'answers' => [
                    ['text' => '#[AsMessageHandler]', 'correct' => true],
                    ['text' => '#[AsEventListener]', 'correct' => false],
                    ['text' => '#[AsCommand]', 'correct' => false],
                    ['text' => '#[Autoconfigure]', 'correct' => false],
                ],
            ],

            // QUESTION 106 - Messenger middleware
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'In Symfony Messenger, what is the role of middleware?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Middleware in Symfony Messenger form a chain that processes the message envelope before and after it reaches the handler. Built-in middleware includes: SendMessageMiddleware (sends to transports), HandleMessageMiddleware (calls handlers), ValidationMiddleware (validates messages), and more. Custom middleware can be added to the message bus.',
                'resourceUrl' => 'https://symfony.com/doc/current/messenger.html#middleware',
                'answers' => [
                    ['text' => 'Middleware form a processing chain around the message handler, allowing logic before and after handling (e.g., validation, transactions).', 'correct' => true],
                    ['text' => 'Middleware are used exclusively for logging message dispatch events.', 'correct' => false],
                    ['text' => 'Middleware replace message handlers — they contain the business logic.', 'correct' => false],
                    ['text' => 'Middleware are only executed when messages are processed asynchronously.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 5: SYMFONY 8 - SERIALIZER & DTO
            // =====================================================

            // QUESTION 107 - Serializer groups
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Consider the following code:
<pre><code class="language-php">use Symfony\Component\Serializer\Attribute\Groups;

class User
{
    #[Groups([\'user:read\', \'user:list\'])]
    public string $name;

    #[Groups([\'user:read\'])]
    public string $email;

    #[Groups([\'admin:read\'])]
    public string $password;
}</code></pre>

When serializing with the group \'user:read\', which properties will be included?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When serializing with groups, only properties belonging to the specified group are included. \'user:read\' includes $name (has user:read) and $email (has user:read). $password is excluded because it only belongs to admin:read.',
                'resourceUrl' => 'https://symfony.com/doc/current/serializer.html#using-serialization-groups-attributes',
                'answers' => [
                    ['text' => '$name and $email only.', 'correct' => true],
                    ['text' => '$name, $email, and $password.', 'correct' => false],
                    ['text' => '$name only.', 'correct' => false],
                    ['text' => 'All properties are included because group filtering is optional.', 'correct' => false],
                ],
            ],

            // QUESTION 108 - #[SerializedName] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'What does the #[SerializedName] attribute do in the Symfony Serializer?
<pre><code class="language-php">use Symfony\Component\Serializer\Attribute\SerializedName;

class Product
{
    #[SerializedName(\'product_name\')]
    public string $name;
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => '#[SerializedName] allows you to customize the key name used during serialization/deserialization. Here, the $name property will be serialized as "product_name" in the output and deserialized from "product_name" in the input.',
                'resourceUrl' => 'https://symfony.com/doc/current/serializer.html#serializedname',
                'answers' => [
                    ['text' => 'It customizes the key name used in the serialized output (e.g., JSON key becomes "product_name").', 'correct' => true],
                    ['text' => 'It renames the PHP property at runtime.', 'correct' => false],
                    ['text' => 'It sets a default value for the property when deserialization fails.', 'correct' => false],
                    ['text' => 'It specifies which serializer format to use (JSON, XML, etc.).', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 6: SYMFONY 8 - FORMS
            // =====================================================

            // QUESTION 109 - Form handleRequest pattern
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Consider the following controller code:
<pre><code class="language-php">class TaskController extends AbstractController
{
    #[Route(\'/task/new\', methods: [\'GET\', \'POST\'])]
    public function new(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // save task...
            return $this->redirectToRoute(\'task_list\');
        }

        return $this->render(\'task/new.html.twig\', [
            \'form\' => $form,
        ]);
    }
}</code></pre>

In Symfony 7+, what is notable about passing $form to the template?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Since Symfony 6.2+, you can pass the Form object directly to the template without calling createView(). Twig will automatically call createView() when needed. Previously, you had to pass $form->createView().',
                'resourceUrl' => 'https://symfony.com/doc/current/forms.html#rendering-forms',
                'answers' => [
                    ['text' => 'You can pass the Form object directly — Twig automatically calls createView() when needed.', 'correct' => true],
                    ['text' => 'You must always call $form->createView() before passing to the template.', 'correct' => false],
                    ['text' => 'The form is automatically rendered without any Twig function calls.', 'correct' => false],
                    ['text' => 'Passing a Form object directly causes a TypeError in Twig.', 'correct' => false],
                ],
            ],

            // QUESTION 110 - PasswordType with hash_property_path
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What does the hash_property_path option do in Symfony\'s PasswordType form field?
<pre><code class="language-php">$builder->add(\'plainPassword\', PasswordType::class, [
    \'hash_property_path\' => \'password\',
    \'mapped\' => false,
]);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The hash_property_path option (introduced in Symfony 6.2) automatically hashes the submitted plain password and sets it on the specified property path of the form\'s underlying object. This eliminates the need to manually hash passwords in the controller.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/forms/types/password.html#hash-property-path',
                'answers' => [
                    ['text' => 'It automatically hashes the submitted password and sets it on the entity\'s \'password\' property.', 'correct' => true],
                    ['text' => 'It verifies that the password matches the existing hash in the database.', 'correct' => false],
                    ['text' => 'It displays a hash of the current password for debugging.', 'correct' => false],
                    ['text' => 'It maps the form field to a hashed property name instead of the field name.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 7: SYMFONY 8 - TWIG & HTTP
            // =====================================================

            // QUESTION 111 - Twig template inheritance
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In Twig, what does the {{ parent() }} function do when used inside a {% block %}?
<pre><code class="language-twig">{% extends \'base.html.twig\' %}

{% block body %}
    {{ parent() }}
    &lt;p&gt;Additional content&lt;/p&gt;
{% endblock %}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The parent() function in Twig renders the content of the same block from the parent template. This allows child templates to extend (not just replace) the parent block content. Without parent(), the child block completely overrides the parent block.',
                'resourceUrl' => 'https://symfony.com/doc/current/templates.html#template-inheritance-and-layouts',
                'answers' => [
                    ['text' => 'It renders the content of the same block from the parent template, allowing to extend rather than fully replace it.', 'correct' => true],
                    ['text' => 'It renders the entire parent template above the current block.', 'correct' => false],
                    ['text' => 'It calls the parent controller action.', 'correct' => false],
                    ['text' => 'It includes a separate template file by name.', 'correct' => false],
                ],
            ],

            // QUESTION 112 - Twig auto escaping
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In Twig, how does auto-escaping work and how can you output raw HTML content?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Auto-escaping in Twig automatically escapes HTML entities in variables to prevent XSS attacks. By default, {{ variable }} escapes HTML special characters. To output raw, unescaped HTML, use the |raw filter: {{ variable|raw }}. You can also disable auto-escaping for a block with {% autoescape false %}.',
                'resourceUrl' => 'https://symfony.com/doc/current/templates.html#output-escaping',
                'answers' => [
                    ['text' => 'Twig escapes HTML by default; use the |raw filter to output unescaped content: {{ variable|raw }}.', 'correct' => true],
                    ['text' => 'Auto-escaping is disabled by default — you must use the |escape filter to enable it.', 'correct' => false],
                    ['text' => 'Auto-escaping only applies to variables passed from Doctrine entities.', 'correct' => false],
                    ['text' => 'Auto-escaping cannot be disabled — all output is always escaped in Twig.', 'correct' => false],
                ],
            ],

            // QUESTION 113 - HTTP Caching: expiration vs validation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'What is the difference between expiration and validation strategies in HTTP caching?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Expiration (Cache-Control: max-age, Expires header) tells the client how long a response is fresh — no request is made until it expires. Validation (ETag, Last-Modified) requires the client to send a conditional request (If-None-Match, If-Modified-Since) to check if the cached version is still valid — the server responds with 304 Not Modified if unchanged.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_cache.html',
                'answers' => [
                    ['text' => 'Expiration avoids requests entirely until TTL expires; validation requires a conditional request to check freshness (304 if unchanged).', 'correct' => true],
                    ['text' => 'Expiration uses ETags; validation uses Cache-Control headers.', 'correct' => false],
                    ['text' => 'Expiration works only with reverse proxies; validation works only with browsers.', 'correct' => false],
                    ['text' => 'There is no difference — they are two names for the same mechanism.', 'correct' => false],
                ],
            ],

            // QUESTION 114 - Symfony HttpClient component
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'Which of the following are features provided by the Symfony HttpClient component?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Symfony HttpClient provides: lazy (non-blocking) responses by default, automatic JSON decoding, scoped clients (base URI, default headers/options), retry on failure via RetryableHttpClient, PSR-18 compatibility, and streaming responses. It does NOT automatically cache responses — you must wrap it with CachingHttpClient for that.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_client.html',
                'answers' => [
                    ['text' => 'Lazy (non-blocking) responses that are only consumed when accessed.', 'correct' => true],
                    ['text' => 'Scoped clients with a base URI and default headers.', 'correct' => true],
                    ['text' => 'Automatic retry on failure via RetryableHttpClient.', 'correct' => true],
                    ['text' => 'PSR-18 (HTTP Client) compatibility.', 'correct' => true],
                    ['text' => 'Automatic response caching without any additional configuration.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 8: SYMFONY 8 - CACHE & HTTP
            // =====================================================

            // QUESTION 115 - Cache contracts interface
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Consider the following code using Symfony\'s Cache Contracts:
<pre><code class="language-php">use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ProductService
{
    public function __construct(private CacheInterface $cache) {}

    public function getProducts(): array
    {
        return $this->cache->get(\'products_list\', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            return $this->fetchProductsFromDatabase();
        });
    }
}</code></pre>

What pattern does this code implement?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'This uses the Cache Contracts pattern (CacheInterface::get()). It implements a "cache stampede prevention" strategy: if the cache is empty, the callback computes the value and stores it. If multiple requests arrive simultaneously, only one callback executes (the others wait). This is called "lazy computation with stampede protection".',
                'resourceUrl' => 'https://symfony.com/doc/current/cache.html#cache-contracts',
                'answers' => [
                    ['text' => 'Cache-aside (lazy computation) with built-in stampede protection.', 'correct' => true],
                    ['text' => 'Write-through cache that always writes to database first.', 'correct' => false],
                    ['text' => 'Invalidation-based cache that deletes expired items proactively.', 'correct' => false],
                    ['text' => 'Cache-only storage that never touches the database.', 'correct' => false],
                ],
            ],

            // QUESTION 116 - Early expiration (Cache beta)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'What does the "beta" parameter do in Symfony Cache Contracts?
<pre><code class="language-php">$value = $cache->get(\'my_key\', function (ItemInterface $item) {
    $item->expiresAfter(3600);
    return computeExpensiveValue();
}, beta: 1.5);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The beta parameter controls early recomputation (probabilistic early expiration). A higher beta means the cache is recomputed earlier before its actual expiration. This helps prevent cache stampedes by having some requests recompute the value before it actually expires. beta=0 disables early recomputation, beta=INF forces immediate recomputation.',
                'resourceUrl' => 'https://symfony.com/doc/current/cache.html#cache-stampede-prevention',
                'answers' => [
                    ['text' => 'It controls probabilistic early cache recomputation to prevent stampedes — higher beta means earlier recomputation.', 'correct' => true],
                    ['text' => 'It sets the number of retries if cache storage fails.', 'correct' => false],
                    ['text' => 'It defines the priority of the cache item.', 'correct' => false],
                    ['text' => 'It enables beta-testing mode for the cache.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 9: SYMFONY 8 - SECURITY
            // =====================================================

            // QUESTION 117 - Security access_token authenticator
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Symfony provides a built-in access_token authenticator for API token-based authentication. Which of the following token extractors are supported?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony\'s access_token authenticator supports three built-in token extractors: HeaderAccessTokenExtractor (from Authorization: Bearer header), QueryAccessTokenExtractor (from a query parameter), and ChainAccessTokenExtractor (tries multiple extractors). There is no CookieAccessTokenExtractor built-in — you would need a custom implementation.',
                'resourceUrl' => 'https://symfony.com/doc/current/security/access_token.html',
                'answers' => [
                    ['text' => 'HeaderAccessTokenExtractor (from Authorization: Bearer header)', 'correct' => true],
                    ['text' => 'QueryAccessTokenExtractor (from query string parameter)', 'correct' => true],
                    ['text' => 'ChainAccessTokenExtractor (tries multiple extractors)', 'correct' => true],
                    ['text' => 'CookieAccessTokenExtractor (from HTTP cookie)', 'correct' => false],
                ],
            ],

            // QUESTION 118 - Security remember_me
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony\'s security system, what is the difference between IS_AUTHENTICATED_FULLY and IS_AUTHENTICATED_REMEMBERED?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'IS_AUTHENTICATED_FULLY means the user has authenticated during this session (with username/password). IS_AUTHENTICATED_REMEMBERED means the user was authenticated via a "remember me" cookie from a previous session. IS_AUTHENTICATED_FULLY is the more secure check and is recommended for sensitive operations.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html#checking-to-see-if-a-user-is-logged-in',
                'answers' => [
                    ['text' => 'FULLY means authenticated this session; REMEMBERED means authenticated via remember_me cookie.', 'correct' => true],
                    ['text' => 'They are identical — REMEMBERED is an alias for FULLY.', 'correct' => false],
                    ['text' => 'FULLY applies to admin users; REMEMBERED applies to regular users.', 'correct' => false],
                    ['text' => 'FULLY means the user has 2FA enabled; REMEMBERED means password-only authentication.', 'correct' => false],
                ],
            ],

            // QUESTION 119 - Password hasher configuration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PasswordHasher'],
                'text' => 'Consider the following security configuration:
<pre><code class="language-yaml">security:
    password_hashers:
        App\Entity\User:
            algorithm: auto</code></pre>

What does the "auto" algorithm do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The "auto" algorithm automatically selects the best available hashing algorithm. Currently, it uses bcrypt or sodium (Argon2id) depending on what is available on the system. As better algorithms become available in future PHP/Symfony versions, "auto" will automatically upgrade to them.',
                'resourceUrl' => 'https://symfony.com/doc/current/security/passwords.html',
                'answers' => [
                    ['text' => 'It automatically selects the best available password hashing algorithm (currently bcrypt or Argon2id).', 'correct' => true],
                    ['text' => 'It uses MD5 for maximum compatibility.', 'correct' => false],
                    ['text' => 'It stores passwords in plain text for development convenience.', 'correct' => false],
                    ['text' => 'It rotates between different algorithms for each user.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 10: SYMFONY 8 - VALIDATION
            // =====================================================

            // QUESTION 120 - Compound constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'What is a Compound constraint in Symfony Validator?
<pre><code class="language-php">use Symfony\Component\Validator\Constraints\Compound;

#[\Attribute]
class PasswordRequirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\Length(min: 8, max: 128),
            new Assert\Regex(\'/[A-Z]/\'),
            new Assert\Regex(\'/[0-9]/\'),
        ];
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Compound constraint allows grouping multiple constraints into a single reusable constraint. This avoids duplicating the same set of constraints across multiple properties. Here, #[PasswordRequirements] applies all four constraints at once.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/Compound.html',
                'answers' => [
                    ['text' => 'It groups multiple constraints into a single reusable constraint that can be applied as one attribute.', 'correct' => true],
                    ['text' => 'It validates a compound data structure like arrays or objects.', 'correct' => false],
                    ['text' => 'It aggregates validation errors from multiple entities.', 'correct' => false],
                    ['text' => 'It creates a conditional constraint that applies only when all sub-constraints pass.', 'correct' => false],
                ],
            ],

            // QUESTION 121 - AtLeastOneOf constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'What does the AtLeastOneOf constraint do?
<pre><code class="language-php">use Symfony\Component\Validator\Constraints as Assert;

class Payment
{
    #[Assert\AtLeastOneOf([
        new Assert\Regex(\'/^FR/\'),
        new Assert\Regex(\'/^DE/\'),
        new Assert\Regex(\'/^ES/\'),
    ])]
    public string $iban;
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The AtLeastOneOf constraint validates that at least one of the specified constraints passes. The value is considered valid if ANY ONE of the sub-constraints is satisfied. Here, the IBAN must start with FR, DE, or ES.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/AtLeastOneOf.html',
                'answers' => [
                    ['text' => 'The value is valid if at least one of the nested constraints passes.', 'correct' => true],
                    ['text' => 'The value is valid only if ALL constraints pass.', 'correct' => false],
                    ['text' => 'It validates that the property has at least one value in an array.', 'correct' => false],
                    ['text' => 'It checks that at least one field in the object is not blank.', 'correct' => false],
                ],
            ],

            // QUESTION 122 - #[Assert\When] conditional constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Consider the following code:
<pre><code class="language-php">use Symfony\Component\Validator\Constraints as Assert;

class Delivery
{
    public bool $isExpress = false;

    #[Assert\When(
        expression: \'this.isExpress == true\',
        constraints: [new Assert\NotBlank(), new Assert\Length(min: 10)],
    )]
    public ?string $trackingNumber = null;
}</code></pre>

When is the NotBlank + Length validation applied to $trackingNumber?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[Assert\\When] constraint conditionally applies other constraints based on an expression. Here, the $trackingNumber is only validated (NotBlank + Length) when $isExpress is true. If isExpress is false, no validation is applied to trackingNumber.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/When.html',
                'answers' => [
                    ['text' => 'Only when isExpress is true — the constraints are conditionally applied based on the expression.', 'correct' => true],
                    ['text' => 'Always — the When attribute only changes the error message.', 'correct' => false],
                    ['text' => 'Never — conditional constraints require a custom validator.', 'correct' => false],
                    ['text' => 'Only during form submission, not during programmatic validation.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 11: SYMFONY 8 - CONSOLE
            // =====================================================

            // QUESTION 123 - #[AsCommand] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Consider the following Symfony console command:
<pre><code class="language-php">use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(
    name: \'app:create-user\',
    description: \'Creates a new user\',
    hidden: true,
    aliases: [\'app:add-user\'],
)]
class CreateUserCommand extends Command
{
    // ...
}</code></pre>

Which of the following statements are correct about the #[AsCommand] attribute?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[AsCommand] attribute configures all command metadata. \'hidden: true\' hides it from bin/console list. \'aliases\' defines alternative command names. The description is shown in the command list and help output.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html',
                'answers' => [
                    ['text' => 'The command will not appear in php bin/console list because hidden is true.', 'correct' => true],
                    ['text' => 'The command can be executed as php bin/console app:add-user.', 'correct' => true],
                    ['text' => 'description is displayed in the command list and in the --help output.', 'correct' => true],
                    ['text' => '#[AsCommand] can only set the name, not description or aliases.', 'correct' => false],
                ],
            ],

            // QUESTION 124 - Console command input option modes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'In Symfony Console, what is the difference between InputOption::VALUE_REQUIRED and InputOption::VALUE_OPTIONAL?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'VALUE_REQUIRED means the option MUST have a value when the option is provided (e.g., --format=json). VALUE_OPTIONAL means the option can be provided with or without a value (e.g., --verbose or --verbose=detailed). If VALUE_OPTIONAL is used without a value, it gets the fallback value.',
                'resourceUrl' => 'https://symfony.com/doc/current/console/input.html#using-command-options',
                'answers' => [
                    ['text' => 'VALUE_REQUIRED: option must have a value when used; VALUE_OPTIONAL: option can be used with or without a value.', 'correct' => true],
                    ['text' => 'VALUE_REQUIRED: the option itself is required; VALUE_OPTIONAL: the option is optional.', 'correct' => false],
                    ['text' => 'VALUE_REQUIRED is for integers; VALUE_OPTIONAL is for strings.', 'correct' => false],
                    ['text' => 'There is no difference — they behave identically.', 'correct' => false],
                ],
            ],

            // QUESTION 125 - SymfonyStyle helper
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Which of the following methods are provided by SymfonyStyle for formatted console output?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'SymfonyStyle provides many formatting helpers: title() for command titles, section() for sections, success() for success messages, error() for error blocks, warning() for warnings, table() for tables, progressStart()/progressAdvance()/progressFinish() for progress bars, ask() for questions, and more.',
                'resourceUrl' => 'https://symfony.com/doc/current/console/style.html',
                'answers' => [
                    ['text' => '$io->success(\'Task completed!\')', 'correct' => true],
                    ['text' => '$io->error(\'Something went wrong!\')', 'correct' => true],
                    ['text' => '$io->table([\'Name\', \'Age\'], $rows)', 'correct' => true],
                    ['text' => '$io->progressStart(100)', 'correct' => true],
                    ['text' => '$io->html(\'<b>Bold text</b>\')', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 12: SYMFONY 8 - ROUTING & ARCHITECTURE
            // =====================================================

            // QUESTION 126 - Routing host/domain matching
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'In Symfony Routing, how can you restrict a route to match only a specific domain or subdomain?
<pre><code class="language-php">#[Route(\'/api/users\', host: \'api.example.com\')]
public function listUsers(): Response { /* ... */ }

#[Route(\'/\', host: \'{subdomain}.example.com\', defaults: [\'subdomain\' => \'www\'])]
public function homepage(string $subdomain): Response { /* ... */ }</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony routing supports host matching using the host option in the #[Route] attribute. You can specify a fixed host or use placeholders like {subdomain}. The route will only match when the request host matches the pattern. Defaults and requirements can also be applied to host placeholders.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#sub-domain-routing',
                'answers' => [
                    ['text' => 'Use the host option in #[Route] — it supports fixed hosts and placeholders like {subdomain}.example.com.', 'correct' => true],
                    ['text' => 'Domain restrictions can only be configured at the web server (Apache/Nginx) level, not in Symfony.', 'correct' => false],
                    ['text' => 'Use a kernel.request event listener to filter requests by host before routing.', 'correct' => false],
                    ['text' => 'Domain matching is not supported — you must use separate Symfony applications per domain.', 'correct' => false],
                ],
            ],

            // QUESTION 127 - Symfony Architecture: PSR interoperability
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'Which PSR (PHP Standards Recommendation) standards does Symfony implement or support?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony implements or provides adapters for several PSRs: PSR-3 (Logger Interface), PSR-4 (Autoloading), PSR-6 and PSR-16 (Cache), PSR-7 (HTTP Messages via the PSR-7 Bridge), PSR-11 (Container Interface), PSR-14 (Event Dispatcher), PSR-17 (HTTP Factories), PSR-18 (HTTP Client). PSR-5 (PHPDoc Standard) has never been accepted and is not implemented.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/psr7.html',
                'answers' => [
                    ['text' => 'PSR-4 (Autoloading)', 'correct' => true],
                    ['text' => 'PSR-6 and PSR-16 (Cache Interfaces)', 'correct' => true],
                    ['text' => 'PSR-11 (Container Interface)', 'correct' => true],
                    ['text' => 'PSR-18 (HTTP Client)', 'correct' => true],
                    ['text' => 'PSR-5 (PHPDoc Standard)', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 13: SYMFONY 8 - TESTING
            // =====================================================

            // QUESTION 128 - WebTestCase vs KernelTestCase
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Testing'],
                'text' => 'What is the difference between KernelTestCase and WebTestCase in Symfony testing?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'KernelTestCase boots the kernel and provides access to the service container, for integration-level tests. WebTestCase extends KernelTestCase and adds an HTTP client (createClient()) for simulating full HTTP requests, for functional/application-level tests.',
                'resourceUrl' => 'https://symfony.com/doc/current/testing.html',
                'answers' => [
                    ['text' => 'KernelTestCase boots the kernel for service-level tests; WebTestCase adds an HTTP client for full request simulation.', 'correct' => true],
                    ['text' => 'KernelTestCase is for unit tests; WebTestCase is for integration tests.', 'correct' => false],
                    ['text' => 'WebTestCase uses a real HTTP server; KernelTestCase uses mocks.', 'correct' => false],
                    ['text' => 'They are identical — WebTestCase is an alias for KernelTestCase.', 'correct' => false],
                ],
            ],

            // QUESTION 129 - assertResponseIsSuccessful
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Testing'],
                'text' => 'Consider the following functional test:
<pre><code class="language-php">use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomePage(): void
    {
        $client = static::createClient();
        $client->request(\'GET\', \'/\');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(\'h1\', \'Welcome\');
    }
}</code></pre>

What does assertResponseIsSuccessful() check?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'assertResponseIsSuccessful() checks that the HTTP response status code is in the 2xx range (200-299). It does NOT check for a specific status code like 200, just that it\'s a successful response.',
                'resourceUrl' => 'https://symfony.com/doc/current/testing.html#testing-application-assertions',
                'answers' => [
                    ['text' => 'It checks that the response status code is in the 2xx range (200-299).', 'correct' => true],
                    ['text' => 'It checks that the response status code is exactly 200.', 'correct' => false],
                    ['text' => 'It checks that the response body is not empty.', 'correct' => false],
                    ['text' => 'It checks that no exceptions were thrown during the request.', 'correct' => false],
                ],
            ],

            // QUESTION 130 - loginUser in functional tests
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Testing'],
                'text' => 'How can you simulate an authenticated user in a Symfony functional test?
<pre><code class="language-php">class AdminControllerTest extends WebTestCase
{
    public function testAdminDashboard(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy([\'email\' => \'admin@example.com\']);

        $client->loginUser($user);
        $client->request(\'GET\', \'/admin/dashboard\');

        $this->assertResponseIsSuccessful();
    }
}</code></pre>

What does $client->loginUser($user) do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The loginUser() method simulates authentication by creating a security token for the given user and setting it in the session. It does NOT send a login form request. The user is immediately authenticated for subsequent requests made by the client.',
                'resourceUrl' => 'https://symfony.com/doc/current/testing.html#logging-in-users-authentication',
                'answers' => [
                    ['text' => 'It simulates authentication by creating a token in the session — no login form submission needed.', 'correct' => true],
                    ['text' => 'It sends a POST request to the /login route with the user\'s credentials.', 'correct' => false],
                    ['text' => 'It bypasses the entire security system for all subsequent requests.', 'correct' => false],
                    ['text' => 'It only works with the in-memory user provider.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 14: SYMFONY 8 - MISCELLANEOUS COMPONENTS (INCLUDED IN CERTIFICATION)
            // =====================================================

            // QUESTION 131 - Clock component
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'What is the purpose of the Symfony Clock component?
<pre><code class="language-php">use Symfony\Component\Clock\ClockInterface;

class SubscriptionService
{
    public function __construct(private ClockInterface $clock) {}

    public function isExpired(\DateTimeImmutable $expiresAt): bool
    {
        return $this->clock->now() > $expiresAt;
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Clock component provides an abstraction over time-related functions. By type-hinting ClockInterface, you can inject a mock clock in tests (MockClock) to control time without waiting. In production, NativeClock is used. This makes time-dependent code testable.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/clock.html',
                'answers' => [
                    ['text' => 'It abstracts time so you can use a mock clock in tests, making time-dependent code testable.', 'correct' => true],
                    ['text' => 'It provides high-performance timers for benchmarking.', 'correct' => false],
                    ['text' => 'It synchronizes time across multiple servers in a cluster.', 'correct' => false],
                    ['text' => 'It replaces PHP\'s native DateTime classes.', 'correct' => false],
                ],
            ],

            // QUESTION 132 - Finder component
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Finder'],
                'text' => 'What does the Symfony Finder component provide?
<pre><code class="language-php">use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder->files()
    ->in(\'/path/to/project/src\')
    ->name(\'*.php\')
    ->size(\'>= 1K\')
    ->date(\'since yesterday\');

foreach ($finder as $file) {
    echo $file->getRealPath();
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Finder component provides a fluent interface for finding files and directories in the filesystem. It supports filtering by name (glob/regex), size, date, content, path depth, and custom criteria. Results are lazy-loaded as iterators. It is used internally by many Symfony components.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/finder.html',
                'answers' => [
                    ['text' => 'A fluent API to find files/directories with filters for name, size, date, content, and depth.', 'correct' => true],
                    ['text' => 'A search engine for indexing and querying Symfony services in the container.', 'correct' => false],
                    ['text' => 'A tool for finding and resolving class dependencies for autoloading.', 'correct' => false],
                    ['text' => 'A database query builder for locating records.', 'correct' => false],
                ],
            ],

            // QUESTION 133 - PropertyAccess component
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'What does the Symfony PropertyAccess component provide?
<pre><code class="language-php">use Symfony\Component\PropertyAccess\PropertyAccess;

$accessor = PropertyAccess::createPropertyAccessor();

$person = new Person();
$accessor->setValue($person, \'address.city\', \'Paris\');
$city = $accessor->getValue($person, \'address.city\');

$data = [\'user\' => [\'name\' => \'Alice\']];
$name = $accessor->getValue($data, \'[user][name]\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The PropertyAccess component provides a unified API to read/write values from/to objects and arrays using string path notation. It supports object property paths ("address.city"), array access ("[key]"), and uses getters/setters/issers. It is used internally by the Form, Serializer, and Validator components.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/property_access.html',
                'answers' => [
                    ['text' => 'A unified API to read/write object properties and array values using string path notation (e.g., \'address.city\', \'[key]\').', 'correct' => true],
                    ['text' => 'A way to define property-level access control (public/private/protected) at runtime.', 'correct' => false],
                    ['text' => 'An ORM for mapping PHP properties to database columns.', 'correct' => false],
                    ['text' => 'A typecasting utility for converting property values between types.', 'correct' => false],
                ],
            ],

            // QUESTION 134 - Expression Language
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'In which Symfony contexts can you use Expression Language expressions?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Expression Language is used in many Symfony contexts: security (access_control, #[IsGranted], voters), service container configuration (e.g., @=service("...") expressions), validation constraints (#[Assert\\Expression]), and routing (condition parameter). It is NOT used for Twig template rendering (Twig has its own syntax).',
                'resourceUrl' => 'https://symfony.com/doc/current/components/expression_language.html',
                'answers' => [
                    ['text' => 'Security (access_control, #[IsGranted] with expressions)', 'correct' => true],
                    ['text' => 'Service container configuration (expression-based arguments)', 'correct' => true],
                    ['text' => 'Validation constraints (#[Assert\\Expression])', 'correct' => true],
                    ['text' => 'Routing conditions', 'correct' => true],
                    ['text' => 'Twig template rendering', 'correct' => false],
                ],
            ],

            // QUESTION 135 - Process component
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Process'],
                'text' => 'What is the Symfony Process component used for?
<pre><code class="language-php">use Symfony\Component\Process\Process;

$process = new Process([\'git\', \'log\', \'--oneline\', \'-5\']);
$process->run();

if (!$process->isSuccessful()) {
    throw new \RuntimeException($process->getErrorOutput());
}

echo $process->getOutput();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Process component allows executing system commands in sub-processes. It provides: timeout management, real-time output streaming (via callbacks), asynchronous process execution ($process->start()), environment variable passing, input/output handling, and signal management. It is a secure wrapper around proc_open().',
                'resourceUrl' => 'https://symfony.com/doc/current/components/process.html',
                'answers' => [
                    ['text' => 'It executes system commands in sub-processes with timeout management, real-time output, and async support.', 'correct' => true],
                    ['text' => 'It manages PHP threads for parallel computing.', 'correct' => false],
                    ['text' => 'It processes queue messages in Symfony Messenger.', 'correct' => false],
                    ['text' => 'It provides a process manager like Supervisord for production deployments.', 'correct' => false],
                ],
            ],

            // QUESTION 136 - Mailer with #[AsEventListener]
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mailer'],
                'text' => 'Which event can you listen to in Symfony Mailer to modify an email just before it is sent?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The MessageEvent is dispatched just before the email is sent by the transport. You can listen to this event to modify the message (add headers, change recipients, etc.) or even prevent the email from being sent. SentMessageEvent is dispatched AFTER the email is sent.',
                'resourceUrl' => 'https://symfony.com/doc/current/mailer.html#mailer-events',
                'answers' => [
                    ['text' => 'Symfony\\Component\\Mailer\\Event\\MessageEvent', 'correct' => true],
                    ['text' => 'Symfony\\Component\\Mailer\\Event\\SentMessageEvent', 'correct' => false],
                    ['text' => 'Symfony\\Component\\Mailer\\Event\\BeforeSendEvent', 'correct' => false],
                    ['text' => 'kernel.mail', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 15: SYMFONY 8 - HTTPKERNEL & HTTPFOUNDATION
            // =====================================================

            // QUESTION 137 - Kernel events order
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the correct order of HttpKernel events during a successful request handling in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The Symfony HttpKernel dispatches events in this order: kernel.request → kernel.controller → kernel.controller_arguments → kernel.view (if controller doesn\'t return Response) → kernel.response → kernel.finish_request → kernel.terminate. kernel.exception is only dispatched if an exception occurs.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/events.html',
                'answers' => [
                    ['text' => 'request → controller → controller_arguments → response → finish_request → terminate', 'correct' => true],
                    ['text' => 'request → response → controller → terminate', 'correct' => false],
                    ['text' => 'controller → request → response → terminate', 'correct' => false],
                    ['text' => 'request → controller → view → exception → terminate', 'correct' => false],
                ],
            ],

            // QUESTION 138 - kernel.view event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'When is the kernel.view event dispatched?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The kernel.view event is dispatched only when the controller does NOT return a Response object. A view listener can then convert the controller return value (e.g., an array, an object, a template name) into a proper Response. If the controller returns a Response, kernel.view is skipped.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/events.html#kernel-view',
                'answers' => [
                    ['text' => 'Only when the controller returns something other than a Response object.', 'correct' => true],
                    ['text' => 'After every controller execution, regardless of the return value.', 'correct' => false],
                    ['text' => 'Before the controller is called, to prepare the view.', 'correct' => false],
                    ['text' => 'Only when a Twig template is rendered.', 'correct' => false],
                ],
            ],

            // QUESTION 139 - JsonResponse helper
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Controllers'],
                'text' => 'In a Symfony controller extending AbstractController, which method creates a JSON response?
<pre><code class="language-php">class ApiController extends AbstractController
{
    #[Route(\'/api/data\')]
    public function getData(): JsonResponse
    {
        return $this->json([\'name\' => \'Alice\', \'age\' => 30]);
    }
}</code></pre>

What does $this->json() do internally?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The json() method in AbstractController creates a JsonResponse. If the Serializer component is available, it uses the Serializer for encoding (supporting serialization groups, normalizers, etc.). Otherwise, it falls back to json_encode(). The Content-Type header is automatically set to application/json.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller.html#returning-json-response',
                'answers' => [
                    ['text' => 'It creates a JsonResponse using the Serializer (if available) or json_encode(), with Content-Type: application/json.', 'correct' => true],
                    ['text' => 'It always uses json_encode() and never the Serializer.', 'correct' => false],
                    ['text' => 'It renders a Twig template that outputs JSON.', 'correct' => false],
                    ['text' => 'It returns a raw string response without any encoding.', 'correct' => false],
                ],
            ],

            // QUESTION 140 - #[ValueResolver] attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the purpose of Value Resolvers in Symfony HttpKernel?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Value Resolvers (formerly ArgumentValueResolvers) determine how controller arguments are resolved from the request. Built-in resolvers handle Request, Session, security User, route parameters, DTO mapping (#[MapRequestPayload], #[MapQueryParameter]), etc. Custom resolvers can be created for specific argument types.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller/value_resolver.html',
                'answers' => [
                    ['text' => 'They determine how controller method arguments are resolved from the request context.', 'correct' => true],
                    ['text' => 'They validate and sanitize user input before it reaches the controller.', 'correct' => false],
                    ['text' => 'They resolve configuration parameter values at compile time.', 'correct' => false],
                    ['text' => 'They convert return values from controllers into HTTP responses.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 16: SYMFONY 8 - CONFIGURATION & ENV
            // =====================================================

            // QUESTION 141 - Environment variable processors
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'Which of the following are valid Symfony environment variable processors?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony provides many env var processors: bool (cast to boolean), int (cast to integer), float (cast to float), json (JSON decode), csv (parse CSV), file (read file content), base64 (base64 decode), url (parse URL), key (get array key). "yaml" is NOT a built-in processor.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration/env_var_processors.html',
                'answers' => [
                    ['text' => '%env(bool:MY_VAR)%', 'correct' => true],
                    ['text' => '%env(json:MY_VAR)%', 'correct' => true],
                    ['text' => '%env(file:MY_VAR)%', 'correct' => true],
                    ['text' => '%env(url:MY_VAR)%', 'correct' => true],
                    ['text' => '%env(yaml:MY_VAR)%', 'correct' => false],
                ],
            ],

            // QUESTION 142 - Secrets management
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'What is the purpose of Symfony\'s secrets management system?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony\'s secrets vault provides a secure way to store sensitive environment variables (API keys, database passwords, etc.) encrypted in the repository. Secrets are stored per-environment and can be committed to version control. Only the decryption key must be kept out of the repository.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration/secrets.html',
                'answers' => [
                    ['text' => 'It encrypts sensitive env vars so they can be safely committed to version control, with only the decrypt key kept secret.', 'correct' => true],
                    ['text' => 'It stores secret values in an external cloud key management service.', 'correct' => false],
                    ['text' => 'It automatically rotates database passwords on each deployment.', 'correct' => false],
                    ['text' => 'It provides two-factor authentication for the Symfony profiler.', 'correct' => false],
                ],
            ],

            // QUESTION 143 - secrets:set command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'Which command is used to add a secret to Symfony\'s secrets vault?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The secrets:set command stores an encrypted secret in the vault. It can also read the value from STDIN for piping. The secret name conventionally follows the ENV_VAR naming pattern.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration/secrets.html#adding-secrets',
                'answers' => [
                    ['text' => 'php bin/console secrets:set SECRET_NAME', 'correct' => true],
                    ['text' => 'php bin/console vault:add SECRET_NAME', 'correct' => false],
                    ['text' => 'php bin/console config:secrets:add SECRET_NAME', 'correct' => false],
                    ['text' => 'php bin/console env:encrypt SECRET_NAME', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 17: SYMFONY 8 - TRANSLATION
            // =====================================================

            // QUESTION 144 - Translation ICU MessageFormat
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'Symfony supports the ICU MessageFormat for translations. What special feature does the +intl-icu file suffix enable?
<pre><code class="language-text"># translations/messages+intl-icu.en.yaml
invitation: >-
    {count, plural,
        one {You have # invitation}
        other {You have # invitations}
    }</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The +intl-icu suffix tells Symfony to parse translations using the ICU MessageFormat instead of the default Symfony format. This enables advanced features like pluralization rules, number/date formatting, and select expressions, all following the international ICU standard.',
                'resourceUrl' => 'https://symfony.com/doc/current/translation/message_format.html',
                'answers' => [
                    ['text' => 'It enables ICU MessageFormat parsing for advanced pluralization, number formatting, and select expressions.', 'correct' => true],
                    ['text' => 'It adds automatic translation caching for improved performance.', 'correct' => false],
                    ['text' => 'It enables right-to-left (RTL) language support.', 'correct' => false],
                    ['text' => 'It generates TypeScript interfaces for translation keys.', 'correct' => false],
                ],
            ],

            // QUESTION 145 - Translation fallback locale
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'Consider the following translation configuration:
<pre><code class="language-yaml"># config/packages/translation.yaml
framework:
    default_locale: fr
    translator:
        fallbacks: [\'en\']</code></pre>

If a translation key is requested in \'fr\' locale but is only defined in the \'en\' translation file, what happens?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When a translation is not found in the requested locale (fr), Symfony falls back to the locales defined in "fallbacks" in order. Here, it will use the English translation. If the key is not found in any fallback either, the translation key itself is returned.',
                'resourceUrl' => 'https://symfony.com/doc/current/translation.html#configuration',
                'answers' => [
                    ['text' => 'Symfony uses the \'en\' translation as a fallback.', 'correct' => true],
                    ['text' => 'An exception is thrown because the translation is missing.', 'correct' => false],
                    ['text' => 'An empty string is returned.', 'correct' => false],
                    ['text' => 'The default_locale setting is used, so the translation key itself is displayed.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 18: SYMFONY 8 - ADVANCED PHP 8.x PATTERNS
            // =====================================================

            // QUESTION 146 - PHP 8.1 enum implements interface
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Interfaces & Traits'],
                'text' => 'Can PHP 8.1 enums implement interfaces?
<pre><code class="language-php">interface HasColor
{
    public function color(): string;
}

enum Suit: string implements HasColor
{
    case Hearts = \'H\';
    case Diamonds = \'D\';
    case Clubs = \'C\';
    case Spades = \'S\';

    public function color(): string
    {
        return match($this) {
            self::Hearts, self::Diamonds => \'red\',
            self::Clubs, self::Spades => \'black\',
        };
    }
}</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, PHP 8.1 enums can implement interfaces. They can have methods and satisfy interface contracts. However, enums cannot extend classes. They can also use traits (as long as the trait doesn\'t define properties).',
                'resourceUrl' => 'https://www.php.net/manual/en/language.enumerations.interfaces.php',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // QUESTION 147 - PHP 8.2 #[\SensitiveParameter]
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is the purpose of the #[\SensitiveParameter] attribute in PHP 8.2?
<pre><code class="language-php">function authenticate(
    string $username,
    #[\SensitiveParameter] string $password,
): bool {
    throw new \RuntimeException(\'Auth failed\');
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The #[\SensitiveParameter] attribute prevents the parameter value from appearing in stack traces and error reports. In the example, if an exception is thrown, the $password value will be replaced with "Object(SensitiveParameterValue)" in the trace instead of showing the actual password.',
                'resourceUrl' => 'https://www.php.net/manual/en/class.sensitiveparameter.php',
                'answers' => [
                    ['text' => 'It hides the parameter value from stack traces and error reports, replacing it with a placeholder.', 'correct' => true],
                    ['text' => 'It encrypts the parameter value at rest in memory.', 'correct' => false],
                    ['text' => 'It prevents the parameter from being logged by any logging framework.', 'correct' => false],
                    ['text' => 'It makes the parameter immutable (readonly) within the function body.', 'correct' => false],
                ],
            ],

            // QUESTION 148 - PHP 8.3 Dynamic class constant fetch
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'Is the following PHP 8.3 code valid?
<pre><code class="language-php">class Config
{
    public const DB_HOST = \'localhost\';
    public const DB_PORT = \'3306\';
}

$name = \'DB_HOST\';
echo Config::{$name};</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'PHP 8.3 introduced dynamic class constant fetch. You can now use a variable expression enclosed in {} to dynamically access class constants: ClassName::{$variable}. This prints \'localhost\'. Before PHP 8.3, you had to use constant(Config::class . \'::$name\').',
                'resourceUrl' => 'https://wiki.php.net/rfc/dynamic_class_constant_fetch',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // QUESTION 149 - PHP 8.4 Property hooks
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'PHP 8.4 introduced property hooks. What do they allow?
<pre><code class="language-php">class User
{
    public string $fullName {
        get => $this->firstName . \' \' . $this->lastName;
        set (string $value) {
            [$this->firstName, $this->lastName] = explode(\' \', $value, 2);
        }
    }

    public function __construct(
        private string $firstName,
        private string $lastName,
    ) {}
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'PHP 8.4 property hooks allow defining custom get and set behavior directly on properties, replacing the need for explicit getter/setter methods. The get hook is called when reading the property, and the set hook is called when writing to it. This is similar to C# properties or Kotlin computed properties.',
                'resourceUrl' => 'https://wiki.php.net/rfc/property-hooks',
                'answers' => [
                    ['text' => 'They define custom get/set logic directly on properties, replacing explicit getter/setter methods.', 'correct' => true],
                    ['text' => 'They add event listeners that fire when a property value changes.', 'correct' => false],
                    ['text' => 'They enable lazy loading of property values from a database.', 'correct' => false],
                    ['text' => 'They provide validation constraints directly in property declarations.', 'correct' => false],
                ],
            ],

            // QUESTION 150 - PHP 8.4 Asymmetric visibility
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:OOP'],
                'text' => 'PHP 8.4 introduced asymmetric visibility for properties. What does the following declaration mean?
<pre><code class="language-php">class Product
{
    public function __construct(
        public private(set) string $name,
        public protected(set) float $price,
    ) {}
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Asymmetric visibility allows different visibility for reading and writing a property. "public private(set)" means: readable from anywhere (public), but writable only from within the class (private). "public protected(set)" means: readable from anywhere, but writable only from within the class and its children.',
                'resourceUrl' => 'https://wiki.php.net/rfc/asymmetric-visibility-v2',
                'answers' => [
                    ['text' => '$name is publicly readable but only writable within the class; $price is publicly readable but only writable within the class and subclasses.', 'correct' => true],
                    ['text' => 'Both properties are fully public for both reading and writing.', 'correct' => false],
                    ['text' => '$name is private and $price is protected — the "public" keyword is ignored.', 'correct' => false],
                    ['text' => 'This syntax is invalid in PHP 8.4.', 'correct' => false],
                ],
            ],
        ];
    }
}
