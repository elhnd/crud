<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 51
 * Symfony 7.4 / 8.0 new features - Part 7
 * Topics: Extending Validation & Serialization, PHP Config, Resource Tags
 */
class CertificationQuestionsFixtures51 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures50::class];
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
            // ── Q1: ExtendsValidationFor – Concept ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Validation'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In Symfony 7.4, how can you add validation constraints to a class from an external bundle without modifying it?</p>',
                'answers' => [
                    ['text' => 'Create a new class with the <code>#[ExtendsValidationFor(TargetClass::class)]</code> attribute and declare properties with the same names and your constraints', 'correct' => true],
                    ['text' => 'Override the bundle class and add constraints directly', 'correct' => false],
                    ['text' => 'Create an XML file in <code>config/validator/</code> with the constraints', 'correct' => false],
                    ['text' => 'Use a compiler pass to inject constraints at compile time', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 introduces #[ExtendsValidationFor] to extend validation metadata of external classes using PHP attributes, replacing the XML/YAML approach.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-extending-validation-and-serialization-with-php-attributes',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q2: ExtendsValidationFor – Verification ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Validation'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>What happens during Symfony container compilation if an <code>#[ExtendsValidationFor]</code> class declares a property that does not exist in the target class?</p>',
                'answers' => [
                    ['text' => 'A <code>MappingException</code> is thrown', 'correct' => true],
                    ['text' => 'The property is silently ignored', 'correct' => false],
                    ['text' => 'A deprecation warning is generated', 'correct' => false],
                    ['text' => 'The property is dynamically added to the target class', 'correct' => false],
                ],
                'explanation' => 'During container compilation, Symfony verifies that all properties and getters in the extension class exist in the target class. If not, a MappingException is thrown.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-extending-validation-and-serialization-with-php-attributes',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q3: ExtendsValidationFor – Merging ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Validation'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>When using <code>#[ExtendsValidationFor]</code>, how are the constraints from the original class and the extension class combined?</p>',
                'answers' => [
                    ['text' => 'They are merged: constraints from both classes are applied together', 'correct' => true],
                    ['text' => 'The extension class overrides all constraints from the original', 'correct' => false],
                    ['text' => 'Only constraints with different groups are merged', 'correct' => false],
                    ['text' => 'The original constraints are removed and replaced', 'correct' => false],
                ],
                'explanation' => 'At runtime, Symfony reads attributes from both the original class and extension class and merges them. Use validation groups to be selective about which apply.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-extending-validation-and-serialization-with-php-attributes',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q4: ExtendsSerializationFor ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Serializer'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>Which attribute introduced in Symfony 7.4 allows you to extend serialization metadata for third-party classes without XML or YAML files?</p>',
                'answers' => [
                    ['text' => '<code>#[ExtendsSerializationFor(TargetClass::class)]</code>', 'correct' => true],
                    ['text' => '<code>#[SerializerExtension(TargetClass::class)]</code>', 'correct' => false],
                    ['text' => '<code>#[ExtendSerializer(TargetClass::class)]</code>', 'correct' => false],
                    ['text' => '<code>#[MetadataFor(TargetClass::class)]</code>', 'correct' => false],
                ],
                'explanation' => '#[ExtendsSerializationFor] lets you declare serialization attributes (Groups, SerializedName, MaxDepth, etc.) for classes you don\'t control.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-extending-validation-and-serialization-with-php-attributes',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q5: Extension classes – abstract ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Validation'),
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'text' => '<p>Validation and serialization extension classes created with <code>#[ExtendsValidationFor]</code> or <code>#[ExtendsSerializationFor]</code> can be declared as <code>abstract</code> since they are not meant to be instantiated.</p>',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
                'explanation' => 'These extension classes are not meant to be instantiated. Declaring them as abstract is a recommended practice.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-extending-validation-and-serialization-with-php-attributes',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q6: Better PHP Configuration – App::config ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In the new Symfony 7.4 array-based PHP configuration format, what function do you use to define configuration?</p>',
                'answers' => [
                    ['text' => '<code>return App::config([...])</code> with a nested array structure', 'correct' => true],
                    ['text' => '<code>return static function (SecurityConfig $config) { ... }</code>', 'correct' => false],
                    ['text' => '<code>return Config::define([...])</code>', 'correct' => false],
                    ['text' => '<code>return new ConfigBuilder([...])</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 introduces App::config([...]) as the new array-based PHP configuration format, replacing the fluent config builder approach.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q7: PHP Config – Deprecation ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>What is being deprecated alongside XML configuration in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'Config builder classes and the fluent PHP configuration format', 'correct' => true],
                    ['text' => 'YAML configuration', 'correct' => false],
                    ['text' => 'Environment variables in configuration', 'correct' => false],
                    ['text' => 'Annotations-based configuration', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 deprecates config builder classes and fluent PHP config. The reason is technical: fluent config is not flexible enough and makes automatic updates via recipes harder.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q8: PHP Config – Array Shapes ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>What advantages does the new Symfony 7.4 array-based PHP config provide thanks to generated array shapes? (Select all that apply)</p>',
                'answers' => [
                    ['text' => 'Full autocompletion in IDEs', 'correct' => true],
                    ['text' => 'Static analysis and type validation', 'correct' => true],
                    ['text' => 'Instant discoverability of config options', 'correct' => true],
                    ['text' => 'Automatic merging with YAML config files', 'correct' => false],
                ],
                'explanation' => 'Array shapes provide autocompletion, static analysis, type validation, and discoverability. The generated reference.php file in config/ defines these shapes.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q9: PHP Config – reference.php ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>What is the <code>reference.php</code> file generated in the <code>config/</code> directory in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'A dynamically generated file containing array shapes for all config options, based on installed bundles', 'correct' => true],
                    ['text' => 'A backup copy of the default YAML configuration', 'correct' => false],
                    ['text' => 'A compiled service container dump', 'correct' => false],
                    ['text' => 'A reference for available console commands', 'correct' => false],
                ],
                'explanation' => 'reference.php contains generated array shapes metadata based on installed bundles. It should be committed to the repository and optionally added to composer.json autoload classmap.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q10: PHP Config – when@env ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Architecture'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>How do you define environment-specific configuration in the new Symfony 7.4 array-based PHP config?</p>',
                'answers' => [
                    ['text' => 'Use the <code>when@{env}</code> key in the config array (e.g. <code>\'when@dev\' => [...]</code>)', 'correct' => true],
                    ['text' => 'Create separate files per environment in <code>config/packages/{env}/</code>', 'correct' => false],
                    ['text' => 'Use <code>App::configForEnv(\'dev\', [...])</code>', 'correct' => false],
                    ['text' => 'Use <code>if (App::getEnv() === \'dev\') { ... }</code>', 'correct' => false],
                ],
                'explanation' => 'In the new array-based config format, use \'when@dev\' => [...] syntax for environment-specific configuration, similar to when@dev in YAML.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q11: Resource Tags – Attribute ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Dependency Injection'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In Symfony 7.4, what attribute can you use to apply resource tags to classes that are not registered as services?</p>',
                'answers' => [
                    ['text' => '<code>#[AutoconfigureResourceTag(\'my.tag\', [\'foo\' => \'bar\'])]</code>', 'correct' => true],
                    ['text' => '<code>#[TaggedResource(\'my.tag\')]</code>', 'correct' => false],
                    ['text' => '<code>#[ServiceTag(\'my.tag\')]</code>', 'correct' => false],
                    ['text' => '<code>#[AsResourceTag(\'my.tag\')]</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 introduces #[AutoconfigureResourceTag] to apply tags to classes that are not registered as services. These tagged classes can later be retrieved or injected.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q12: Resource Tags – YAML ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Dependency Injection'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>How do you configure resource tags in YAML in Symfony 7.4?</p>',
                'answers' => [
                    ['text' => 'Use the <code>resource_tags</code> key under a service definition with a list of tag names and attributes', 'correct' => true],
                    ['text' => 'Use the <code>tags</code> key with a special <code>resource: true</code> flag', 'correct' => false],
                    ['text' => 'Use a separate <code>resource_tags.yaml</code> file', 'correct' => false],
                    ['text' => 'Resource tags can only be configured via PHP attributes', 'correct' => false],
                ],
                'explanation' => 'In YAML, use "resource_tags:" under a service definition with a list of tag objects (name + attributes) or tag name strings.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q13: Url Constraint – Wildcard Protocol ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Validation'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>In Symfony 7.4, how can you configure the <code>Url</code> constraint to accept any protocol that follows RFC 3986?</p>',
                'answers' => [
                    ['text' => 'Use <code>protocols: [\'*\']</code> to match any valid protocol', 'correct' => true],
                    ['text' => 'Set <code>allowAllProtocols: true</code> on the constraint', 'correct' => false],
                    ['text' => 'Omit the <code>protocols</code> option entirely', 'correct' => false],
                    ['text' => 'Use <code>protocols: [\'any\']</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds wildcard support to the Url constraint: protocols: [\'*\'] accepts any RFC 3986-compliant protocol (https, git+ssh, file, custom, etc.).',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q14: Link Header Parsing ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:HTTP'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'text' => '<p>Which new class was added to Symfony 7.4\'s WebLink component for parsing <code>Link</code> HTTP headers from API responses?</p>',
                'answers' => [
                    ['text' => '<code>HttpHeaderParser</code>', 'correct' => true],
                    ['text' => '<code>LinkHeaderReader</code>', 'correct' => false],
                    ['text' => '<code>WebLinkParser</code>', 'correct' => false],
                    ['text' => '<code>ResponseLinkExtractor</code>', 'correct' => false],
                ],
                'explanation' => 'Symfony 7.4 adds HttpHeaderParser to the WebLink component. It parses Link HTTP headers (used for pagination in APIs) and returns structured link objects.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-1',
                'symfonyVersion' => '7.4/8.0',
            ],

            // ── Q15: Explicit Query Parameters ──
            [
                'category' => $symfony,
                'subcategory' => $this->getSubcategory($subcategories, 'Symfony:Routing'),
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'text' => '<p>In Symfony 7.4, what special key can you use in URL generation to force parameters into the query string instead of matching route placeholders?</p>',
                'answers' => [
                    ['text' => '<code>_query</code> — values under this key are always added as query string parameters', 'correct' => true],
                    ['text' => '<code>_extra</code> — extra parameters are appended to the query string', 'correct' => false],
                    ['text' => '<code>_params</code> — forces all values into the query string', 'correct' => false],
                    ['text' => '<code>queryString</code> — a dedicated option for URL generation', 'correct' => false],
                ],
                'explanation' => 'The _query key in route parameters forces values into the query string. This solves cases where a parameter name exists both as a route placeholder and as a needed query parameter.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-7-4-misc-features-part-2',
                'symfonyVersion' => '7.4/8.0',
            ],
        ];
    }
}
