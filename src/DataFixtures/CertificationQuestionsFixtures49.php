<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 49
 * Symfony 7.4 / 8.0 new features - Part 5
 * Topics: Workflow, Security Voters, Caching HTTP Client, Twig, Messenger
 */
class CertificationQuestionsFixtures49 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures48::class];
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
            // ── Q1: Weighted Workflow Transitions – Concept ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>What is the purpose of weighted transitions introduced in Symfony 7.4\'s <code>Workflow</code> component?</p>',
                'answers' => [
                    ['text' => 'They allow a place to track how many times an object is in that place, enabling multiplicity (e.g. "collect 4 legs before assembling a table")', 'correct' => true],
                    ['text' => 'They assign priority values to transitions so the most important ones fire first', 'correct' => false],
                    ['text' => 'They measure the execution time of each transition for performance monitoring', 'correct' => false],
                    ['text' => 'They add conditional probabilities to determine which transition fires', 'correct' => false],
                ],
                'explanation' => 'Weighted transitions introduce multiplicity: a place can track how many times an object is in it. This is useful for synchronization, such as "require 4 instances before proceeding".',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-weighted-workflow-transitions',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q2: Weighted Workflow – Output Weight ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In a Symfony 7.4 workflow definition, what does a weight on the <code>to</code> (output) side of a transition do?</p>',
                'answers' => [
                    ['text' => 'It places the object in the target place N times', 'correct' => true],
                    ['text' => 'It requires the object to be in the target place N times before the transition can fire', 'correct' => false],
                    ['text' => 'It delays the transition by N seconds', 'correct' => false],
                    ['text' => 'It creates N parallel copies of the workflow subject', 'correct' => false],
                ],
                'explanation' => 'Weight on output (to) means the transition places the object in the target place N times. Weight on input (from) means the transition requires N instances before firing.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-weighted-workflow-transitions',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q3: Weighted Workflow – YAML Config ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>How do you define a weighted transition in a Symfony 7.4 workflow YAML configuration to place an object in <code>prepare_leg</code> 4 times?</p>',
                'answers' => [
                    ['text' => 'Use an object with <code>place: prepare_leg</code> and <code>weight: 4</code> under the <code>to</code> key', 'correct' => true],
                    ['text' => 'Use <code>prepare_leg: 4</code> under the <code>to</code> key', 'correct' => false],
                    ['text' => 'Use <code>to: prepare_leg</code> with a <code>repeat: 4</code> option', 'correct' => false],
                    ['text' => 'Use <code>to: [prepare_leg, prepare_leg, prepare_leg, prepare_leg]</code>', 'correct' => false],
                ],
                'explanation' => 'In YAML, use "- place: prepare_leg" and "weight: 4" under the "to" key of a transition. The same structure applies to the "from" key for input weights.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-weighted-workflow-transitions',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q4: Weighted Workflow – Synchronization ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 3,
                'text' => '<p>In Symfony 7.4, weighted workflow transitions can create synchronization points where a transition requires multiple instances in a place before it can fire.</p>',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
                'explanation' => 'Weighted transitions on the "from" side create synchronization: the transition requires the object to be in the source place N times before it can fire.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-weighted-workflow-transitions',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q5: Security Voter – access_decision() Twig ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, what do the new <code>access_decision()</code> and <code>access_decision_for_user()</code> Twig functions return?</p>',
                'answers' => [
                    ['text' => 'An <code>AccessDecision</code> object containing the verdict, votes, and resulting message', 'correct' => true],
                    ['text' => 'A boolean value (true/false) like <code>is_granted()</code>', 'correct' => false],
                    ['text' => 'An array of voter names that voted', 'correct' => false],
                    ['text' => 'A string describing the access decision reason', 'correct' => false],
                ],
                'explanation' => 'access_decision() returns an AccessDecision DTO that stores the access verdict, the collection of votes, and the resulting message (e.g. "Access granted").',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-security-voter-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q6: Security Voter – AccessDecision usage ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In a Twig template, how do you check if access is granted using the new <code>access_decision()</code> function introduced in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => '<code>{% set decision = access_decision(\'post_edit\', post) %} {% if decision.isGranted() %}</code>', 'correct' => true],
                    ['text' => '<code>{% if access_decision(\'post_edit\', post) %}</code>', 'correct' => false],
                    ['text' => '<code>{% set decision = access_decision(\'post_edit\') %} {% if decision == true %}</code>', 'correct' => false],
                    ['text' => '<code>{% if access_decision(\'post_edit\', post).granted %}</code>', 'correct' => false],
                ],
                'explanation' => 'You call access_decision() to get an AccessDecision object, then call isGranted() on it. You can also access decision.message for the reason.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-security-voter-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q7: Vote extraData ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In Symfony 7.4, how can you attach custom metadata to a security <code>Vote</code> object inside a voter?</p>',
                'answers' => [
                    ['text' => 'Via the <code>$vote->extraData</code> property (e.g. <code>$vote->extraData[\'score\'] = 10</code>)', 'correct' => true],
                    ['text' => 'By returning an array from <code>voteOnAttribute()</code>', 'correct' => false],
                    ['text' => 'By calling <code>$vote->setMetadata(\'key\', \'value\')</code>', 'correct' => false],
                    ['text' => 'Through the <code>VoterMetadata</code> attribute on the voter class', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 extends the Vote object with an extraData property where you can attach arbitrary metadata, useful in custom access decision strategies.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-security-voter-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q8: Custom Access Decision Strategy with extraData ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>What is a practical use case for the new <code>Vote</code> <code>extraData</code> feature in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'Assigning scores/weights to votes and using them in a custom <code>AccessDecisionStrategyInterface</code> implementation', 'correct' => true],
                    ['text' => 'Caching voter results between requests', 'correct' => false],
                    ['text' => 'Logging voter decisions to a file automatically', 'correct' => false],
                    ['text' => 'Sending voter metadata to the frontend via JSON response headers', 'correct' => false],
                ],
                'explanation' => 'You can use extraData to assign scores to votes (e.g. $vote->extraData[\'score\'] = 10) and aggregate them in a custom AccessDecisionStrategyInterface.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-security-voter-improvements',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q9: Caching HTTP Client – Configuration ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, how do you enable the new RFC 9111-compliant caching for an HTTP client?</p>',
                'answers' => [
                    ['text' => 'Add a <code>caching</code> option with a <code>cache_pool</code> key pointing to a tag-aware cache pool under the scoped client configuration', 'correct' => true],
                    ['text' => 'Wrap the client with <code>new CachingHttpClient($client, $store)</code> using <code>HttpCache</code>', 'correct' => false],
                    ['text' => 'Set <code>cache: true</code> on the <code>framework.http_client</code> configuration', 'correct' => false],
                    ['text' => 'Install the <code>symfony/cache-http-client</code> package', 'correct' => false],
                ],
                'explanation' => 'In Symfony 7.4, add "caching: { cache_pool: my_pool }" to a scoped client. The cache pool must be tag-aware. This replaces the old CachingHttpClient/HttpCache approach.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-caching-http-client',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q10: Caching HTTP Client – Options ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Which options does the new caching configuration for HTTP clients in Symfony 7.4 support?</p>',
                'answers' => [
                    ['text' => '<code>shared</code>: uses a shared cache so responses can be reused across users', 'correct' => true],
                    ['text' => '<code>max_ttl</code>: caps server-provided TTLs to a maximum value', 'correct' => true],
                    ['text' => '<code>cache_pool</code>: the tag-aware cache pool to use', 'correct' => true],
                    ['text' => '<code>invalidation_strategy</code>: defines how cached entries are invalidated', 'correct' => false],
                ],
                'explanation' => 'The caching option supports: cache_pool (required, must be tag-aware), shared (default true, shared cache across users), and max_ttl (cap on server TTLs).',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-caching-http-client',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q11: Caching HTTP Client – Shared Cache ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'text' => '<p>By default, the new Symfony 7.4 caching HTTP client uses a shared cache, meaning cached responses can be reused across different users.</p>',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
                'explanation' => 'The "shared" option defaults to true, so cached responses can be reused across users (shared cache as defined in HTTP caching specifications).',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-caching-http-client',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q12: Caching HTTP Client – RFC ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>The new caching HTTP client in Symfony 7.4 is fully compliant with which RFC?</p>',
                'answers' => [
                    ['text' => 'RFC 9111 (HTTP Caching)', 'correct' => true],
                    ['text' => 'RFC 7234 (HTTP/1.1 Caching)', 'correct' => false],
                    ['text' => 'RFC 9110 (HTTP Semantics)', 'correct' => false],
                    ['text' => 'RFC 7540 (HTTP/2)', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 revamped the CachingHttpClient to be fully compliant with RFC 9111, the current HTTP caching specification, using the Cache component instead of HttpCache.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-caching-http-client',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q13: ClockMock Mockable Functions ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Testing'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Which PHP functions can be mocked using Symfony\'s <code>ClockMock</code> (PHPUnit Bridge) as of Symfony 7.4? (Select all that apply)</p>',
                'answers' => [
                    ['text' => '<code>time()</code> and <code>microtime()</code>', 'correct' => true],
                    ['text' => '<code>sleep()</code> and <code>usleep()</code>', 'correct' => true],
                    ['text' => '<code>strtotime()</code> and <code>gmdate()</code>', 'correct' => true],
                    ['text' => '<code>date()</code> and <code>mktime()</code>', 'correct' => false],
                ],
                'explanation' => 'As of Symfony 7.4, ClockMock can mock: time(), microtime(), sleep(), usleep(), gmdate(), hrtime(), and the newly added strtotime().',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q14: Form Accessibility – WCAG Standard ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Forms'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>The improved form accessibility in Symfony 7.4 follows which accessibility standard and technique?</p>',
                'answers' => [
                    ['text' => 'WCAG 2.1, specifically the ARIA 21 technique', 'correct' => true],
                    ['text' => 'WCAG 3.0, specifically the Form Validation technique', 'correct' => false],
                    ['text' => 'Section 508, specifically the Error Handling technique', 'correct' => false],
                    ['text' => 'WAI-ARIA 1.2, specifically the Alert Role pattern', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 form accessibility follows WCAG 2.1 (Error Identification criterion) and the ARIA 21 technique, adding aria-invalid and aria-describedby to fields with errors.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q15: FrankenPHP – Official Status ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'text' => '<p>Under which organization did the FrankenPHP project move, making it the official PHP application server?</p>',
                'answers' => [
                    ['text' => 'The PHP organization (<code>github.com/php/frankenphp</code>)', 'correct' => true],
                    ['text' => 'The Symfony organization', 'correct' => false],
                    ['text' => 'The PHP-FIG organization', 'correct' => false],
                    ['text' => 'The Apache Software Foundation', 'correct' => false],
                ],
                'explanation' => 'FrankenPHP moved under the PHP organization (github.com/php/frankenphp), becoming the official application server for PHP applications.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-dx-improvements-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],
        ];
    }
}
