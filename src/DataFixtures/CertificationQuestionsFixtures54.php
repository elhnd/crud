<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 54
 * Symfony 7.4 / 8.0 new features - Part 10
 * Topics: Cross-cutting synthesis, upgrade strategy, advanced scenarios
 */
class CertificationQuestionsFixtures54 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures53::class];
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
            // ── Q1: Symfony 7.4 → 8.0 Relationship ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>What is the primary purpose of Symfony 7.4 in the release cycle leading to Symfony 8.0?</p>',
                'answers' => [
                    ['text' => 'It is the last minor release of the 7.x branch and contains all deprecation notices for features removed in 8.0', 'correct' => true],
                    ['text' => 'It is a beta version of Symfony 8.0 for early testing', 'correct' => false],
                    ['text' => 'It is a security-only maintenance release', 'correct' => false],
                    ['text' => 'It introduces new features that will not be available in 8.0', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 and 8.0 were released simultaneously. 7.4 includes all new features + deprecation notices, while 8.0 removes the deprecated code. Fixing all 7.4 deprecations = clean 8.0 migration.',
                'resourceUrl' => 'https://symfony.com/releases',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q2: PHP 8.4 + Symfony Synergy ──
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>Which PHP 8.4 features does Symfony 7.4 specifically leverage? (Select all that apply)</p>',
                'answers' => [
                    ['text' => 'Native HTML5 parser (<code>\Dom\HTMLDocument</code>) in <code>DomCrawler</code>', 'correct' => true],
                    ['text' => '<code>request_parse_body()</code> for non-POST body parsing', 'correct' => true],
                    ['text' => 'Property hooks for configuration objects', 'correct' => false],
                    ['text' => 'Lazy objects for proxy generation in the DI container', 'correct' => true],
                ],
                'explanation' => 'Symfony 7.4 uses PHP 8.4\'s HTML5 parser in DomCrawler, request_parse_body() for parsing PUT/PATCH/DELETE bodies, and lazy objects for DI container lazy service proxies.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q3: Message Signing + Security ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Messenger'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>How does Symfony 7.4\'s message signing in the Messenger component protect against tampering?</p>',
                'answers' => [
                    ['text' => 'It signs serialized messages with HMAC and verifies the signature on consumption, rejecting tampered messages', 'correct' => true],
                    ['text' => 'It encrypts the message payload with AES-256 before sending', 'correct' => false],
                    ['text' => 'It stores a hash in a separate database table for verification', 'correct' => false],
                    ['text' => 'It uses public-key cryptography with X.509 certificates', 'correct' => false],
                ],
                'explanation' => 'Message signing uses HMAC to create a signature of the serialized message. On consumption, the signature is verified to ensure the message has not been modified.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-signing-messages',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q4: Weighted Workflows + Enums ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>When using weighted transitions with enum-based places in Symfony 7.4 workflows, what determines which transition is displayed first in the UI?</p>',
                'answers' => [
                    ['text' => 'The output weight assigned to each transition — lower weight means higher priority', 'correct' => true],
                    ['text' => 'The order the transitions were defined in YAML', 'correct' => false],
                    ['text' => 'The enum case value in alphabetical order', 'correct' => false],
                    ['text' => 'Transitions are always displayed randomly', 'correct' => false],
                ],
                'explanation' => 'Weighted transitions use output_weight to determine display order. Combined with enum places (!php/enum syntax), this provides type-safe, ordered workflow configurations.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-weighted-workflow-transitions',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q5: Caching HTTP Client + RFC 9111 ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>The Symfony 7.4 Caching HTTP Client implements RFC 9111. Which of the following describes how it handles shared cache behavior?</p>',
                'answers' => [
                    ['text' => 'By default it operates as a shared cache, respecting <code>s-maxage</code> and <code>Cache-Control: public/private</code> directives', 'correct' => true],
                    ['text' => 'It always operates as a private cache since it runs in the application', 'correct' => false],
                    ['text' => 'Shared cache mode must be explicitly enabled via a configuration option', 'correct' => false],
                    ['text' => 'It ignores cache directives and uses a fixed TTL only', 'correct' => false],
                ],
                'explanation' => 'The Caching HTTP Client defaults to shared cache mode (like a CDN/reverse proxy), respecting s-maxage. Set shared: false for private cache behavior.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-caching-http-client',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q6: Security Voter Improvements ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>What new information can a Security Voter provide via the <code>Vote</code> object in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'Extra data via <code>$vote->setExtraData()</code>, accessible in <code>access_decision()</code> Twig function for detailed authorization feedback', 'correct' => true],
                    ['text' => 'The voter\'s execution time for profiling purposes', 'correct' => false],
                    ['text' => 'Custom HTTP headers to set on the response', 'correct' => false],
                    ['text' => 'Alternative roles that would have granted access', 'correct' => false],
                ],
                'explanation' => 'Voters can attach extra data to Vote objects. The access_decision() Twig function returns an AccessDecision object containing these votes with their extra data.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-security-voter-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q7: Combining Multiple New Features ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>A Symfony 7.4 application needs to: (1) decorate three HTTP clients with logging, (2) restrict the debug endpoint to dev/test, (3) validate a third-party DTO. Which combination of new attributes achieves this?</p>',
                'answers' => [
                    ['text' => '<code>#[AsDecorator]</code> (repeatable) + <code>#[Route(..., env: [\'dev\', \'test\'])]</code> + <code>#[ExtendsValidationFor]</code>', 'correct' => true],
                    ['text' => '<code>#[AsTaggedItem]</code> + <code>#[When(env: \'dev\')]</code> + <code>#[Assert\\Valid]</code>', 'correct' => false],
                    ['text' => '<code>#[Autoconfigure]</code> + <code>#[Route(..., condition: ...)]</code> + custom validator', 'correct' => false],
                    ['text' => '<code>#[AsDecorator]</code> + <code>#[IsGranted]</code> + XML validation config', 'correct' => false],
                ],
                'explanation' => 'Repeatable #[AsDecorator] decorates multiple clients, #[Route] env array limits environment access, and #[ExtendsValidationFor] adds constraints to external DTOs.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q8: FlowType + Validation Extension ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Forms'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In a multi-step form wizard built with Symfony 7.4\'s <code>AbstractFlowType</code>, how do you validate a third-party value object used as a form data class?</p>',
                'answers' => [
                    ['text' => 'Create an extension class with <code>#[ExtendsValidationFor(ThirdPartyDto::class)]</code> and use validation groups matching each flow step', 'correct' => true],
                    ['text' => 'Override the third-party class and add constraints directly', 'correct' => false],
                    ['text' => 'Validation of third-party classes is not possible in flow forms', 'correct' => false],
                    ['text' => 'Use a custom form event subscriber to manually validate', 'correct' => false],
                ],
                'explanation' => 'Combine #[ExtendsValidationFor] with validation groups per flow step. Since extension constraints are merged, groups control which constraints apply at each step.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-multi-step-forms',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q9: Config + DI Integration ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Dependency Injection'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In the new Symfony 7.4 PHP configuration format (<code>App::config</code>), how do you configure a <code>tagged_iterator</code> injection?</p>',
                'answers' => [
                    ['text' => 'Use array syntax with the same keys as YAML: <code>\'arguments\' => [\'$handlers\' => tagged_iterator(\'app.handler\')]</code>', 'correct' => true],
                    ['text' => 'Use the fluent builder: <code>$services->get(\'service\')->args([tagged_iterator(\'tag\')])</code>', 'correct' => false],
                    ['text' => 'It is not possible in the new array format; use attributes instead', 'correct' => false],
                    ['text' => 'Use the special <code>!tagged_iterator</code> YAML tag embedded in the PHP array', 'correct' => false],
                ],
                'explanation' => 'The new App::config format uses the same PHP functions (tagged_iterator, service, env, etc.) as before but within an array structure instead of fluent calls.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q10: DomCrawler + HTML5 Compatibility ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Testing'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>How does Symfony 7.4\'s <code>DomCrawler</code> handle HTML5 parsing on PHP versions below 8.4?</p>',
                'answers' => [
                    ['text' => 'It falls back to libxml-based parsing, which may not handle all HTML5 features correctly', 'correct' => true],
                    ['text' => 'It throws an exception requiring PHP 8.4', 'correct' => false],
                    ['text' => 'It uses the Masterminds HTML5 library as a mandatory dependency', 'correct' => false],
                    ['text' => 'It disables HTML parsing entirely', 'correct' => false],
                ],
                'explanation' => 'When PHP 8.4\'s \Dom\HTMLDocument is not available, DomCrawler falls back to the existing libxml parser. PHP 8.4 provides a standards-compliant parser.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q11: YAML vs PHP Config Recommendation ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, what is the official recommendation regarding YAML vs the new PHP configuration format?</p>',
                'answers' => [
                    ['text' => 'YAML remains the recommended format; PHP config is offered as an alternative with IDE benefits', 'correct' => true],
                    ['text' => 'PHP config is now the recommended format, YAML is deprecated', 'correct' => false],
                    ['text' => 'Both are equally recommended with no preference', 'correct' => false],
                    ['text' => 'YAML is deprecated in favor of PHP config in all new projects', 'correct' => false],
                ],
                'explanation' => 'Symfony still recommends YAML for simplicity. The new PHP config format is an alternative that provides IDE autocompletion and static analysis via generated array shapes.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q12: Full Deprecation Audit ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>Which of the following are deprecated in Symfony 7.4 and will be removed in 8.0? (Select all that apply)</p>',
                'answers' => [
                    ['text' => '<code>Request::get()</code> method', 'correct' => true],
                    ['text' => 'XML configuration format', 'correct' => true],
                    ['text' => 'Fluent PHP config builder classes', 'correct' => true],
                    ['text' => 'YAML configuration format', 'correct' => false],
                ],
                'explanation' => 'Request::get(), XML config, and fluent PHP config builders are all deprecated in 7.4. YAML is NOT deprecated and remains the recommended format.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q13: Testing with New Features ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Testing'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In a Symfony 7.4 functional test, how do you access the session without directly accessing the container?</p>',
                'answers' => [
                    ['text' => 'Use <code>$client->getSession()</code> which returns the session from the last request', 'correct' => true],
                    ['text' => 'Use <code>$this->getContainer()->get(\'session\')</code>', 'correct' => false],
                    ['text' => 'Use <code>$client->request(\'GET\', \'/\')->getSession()</code>', 'correct' => false],
                    ['text' => 'Sessions cannot be accessed in functional tests', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds $client->getSession() for cleaner session access in functional tests, avoiding direct container access.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q14: DI Attribute Ecosystem ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Dependency Injection'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>Which dependency injection attributes were improved or added in Symfony 7.4? (Select all that apply)</p>',
                'answers' => [
                    ['text' => '<code>#[AsDecorator]</code> — now repeatable to decorate multiple services', 'correct' => true],
                    ['text' => '<code>#[AutoconfigureResourceTag]</code> — for tagging non-service classes', 'correct' => true],
                    ['text' => '<code>#[Target]</code> — simplified form without <code>service()</code> wrapper', 'correct' => true],
                    ['text' => '<code>#[Inject]</code> — new attribute for constructor injection', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 added #[AutoconfigureResourceTag], made #[AsDecorator] repeatable, and simplified #[Target] usage. #[Inject] does not exist in Symfony.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q15: Migration Checklist ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>When upgrading from Symfony 7.3 to 7.4, which of these changes should be addressed before migrating to 8.0? (Select all that apply)</p>',
                'answers' => [
                    ['text' => 'Replace <code>Request::get()</code> calls with specific property bag accessors', 'correct' => true],
                    ['text' => 'Migrate XML configuration to YAML or the new PHP array format', 'correct' => true],
                    ['text' => 'Update HTTP method override configuration to use <code>allowed_http_method_override</code>', 'correct' => true],
                    ['text' => 'Rewrite all controllers to use invokable controllers', 'correct' => false],
                ],
                'explanation' => 'These are all deprecations in 7.4 that become breaking changes in 8.0. Invokable controllers are a pattern choice, not a requirement.',
                'resourceUrl' => 'https://symfony.com/releases',
                'symfonyVersion' => '7.4/8.0',
            ],
        ];
    }
}
