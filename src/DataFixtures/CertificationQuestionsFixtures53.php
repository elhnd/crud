<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 53
 * Symfony 7.4 / 8.0 new features - Part 9
 * Topics: Attribute Improvements, Request Class Improvements
 */
class CertificationQuestionsFixtures53 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures52::class];
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
            // ── Q1: #[CurrentUser] Union Types ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, how can the <code>#[CurrentUser]</code> attribute handle multiple user types?</p>',
                'answers' => [
                    ['text' => 'By using a union type in the controller argument, e.g. <code>#[CurrentUser] AdminUser|Customer $user</code>', 'correct' => true],
                    ['text' => 'By passing an array of classes to the attribute: <code>#[CurrentUser([AdminUser::class, Customer::class])]</code>', 'correct' => false],
                    ['text' => 'By creating a separate controller method for each user type', 'correct' => false],
                    ['text' => 'Union types are not supported with <code>#[CurrentUser]</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 improves #[CurrentUser] to support PHP union types. The controller argument can type-hint multiple user classes, and Symfony injects the matching authenticated user.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q2: #[Route] Multiple Environments ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Routing'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>What improvement was made to the <code>env</code> option of the <code>#[Route]</code> attribute in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'It now accepts an array of environments, e.g. <code>env: [\'dev\', \'test\']</code>', 'correct' => true],
                    ['text' => 'It supports regex patterns for matching environments', 'correct' => false],
                    ['text' => 'It was renamed from <code>env</code> to <code>environments</code>', 'correct' => false],
                    ['text' => 'It was removed in favor of <code>#[When]</code> attribute', 'correct' => false],
                ],
                'explanation' => 'The #[Route] env option previously only accepted a single string. In Symfony 7.4, it accepts an array for multi-environment routing, e.g. env: [\'dev\', \'test\'].',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q3: Repeatable #[AsDecorator] ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Dependency Injection'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In Symfony 7.4, the <code>#[AsDecorator]</code> attribute became repeatable. What does this enable?</p>',
                'answers' => [
                    ['text' => 'A single class can decorate multiple services by applying <code>#[AsDecorator]</code> multiple times with different service IDs', 'correct' => true],
                    ['text' => 'Multiple decorators can be applied to a single service with stacking', 'correct' => false],
                    ['text' => 'The same decorator can be applied at class and method level', 'correct' => false],
                    ['text' => 'Decorators can be conditionally applied based on environment', 'correct' => false],
                ],
                'explanation' => 'Making #[AsDecorator] repeatable allows one class to decorate multiple services. For example, a logging decorator can be applied to api1.client, api2.client, etc.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q4: #[AsEventListener] Union Types ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, event listener methods using <code>#[AsEventListener]</code> can type-hint union types (e.g., <code>CustomEvent|AnotherEvent $event</code>) and Symfony will register the listener for all specified event types.</p>',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 supports union types in #[AsEventListener] method signatures. When you type-hint multiple events, the listener is registered for each event type.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q5: #[IsGranted] Methods Option ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>What new option was added to the <code>#[IsGranted]</code> attribute in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => '<code>methods</code> — restricts the security check to specific HTTP methods only', 'correct' => true],
                    ['text' => '<code>env</code> — restricts the check to specific environments', 'correct' => false],
                    ['text' => '<code>priority</code> — sets the order of multiple security checks', 'correct' => false],
                    ['text' => '<code>lazy</code> — defers the check until the response is generated', 'correct' => false],
                ],
                'explanation' => 'The methods option allows #[IsGranted] to only check access for specific HTTP methods. E.g., #[IsGranted(\'ROLE_ADMIN\', methods: [\'POST\'])] only enforces the check on POST requests.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q6: Route Auto Registration ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Routing'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>How does route attribute auto-registration work in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'Symfony applies a <code>routing.controller</code> service tag to any class with <code>#[Route]</code>, and a compiler pass automatically registers their routes', 'correct' => true],
                    ['text' => 'All PHP files in the project are scanned for route annotations at runtime', 'correct' => false],
                    ['text' => 'A console command generates a routes cache from all <code>#[Route]</code> attributes', 'correct' => false],
                    ['text' => 'Routes must be explicitly imported using a new <code>routing:import</code> command', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 auto-tags classes with #[Route] using routing.controller tag. A compiler pass collects them and registers routes automatically, simplifying config/routes.yaml to: resource: routing.controllers.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q7: Route Auto Registration – Config ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Routing'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>With the new route auto-registration in Symfony 7.4, how can you simplify <code>config/routes.yaml</code>?</p>',
                'answers' => [
                    ['text' => 'Replace the path/namespace/type configuration with: <code>resource: routing.controllers</code>', 'correct' => true],
                    ['text' => 'Remove <code>config/routes.yaml</code> entirely', 'correct' => false],
                    ['text' => 'Use: <code>auto_discover: true</code>', 'correct' => false],
                    ['text' => 'Use: <code>scan_attributes: true</code>', 'correct' => false],
                ],
                'explanation' => 'The traditional resource/path/namespace/type config block can be simplified to "controllers: { resource: routing.controllers }" since Symfony auto-discovers all #[Route] attributes.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q8: #[IsSignatureValid] ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>Which statements about the new <code>#[IsSignatureValid]</code> attribute in Symfony 7.4 are true? (Select all that apply)</p>',
                'answers' => [
                    ['text' => 'It automatically validates URI signatures before the controller action runs', 'correct' => true],
                    ['text' => 'It can be applied at both class level and method level', 'correct' => true],
                    ['text' => 'It supports a <code>methods</code> option to restrict validation to specific HTTP methods', 'correct' => true],
                    ['text' => 'It requires manually calling <code>UriSigner</code> to verify the signature', 'correct' => false],
                ],
                'explanation' => '#[IsSignatureValid] automatically verifies URI signatures. It works at class (all methods) or method level, and optionally for specific HTTP methods via the methods option.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q9: Request::get() Deprecation ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>The <code>Request::get()</code> method is deprecated in Symfony 7.4. What should developers use instead?</p>',
                'answers' => [
                    ['text' => 'Use <code>$request->attributes->get()</code>, <code>$request->query->get()</code>, or <code>$request->request->get()</code> depending on the parameter source', 'correct' => true],
                    ['text' => 'Use <code>$request->getParameter()</code> which replaces <code>get()</code>', 'correct' => false],
                    ['text' => 'Use <code>$request->input()</code> as a universal accessor', 'correct' => false],
                    ['text' => 'Use the <code>#[MapQueryParameter]</code> attribute exclusively', 'correct' => false],
                ],
                'explanation' => 'Request::get() checked route attributes, GET, and POST in order, which was ambiguous. Use the specific property bag (attributes, query, or request) instead.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-request-class-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q10: Request Body Parsing for Non-POST ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>Thanks to PHP 8.4\'s <code>request_parse_body()</code> function, Symfony 7.4 now parses the request body for which HTTP methods? (Select all that apply)</p>',
                'answers' => [
                    ['text' => '<code>PUT</code>', 'correct' => true],
                    ['text' => '<code>PATCH</code>', 'correct' => true],
                    ['text' => '<code>DELETE</code>', 'correct' => true],
                    ['text' => '<code>GET</code>', 'correct' => false],
                ],
                'explanation' => 'Previously only POST body was parsed. With PHP 8.4\'s request_parse_body(), Symfony 7.4 now also parses the body for PUT, PATCH, DELETE, and QUERY requests.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-request-class-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q11: HTTP Method Override Deprecation ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In Symfony 7.4, overriding which HTTP methods is deprecated for safety reasons? (Select all that apply)</p>',
                'answers' => [
                    ['text' => '<code>GET</code>', 'correct' => true],
                    ['text' => '<code>HEAD</code>', 'correct' => true],
                    ['text' => '<code>CONNECT</code>', 'correct' => true],
                    ['text' => '<code>PUT</code>', 'correct' => false],
                ],
                'explanation' => 'Overriding GET, HEAD, CONNECT, and TRACE is now deprecated. PUT, PATCH, and DELETE remain the valid override targets. Use allowed_http_method_override config option.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-request-class-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q12: Allowed HTTP Method Override Config ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>How do you configure which HTTP methods can be overridden in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'Use <code>framework.allowed_http_method_override</code> in config (e.g. <code>[\'PUT\', \'DELETE\', \'PATCH\']</code>)', 'correct' => true],
                    ['text' => 'Use <code>framework.http_method_override: true/false</code> only', 'correct' => false],
                    ['text' => 'Set the <code>X-Allowed-Methods</code> response header', 'correct' => false],
                    ['text' => 'Override is always enabled for all methods in Symfony 7.4', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds framework.allowed_http_method_override config option and Request::setAllowedHttpMethodOverride() method for fine-grained control over method overrides.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-request-class-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q13: Request Body Parsing – PHP Function ──
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Which PHP 8.4 function enables Symfony 7.4 to parse <code>multipart/form-data</code> request bodies for non-POST HTTP methods?</p>',
                'answers' => [
                    ['text' => '<code>request_parse_body()</code>', 'correct' => true],
                    ['text' => '<code>parse_request()</code>', 'correct' => false],
                    ['text' => '<code>http_parse_body()</code>', 'correct' => false],
                    ['text' => '<code>multipart_decode()</code>', 'correct' => false],
                ],
                'explanation' => 'PHP 8.4 introduces request_parse_body() which parses multipart/form-data and x-www-form-urlencoded bodies for any HTTP method, overcoming PHP\'s previous POST-only limitation.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-request-class-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q14: Request::get() Order ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Before its deprecation, in what order did <code>Request::get()</code> search for parameters?</p>',
                'answers' => [
                    ['text' => 'Route attributes → GET query parameters → POST body parameters', 'correct' => true],
                    ['text' => 'POST body parameters → GET query parameters → Route attributes', 'correct' => false],
                    ['text' => 'GET query parameters → POST body parameters → Route attributes', 'correct' => false],
                    ['text' => 'It searched all sources simultaneously with no order', 'correct' => false],
                ],
                'explanation' => 'Request::get() checked route attributes first, then query string (GET), then request body (POST), returning the first match. This ambiguity led to its deprecation.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-request-class-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q15: #[Route] env Security Consideration ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Why is the <code>env</code> option of the <code>#[Route]</code> attribute particularly important for security?</p>',
                'answers' => [
                    ['text' => 'It prevents debug and development routes from being exposed in production, reducing the attack surface', 'correct' => true],
                    ['text' => 'It encrypts route parameters in non-prod environments', 'correct' => false],
                    ['text' => 'It automatically applies rate limiting in production', 'correct' => false],
                    ['text' => 'It enforces HTTPS only in production environment', 'correct' => false],
                ],
                'explanation' => 'The env option ensures that debug routes (like /_debug/mail-preview) are only available in the specified environments, preventing accidental exposure in production.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],
        ];
    }
}
