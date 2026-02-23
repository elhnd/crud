<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 46
 * Symfony 7.4 / 8.0 new features - Part 2
 * Topics: Forms, Messenger, Validation, Serializer, Twig, Configuration
 */
class CertificationQuestionsFixtures46 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures45::class];
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
            // Q1 - Forms - Multi-step forms (AbstractFlowType)
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Forms'),
                'text' => '<p>Symfony 7.4 introduces multi-step forms called "form flows". Which base class should you extend to create a multi-step form?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Multi-step forms in Symfony 7.4 are built by extending AbstractFlowType instead of AbstractType. You use buildFormFlow() instead of buildForm(), and each step is a separate regular Symfony form added via addStep().',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-multi-step-forms',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => '<code>AbstractFlowType</code>', 'correct' => true],
                    ['text' => '<code>AbstractType</code> with a <code>steps</code> option', 'correct' => false],
                    ['text' => '<code>AbstractWizardType</code>', 'correct' => false],
                    ['text' => '<code>FormFlowInterface</code>', 'correct' => false],
                ],
            ],

            // Q2 - Forms - Form flow controller handling
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Forms'),
                'text' => '<p>Consider the following controller using a Symfony 7.4 multi-step form:</p>
<pre><code class="language-php">$flow = $this->createForm(UserSignUpType::class, new UserSignUp())
    ->handleRequest($request);

if ($flow->isSubmitted() && $flow->isValid() && $flow->isFinished()) {
    // process complete
    return $this->redirectToRoute(\'app_success\');
}

return $this->render(\'signup/flow.html.twig\', [
    \'form\' => $flow->getStepForm(),
]);</code></pre>
<p>What does the <code>$flow->isFinished()</code> check accomplish?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In multi-step forms, isFinished() determines if the user has completed all steps and clicked the final submit button (FinishFlowType). The form can be valid at each step, but only isFinished() confirms the entire flow is done. getStepForm() creates the form view for the current step.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-multi-step-forms',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'It checks if the user has completed the final step and marked the entire flow as done', 'correct' => true],
                    ['text' => 'It checks if all form fields across all steps have been filled', 'correct' => false],
                    ['text' => 'It verifies that the form has been persisted to the database', 'correct' => false],
                    ['text' => 'It checks if the session storage for the flow has been cleared', 'correct' => false],
                ],
            ],

            // Q3 - Forms - Form flow validation groups
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Forms'),
                'text' => '<p>In Symfony 7.4 multi-step forms, each step is defined with a unique name:</p>
<pre><code class="language-php">$builder->addStep(\'personal\', UserPersonalType::class);
$builder->addStep(\'account\', UserAccountType::class);</code></pre>
<p>How does the step name relate to validation?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Form flows automatically set the current step name as the active validation group. This means you can use the step name in your validation constraint groups to ensure that only the constraints relevant to the current step are validated.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-multi-step-forms',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'The step name is automatically used as the active validation group for that step', 'correct' => true],
                    ['text' => 'The step name is used as a prefix for all form field names', 'correct' => false],
                    ['text' => 'The step name is only used for URL routing between steps', 'correct' => false],
                    ['text' => 'The step name has no relation to validation; groups must be configured manually', 'correct' => false],
                ],
            ],

            // Q4 - Forms - Flow navigation buttons
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Forms'),
                'text' => '<p>Which of the following are valid button types for navigating Symfony 7.4 multi-step forms?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 7.4 multi-step forms provide four navigation button types that extend ButtonFlowType: NextFlowType (go to next step), PreviousFlowType (go to previous step), FinishFlowType (complete the flow), and ResetFlowType (restart from beginning). They also provide options like skip, back_to, include_if, and clear_submission for advanced navigation.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-multi-step-forms',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => '<code>NextFlowType</code>', 'correct' => true],
                    ['text' => '<code>PreviousFlowType</code>', 'correct' => true],
                    ['text' => '<code>FinishFlowType</code>', 'correct' => true],
                    ['text' => '<code>ResetFlowType</code>', 'correct' => true],
                    ['text' => '<code>SkipFlowType</code>', 'correct' => false],
                    ['text' => '<code>CancelFlowType</code>', 'correct' => false],
                ],
            ],

            // Q5 - Messenger - Message signing
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Messenger'),
                'text' => '<p>Symfony 7.4 introduces message signing in the Messenger component. Consider the following handler:</p>
<pre><code class="language-php">#[AsMessageHandler(sign: true)]
class SmsNotificationHandler
{
    public function __invoke(SmsNotification $message): void
    {
        // ...
    }
}</code></pre>
<p>What happens when a message with an invalid or missing signature arrives?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When signing is enabled with sign: true on the handler, each message is signed using an HMAC computed with the application\'s kernel.secret parameter. When received, the signature is verified automatically. If it is missing or invalid, an InvalidMessageSignatureException is thrown and the message is NOT processed.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-signing-messages',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'An <code>InvalidMessageSignatureException</code> is thrown and the message is not processed', 'correct' => true],
                    ['text' => 'The message is processed normally but a warning is logged', 'correct' => false],
                    ['text' => 'The message is silently discarded without any exception', 'correct' => false],
                    ['text' => 'Symfony retries the message after re-signing it automatically', 'correct' => false],
                ],
            ],

            // Q6 - Messenger - Signing mechanism
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Messenger'),
                'text' => '<p>When message signing is enabled in Symfony 7.4 Messenger, which secret is used to compute the HMAC signature?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The message signature is computed using the kernel.secret parameter (the APP_SECRET environment variable). The signature and algorithm are stored in the message headers (Body-Sign and Sign-Algo) when the message is dispatched.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-signing-messages',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'The <code>kernel.secret</code> parameter (<code>APP_SECRET</code>)', 'correct' => true],
                    ['text' => 'A dedicated per-transport secret configured in <code>messenger.yaml</code>', 'correct' => false],
                    ['text' => 'An auto-generated key stored in the <code>var/</code> directory', 'correct' => false],
                    ['text' => 'The TLS certificate of the queue server', 'correct' => false],
                ],
            ],

            // Q7 - Messenger - Signing headers
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Messenger'),
                'text' => '<p>When a message is signed in Symfony 7.4 Messenger, which headers are added to the message envelope?</p>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'When signing is enabled, two headers are added: "Body-Sign" (containing the HMAC signature) and "Sign-Algo" (containing the algorithm used). These are verified when the message is received.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-signing-messages',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => '<code>Body-Sign</code>', 'correct' => true],
                    ['text' => '<code>Sign-Algo</code>', 'correct' => true],
                    ['text' => '<code>X-Signature</code>', 'correct' => false],
                    ['text' => '<code>Message-Digest</code>', 'correct' => false],
                ],
            ],

            // Q8 - Validation - Extending with PHP attributes
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Validation'),
                'text' => '<p>Symfony 7.4 introduces new PHP attributes that let you extend validation metadata for classes you do not control. What problem does this solve?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Before Symfony 7.4, adding validation constraints to third-party classes (from libraries or vendor code) required using YAML or XML configuration. The new PHP attributes allow extending validation metadata declaratively, keeping the configuration closer to where it is used, without modifying the third-party code.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-extending-validation-and-serialization-with-php-attributes',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'It allows adding validation constraints to third-party classes without modifying their code or using YAML/XML', 'correct' => true],
                    ['text' => 'It enables runtime validation of method return types', 'correct' => false],
                    ['text' => 'It replaces the Validator component with native PHP attribute validation', 'correct' => false],
                    ['text' => 'It adds validation to database columns directly via Doctrine attributes', 'correct' => false],
                ],
            ],

            // Q9 - Configuration - Deprecated XML configuration
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Configuration'),
                'text' => '<p>In Symfony 7.4, which configuration format has been deprecated for configuring Symfony applications?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 7.4 deprecates XML as a configuration format to simplify the configuration of Symfony applications. YAML and PHP remain the recommended formats.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-deprecated-xml-configuration',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'XML', 'correct' => true],
                    ['text' => 'YAML', 'correct' => false],
                    ['text' => 'PHP', 'correct' => false],
                    ['text' => 'JSON', 'correct' => false],
                ],
            ],

            // Q10 - Configuration - Better PHP configuration
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Configuration'),
                'text' => '<p>Symfony 7.4 introduces a new PHP configuration format. What is the main advantage of this new format over the previous fluent API?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The new array-based PHP configuration format replaces the previous fluent API. It provides full autocompletion, static analysis support, and dynamically generated array shapes, making the configuration experience much better than the fluent builder pattern.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Full autocompletion and static analysis support through array shapes', 'correct' => true],
                    ['text' => 'It executes faster at runtime because arrays are simpler than objects', 'correct' => false],
                    ['text' => 'It allows configuration to be split across multiple files automatically', 'correct' => false],
                    ['text' => 'It supports environment-specific configuration natively without <code>.env</code> files', 'correct' => false],
                ],
            ],

            // Q11 - Security - Voter improvements
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Security'),
                'text' => '<p>Symfony 7.4 improves security voters by adding new Twig functions. What can you do with these new functions?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 7.4 adds new Twig functions for security voters that allow you to access customizable vote metadata. This means you can check not just whether access is granted, but also get detailed information about why a decision was made.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-security-voter-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Access customizable vote metadata and detailed information about authorization decisions', 'correct' => true],
                    ['text' => 'Register new voters directly from Twig templates', 'correct' => false],
                    ['text' => 'Override voter results from within Twig templates', 'correct' => false],
                    ['text' => 'Cache voter decisions for the entire request lifecycle', 'correct' => false],
                ],
            ],

            // Q12 - HttpClient - Caching HTTP Client
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HttpClient'),
                'text' => '<p>Symfony 7.4 introduces a caching HTTP client. Which HTTP standard does it follow for client-side caching?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The new caching HTTP client in Symfony 7.4 follows RFC 9111 (HTTP Caching) for client-side caching. It is powered by the Symfony Cache component and automatically handles cache-control headers, ETags, and other caching mechanisms defined in the standard.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-caching-http-client',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'RFC 9111 (HTTP Caching)', 'correct' => true],
                    ['text' => 'RFC 7234 (superseded caching standard)', 'correct' => false],
                    ['text' => 'RFC 9110 (HTTP Semantics)', 'correct' => false],
                    ['text' => 'There is no specific standard; it uses a custom caching strategy', 'correct' => false],
                ],
            ],

            // Q13 - Event Dispatcher - Union types in #[AsEventListener]
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Event Dispatcher'),
                'text' => '<p>In Symfony 7.4, the following event listener code is valid:</p>
<pre><code class="language-php">use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class SomeListener
{
    #[AsEventListener]
    public function doSomething(CustomEvent|AnotherCustomEvent $event): void
    {
        // ...
    }
}</code></pre>
<p>What does this code accomplish?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 7.4, the #[AsEventListener] attribute supports union types in method signatures. When no explicit "event" argument is specified in the attribute, Symfony reads the type-hint and registers the listener for each event type in the union. The listener method is called for both CustomEvent and AnotherCustomEvent dispatches.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-attribute-improvements',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'The listener is registered for both <code>CustomEvent</code> and <code>AnotherCustomEvent</code> automatically', 'correct' => true],
                    ['text' => 'Only the first type (<code>CustomEvent</code>) is used; the second is ignored', 'correct' => false],
                    ['text' => 'This code is invalid because <code>#[AsEventListener]</code> requires specifying the event class explicitly', 'correct' => false],
                    ['text' => 'The listener is registered for a special <code>UnionEvent</code> that aggregates both events', 'correct' => false],
                ],
            ],

            // Q14 - Miscellaneous - Share directory
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'text' => '<p>Symfony 7.4 introduces a new directory in the application structure. What is the purpose of the <code>share/</code> directory?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The share/ directory is designed to store data that should be shared across multiple servers in a distributed setup. Unlike var/cache/ which is local to each server, the share/ directory can be mounted on a network filesystem or synchronized between servers.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-share-directory',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'To store data shared across multiple servers, like application-level caches', 'correct' => true],
                    ['text' => 'To store public assets that should be served directly by the web server', 'correct' => false],
                    ['text' => 'To store shared vendor dependencies between multiple applications', 'correct' => false],
                    ['text' => 'To store user-uploaded files that need to persist across deployments', 'correct' => false],
                ],
            ],

            // Q15 - Validation - Video constraint
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Validation'),
                'text' => '<p>Symfony 7.4 introduces a new validation constraint for files. Which type of files does this new constraint specifically target?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 7.4 introduces the Video constraint to validate video files. It provides options to control dimensions, codecs, and formats, similar to the existing Image constraint for image files.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-video-constraint',
                'symfonyVersion' => '7.4/8.0',
                'answers' => [
                    ['text' => 'Video files (with options for dimensions, codecs, and formats)', 'correct' => true],
                    ['text' => 'Audio files (with options for bitrate and sample rate)', 'correct' => false],
                    ['text' => 'PDF documents (with options for pages and size)', 'correct' => false],
                    ['text' => 'Archive files (ZIP, TAR with content validation)', 'correct' => false],
                ],
            ],
        ];
    }
}
