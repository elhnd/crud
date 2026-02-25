<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Symfony 8 Certification Exam Questions (Questions 226-300)
 * Deeper UPGRADE-8.0 coverage: Workflow, VarExporter, HttpClient, Mailer, Mime,
 * HttpFoundation advanced, Security advanced, FrameworkBundle, DomCrawler, BrowserKit, Yaml, HtmlSanitizer
 * Sources: UPGRADE-8.0.md + CHANGE-LOG.md
 */
class CertificationExamFixtures5 extends Fixture implements FixtureGroupInterface
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
            // SECTION 1: WORKFLOW (UPGRADE-8.0)
            // =====================================================

            // QUESTION 226 - Workflow: getEnabledTransition() on WorkflowInterface
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Symfony 8 added <code>getEnabledTransition()</code> to <code>WorkflowInterface</code>. What does this method return?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'getEnabledTransition() returns a single enabled transition by name. Unlike getEnabledTransitions() which returns all enabled transitions, this method targets a specific transition name and returns it if it\'s enabled, or null otherwise.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'A single enabled Transition object for a given name, or null.', 'correct' => true],
                    ['text' => 'An array of all enabled transitions.', 'correct' => false],
                    ['text' => 'A boolean indicating if transitions are enabled.', 'correct' => false],
                    ['text' => 'The last applied transition.', 'correct' => false],
                ],
            ],

            // QUESTION 227 - Workflow: Marking::mark/unmark $nbToken
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'In Symfony 8, the <code>Marking::mark()</code> and <code>Marking::unmark()</code> methods received a new parameter. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 8 added the $nbToken argument to Marking::mark() and Marking::unmark(). This supports weighted transitions in Petri net workflows, where moving a token can consume or produce multiple tokens at a place.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '$nbToken — to support weighted transitions (Petri nets).', 'correct' => true],
                    ['text' => '$force — to forcibly mark/unmark regardless of guards.', 'correct' => false],
                    ['text' => '$context — to pass metadata with the marking change.', 'correct' => false],
                    ['text' => '$user — to track which user triggered the marking.', 'correct' => false],
                ],
            ],

            // QUESTION 228 - Workflow: BackedEnum in MethodMarkingStore
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Symfony 8 enhanced the <code>MethodMarkingStore</code> for Workflows. What new type support was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added support for BackedEnum in MethodMarkingStore. You can now use a BackedEnum to represent workflow places, making the marking type-safe.',
                'resourceUrl' => 'https://symfony.com/doc/current/workflow.html',
                'answers' => [
                    ['text' => 'BackedEnum support — workflow places can be represented by enum cases.', 'correct' => true],
                    ['text' => 'UnitEnum support — any enum can represent a place.', 'correct' => false],
                    ['text' => 'Integer places — places can be numeric instead of strings.', 'correct' => false],
                    ['text' => 'Array places — multiple states per marking.', 'correct' => false],
                ],
            ],

            // QUESTION 229 - Workflow: glob patterns for places
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'Symfony 8 added a new feature for configuring workflow places in FrameworkBundle. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 8 allows configuring workflow places with glob patterns matching class constants or backed enum cases. This avoids manually listing every place and keeps the workflow configuration in sync with the code.',
                'resourceUrl' => 'https://symfony.com/doc/current/workflow.html',
                'answers' => [
                    ['text' => 'Glob patterns matching class constants and backed enums for place definitions.', 'correct' => true],
                    ['text' => 'Regular expressions for matching transition names.', 'correct' => false],
                    ['text' => 'Wildcard imports for place configuration from YAML files.', 'correct' => false],
                    ['text' => 'Automatic place discovery from entity properties.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 2: VAREXPORTER / LAZY OBJECTS (UPGRADE-8.0)
            // =====================================================

            // QUESTION 230 - ProxyHelper restricted to abstraction-based lazy decorators
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'In Symfony 8, <code>ProxyHelper::generateLazyProxy()</code> was restricted. What change was made?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, ProxyHelper::generateLazyProxy() is restricted to generating abstraction-based lazy decorators only (i.e., proxies based on interfaces or abstract classes). For concrete classes, PHP 8.4 native lazy objects should be used instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'It only generates abstraction-based (interface/abstract class) lazy decorators; use native lazy objects for concrete classes.', 'correct' => true],
                    ['text' => 'It no longer supports any proxy generation.', 'correct' => false],
                    ['text' => 'It only works with final classes now.', 'correct' => false],
                    ['text' => 'It requires an explicit proxy interface parameter.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 3: HTTPCLIENT (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 231 - setLogger() removed on decorators
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'In Symfony 8, <code>setLogger()</code> was removed from HttpClient decorator classes. How should logging be configured?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, setLogger() methods were removed from decorator classes (like RetryableHttpClient, ScopingHttpClient, etc.). The logger must be configured directly on the wrapped/base client instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Configure the logger directly on the wrapped/base client, not on decorators.', 'correct' => true],
                    ['text' => 'Use monolog channels instead of setLogger().', 'correct' => false],
                    ['text' => 'Logging is now always enabled by default.', 'correct' => false],
                    ['text' => 'Use the #[AsHttpClientLogger] attribute on a service.', 'correct' => false],
                ],
            ],

            // QUESTION 232 - CachingHttpClient uses TagAwareCacheInterface
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'In Symfony 8, the <code>CachingHttpClient</code> constructor changed. What must be used as the <code>$cache</code> argument?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, passing a StoreInterface instance as $cache to CachingHttpClient is no longer supported. A TagAwareCacheInterface must be used instead, aligning HTTP caching with RFC 9111.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'A TagAwareCacheInterface — StoreInterface is no longer supported.', 'correct' => true],
                    ['text' => 'A FilesystemAdapter — only filesystem caching is supported.', 'correct' => false],
                    ['text' => 'A PSR-6 CacheItemPoolInterface.', 'correct' => false],
                    ['text' => 'Any PSR-16 SimpleCacheInterface.', 'correct' => false],
                ],
            ],

            // QUESTION 233 - auto_upgrade_http_version option (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'Symfony 8 added a new option to <code>HttplugClient</code> and <code>Psr18Client</code>. What does <code>auto_upgrade_http_version</code> do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The auto_upgrade_http_version option controls how the request HTTP version is handled in HttplugClient and Psr18Client. It can automatically upgrade HTTP/1.1 requests to HTTP/2 when the server supports it.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_client.html',
                'answers' => [
                    ['text' => 'Controls automatic HTTP version upgrades (e.g., HTTP/1.1 to HTTP/2) based on server support.', 'correct' => true],
                    ['text' => 'Forces all requests to use HTTP/3.', 'correct' => false],
                    ['text' => 'Downgrades HTTP/2 requests to HTTP/1.1 for compatibility.', 'correct' => false],
                    ['text' => 'Enables HTTP version negotiation via ALPN.', 'correct' => false],
                ],
            ],

            // QUESTION 234 - QUERY method retriable (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'],
                'text' => 'In Symfony 8, the <code>QUERY</code> HTTP method was added to which list in HttpClient?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added QUERY to the list of retriable HTTP methods in the HttpClient component. Since QUERY is safe and idempotent (like GET), it can be safely retried on failure.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_client.html',
                'answers' => [
                    ['text' => 'The list of retriable HTTP methods — QUERY can be retried on failure.', 'correct' => true],
                    ['text' => 'The list of cacheable HTTP methods.', 'correct' => false],
                    ['text' => 'The list of methods that support request body.', 'correct' => false],
                    ['text' => 'The list of methods requiring authentication.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 4: HTTPFOUNDATION ADVANCED (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 235 - Request::sendHeaders() after headers sent
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'In Symfony 8, what happens when you call <code>Request::sendHeaders()</code> after HTTP headers have already been sent?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, calling Request::sendHeaders() after headers have already been sent triggers a PHP warning. You should use StreamedResponse instead to handle headers before body output.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'A PHP warning is triggered — use StreamedResponse instead.', 'correct' => true],
                    ['text' => 'Headers are silently ignored.', 'correct' => false],
                    ['text' => 'An HttpException is thrown.', 'correct' => false],
                    ['text' => 'Headers are appended as trailers.', 'correct' => false],
                ],
            ],

            // QUESTION 236 - IpUtils::anonymize() new args
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'In Symfony 8, <code>IpUtils::anonymize()</code> gained new parameters. What do <code>$v4Bytes</code> and <code>$v6Bytes</code> control?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The $v4Bytes and $v6Bytes arguments allow controlling how many bytes of IPv4 and IPv6 addresses are anonymized. This gives finer control over the privacy-vs-geolocation trade-off.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'The number of bytes to anonymize in IPv4 and IPv6 addresses respectively.', 'correct' => true],
                    ['text' => 'The number of bytes to keep in the anonymized addresses.', 'correct' => false],
                    ['text' => 'The encryption key lengths for IP anonymization.', 'correct' => false],
                    ['text' => 'The maximum IP address lengths accepted.', 'correct' => false],
                ],
            ],

            // QUESTION 237 - #[IsSignatureValid] attribute (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Symfony 8 introduced the <code>#[IsSignatureValid]</code> attribute in HttpFoundation. What is its purpose?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The #[IsSignatureValid] attribute validates that a request has a valid signature (from UriSigner). It can be placed on controller methods to automatically reject requests with invalid or expired signed URLs.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_foundation.html',
                'answers' => [
                    ['text' => 'Validates that the request URL has a valid cryptographic signature from UriSigner.', 'correct' => true],
                    ['text' => 'Validates the request body against a JSON schema.', 'correct' => false],
                    ['text' => 'Validates HMAC signatures in API requests.', 'correct' => false],
                    ['text' => 'Validates digital signatures on uploaded files.', 'correct' => false],
                ],
            ],

            // QUESTION 238 - Structured MIME suffix support (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Symfony 8 added support for structured MIME suffixes in HttpFoundation. What does this enable?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Structured MIME suffixes (like +json, +xml) allow the framework to recognize that application/vnd.api+json is JSON-based, enabling proper content negotiation and format detection.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_foundation.html',
                'answers' => [
                    ['text' => 'Recognition of types like application/vnd.api+json as JSON-based for content negotiation.', 'correct' => true],
                    ['text' => 'Support for compound MIME types with multiple suffixes.', 'correct' => false],
                    ['text' => 'Automatic file extension detection from MIME types.', 'correct' => false],
                    ['text' => 'Custom MIME type registration.', 'correct' => false],
                ],
            ],

            // QUESTION 239 - UriSigner $expiration parameter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Symfony 8 added the <code>$expiration</code> parameter to <code>UriSigner::sign()</code>. What does it do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The $expiration parameter allows setting a time limit on signed URLs. Once the expiration time passes, the signed URL is no longer valid, even if the signature itself is correct.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Sets a time limit after which the signed URL becomes invalid.', 'correct' => true],
                    ['text' => 'Sets the maximum number of times the URL can be used.', 'correct' => false],
                    ['text' => 'Sets the caching TTL for the signed response.', 'correct' => false],
                    ['text' => 'Sets the signature algorithm expiry date.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 5: SECURITY ADVANCED (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 240 - AccessDecisionStrategyInterface::decide() new $accessDecision arg
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony 8, the <code>AccessDecisionStrategyInterface::decide()</code> method gained a new parameter. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, an $accessDecision argument was added to AccessDecisionStrategyInterface::decide(). This object collects detailed information about the decision, including individual voter votes, enabling audit trails and debugging.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '$accessDecision — collects detailed decision info including individual voter votes.', 'correct' => true],
                    ['text' => '$context — provides the request context to voters.', 'correct' => false],
                    ['text' => '$priority — sets the decision strategy priority.', 'correct' => false],
                    ['text' => '$fallback — defines a fallback decision when no voter votes.', 'correct' => false],
                ],
            ],

            // QUESTION 241 - VoterInterface::vote() new $vote arg
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony 8, <code>VoterInterface::vote()</code> received a new <code>$vote</code> argument. What is its purpose?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, VoterInterface::vote() gained a $vote argument. This Vote object captures the voter\'s decision (granted/denied/abstain) along with metadata, enabling detailed access decision audits.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'A Vote object that captures the voter decision with metadata for auditing.', 'correct' => true],
                    ['text' => 'A boolean indicating if the vote should be counted.', 'correct' => false],
                    ['text' => 'An integer weight for the vote.', 'correct' => false],
                    ['text' => 'A callback for lazy vote evaluation.', 'correct' => false],
                ],
            ],

            // QUESTION 242 - RememberMeDetails constructor change
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony 8, the <code>RememberMeDetails</code> constructor was modified. What parameter was removed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the $userFqcn parameter was removed from RememberMeDetails constructor. The user FQCN is no longer stored in the remember-me cookie for security reasons.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '$userFqcn — the user class name is no longer stored in the remember-me cookie.', 'correct' => true],
                    ['text' => '$secret — remember-me tokens no longer use a secret.', 'correct' => false],
                    ['text' => '$expires — expiration is now handled externally.', 'correct' => false],
                    ['text' => '$value — the token value is auto-generated.', 'correct' => false],
                ],
            ],

            // QUESTION 243 - OIDC: algorithms and keyset replace algorithm and key
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony 8, the OIDC token handler configuration changed. What happened to the <code>algorithm</code> and <code>key</code> options?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the singular "algorithm" and "key" options were replaced by "algorithms" (array) and "keyset". This supports multiple signing algorithms and key rotation.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Replaced by "algorithms" (array) and "keyset" to support multiple algorithms and key rotation.', 'correct' => true],
                    ['text' => 'Merged into a single "oidc_signing" option.', 'correct' => false],
                    ['text' => 'Moved to environment variables only.', 'correct' => false],
                    ['text' => 'Removed entirely — OIDC now uses auto-discovery only.', 'correct' => false],
                ],
            ],

            // QUESTION 244 - Union type for #[CurrentUser] (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Symfony 8 improved the <code>#[CurrentUser]</code> attribute. What type support was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added support for union types on the #[CurrentUser] attribute. This allows type-hinting a parameter as MyUser|null to handle cases where no user is authenticated.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html',
                'answers' => [
                    ['text' => 'Union type support — e.g., MyUser|null to handle unauthenticated requests.', 'correct' => true],
                    ['text' => 'Interface type support — e.g., UserInterface.', 'correct' => false],
                    ['text' => 'Array type support — to inject multiple users.', 'correct' => false],
                    ['text' => 'Intersection type support — e.g., AdminInterface&UserInterface.', 'correct' => false],
                ],
            ],

            // QUESTION 245 - security:oidc-token:generate command (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Symfony 8 added a new security-related console command. What is <code>security:oidc-token:generate</code> used for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The security:oidc-token:generate command generates OIDC (OpenID Connect) tokens for testing purposes. This allows developers to test OIDC authentication workflows locally without a real identity provider.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html',
                'answers' => [
                    ['text' => 'Generates OIDC tokens for local testing without a real identity provider.', 'correct' => true],
                    ['text' => 'Generates API tokens for production use.', 'correct' => false],
                    ['text' => 'Generates JWT tokens for stateless authentication.', 'correct' => false],
                    ['text' => 'Generates OAuth2 client credentials.', 'correct' => false],
                ],
            ],

            // QUESTION 246 - Role hierarchy mermaid dump (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Symfony 8 added a new visualization feature for the Security component. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 can dump the role hierarchy as a Mermaid chart. This provides a visual representation of how roles are inherited, useful for documentation and debugging.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html',
                'answers' => [
                    ['text' => 'Dumping the role hierarchy as a Mermaid chart for visual representation.', 'correct' => true],
                    ['text' => 'A web-based role hierarchy editor in the profiler.', 'correct' => false],
                    ['text' => 'An ASCII art representation of the role hierarchy.', 'correct' => false],
                    ['text' => 'A JSON export of all user roles.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 6: FRAMEWORKBUNDLE (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 247 - ControllerHelper service (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Controllers'],
                'text' => 'Symfony 8 introduced <code>ControllerHelper</code>. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ControllerHelper provides the same helper methods as AbstractController (like render(), redirect(), json(), etc.) but as a standalone injectable service. This allows using controller helpers in invokable controllers or services that don\'t extend AbstractController.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller.html',
                'answers' => [
                    ['text' => 'A standalone injectable service providing the same helpers as AbstractController.', 'correct' => true],
                    ['text' => 'A trait to add controller functionality to any class.', 'correct' => false],
                    ['text' => 'A base class replacing AbstractController.', 'correct' => false],
                    ['text' => 'A Twig extension for rendering controller output.', 'correct' => false],
                ],
            ],

            // QUESTION 248 - Classes made final in FrameworkBundle
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'In Symfony 8, several cache warmer classes in FrameworkBundle were made <code>final</code>. Which of the following is true?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, ConfigBuilderCacheWarmer, TranslationsCacheWarmer, and ValidatorCacheWarmer were all made final. These classes can no longer be extended — use decoration if you need custom behavior.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'ConfigBuilderCacheWarmer is final.', 'correct' => true],
                    ['text' => 'TranslationsCacheWarmer is final.', 'correct' => true],
                    ['text' => 'ValidatorCacheWarmer is final.', 'correct' => true],
                    ['text' => 'KernelCacheWarmer is final.', 'correct' => false],
                    ['text' => 'AnnotationCacheWarmer is final.', 'correct' => false],
                ],
            ],

            // QUESTION 249 - Session config options removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'In Symfony 8, which session configuration options were removed from NativeSessionStorage?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Several deprecated session options were removed in Symfony 8: referer_check, use_only_cookies, use_trans_sid, sid_length, sid_bits_per_character, trans_sid_hosts, and trans_sid_tags. Modern PHP defaults make these unnecessary.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'sid_length and sid_bits_per_character', 'correct' => true],
                    ['text' => 'use_trans_sid and trans_sid_hosts', 'correct' => true],
                    ['text' => 'referer_check and use_only_cookies', 'correct' => true],
                    ['text' => 'save_path and gc_maxlifetime', 'correct' => false],
                    ['text' => 'cookie_lifetime and cookie_httponly', 'correct' => false],
                ],
            ],

            // QUESTION 250 - Auto-generate config/reference.php (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'Symfony 8 added a new feature for discovering application configuration. What is <code>config/reference.php</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 can auto-generate a config/reference.php file that documents all available configuration options for your application. This assists developers in writing correct configuration.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration.html',
                'answers' => [
                    ['text' => 'An auto-generated file documenting all available configuration options for the application.', 'correct' => true],
                    ['text' => 'A runtime configuration validation file.', 'correct' => false],
                    ['text' => 'A configuration file for cross-referencing services.', 'correct' => false],
                    ['text' => 'A compiled configuration cache file.', 'correct' => false],
                ],
            ],

            // QUESTION 251 - ServicesResetter final
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'In Symfony 8, <code>ServicesResetter</code> was made <code>final</code>. What does ServicesResetter do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'ServicesResetter resets services between requests when using long-running processes (e.g., with FrankenPHP worker mode). Making it final prevents custom subclasses — use service decoration instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Resets stateful services between requests in long-running processes.', 'correct' => true],
                    ['text' => 'Clears all caches when deploying a new version.', 'correct' => false],
                    ['text' => 'Removes unused services from the container.', 'correct' => false],
                    ['text' => 'Restarts the service container periodically.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 7: DOMCRAWLER & BROWSERCRAWLER (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 252 - Native HTML5 parser unconditional
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Testing'],
                'text' => 'In Symfony 8, how does the DomCrawler handle HTML parsing?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, both DomCrawler and BrowserKit use the native HTML5 parser unconditionally. The useHtml5Parser() method was removed from AbstractBrowser and the $useHtml5Parser constructor argument was removed from Crawler. PHP 8.4 includes a native HTML5 parser (Dom\\HTMLDocument).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'The native HTML5 parser (PHP 8.4) is always used — useHtml5Parser() and $useHtml5Parser were removed.', 'correct' => true],
                    ['text' => 'The Masterminds HTML5 parser is still the default.', 'correct' => false],
                    ['text' => 'HTML5 parsing is optional and must be enabled.', 'correct' => false],
                    ['text' => 'Only DomCrawler uses HTML5 parsing, BrowserKit does not.', 'correct' => false],
                ],
            ],

            // QUESTION 253 - BrowserKit PHPUnit constraints (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Testing'],
                'text' => 'Symfony 8 added new PHPUnit constraints for BrowserKit. Which were added?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added BrowserHistoryIsOnFirstPage and BrowserHistoryIsOnLastPage PHPUnit constraints, plus isFirstPage() and isLastPage() methods on History. These help assert navigation state in browser tests.',
                'resourceUrl' => 'https://symfony.com/doc/current/testing.html',
                'answers' => [
                    ['text' => 'BrowserHistoryIsOnFirstPage', 'correct' => true],
                    ['text' => 'BrowserHistoryIsOnLastPage', 'correct' => true],
                    ['text' => 'BrowserHistoryHasRedirect', 'correct' => false],
                    ['text' => 'BrowserHistoryHasCookie', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 8: MAILER (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 254 - assertEmailAddressNotContains (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mailer'],
                'text' => 'Symfony 8 added a new mailer assertion for testing. What is <code>assertEmailAddressNotContains</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'assertEmailAddressNotContains is a new testing assertion that verifies an email address is NOT present in a specific header (to, cc, bcc, from) of a sent email. It complements the existing assertEmailAddressContains.',
                'resourceUrl' => 'https://symfony.com/doc/current/mailer.html',
                'answers' => [
                    ['text' => 'Asserts that a specific email address is NOT present in a header (to, cc, bcc, from).', 'correct' => true],
                    ['text' => 'Asserts that the email body does not contain a specific address.', 'correct' => false],
                    ['text' => 'Asserts that the email was not sent to a specific address.', 'correct' => false],
                    ['text' => 'Asserts that an email address is not valid.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 9: MESSENGER ADVANCED (CHANGELOG)
            // =====================================================

            // QUESTION 255 - Signing messages per handler (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Symfony 8 added a new security feature to the Messenger component. What is "message signing per handler"?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 8 supports signing messages on a per-handler basis. This allows cryptographic signing of messages dispatched to specific transports, ensuring message integrity and preventing tampering.',
                'resourceUrl' => 'https://symfony.com/doc/current/messenger.html',
                'answers' => [
                    ['text' => 'Cryptographic signing of messages for specific handlers to ensure integrity.', 'correct' => true],
                    ['text' => 'Assigning a handler signature for logging purposes.', 'correct' => false],
                    ['text' => 'Adding a digital signature to message envelopes for routing.', 'correct' => false],
                    ['text' => 'Encrypting message payloads per handler.', 'correct' => false],
                ],
            ],

            // QUESTION 256 - DefaultStampsProviderInterface (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Symfony 8 introduced <code>DefaultStampsProviderInterface</code> in Messenger. What does it do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'DefaultStampsProviderInterface allows defining default stamps that should be automatically added to all messages. This avoids repetitive stamp addition when dispatching messages.',
                'resourceUrl' => 'https://symfony.com/doc/current/messenger.html',
                'answers' => [
                    ['text' => 'Defines default stamps automatically added to all dispatched messages.', 'correct' => true],
                    ['text' => 'Provides a list of valid stamp types for validation.', 'correct' => false],
                    ['text' => 'Creates custom stamp classes at runtime.', 'correct' => false],
                    ['text' => 'Removes default stamps from incoming messages.', 'correct' => false],
                ],
            ],

            // QUESTION 257 - SQS retry/DLQ handling (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Symfony 8 improved Amazon SQS transport handling. What new capability was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 allows SQS to handle its own retry and dead-letter queue (DLQ) logic. Instead of relying solely on Symfony\'s retry mechanism, the native SQS retry and DLQ features can be used.',
                'resourceUrl' => 'https://symfony.com/doc/current/messenger.html',
                'answers' => [
                    ['text' => 'SQS can handle its own retry/DLQ natively instead of using Symfony retry only.', 'correct' => true],
                    ['text' => 'SQS messages are automatically compressed for better performance.', 'correct' => false],
                    ['text' => 'SQS FIFO queues are now supported.', 'correct' => false],
                    ['text' => 'SQS batch message processing was added.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 10: FORM ADVANCED (CHANGELOG)
            // =====================================================

            // QUESTION 258 - CurrencyType new options (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Symfony 8 added new options to <code>CurrencyType</code>. Which ones?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added active_at, not_active_at, and legal_tender options to CurrencyType. These allow filtering currencies based on their activity status and legal tender status.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/forms/types/currency.html',
                'answers' => [
                    ['text' => 'active_at — filter currencies active at a specific date.', 'correct' => true],
                    ['text' => 'not_active_at — exclude currencies active at a specific date.', 'correct' => true],
                    ['text' => 'legal_tender — filter by legal tender status.', 'correct' => true],
                    ['text' => 'exchange_rate — display with current exchange rate.', 'correct' => false],
                ],
            ],

            // QUESTION 259 - input=date_point for DateType/TimeType/DateTimeType (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Symfony 8 added a new <code>input</code> option value for <code>DateTimeType</code>, <code>DateType</code>, and <code>TimeType</code>. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added input=\'date_point\' to DateTimeType, DateType, and TimeType. DatePoint is Symfony Clock\'s immutable date class, providing better integration with the Clock component.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/forms/types/date.html',
                'answers' => [
                    ['text' => 'date_point — to use Symfony Clock\'s DatePoint class as the model data.', 'correct' => true],
                    ['text' => 'carbon — to use Carbon date library objects.', 'correct' => false],
                    ['text' => 'timestamp_ms — to use millisecond timestamps.', 'correct' => false],
                    ['text' => 'iso8601 — to use ISO 8601 formatted strings.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 11: CONSOLE ADVANCED (CHANGELOG)
            // =====================================================

            // QUESTION 260 - Cursor helper in invokable commands
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Symfony 8 added support for a new helper in invokable commands. What is the <code>Cursor</code> helper?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Cursor helper allows moving the cursor position in the terminal output. In Symfony 8, it can be injected directly into invokable command methods for fine-grained output control.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html',
                'answers' => [
                    ['text' => 'A helper for controlling cursor position in terminal output, injectable in invokable commands.', 'correct' => true],
                    ['text' => 'A helper for navigating through paginated console output.', 'correct' => false],
                    ['text' => 'A helper for reading character-by-character input.', 'correct' => false],
                    ['text' => 'A helper for creating progress cursors.', 'correct' => false],
                ],
            ],

            // QUESTION 261 - QuestionHelper timeout (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Symfony 8 added a timeout feature to <code>QuestionHelper</code>. What does it do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added an optional timeout for human interaction in QuestionHelper. If the user doesn\'t respond within the specified time, the question can use a default value or throw an exception.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/console/helpers/questionhelper.html',
                'answers' => [
                    ['text' => 'Sets a maximum wait time for user input, after which a default value is used.', 'correct' => true],
                    ['text' => 'Limits the total time a command can run.', 'correct' => false],
                    ['text' => 'Sets a delay between displaying question options.', 'correct' => false],
                    ['text' => 'Timeouts the validation of user input.', 'correct' => false],
                ],
            ],

            // QUESTION 262 - Usage via #[AsCommand] (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Symfony 8 enhanced the <code>#[AsCommand]</code> attribute. What new feature was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 allows specifying command usage examples directly in the #[AsCommand] attribute. This removes the need to override the configure() method just to set usage information.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html',
                'answers' => [
                    ['text' => 'Usage examples can be specified directly in the #[AsCommand] attribute.', 'correct' => true],
                    ['text' => 'The attribute now supports specifying input definitions.', 'correct' => false],
                    ['text' => 'The attribute can define help text in multiple languages.', 'correct' => false],
                    ['text' => 'The attribute now supports command groups.', 'correct' => false],
                ],
            ],

            // QUESTION 263 - Invokable commands in CommandTester (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Testing'],
                'text' => 'Symfony 8 improved <code>CommandTester</code>. What new support was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added support for invokable commands in CommandTester. You can now test invokable commands directly without wrapping them in a traditional Command class first.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html',
                'answers' => [
                    ['text' => 'Direct testing of invokable commands without wrapping in traditional Command class.', 'correct' => true],
                    ['text' => 'Testing commands in parallel execution mode.', 'correct' => false],
                    ['text' => 'Mocking user input with predefined sequences.', 'correct' => false],
                    ['text' => 'Capturing ANSI-formatted output in assertions.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 12: SERIALIZER ADVANCED (CHANGELOG)
            // =====================================================

            // QUESTION 264 - XmlEncoder: preserving array keys (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Symfony 8 improved <code>XmlEncoder</code>. What new feature was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added support for preserving array keys when using XmlEncoder. Previously, array keys could be lost during XML encoding/decoding cycles.',
                'resourceUrl' => 'https://symfony.com/doc/current/serializer.html',
                'answers' => [
                    ['text' => 'Support for preserving array keys during XML encoding/decoding.', 'correct' => true],
                    ['text' => 'Support for nested CDATA sections.', 'correct' => false],
                    ['text' => 'Automatic namespace resolution.', 'correct' => false],
                    ['text' => 'Schema validation during encoding.', 'correct' => false],
                ],
            ],

            // QUESTION 265 - CDATA_WRAPPING_NAME_PATTERN (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Symfony 8 added <code>CDATA_WRAPPING_NAME_PATTERN</code> support to <code>XmlEncoder</code>. What does it control?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'CDATA_WRAPPING_NAME_PATTERN allows specifying a regex pattern to match XML element names that should have their content wrapped in CDATA sections during encoding.',
                'resourceUrl' => 'https://symfony.com/doc/current/serializer.html',
                'answers' => [
                    ['text' => 'A regex pattern to determine which XML elements should wrap their content in CDATA sections.', 'correct' => true],
                    ['text' => 'A pattern for naming CDATA sections in the output.', 'correct' => false],
                    ['text' => 'A validation pattern for CDATA content.', 'correct' => false],
                    ['text' => 'A pattern for stripping CDATA from input XML.', 'correct' => false],
                ],
            ],

            // QUESTION 266 - "can" accessor prefix (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Symfony 8 added a new accessor prefix to the Serializer\'s <code>AttributeLoader</code>. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added "can" to the recognized accessor prefixes in AttributeLoader. Along with "get", "is", and "has", the loader now recognizes "can" — e.g., canPublish() would be recognized as an accessor for the "publish" property.',
                'resourceUrl' => 'https://symfony.com/doc/current/serializer.html',
                'answers' => [
                    ['text' => '"can" — e.g., canPublish() maps to the "publish" property.', 'correct' => true],
                    ['text' => '"should" — e.g., shouldRender() maps to the "render" property.', 'correct' => false],
                    ['text' => '"does" — e.g., doesExist() maps to the "exist" property.', 'correct' => false],
                    ['text' => '"will" — e.g., willExpire() maps to the "expire" property.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 13: DI ADVANCED (CHANGELOG)
            // =====================================================

            // QUESTION 267 - Array-shape configs for PHP (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Symfony 8 improved PHP configuration files for DI and Routing. What is the "array-shapes" feature?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 8 defined array-shapes (PHPStan/Psalm style) to help write PHP configs using YAML-like arrays. This provides IDE autocompletion and validation for PHP service/route configurations.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html',
                'answers' => [
                    ['text' => 'PHPDoc array-shapes that enable IDE autocompletion for PHP config files written as YAML-like arrays.', 'correct' => true],
                    ['text' => 'A new array syntax that replaces YAML configuration.', 'correct' => false],
                    ['text' => 'JSON schema files for validating config arrays.', 'correct' => false],
                    ['text' => 'A config validation middleware for arrays.', 'correct' => false],
                ],
            ],

            // QUESTION 268 - Extending #[AsAlias] (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Symfony 8 allows extending the <code>#[AsAlias]</code> attribute. What benefit does this provide?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Extending #[AsAlias] allows creating custom attribute shortcuts for common alias patterns. You can define reusable aliases for your project\'s conventions.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/alias.html',
                'answers' => [
                    ['text' => 'Custom attribute shortcuts for common alias patterns can be created by extending #[AsAlias].', 'correct' => true],
                    ['text' => 'Multiple aliases can be defined on a single attribute.', 'correct' => false],
                    ['text' => 'Aliases can now be conditional based on the environment.', 'correct' => false],
                    ['text' => 'Aliases can reference services from other bundles.', 'correct' => false],
                ],
            ],

            // QUESTION 269 - #[Target] without suffix (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Symfony 8 simplified the use of <code>#[Target]</code> attribute. What changed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, when using #[Target] for custom services, you can now use just the service name without any added suffix. Previously, suffixes were sometimes required for disambiguation.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html',
                'answers' => [
                    ['text' => 'Services can be targeted by their name without requiring added suffixes.', 'correct' => true],
                    ['text' => '#[Target] can now target abstract services.', 'correct' => false],
                    ['text' => '#[Target] supports multiple service IDs.', 'correct' => false],
                    ['text' => '#[Target] works on constructor parameters only.', 'correct' => false],
                ],
            ],

            // QUESTION 270 - Parsing attributes on abstract classes (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Symfony 8 improved resource definitions for DI. What new behavior was added for abstract classes?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 8 parses attributes found on abstract classes for resource definitions. This means service tags, autowiring configuration, and other attributes on abstract classes are properly inherited by concrete implementations.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html',
                'answers' => [
                    ['text' => 'Attributes on abstract classes are parsed and applied to concrete implementations.', 'correct' => true],
                    ['text' => 'Abstract classes are automatically registered as services.', 'correct' => false],
                    ['text' => 'Abstract class methods are scanned for route definitions.', 'correct' => false],
                    ['text' => 'Abstract classes can declare #[AsDecorator] attributes.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 14: VALIDATOR ADVANCED (CHANGELOG)
            // =====================================================

            // QUESTION 271 - Url constraint ANY protocol option (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Symfony 8 added a new protocol option to the <code>Url</code> constraint. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added the ability to allow ANY protocol in the Url constraint. This accepts URLs like ftp://, ssh://, custom://, etc., not just http and https.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/Url.html',
                'answers' => [
                    ['text' => 'An option to accept ANY protocol scheme, not just http/https.', 'correct' => true],
                    ['text' => 'An option to require a specific custom protocol.', 'correct' => false],
                    ['text' => 'An option to validate the protocol against a whitelist.', 'correct' => false],
                    ['text' => 'An option to strip the protocol before validation.', 'correct' => false],
                ],
            ],

            // QUESTION 272 - LengthValidator min/max in error messages (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Symfony 8 improved error messages in <code>LengthValidator</code>. What data is now included?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 8 includes both "min" and "max" values in both the "too short" and "too long" error messages of LengthValidator. Previously, only the relevant bound was shown.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/Length.html',
                'answers' => [
                    ['text' => 'Both min and max values are now included in both error messages.', 'correct' => true],
                    ['text' => 'The actual string length is now included.', 'correct' => false],
                    ['text' => 'A suggested valid value is now included.', 'correct' => false],
                    ['text' => 'The character encoding is now included.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 15: TWIG ADVANCED (CHANGELOG)
            // =====================================================

            // QUESTION 273 - aria-invalid and aria-describedby on form errors
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Symfony 8 improved Twig form rendering for accessibility. What attributes are now automatically added?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 automatically adds aria-invalid and aria-describedby attributes to form inputs when validation errors exist. This improves accessibility for screen readers.',
                'resourceUrl' => 'https://symfony.com/doc/current/form/form_themes.html',
                'answers' => [
                    ['text' => 'aria-invalid — marks the input as invalid.', 'correct' => true],
                    ['text' => 'aria-describedby — links the input to its error message.', 'correct' => true],
                    ['text' => 'aria-required — marks mandatory inputs.', 'correct' => false],
                    ['text' => 'aria-label — describes the input purpose.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 16: MIME (UPGRADE-8.0)
            // =====================================================

            // QUESTION 274 - Mime: __sleep/wakeup replaced
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Miscellaneous'],
                'text' => 'In Symfony 8, the <code>Mime</code> component changed its serialization approach. What was updated?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, __sleep()/__wakeup() were replaced by __serialize()/__unserialize() on AbstractPart implementations in the Mime component. This aligns with modern PHP serialization practices.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '__sleep/__wakeup replaced by __serialize/__unserialize on AbstractPart implementations.', 'correct' => true],
                    ['text' => 'JSON serialization replaced binary serialization.', 'correct' => false],
                    ['text' => 'Serialization was completely removed from MIME parts.', 'correct' => false],
                    ['text' => 'igbinary replaced the default PHP serializer.', 'correct' => false],
                ],
            ],

            // QUESTION 275 - HtmlSanitizer: NativeParser replaces MastermindsParser
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Miscellaneous'],
                'text' => 'In Symfony 8, the <code>HtmlSanitizer</code> component changed its parser. What happened?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'MastermindsParser was removed in Symfony 8. NativeParser is now used instead, leveraging PHP 8.4\'s built-in HTML5 parser (Dom\\HTMLDocument). The ParserInterface::parse() method also gained a $context argument.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'MastermindsParser removed; NativeParser using PHP 8.4\'s built-in HTML5 parser is used instead.', 'correct' => true],
                    ['text' => 'A new third-party parser library is required.', 'correct' => false],
                    ['text' => 'The HtmlSanitizer no longer parses HTML — it works on DOM objects.', 'correct' => false],
                    ['text' => 'MastermindsParser was made optional.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 17: MIXED UPGRADE TOPICS
            // =====================================================

            // QUESTION 276 - Yaml: duplicate keys error
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'In Symfony 8, the Yaml parser behavior changed for duplicate keys. What is the new behavior?
<pre><code class="language-yaml">parameters:
    app.name: MyApp
    app.name: ~  # duplicate key with null</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, duplicate mapping keys (even with null values) raise a parse error. Previously, duplicate keys with null values were silently ignored.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'A parse error is raised — duplicate keys are no longer allowed.', 'correct' => true],
                    ['text' => 'The last value wins silently.', 'correct' => false],
                    ['text' => 'Duplicate keys create an array of values.', 'correct' => false],
                    ['text' => 'A deprecation warning is issued but parsing continues.', 'correct' => false],
                ],
            ],

            // QUESTION 277 - WebProfilerBundle: XML routing removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'In Symfony 8, which routing configuration files were removed from WebProfilerBundle?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, profiler.xml and wdt.xml routing configuration files were removed from WebProfilerBundle. Their PHP equivalents should be used instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'profiler.xml and wdt.xml — use their PHP equivalents instead.', 'correct' => true],
                    ['text' => 'routing.xml and config.xml.', 'correct' => false],
                    ['text' => 'services.xml and parameters.xml.', 'correct' => false],
                    ['text' => 'All routing files — routes are now auto-discovered.', 'correct' => false],
                ],
            ],

            // QUESTION 278 - Routing: request_context _locale initialization (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Symfony 8 improved the <code>router.request_context</code> service. What new parameter initialization was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 initializes the router.request_context\'s _locale parameter to kernel.default_locale. This ensures URL generation respects the application\'s default locale even before a request is received.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html',
                'answers' => [
                    ['text' => '_locale is initialized to kernel.default_locale for proper URL generation.', 'correct' => true],
                    ['text' => '_format is initialized to \'html\' by default.', 'correct' => false],
                    ['text' => '_controller is initialized to the default controller.', 'correct' => false],
                    ['text' => 'base_url is initialized from the server configuration.', 'correct' => false],
                ],
            ],

            // QUESTION 279 - Config: acceptAndWrap() (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'Symfony 8 added <code>ArrayNodeDefinition::acceptAndWrap()</code>. What does this method do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'acceptAndWrap() allows an array node to accept alternative types (like strings or integers) and automatically wrap them into an array. This enables shorthand configuration syntax.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/config/definition.html',
                'answers' => [
                    ['text' => 'Accepts alternative non-array types and wraps them into an array automatically.', 'correct' => true],
                    ['text' => 'Accepts and sanitizes user-provided configuration values.', 'correct' => false],
                    ['text' => 'Wraps the node definition in a prototype for collections.', 'correct' => false],
                    ['text' => 'Accepts null values and wraps them in an empty array.', 'correct' => false],
                ],
            ],

            // QUESTION 280 - Union type on #[AsEventListener] (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Symfony 8 added union type support on <code>#[AsEventListener]</code>. What does this enable?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 supports union types on #[AsEventListener], allowing a single listener method to handle multiple event types. The listener will be registered for each event type in the union.',
                'resourceUrl' => 'https://symfony.com/doc/current/event_dispatcher.html',
                'answers' => [
                    ['text' => 'A listener method can handle multiple event types using a union type parameter.', 'correct' => true],
                    ['text' => 'Event listeners can return multiple response types.', 'correct' => false],
                    ['text' => 'Multiple listener attributes can be merged into a union.', 'correct' => false],
                    ['text' => 'Events can be dispatched to multiple listener queues.', 'correct' => false],
                ],
            ],

            // QUESTION 281 - Request::setAllowedHttpMethodOverride (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Symfony 8 added <code>Request::setAllowedHttpMethodOverride()</code> and <code>getAllowedHttpMethodOverride()</code>. What do they control?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'These methods allow fine-grained control over which HTTP methods can be overridden via the _method parameter. By default, only POST can be overridden, but this list can be customized.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_foundation.html',
                'answers' => [
                    ['text' => 'Control exactly which HTTP methods can be overridden via _method.', 'correct' => true],
                    ['text' => 'Control which HTTP methods are allowed on a specific route.', 'correct' => false],
                    ['text' => 'Override the HTTP method for outgoing responses.', 'correct' => false],
                    ['text' => 'Set API versioning based on HTTP method.', 'correct' => false],
                ],
            ],

            // QUESTION 282 - Ldap: saslBind() and whoami()
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Miscellaneous'],
                'text' => 'Symfony 8 added new methods to the LDAP component. Which were added?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added saslBind() and whoami() methods to ConnectionInterface and LdapInterface. saslBind() enables SASL authentication, and whoami() returns the current bound user DN.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'saslBind() — for SASL authentication.', 'correct' => true],
                    ['text' => 'whoami() — returns the current bound user DN.', 'correct' => true],
                    ['text' => 'search() — for performing LDAP searches.', 'correct' => false],
                    ['text' => 'unbind() — for closing the connection.', 'correct' => false],
                ],
            ],

            // QUESTION 283 - Routing: allow query-specific parameters with _query (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Symfony 8 improved URL generation with the <code>_query</code> parameter. What does it allow?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The _query parameter in UrlGenerator allows explicitly adding query string parameters when generating URLs. This separates query parameters from route parameters, avoiding confusion.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html',
                'answers' => [
                    ['text' => 'Explicitly adding query string parameters to generated URLs, separate from route parameters.', 'correct' => true],
                    ['text' => 'Filtering which query parameters are preserved from the current request.', 'correct' => false],
                    ['text' => 'Encoding query parameters in a specific format.', 'correct' => false],
                    ['text' => 'Validating query parameters against route requirements.', 'correct' => false],
                ],
            ],

            // QUESTION 284 - HttpCache: "waiting" trace (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Symfony 8 improved HttpCache tracing. What new trace was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added a "waiting" trace when the cache lock is found locked. This helps debug cache stampede situations where multiple requests wait for the same cache entry.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_cache.html',
                'answers' => [
                    ['text' => 'A "waiting" trace when the cache is locked (cache stampede detection).', 'correct' => true],
                    ['text' => 'A "stale" trace when serving expired content.', 'correct' => false],
                    ['text' => 'A "revalidate" trace when checking freshness.', 'correct' => false],
                    ['text' => 'A "bypass" trace when cache is skipped.', 'correct' => false],
                ],
            ],

            // QUESTION 285 - ProgressIndicator::finish() new arg
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'In Symfony 8, <code>ProgressIndicator::finish()</code> gained a new parameter. What is <code>$finishedIndicator</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The $finishedIndicator parameter allows specifying a custom character/string to display when the progress indicator finishes, replacing the default indicator.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'A custom character/string displayed when the indicator finishes.', 'correct' => true],
                    ['text' => 'A boolean to show/hide the finished state.', 'correct' => false],
                    ['text' => 'A callback to run when the indicator finishes.', 'correct' => false],
                    ['text' => 'A color code for the finished state.', 'correct' => false],
                ],
            ],

            // QUESTION 286 - APP_RUNTIME exposed in $_SERVER (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'Symfony 8 exposed the <code>APP_RUNTIME</code> variable. Where is it available?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 exposes the runtime class name in $_SERVER[\'APP_RUNTIME\']. This allows applications and libraries to detect which runtime is being used.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/runtime.html',
                'answers' => [
                    ['text' => 'In $_SERVER[\'APP_RUNTIME\'] — contains the runtime class name.', 'correct' => true],
                    ['text' => 'In $_ENV[\'APP_RUNTIME\'] only — available through getenv().', 'correct' => false],
                    ['text' => 'In the kernel parameters — accessible as %app.runtime%.', 'correct' => false],
                    ['text' => 'In the .env file — auto-configured during installation.', 'correct' => false],
                ],
            ],

            // QUESTION 287 - APP_RUNTIME_MODE for error renderer (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'Symfony 8 added <code>APP_RUNTIME_MODE</code> support. What does it control?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'APP_RUNTIME_MODE allows selecting the appropriate error renderer based on the runtime mode. For example, a CLI runtime might use a text error renderer while a web runtime uses an HTML renderer.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/runtime.html',
                'answers' => [
                    ['text' => 'Selects the appropriate error renderer based on the runtime context (web vs CLI).', 'correct' => true],
                    ['text' => 'Switches between development and production modes.', 'correct' => false],
                    ['text' => 'Controls the HTTP response caching behavior.', 'correct' => false],
                    ['text' => 'Determines which kernel class to boot.', 'correct' => false],
                ],
            ],

            // QUESTION 288 - Routing: RequestContext with parameters directly (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Symfony 8 simplified <code>RequestContext</code> creation. What new capability was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 allows creating a RequestContext with parameters directly in the constructor, instead of setting each parameter individually via setter methods.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html',
                'answers' => [
                    ['text' => 'Parameters can be passed directly to the RequestContext constructor.', 'correct' => true],
                    ['text' => 'RequestContext can be created from a Route object.', 'correct' => false],
                    ['text' => 'RequestContext supports builder pattern construction.', 'correct' => false],
                    ['text' => 'RequestContext can be serialized and restored.', 'correct' => false],
                ],
            ],

            // QUESTION 289 - Profiler toolbar: runner class display (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'Symfony 8 improved the web profiler toolbar. What new information is displayed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 8 displays the runner class (e.g., FrankenPHPRunner, SymfonyRunner) in the profiler toolbar, helping developers identify which runtime is serving the request.',
                'resourceUrl' => 'https://symfony.com/doc/current/profiler.html',
                'answers' => [
                    ['text' => 'The runner class name — identifying which runtime is serving the request.', 'correct' => true],
                    ['text' => 'The PHP SAPI name and version.', 'correct' => false],
                    ['text' => 'The web server software and version.', 'correct' => false],
                    ['text' => 'The HTTP protocol version used.', 'correct' => false],
                ],
            ],

            // QUESTION 290 - HttpCache: no-store prevents private cache-control
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Symfony 8 improved cache-control handling in HttpKernel. What changed when <code>no-store</code> is set?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 8 no longer overrides no-store with private cache-control. When a response has no-store, the kernel respects it and doesn\'t supersede it with a private directive.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_cache.html',
                'answers' => [
                    ['text' => 'The kernel no longer supersedes no-store with private cache-control.', 'correct' => true],
                    ['text' => 'no-store automatically implies no-cache.', 'correct' => false],
                    ['text' => 'no-store disables all HTTP caching headers.', 'correct' => false],
                    ['text' => 'no-store is now the default for all responses.', 'correct' => false],
                ],
            ],

            // QUESTION 291 - Profile class made final (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'In Symfony 8, the <code>Profile</code> class in HttpKernel was changed. What happened?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The Profile class was made final in Symfony 8. Custom Profile subclasses are no longer possible — use composition or decoration if you need to extend profiler functionality.',
                'resourceUrl' => 'https://symfony.com/doc/current/profiler.html',
                'answers' => [
                    ['text' => 'Profile was made final — it can no longer be extended.', 'correct' => true],
                    ['text' => 'Profile was deprecated in favor of ProfileData.', 'correct' => false],
                    ['text' => 'Profile now requires a constructor argument.', 'correct' => false],
                    ['text' => 'Profile was moved to the WebProfilerBundle.', 'correct' => false],
                ],
            ],

            // QUESTION 292 - VarDumper: HTML-only for Accept header (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Miscellaneous'],
                'text' => 'Symfony 8 changed how <code>HtmlDumper</code> is selected in VarDumper. What is the new behavior?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 only selects HtmlDumper when the Accept header contains "html". This prevents HTML dumps in API responses or non-HTML contexts.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/var_dumper.html',
                'answers' => [
                    ['text' => 'HtmlDumper is selected only when the Accept header contains "html".', 'correct' => true],
                    ['text' => 'HtmlDumper is always used regardless of the Accept header.', 'correct' => false],
                    ['text' => 'HtmlDumper is only used in the dev environment.', 'correct' => false],
                    ['text' => 'HtmlDumper is selected based on the Content-Type of the response.', 'correct' => false],
                ],
            ],

            // QUESTION 293 - Serializer AttributeMetadata/ClassMetadata final (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'In Symfony 8, <code>AttributeMetadata</code> and <code>ClassMetadata</code> in the Serializer were made <code>final</code>. What impact does this have?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Making AttributeMetadata and ClassMetadata final means they can no longer be subclassed. Custom metadata needs should be handled through the metadata factory or custom loaders instead.',
                'resourceUrl' => 'https://symfony.com/doc/current/serializer.html',
                'answers' => [
                    ['text' => 'They cannot be subclassed — use metadata factories or custom loaders for customization.', 'correct' => true],
                    ['text' => 'They are now immutable and cannot be modified after creation.', 'correct' => false],
                    ['text' => 'They are now singletons in the service container.', 'correct' => false],
                    ['text' => 'They are now lazy-loaded for performance.', 'correct' => false],
                ],
            ],

            // QUESTION 294 - Cache: TagAwareAdapterInterface on NullAdapter (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Symfony 8 improved <code>NullAdapter</code> in the Cache component. What was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 made NullAdapter implement TagAwareAdapterInterface. This allows NullAdapter to be used as a drop-in replacement for TagAwareAdapter in testing or development.',
                'resourceUrl' => 'https://symfony.com/doc/current/cache.html',
                'answers' => [
                    ['text' => 'NullAdapter implements TagAwareAdapterInterface — usable as a tag-aware adapter replacement.', 'correct' => true],
                    ['text' => 'NullAdapter now logs cache misses.', 'correct' => false],
                    ['text' => 'NullAdapter returns null instead of throwing on miss.', 'correct' => false],
                    ['text' => 'NullAdapter simulates cache hits for testing.', 'correct' => false],
                ],
            ],

            // QUESTION 295 - Symfony 8 requires PHP 8.4
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'What is the minimum PHP version required by Symfony 8?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 8 requires PHP 8.4 or higher. This allows Symfony to leverage new PHP 8.4 features like native lazy objects, property hooks, the new HTML5 parser, and more.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'PHP 8.4', 'correct' => true],
                    ['text' => 'PHP 8.3', 'correct' => false],
                    ['text' => 'PHP 8.2', 'correct' => false],
                    ['text' => 'PHP 8.1', 'correct' => false],
                ],
            ],

            // QUESTION 296 - Symfony 7.4 and 8.0 simultaneous release
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'What is the relationship between Symfony 7.4 and Symfony 8.0?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 7.4 and 8.0 are released simultaneously with the same features. The only difference is that Symfony 8.0 removes all deprecated features that were present in 7.x. Upgrading requires resolving all deprecation notices first.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'They share the same features, but 8.0 removes all deprecated code from 7.x.', 'correct' => true],
                    ['text' => '8.0 has entirely new features not found in 7.4.', 'correct' => false],
                    ['text' => '7.4 is the LTS version while 8.0 is the latest.', 'correct' => false],
                    ['text' => '8.0 is a complete rewrite of the framework.', 'correct' => false],
                ],
            ],

            // QUESTION 297 - AddAnnotatedClassesToCachePass removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'In Symfony 8, <code>AddAnnotatedClassesToCachePass</code> and related methods were removed. What were they used for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'AddAnnotatedClassesToCachePass was used to precompile annotated classes to opcache for performance. This is no longer needed with modern PHP opcache JIT and preloading features.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Precompiling annotated classes to opcache — no longer needed with modern PHP.', 'correct' => true],
                    ['text' => 'Compiling Twig templates to PHP classes.', 'correct' => false],
                    ['text' => 'Caching Doctrine entity metadata.', 'correct' => false],
                    ['text' => 'Caching route annotations for faster routing.', 'correct' => false],
                ],
            ],

            // QUESTION 298 - EventSource in debug toolbar (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'Symfony 8 improved the web profiler toolbar. What new type of requests is now displayed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 shows EventSource (Server-Sent Events) requests in the debug toolbar. This helps developers monitor and debug SSE connections alongside regular HTTP requests.',
                'resourceUrl' => 'https://symfony.com/doc/current/profiler.html',
                'answers' => [
                    ['text' => 'EventSource (Server-Sent Events) requests.', 'correct' => true],
                    ['text' => 'WebSocket connections.', 'correct' => false],
                    ['text' => 'GraphQL subscriptions.', 'correct' => false],
                    ['text' => 'Long-polling requests.', 'correct' => false],
                ],
            ],

            // QUESTION 299 - Functional test session preparation (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Testing'],
                'text' => 'Symfony 8 improved session handling in functional tests. What feature was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added the ability to prepare/pre-populate the session in functional tests before making a request. This simplifies testing scenarios that depend on session state.',
                'resourceUrl' => 'https://symfony.com/doc/current/testing.html',
                'answers' => [
                    ['text' => 'Pre-populating the session before making test requests.', 'correct' => true],
                    ['text' => 'Automatic session cleanup between test methods.', 'correct' => false],
                    ['text' => 'Session encryption in test environment.', 'correct' => false],
                    ['text' => 'Mock session handlers for unit testing.', 'correct' => false],
                ],
            ],

            // QUESTION 300 - debug:router relevant columns and colors (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Symfony 8 improved the <code>debug:router</code> command output. What changed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 8 shows only relevant columns and adds color-coded output to the debug:router command. This reduces clutter and improves readability.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html',
                'answers' => [
                    ['text' => 'Only relevant columns are shown and the output is color-coded.', 'correct' => true],
                    ['text' => 'Routes are now sorted by priority instead of name.', 'correct' => false],
                    ['text' => 'The command now supports JSON output format.', 'correct' => false],
                    ['text' => 'Routes with no controller are highlighted in red.', 'correct' => false],
                ],
            ],
        ];
    }
}
