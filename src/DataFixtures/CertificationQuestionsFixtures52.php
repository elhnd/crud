<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 52
 * Symfony 7.4 / 8.0 new features - Part 8
 * Topics: Misc Features (HTML5 Parser, HTTP QUERY, Workflows, DynamoDB Lock, Doctrine Types, Mailer)
 */
class CertificationQuestionsFixtures52 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures51::class];
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
            // ── Q1: Native HTML5 Parser ──
            [
                'category' => $php,
                'subcategory' => $this->getSubcategory($subcategories, 'PHP:PHP Basics'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>PHP 8.4 introduces a native HTML5 parser class. How does Symfony 7.4 leverage this?</p>',
                'answers' => [
                    ['text' => 'The <code>DomCrawler</code> component now uses the native <code>\Dom\HTMLDocument</code> parser when available in PHP 8.4', 'correct' => true],
                    ['text' => 'Twig templates are now parsed using <code>\Dom\HTMLDocument</code>', 'correct' => false],
                    ['text' => 'Symfony replaces the Masterminds HTML5 library with a custom polyfill', 'correct' => false],
                    ['text' => 'Forms are rendered using a new HTML5 output engine', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 uses PHP 8.4\'s native \Dom\HTMLDocument for HTML5 parsing in the DomCrawler component, which is faster and more standards-compliant than libxml.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q2: HTTP QUERY Method ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>Symfony 7.4 adds support for a new HTTP method defined in RFC 9110. What is it?</p>',
                'answers' => [
                    ['text' => '<code>QUERY</code> — similar to GET but allows a request body for complex queries', 'correct' => true],
                    ['text' => '<code>SEARCH</code> — for performing full-text search operations', 'correct' => false],
                    ['text' => '<code>LINK</code> — for establishing relationships between resources', 'correct' => false],
                    ['text' => '<code>FETCH</code> — an alternative to GET with streaming support', 'correct' => false],
                ],
                'explanation' => 'The HTTP QUERY method allows sending a request body (like POST) but is safe and idempotent (like GET). It\'s useful when queries are too complex for URL query strings.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q3: HTTP QUERY – Routing ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Routing'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>How do you define a route that responds to the new HTTP <code>QUERY</code> method in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'Use <code>#[Route(\'/search\', methods: [\'QUERY\'])]</code> like any other HTTP method', 'correct' => true],
                    ['text' => 'Use <code>#[Query(\'/search\')]</code> as a dedicated attribute', 'correct' => false],
                    ['text' => 'Set <code>query: true</code> in the route definition', 'correct' => false],
                    ['text' => '<code>QUERY</code> is only supported via YAML route configuration', 'correct' => false],
                ],
                'explanation' => 'The QUERY method is supported as a standard HTTP method in Symfony routing. Use methods: [\'QUERY\'] in the #[Route] attribute.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q4: Enum Support in Workflows ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>How can you use PHP enums as workflow places in Symfony 7.4 YAML configuration?</p>',
                'answers' => [
                    ['text' => 'Use the <code>!php/enum</code> tag (e.g. <code>!php/enum App\\Enum\\Status::Draft</code>)', 'correct' => true],
                    ['text' => 'Use the <code>enum()</code> function (e.g. <code>enum(App\\Enum\\Status::Draft)</code>)', 'correct' => false],
                    ['text' => 'Specify enum class in the workflow type configuration', 'correct' => false],
                    ['text' => 'Enums are not supported as workflow places in YAML', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds native enum support in workflows. In YAML, use the !php/enum tag to reference enum cases as places and in transitions.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q5: DynamoDB Lock Store ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Which new lock store was added in Symfony 7.4 for cloud-native distributed locking?</p>',
                'answers' => [
                    ['text' => '<code>DynamoDB Lock Store</code> — backed by AWS DynamoDB', 'correct' => true],
                    ['text' => '<code>CloudFlare Lock Store</code> — backed by CloudFlare KV', 'correct' => false],
                    ['text' => '<code>FirestoreLockStore</code> — backed by Google Firestore', 'correct' => false],
                    ['text' => '<code>CosmosDB Lock Store</code> — backed by Azure CosmosDB', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds a DynamoDB Lock Store for distributed locking using AWS DynamoDB, ideal for serverless or cloud-native applications.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q6: DayPointType & TimePointType ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Which new Doctrine types does Symfony 7.4 introduce for the Clock component? (Select all that apply)</p>',
                'answers' => [
                    ['text' => '<code>DayPointType</code> — for storing dates (without time) in the database', 'correct' => true],
                    ['text' => '<code>TimePointType</code> — for storing date+time in the database', 'correct' => true],
                    ['text' => '<code>ClockType</code> — a generic type for any clock-related value', 'correct' => false],
                    ['text' => '<code>InstantType</code> — for storing microsecond-precision timestamps', 'correct' => false],
                ],
                'explanation' => 'DayPointType and TimePointType are new Doctrine types that map database columns to the Clock component\'s DayPoint and TimePoint value objects.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q7: CDATA Wrapping Per Field ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Serializer'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In the XML serializer, how can you configure per-field CDATA wrapping in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'Use the <code>CDATA_WRAPPING_NAME_PATTERN</code> option with a regex that matches property names to wrap', 'correct' => true],
                    ['text' => 'Mark properties with <code>#[CdataWrap]</code> attribute', 'correct' => false],
                    ['text' => 'Set CDATA wrapping per field in serialization groups', 'correct' => false],
                    ['text' => 'Use a custom normalizer for CDATA support', 'correct' => false],
                ],
                'explanation' => 'CDATA_WRAPPING_NAME_PATTERN context option accepts a regex pattern. Fields matching the pattern are wrapped in CDATA sections in the XML output.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q8: Structured MIME Suffix ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In Symfony 7.4, how does the <code>Request::getFormat()</code> method handle structured MIME suffixes (e.g., <code>application/vnd.api+json</code>)?</p>',
                'answers' => [
                    ['text' => 'It now recognizes the <code>+json</code>, <code>+xml</code>, etc. suffix and returns the correct format even for vendor-prefixed MIME types', 'correct' => true],
                    ['text' => 'It ignores suffixes and only matches exact MIME types', 'correct' => false],
                    ['text' => 'It returns the full MIME type including the vendor prefix', 'correct' => false],
                    ['text' => 'It throws an exception for unknown vendor prefixes', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds support for RFC 6838 structured MIME suffix parsing. This means getFormat("application/vnd.api+json") now returns "json".',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-3',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q9: MicrosoftGraph Mailer ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Which new email delivery integration was added as a Mailer transport in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => '<code>Microsoft Graph</code> — for sending emails via the Microsoft Graph API', 'correct' => true],
                    ['text' => '<code>ProtonMail</code> — for sending emails via the ProtonMail API', 'correct' => false],
                    ['text' => '<code>Yahoo Mail</code> — for sending emails via the Yahoo SMTP bridge', 'correct' => false],
                    ['text' => '<code>FastMail</code> — for sending emails via the FastMail API', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds a new MicrosoftGraph mailer transport, allowing emails to be sent through the Microsoft Graph API (used by Office 365 and Outlook).',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-3',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q10: StaticMessage (Untranslated) ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>What is the purpose of the new <code>StaticMessage</code> class in Symfony 7.4\'s Translation component?</p>',
                'answers' => [
                    ['text' => 'To create messages that should never be translated, preventing accidental translation of technical identifiers', 'correct' => true],
                    ['text' => 'To cache translations statically for better performance', 'correct' => false],
                    ['text' => 'To define translation messages as PHP constants', 'correct' => false],
                    ['text' => 'To generate static translation catalogues at build time', 'correct' => false],
                ],
                'explanation' => 'StaticMessage wraps a string that should not be translated. It implements TranslatableInterface but always returns the original string, useful for technical identifiers mixed with translatable content.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-3',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q11: assertEmailAddressNotContains ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Testing'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Which new email assertion method was added in Symfony 7.4 for testing mailer?</p>',
                'answers' => [
                    ['text' => '<code>assertEmailAddressNotContains()</code> — asserts an email address is NOT in a given header', 'correct' => true],
                    ['text' => '<code>assertEmailNotSent()</code> — asserts no email was sent at all', 'correct' => false],
                    ['text' => '<code>assertEmailBodyNotContains()</code> — asserts the body does not contain a string', 'correct' => false],
                    ['text' => '<code>assertEmailNotQueued()</code> — asserts the email was not queued', 'correct' => false],
                ],
                'explanation' => 'assertEmailAddressNotContains() complements existing assertEmailAddressContains(). It\'s useful for verifying sensitive addresses are excluded from headers like To, Cc, Bcc.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-3',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q12: EventSource Profiling ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'text' => '<p>Symfony 7.4 adds profiling support for Server-Sent Events (<code>EventSource</code>) requests in the Web Profiler.</p>',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
                'explanation' => 'The Symfony Profiler now captures and displays information about SSE/EventSource requests, making it easier to debug real-time streaming endpoints.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-3',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q13: HTTP QUERY vs GET ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>Which of the following are true about the HTTP <code>QUERY</code> method supported in Symfony 7.4? (Select all that apply)</p>',
                'answers' => [
                    ['text' => 'It is safe and idempotent, like <code>GET</code>', 'correct' => true],
                    ['text' => 'It allows a request body, unlike <code>GET</code>', 'correct' => true],
                    ['text' => 'It modifies server-side state, like <code>POST</code>', 'correct' => false],
                    ['text' => 'It is ideal for complex search queries that exceed URL length limits', 'correct' => true],
                ],
                'explanation' => 'QUERY combines the safety/idempotence of GET with the body support of POST. It is designed for complex queries that don\'t fit in URL query strings.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q14: DynamoDB Lock DSN ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>What DSN scheme is used to configure the DynamoDB Lock Store in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => '<code>dynamodb://</code> with a table name and optional region/endpoint parameters', 'correct' => true],
                    ['text' => '<code>aws-lock://</code> with an ARN resource identifier', 'correct' => false],
                    ['text' => '<code>lock+dynamodb://</code> with API credentials in the URL', 'correct' => false],
                    ['text' => '<code>dynamo://</code> with a connection string', 'correct' => false],
                ],
                'explanation' => 'The DynamoDB Lock Store uses the dynamodb:// scheme. Configuration includes the table name and optionally the AWS region and endpoint.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q15: Serialization Extension – Groups ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Serializer'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>When using <code>#[ExtendsSerializationFor]</code> to add serialization groups to a third-party class, how can you control which groups apply?</p>',
                'answers' => [
                    ['text' => 'Use serialization groups on the extension class properties — only matching groups are applied during serialization', 'correct' => true],
                    ['text' => 'Use the <code>merge_groups</code> option in the attribute to specify which groups to merge', 'correct' => false],
                    ['text' => 'Pass a <code>groups</code> array directly to the <code>#[ExtendsSerializationFor]</code> attribute', 'correct' => false],
                    ['text' => 'Groups cannot be filtered — all groups from both classes always apply', 'correct' => false],
                ],
                'explanation' => 'Since constraints/metadata are merged (not overridden), use serialization groups on extension properties to selectively apply them during serialization.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-extending-validation-and-serialization-with-php-attributes',
                'symfonyVersion' => '7.4/8.0',
            ],
        ];
    }
}
