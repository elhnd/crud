<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 48
 * Symfony 7.4 / 8.0 new features - Part 4
 * Topics: DX Improvements, Testing, Forms, Console, BrowserKit, VarDumper
 */
class CertificationQuestionsFixtures48 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures47::class];
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
            // ── Q1: Session in Functional Tests ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Testing'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4 functional tests, how can you preset session data (e.g. CSRF tokens) before making a request?</p>',
                'answers' => [
                    ['text' => 'Call <code>$client->getSession()</code>, set data on it, call <code>$session->save()</code>, then make the request', 'correct' => true],
                    ['text' => 'Inject the <code>SessionInterface</code> directly into the test method', 'correct' => false],
                    ['text' => 'Use <code>$client->setSessionValue(\'key\', \'value\')</code>', 'correct' => false],
                    ['text' => 'Access the session via <code>$client->getContainer()->get(\'session\')</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds a getSession() method on the test client. You set data on it, call save(), and then make your request.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q2: Session save() requirement ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Testing'),
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'text' => '<p>In Symfony 7.4, when presetting session data in functional tests with <code>$client->getSession()</code>, you must call <code>$session->save()</code> before making the request.</p>',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
                'explanation' => 'After setting session data via $client->getSession(), you must call $session->save() to persist it before making the HTTP request.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q3: Improved Route Debugging ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Routing'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 1,
                'text' => '<p>Which improvements were made to the <code>debug:router</code> command in Symfony 7.4? (Select all that apply)</p>',
                'answers' => [
                    ['text' => 'The Scheme and Host columns are hidden when their values are "ANY"', 'correct' => true],
                    ['text' => 'HTTP methods are displayed in color (e.g. blue for GET, yellow for POST)', 'correct' => true],
                    ['text' => 'Routes are now sorted by priority automatically', 'correct' => false],
                    ['text' => 'The command now displays middleware information for each route', 'correct' => false],
                ],
                'explanation' => 'In Symfony 7.4, debug:router hides Scheme/Host columns when they are "ANY" and uses colored HTTP methods (Swagger-style colors).',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q4: Form Accessibility ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Forms'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, which ARIA attributes are now automatically added to form fields with validation errors?</p>',
                'answers' => [
                    ['text' => '<code>aria-invalid</code>', 'correct' => true],
                    ['text' => '<code>aria-describedby</code>', 'correct' => true],
                    ['text' => '<code>aria-required</code>', 'correct' => false],
                    ['text' => '<code>aria-errormessage</code>', 'correct' => false],
                ],
                'explanation' => 'In Symfony 7.4, fields with validation errors automatically include aria-invalid and aria-describedby attributes, following WCAG 2.1 (ARIA 21 technique).',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q5: ClockMock strtotime ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Testing'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Which PHP function was added to the list of mockable functions in <code>ClockMock</code> (PHPUnit Bridge) in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => '<code>strtotime()</code>', 'correct' => true],
                    ['text' => '<code>date()</code>', 'correct' => false],
                    ['text' => '<code>mktime()</code>', 'correct' => false],
                    ['text' => '<code>strftime()</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 added strtotime() to the list of mockable functions in ClockMock, alongside time(), microtime(), sleep(), usleep(), gmdate(), and hrtime().',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q6: Messenger --exclude-receivers ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Messenger'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, the <code>messenger:consume</code> command added a new option to exclude specific receivers when using <code>--all</code>. What is this option?</p>',
                'answers' => [
                    ['text' => '<code>--exclude-receivers</code>', 'correct' => true],
                    ['text' => '<code>--skip-transports</code>', 'correct' => false],
                    ['text' => '<code>--ignore-receivers</code>', 'correct' => false],
                    ['text' => '<code>--without</code>', 'correct' => false],
                ],
                'explanation' => 'The new --exclude-receivers option is used with --all to exclude specific receivers (e.g. failed receivers) when consuming messages.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q7: FrankenPHP Worker Mode ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'text' => '<p>Starting from Symfony 7.4, you no longer need to install the <code>runtime/frankenphp-symfony</code> package to use FrankenPHP worker mode — it is supported natively.</p>',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 natively supports FrankenPHP worker mode out of the box, removing the need for the runtime/frankenphp-symfony package.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q8: Question Helper Timeout ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Console'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, the Console <code>Question</code> helper supports timeouts. What exception is thrown if the user does not respond within the specified time?</p>',
                'answers' => [
                    ['text' => '<code>MissingInputException</code>', 'correct' => true],
                    ['text' => '<code>TimeoutException</code>', 'correct' => false],
                    ['text' => '<code>RuntimeException</code>', 'correct' => false],
                    ['text' => '<code>ConsoleTimeoutException</code>', 'correct' => false],
                ],
                'explanation' => 'When a timeout is set via $question->setTimeout(10) and the user doesn\'t respond in time, a MissingInputException is thrown.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q9: Enum Type Guesser for Forms ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Forms'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Symfony 7.4 added a new type guesser for forms. What does it automatically detect?</p>',
                'answers' => [
                    ['text' => 'PHP enum fields, automatically using <code>EnumType</code> with the correct <code>class</code> option', 'correct' => true],
                    ['text' => '<code>DateTime</code> fields, automatically using <code>DateTimeType</code>', 'correct' => false],
                    ['text' => 'UUID fields, automatically using <code>UuidType</code>', 'correct' => false],
                    ['text' => 'Boolean fields, automatically using <code>CheckboxType</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds an enum type guesser that automatically uses EnumType and sets its class option when it detects a PHP enum field.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q10: OIDC Token Generation Command ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>What is the new console command introduced in Symfony 7.4 for generating OIDC (JWT) tokens for testing purposes?</p>',
                'answers' => [
                    ['text' => '<code>security:oidc-token:generate</code>', 'correct' => true],
                    ['text' => '<code>security:jwt:create</code>', 'correct' => false],
                    ['text' => '<code>make:jwt-token</code>', 'correct' => false],
                    ['text' => '<code>debug:security:token</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 introduces the security:oidc-token:generate command to create JWTs for testing and development, supporting options like --firewall, --algorithm, --issuer, --ttl, and --not-before.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q11: Default Locale Outside HTTP Context ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Routing'),
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, the <code>kernel.default_locale</code> parameter is now also used as the default locale when generating URLs outside HTTP requests (e.g. in console commands).</p>',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
                'explanation' => 'Previously, generating URLs in commands could miss the locale. In Symfony 7.4, kernel.default_locale is used as the fallback locale in non-HTTP contexts.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q12: BrowserKit Helper Methods ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Testing'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Which new BrowserKit helper methods and their corresponding PHPUnit assertions were introduced in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => '<code>isFirstPage()</code> and <code>assertBrowserHistoryIsOnFirstPage()</code>', 'correct' => true],
                    ['text' => '<code>isLastPage()</code> and <code>assertBrowserHistoryIsOnLastPage()</code>', 'correct' => true],
                    ['text' => '<code>hasHistory()</code> and <code>assertBrowserHasHistory()</code>', 'correct' => false],
                    ['text' => '<code>canGoBack()</code> and <code>assertBrowserCanGoBack()</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds isFirstPage()/isLastPage() to BrowserKit and the corresponding assertBrowserHistoryIsOnFirstPage/assertBrowserHistoryIsOnLastPage assertions.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q13: Better Dumps in non-HTML Contexts ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, when does the <code>dump()</code>/<code>dd()</code> function render HTML output?</p>',
                'answers' => [
                    ['text' => 'Only when the request contains an <code>Accept</code> header with an <code>html</code> value', 'correct' => true],
                    ['text' => 'Always, regardless of context', 'correct' => false],
                    ['text' => 'Only when running in the web profiler', 'correct' => false],
                    ['text' => 'Only when the <code>APP_DEBUG</code> variable is true', 'correct' => false],
                ],
                'explanation' => 'In Symfony 7.4, dump()/dd() renders HTML only when the request contains an Accept header with html. In all other cases (API, terminal), output is plain text.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q14: Simpler Target Attributes ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Dependency Injection'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, the <code>#[Target]</code> attribute for autowiring was simplified. Before, to target a rate limiter named "anonymous_api", you had to write <code>#[Target(\'anonymous_api.limiter\')]</code>. What is the simplified syntax?</p>',
                'answers' => [
                    ['text' => '<code>#[Target(\'anonymous_api\')]</code>', 'correct' => true],
                    ['text' => '<code>#[Target(service: \'anonymous_api\')]</code>', 'correct' => false],
                    ['text' => '<code>#[Autowire(target: \'anonymous_api\')]</code>', 'correct' => false],
                    ['text' => '<code>#[Target(\'anonymous_api\', stripSuffix: true)]</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 simplifies #[Target] by removing the need for type-based suffixes like .limiter, .lock.factory, or .package. Just use the configuration name directly.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q15: OIDC Token Command Options ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>Which of the following are valid options for the <code>security:oidc-token:generate</code> command in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => '<code>--firewall</code>', 'correct' => true],
                    ['text' => '<code>--algorithm</code>', 'correct' => true],
                    ['text' => '<code>--ttl</code>', 'correct' => true],
                    ['text' => '<code>--audience</code>', 'correct' => false],
                ],
                'explanation' => 'The security:oidc-token:generate command supports --firewall, --algorithm (e.g. HS256), --issuer, --ttl, and --not-before. There is no --audience option.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],
        ];
    }
}
