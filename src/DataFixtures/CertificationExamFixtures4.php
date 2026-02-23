<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Symfony 8 Certification Exam Questions (Questions 151-225)
 * Based on: UPGRADE-8.0.md and CHANGE-LOG.md (Symfony 8.0)
 * Official topics: https://certification.symfony.com/exams/symfony.html
 * Topics: Console, DI, Forms, HttpFoundation, HttpKernel, Security, Routing,
 * Serializer, Validator, Messenger, Translation, Twig, Configuration, Architecture, Miscellaneous
 * Excluded: Doctrine, Symfony UX, AssetMapper, Lock, Uid, TypeInfo, third-party bridges
 */
class CertificationExamFixtures4 extends Fixture implements FixtureGroupInterface
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
            // SECTION 1: CONSOLE (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 151 - AsCommand is final
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'In Symfony 8, the <code>#[AsCommand]</code> attribute class has been changed. What is the key change?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the #[AsCommand] attribute class has been made final. This means it can no longer be extended. If you previously created custom attributes extending AsCommand, you must refactor your code.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '#[AsCommand] has been made final and can no longer be extended.', 'correct' => true],
                    ['text' => '#[AsCommand] has been deprecated in favor of #[ConsoleCommand].', 'correct' => false],
                    ['text' => '#[AsCommand] now requires a description to be specified.', 'correct' => false],
                    ['text' => '#[AsCommand] has been moved to the HttpKernel component.', 'correct' => false],
                ],
            ],

            // QUESTION 152 - getDefaultName/getDefaultDescription removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'In Symfony 8, how must a console command define its name and description?
<pre><code class="language-php">// Which approach is valid in Symfony 8?

// A)
class MyCommand extends Command
{
    protected static function getDefaultName(): string
    {
        return \'app:my-command\';
    }
}

// B)
#[AsCommand(name: \'app:my-command\', description: \'My command\')]
class MyCommand extends Command { }</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, Command::getDefaultName() and Command::getDefaultDescription() have been removed. The only way to define a command name and description is via the #[AsCommand] attribute.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Only approach B is valid — the #[AsCommand] attribute is the only way.', 'correct' => true],
                    ['text' => 'Only approach A is valid — static methods are required.', 'correct' => false],
                    ['text' => 'Both approaches are valid in Symfony 8.', 'correct' => false],
                    ['text' => 'Neither is valid — commands must use XML configuration.', 'correct' => false],
                ],
            ],

            // QUESTION 153 - setCode closure types
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'In Symfony 8, what requirement applies to closures passed to <code>Command::setCode()</code>?
<pre><code class="language-php">$command->setCode(function ($input, $output) {
    // ...
    return 0;
});</code></pre>

Is this code valid in Symfony 8?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, closures passed to Command::setCode() must have properly typed parameters (InputInterface, OutputInterface) and must declare an int return type. Untyped parameters are no longer accepted.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'No — the closure parameters must be typed as InputInterface and OutputInterface, and the return type must be int.', 'correct' => true],
                    ['text' => 'Yes — closures with untyped parameters are still valid.', 'correct' => false],
                    ['text' => 'No — setCode() has been removed entirely.', 'correct' => false],
                    ['text' => 'Yes — but the return type must be void.', 'correct' => false],
                ],
            ],

            // QUESTION 154 - Application::add() removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'In Symfony 8, the <code>Application::add()</code> method for console commands has been removed. What is the replacement?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, Application::add() has been removed. You should use Application::addCommand() instead to register commands programmatically.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Use Application::addCommand() instead.', 'correct' => true],
                    ['text' => 'Use Application::register() instead.', 'correct' => false],
                    ['text' => 'Use Application::registerCommand() instead.', 'correct' => false],
                    ['text' => 'Commands can only be registered via the service container now.', 'correct' => false],
                ],
            ],

            // QUESTION 155 - isSilent() on OutputInterface
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Symfony 8 added a new method to <code>OutputInterface</code>. Which of the following is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added the isSilent() method to OutputInterface. This method returns true when verbosity is set to VERBOSITY_SILENT, meaning no output should be displayed at all.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'isSilent() — returns true when verbosity is VERBOSITY_SILENT.', 'correct' => true],
                    ['text' => 'isMuted() — returns true when output is suppressed.', 'correct' => false],
                    ['text' => 'isQuiet() — returns true when verbosity is minimal.', 'correct' => false],
                    ['text' => 'isDisabled() — returns true when output is turned off.', 'correct' => false],
                ],
            ],

            // QUESTION 156 - #[Interact] and #[Ask] attributes (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Symfony 8 introduced new attributes for invokable console commands. What do <code>#[Interact]</code> and <code>#[Ask]</code> do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, invokable commands can use the #[Interact] attribute on a method to define the command\'s interact phase (replacing the interact() method). The #[Ask] attribute can be used on parameters to automatically prompt the user for input values.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html',
                'answers' => [
                    ['text' => '#[Interact] marks a method as the command\'s interact phase; #[Ask] prompts the user for a parameter value.', 'correct' => true],
                    ['text' => '#[Interact] enables interactive mode; #[Ask] defines confirmation dialogs.', 'correct' => false],
                    ['text' => '#[Interact] is used for command chaining; #[Ask] is for input validation.', 'correct' => false],
                    ['text' => '#[Interact] replaces configure(); #[Ask] replaces execute().', 'correct' => false],
                ],
            ],

            // QUESTION 157 - #[Input] attribute for DTOs (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Symfony 8 introduced the <code>#[Input]</code> attribute for invokable console commands. What does it do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The #[Input] attribute allows invokable commands to map all console input (arguments and options) to a DTO (Data Transfer Object) automatically. Instead of using $input->getArgument() and $input->getOption(), you receive a populated DTO.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html',
                'answers' => [
                    ['text' => 'It maps console arguments and options to a DTO automatically.', 'correct' => true],
                    ['text' => 'It validates console input against a set of rules.', 'correct' => false],
                    ['text' => 'It reads input from a file instead of the command line.', 'correct' => false],
                    ['text' => 'It makes InputInterface available as a service.', 'correct' => false],
                ],
            ],

            // QUESTION 158 - BackedEnum support in invokable commands (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'In Symfony 8 invokable console commands, what new type can be used for command arguments and options?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added BackedEnum support for invokable command arguments and options. You can type-hint a parameter with a BackedEnum, and Symfony will automatically convert the string input to the corresponding enum case.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html',
                'answers' => [
                    ['text' => 'BackedEnum — the string input is automatically converted to the matching enum case.', 'correct' => true],
                    ['text' => 'UnitEnum — all enums are now supported without backed values.', 'correct' => false],
                    ['text' => 'stdClass — objects are now serialized from JSON input.', 'correct' => false],
                    ['text' => 'DateTimeInterface — dates are automatically parsed.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 2: DEPENDENCY INJECTION (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 159 - TaggedIterator/TaggedLocator removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'In Symfony 8, the <code>#[TaggedIterator]</code> and <code>#[TaggedLocator]</code> attributes have been removed. What are the replacements?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, #[TaggedIterator] was replaced by #[AutowireIterator] and #[TaggedLocator] was replaced by #[AutowireLocator]. The old attributes were deprecated in Symfony 7 and fully removed in Symfony 8.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '#[AutowireIterator] replaces #[TaggedIterator] and #[AutowireLocator] replaces #[TaggedLocator].', 'correct' => true],
                    ['text' => '#[ServiceIterator] replaces #[TaggedIterator] and #[ServiceLocator] replaces #[TaggedLocator].', 'correct' => false],
                    ['text' => 'Both are replaced by #[Autowire] with a tag option.', 'correct' => false],
                    ['text' => 'Tagged services must now be manually iterated through the container.', 'correct' => false],
                ],
            ],

            // QUESTION 160 - !tagged removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'In Symfony 8 YAML service configuration, the <code>!tagged</code> tag has been removed. What must be used instead?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the !tagged YAML tag was removed. You must use !tagged_iterator instead to inject tagged services as an iterable.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '!tagged_iterator', 'correct' => true],
                    ['text' => '!tagged_services', 'correct' => false],
                    ['text' => '!service_iterator', 'correct' => false],
                    ['text' => '!iterate', 'correct' => false],
                ],
            ],

            // QUESTION 161 - XML configuration removed for DI
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which configuration format has been removed from Symfony 8 for Dependency Injection service definitions?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 removed the XML format for DependencyInjection extension configuration. YAML and PHP are the supported formats. The fluent PHP format was also removed in favor of the array-based PHP format.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'The XML configuration format has been removed.', 'correct' => true],
                    ['text' => 'The YAML configuration format has been removed.', 'correct' => false],
                    ['text' => 'The PHP configuration format has been removed.', 'correct' => false],
                    ['text' => 'The annotation-based configuration has been removed.', 'correct' => false],
                ],
            ],

            // QUESTION 162 - Fluent PHP format removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'In Symfony 8, the "fluent" PHP configuration format for semantic config has been removed. Which of the following is the valid approach?
<pre><code class="language-php">// A) Fluent format (removed)
return static function (FrameworkConfig $framework) {
    $framework->secret(\'my_secret\');
};

// B) Array format (valid)
return [
    \'framework\' => [
        \'secret\' => \'my_secret\',
    ],
];</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, the fluent PHP format using config builder classes (like FrameworkConfig) for semantic configuration was removed. You must use the array-based PHP format or YAML instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Only approach B (array-based PHP format) is valid in Symfony 8.', 'correct' => true],
                    ['text' => 'Only approach A (fluent format) is valid in Symfony 8.', 'correct' => false],
                    ['text' => 'Both approaches are valid in Symfony 8.', 'correct' => false],
                    ['text' => 'Neither is valid — Symfony 8 only supports YAML.', 'correct' => false],
                ],
            ],

            // QUESTION 163 - Multiple #[AsDecorator] (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Symfony 8 introduced a new capability for the <code>#[AsDecorator]</code> attribute. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, you can apply multiple #[AsDecorator] attributes on a single class. This allows one service to decorate multiple services simultaneously.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/service_decoration.html',
                'answers' => [
                    ['text' => 'A single class can have multiple #[AsDecorator] attributes to decorate several services.', 'correct' => true],
                    ['text' => '#[AsDecorator] can now specify a priority for decoration order.', 'correct' => false],
                    ['text' => '#[AsDecorator] now supports decorating abstract services.', 'correct' => false],
                    ['text' => '#[AsDecorator] can be used on interfaces instead of classes.', 'correct' => false],
                ],
            ],

            // QUESTION 164 - Class::function(...) closures in PHP DSL (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Symfony 8 added support for a new syntax in the PHP service configuration DSL for defining factories. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 8 allows using Class::function(...) first-class callable syntax as closures in the PHP DSL for factory definitions. This leverages PHP 8.1\'s first-class callable syntax.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/factories.html',
                'answers' => [
                    ['text' => 'Class::method(...) first-class callable syntax for factory closures.', 'correct' => true],
                    ['text' => 'Arrow functions (fn() =>) for inline factory definitions.', 'correct' => false],
                    ['text' => 'Anonymous class syntax for factory definitions.', 'correct' => false],
                    ['text' => 'Named function references using function_name(...).', 'correct' => false],
                ],
            ],

            // QUESTION 165 - Service without class throws error
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'In Symfony 8, what happens when a service is defined without a class and has a non-existing FQCN as its ID?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, defining a service whose ID is a FQCN that does not exist, without specifying an explicit class, throws an error. Previously this was silently ignored or deprecated.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'An error is thrown because the FQCN cannot be resolved to an existing class.', 'correct' => true],
                    ['text' => 'The service is silently skipped.', 'correct' => false],
                    ['text' => 'A deprecation warning is emitted at runtime.', 'correct' => false],
                    ['text' => 'The service is registered with stdClass as its class.', 'correct' => false],
                ],
            ],

            // QUESTION 166 - $this scope removed from PHP config files
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'In Symfony 8 PHP configuration files, what change was made regarding the loader scope?
<pre><code class="language-php">// Before (Symfony 7):
return function (ContainerConfigurator $container) {
    $this->import(\'other.php\'); // Using $this
};

// Symfony 8: Which is correct?</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, the internal scope ($this / loader) is no longer available in PHP config files. You must use only the public API of the ContainerConfigurator parameter (or $loader parameter) to configure services.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '$this is no longer available — use only the public API of the configurator parameter.', 'correct' => true],
                    ['text' => '$this is still available but deprecated.', 'correct' => false],
                    ['text' => '$this has been replaced by self::.', 'correct' => false],
                    ['text' => 'The change only affects YAML files, not PHP.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 3: FORMS (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 167 - UrlType default_protocol null
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'In Symfony 8, the <code>UrlType</code> form field has a breaking change regarding <code>default_protocol</code>. What changed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the default_protocol option of UrlType now defaults to null instead of \'http\'. This means URLs without a protocol are no longer automatically prefixed with http://.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'default_protocol now defaults to null instead of \'http\' — URLs without a protocol are no longer modified.', 'correct' => true],
                    ['text' => 'default_protocol now defaults to \'https\' instead of \'http\'.', 'correct' => false],
                    ['text' => 'The default_protocol option has been removed entirely.', 'correct' => false],
                    ['text' => 'UrlType has been deprecated in favor of TextType.', 'correct' => false],
                ],
            ],

            // QUESTION 168 - ResizeFormListener::preSetData removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'In Symfony 8, <code>ResizeFormListener::preSetData()</code> has been removed. What is the replacement?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, the ResizeFormListener::preSetData() method was removed. The replacement is postSetData(), which runs after the data has been set on the form.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Use postSetData() instead.', 'correct' => true],
                    ['text' => 'Use onSetData() instead.', 'correct' => false],
                    ['text' => 'Use preSubmit() instead.', 'correct' => false],
                    ['text' => 'ResizeFormListener has been removed entirely.', 'correct' => false],
                ],
            ],

            // QUESTION 169 - FormFlow for multistep forms (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Symfony 8 introduced a new feature for managing multi-step forms. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 introduced FormFlow, a built-in solution for managing multi-step (wizard-style) forms. This replaces the need for third-party bundles like CraueFormFlowBundle.',
                'resourceUrl' => 'https://symfony.com/doc/current/form/form_flow.html',
                'answers' => [
                    ['text' => 'FormFlow — a built-in component for wizard-style multi-step forms.', 'correct' => true],
                    ['text' => 'FormWizard — an abstract class for step-by-step form handling.', 'correct' => false],
                    ['text' => 'MultiStepType — a new form type for multi-page forms.', 'correct' => false],
                    ['text' => 'StepHandler — a service for managing form steps via sessions.', 'correct' => false],
                ],
            ],

            // QUESTION 170 - EnumType guesser (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Symfony 8 improved form type guessing. Which new guesser was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added a form type guesser for EnumType. When a property is typed as a PHP enum, the form system can now automatically guess that it should use EnumType.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/forms/types/enum.html',
                'answers' => [
                    ['text' => 'A guesser for EnumType — properties typed as BackedEnum are automatically mapped to EnumType.', 'correct' => true],
                    ['text' => 'A guesser for JsonType — JSON-formatted data is automatically detected.', 'correct' => false],
                    ['text' => 'A guesser for UuidType — UUID properties are automatically detected.', 'correct' => false],
                    ['text' => 'A guesser for DateTimeImmutableType — immutable datetime properties are detected.', 'correct' => false],
                ],
            ],

            // QUESTION 171 - validation.xml removed, replaced by attributes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'In Symfony 8, how are form validation constraints defined for built-in form types?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the internal validation.xml files were removed and replaced by PHP attributes on the form type classes. This is part of the broader move away from XML configuration in Symfony 8.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Via PHP attributes — the internal validation.xml files have been removed.', 'correct' => true],
                    ['text' => 'Via YAML configuration files only.', 'correct' => false],
                    ['text' => 'The same validation.xml files are still used internally.', 'correct' => false],
                    ['text' => 'Through annotations in docblocks.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 4: HTTP / HTTPFOUNDATION (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 172 - Request::get() removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'In Symfony 8, <code>Request::get()</code> has been removed. What are the correct replacements?
<pre><code class="language-php">// Before:
$value = $request->get(\'param\');

// After (Symfony 8): ?</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Request::get() was removed because it searched across multiple bags (attributes, query, request) causing ambiguity. In Symfony 8, you must explicitly use $request->query->get(), $request->request->get(), or $request->attributes->get() depending on where the parameter comes from.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '$request->query->get(\'param\') for query string parameters.', 'correct' => true],
                    ['text' => '$request->request->get(\'param\') for POST body parameters.', 'correct' => true],
                    ['text' => '$request->attributes->get(\'param\') for route attributes.', 'correct' => true],
                    ['text' => '$request->getParameter(\'param\') as the new generic method.', 'correct' => false],
                    ['text' => '$request->input(\'param\') like in Laravel.', 'correct' => false],
                ],
            ],

            // QUESTION 173 - HTTP method override dropped for certain methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'In Symfony 8, HTTP method override (via <code>_method</code>) has been restricted. For which HTTP methods is it still allowed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, HTTP method override was dropped for GET, HEAD, CONNECT, and TRACE methods. It is only supported for POST requests (to override to PUT, PATCH, DELETE, etc.).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Only POST requests can use _method to override the HTTP method.', 'correct' => true],
                    ['text' => 'All HTTP methods can still use _method override.', 'correct' => false],
                    ['text' => 'GET and POST can both use _method override.', 'correct' => false],
                    ['text' => 'HTTP method override has been completely removed in Symfony 8.', 'correct' => false],
                ],
            ],

            // QUESTION 174 - QUERY HTTP method (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Symfony 8 added support for a new HTTP method. Which one?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added support for the QUERY HTTP method (RFC draft). This method is similar to GET but allows a request body, and Request::createFromGlobals() now also parses the body of PUT, DELETE, PATCH, and QUERY requests.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_foundation.html',
                'answers' => [
                    ['text' => 'QUERY — similar to GET but allows a request body.', 'correct' => true],
                    ['text' => 'SEARCH — for full-text search operations.', 'correct' => false],
                    ['text' => 'LINK — for establishing resource relationships.', 'correct' => false],
                    ['text' => 'PURGE — for cache invalidation.', 'correct' => false],
                ],
            ],

            // QUESTION 175 - Request::createFromGlobals parses PUT/DELETE/PATCH/QUERY bodies
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'In Symfony 8, <code>Request::createFromGlobals()</code> was enhanced. What new behavior was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, Request::createFromGlobals() now automatically parses the request body for PUT, DELETE, PATCH, and QUERY methods. Previously, only POST bodies were parsed into the request parameter bag.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_foundation.html',
                'answers' => [
                    ['text' => 'It now parses the body of PUT, DELETE, PATCH, and QUERY requests into the request parameter bag.', 'correct' => true],
                    ['text' => 'It now supports multipart/form-data for all HTTP methods.', 'correct' => false],
                    ['text' => 'It now validates the Content-Type header before parsing.', 'correct' => false],
                    ['text' => 'It now throws an exception for unsupported HTTP methods.', 'correct' => false],
                ],
            ],

            // QUESTION 176 - Sec-Fetch-Site CSRF protection (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Symfony 8 added a new CSRF protection mechanism. What is it based on?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 8 introduced CSRF protection based on the Sec-Fetch-Site HTTP header. This browser-set header indicates the relationship between the request origin and the target, offering an alternative to CSRF tokens for modern browsers.',
                'resourceUrl' => 'https://symfony.com/doc/current/security/csrf.html',
                'answers' => [
                    ['text' => 'The Sec-Fetch-Site header — a browser-set header indicating the origin-to-target relationship.', 'correct' => true],
                    ['text' => 'The X-CSRF-Token header — automatically set by Symfony\'s JavaScript.', 'correct' => false],
                    ['text' => 'Double-submit cookie pattern using SameSite=Strict.', 'correct' => false],
                    ['text' => 'Encrypted timestamps in form submissions.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 5: HTTPKERNEL (UPGRADE-8.0)
            // =====================================================

            // QUESTION 177 - getShareDir() added
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Symfony 8 added a new method to <code>KernelInterface</code>. What is <code>getShareDir()</code> used for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'getShareDir() returns the path to a directory for shared, read-only data (like translations, templates, configuration). It is separate from getCacheDir() (writable cache) and getLogDir() (logs). The APP_SHARE_DIR env var and kernel.share_dir parameter are also available.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'It returns the path to a directory for shared, read-only data like translations and templates.', 'correct' => true],
                    ['text' => 'It returns the path where shared libraries are stored.', 'correct' => false],
                    ['text' => 'It returns the path for sharing files between containers in a cluster.', 'correct' => false],
                    ['text' => 'It returns the path where publicly shared assets are stored.', 'correct' => false],
                ],
            ],

            // QUESTION 178 - __sleep/wakeup replaced by __(un)serialize
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'In Symfony 8, which serialization methods have been replaced on Kernel and DataCollector classes?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 replaced __sleep()/__wakeup() with __serialize()/__unserialize() on Kernel classes and DataCollector classes. This aligns with modern PHP practices (PHP 7.4+) for custom serialization.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '__sleep()/__wakeup() replaced by __serialize()/__unserialize().', 'correct' => true],
                    ['text' => 'Serializable::serialize/unserialize() replaced by JsonSerializable.', 'correct' => false],
                    ['text' => '__toString() replaced by __debugInfo().', 'correct' => false],
                    ['text' => 'json_encode/json_decode replaced by igbinary functions.', 'correct' => false],
                ],
            ],

            // QUESTION 179 - APP_PROJECT_DIR env var (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'Symfony 8 exposed a new environment variable via the Runtime component. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 exposed the APP_PROJECT_DIR environment variable through the Runtime component. This provides a reliable way to reference the project root directory without hardcoding paths.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration.html',
                'answers' => [
                    ['text' => 'APP_PROJECT_DIR — the absolute path to the project root directory.', 'correct' => true],
                    ['text' => 'APP_ROOT — the web server document root.', 'correct' => false],
                    ['text' => 'APP_BASE_PATH — the base path for URL generation.', 'correct' => false],
                    ['text' => 'APP_SRC_DIR — the path to the src/ directory.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 6: SECURITY (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 180 - eraseCredentials() removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony 8, <code>UserInterface::eraseCredentials()</code> has been removed. How should sensitive data be erased instead?
<pre><code class="language-php">// Before (Symfony 7):
public function eraseCredentials(): void
{
    $this->plainPassword = null;
}

// Symfony 8: What is the recommended approach?</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, eraseCredentials() was removed from UserInterface and TokenInterface. The replacement is to use __serialize() to exclude sensitive data from serialization, ensuring passwords are not stored in the session.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Use __serialize() to exclude sensitive fields (like password) from the serialized data.', 'correct' => true],
                    ['text' => 'Use a custom event listener on the security.authentication.success event.', 'correct' => false],
                    ['text' => 'Override the getPassword() method to return null after authentication.', 'correct' => false],
                    ['text' => 'Implement the CredentialErasableInterface instead.', 'correct' => false],
                ],
            ],

            // QUESTION 181 - expose_security_errors replaces hide_user_not_found
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony 8, the <code>hide_user_not_found</code> configuration option has been replaced. What is the new option and what values does it accept?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The new expose_security_errors option accepts three values: \'none\' (equivalent to hide_user_not_found: true), \'all\' (equivalent to hide_user_not_found: false), and \'account_status\' (new — only exposes account status errors like locked/disabled).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'The new option is expose_security_errors.', 'correct' => true],
                    ['text' => '\'none\' hides all security errors (like hide_user_not_found: true).', 'correct' => true],
                    ['text' => '\'account_status\' only exposes account status errors (locked, disabled).', 'correct' => true],
                    ['text' => '\'all\' exposes all security errors (like hide_user_not_found: false).', 'correct' => true],
                    ['text' => '\'partial\' shows only authentication errors.', 'correct' => false],
                ],
            ],

            // QUESTION 182 - ExposeSecurityLevel enum
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony 8, the <code>AuthenticatorManager</code> accepts an <code>ExposeSecurityLevel</code> enum for its <code>$exposeSecurityErrors</code> argument. What type does this replace?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, the AuthenticatorManager\'s $exposeSecurityErrors argument now only accepts ExposeSecurityLevel enum values instead of boolean or string values. This is part of the broader adoption of PHP enums in Symfony\'s configuration.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'It replaces boolean/string values with typed ExposeSecurityLevel enum cases.', 'correct' => true],
                    ['text' => 'It replaces integer constants defined in the SecurityBundle.', 'correct' => false],
                    ['text' => 'It replaces an array of allowed error types.', 'correct' => false],
                    ['text' => 'It replaces a callback function that filters errors.', 'correct' => false],
                ],
            ],

            // QUESTION 183 - RememberMeToken::getSecret() removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which of the following have been removed from Symfony 8\'s Security component?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, RememberMeToken::getSecret() was removed, the user FQCN was removed from the remember-me cookie, PersistentTokenInterface::getClass() was removed, and RememberMeDetails::getUserFqcn() was removed.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'RememberMeToken::getSecret()', 'correct' => true],
                    ['text' => 'PersistentTokenInterface::getClass()', 'correct' => true],
                    ['text' => 'RememberMeDetails::getUserFqcn()', 'correct' => true],
                    ['text' => 'RememberMeToken::getUser()', 'correct' => false],
                    ['text' => 'PersistentTokenInterface::getLastUsed()', 'correct' => false],
                ],
            ],

            // QUESTION 184 - #[IsGranted] $methods (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Symfony 8 enhanced the <code>#[IsGranted]</code> attribute with a new parameter. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the #[IsGranted] attribute gained a $methods parameter that allows restricting the security check to specific HTTP methods. For example, you can require ROLE_ADMIN only for POST requests.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html',
                'answers' => [
                    ['text' => '$methods — restricts the security check to specific HTTP methods (e.g., only for POST).', 'correct' => true],
                    ['text' => '$voters — specifies which voters should evaluate the check.', 'correct' => false],
                    ['text' => '$priority — sets the order of evaluation for multiple attributes.', 'correct' => false],
                    ['text' => '$redirect — defines a redirect URL for unauthorized access.', 'correct' => false],
                ],
            ],

            // QUESTION 185 - #[IsCsrfTokenValid] tokenSource (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Symfony 8 enhanced the <code>#[IsCsrfTokenValid]</code> attribute. What new capability was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, #[IsCsrfTokenValid] gained a $tokenSource parameter that allows the CSRF token to be read from query string parameters or HTTP headers, not just from the request body.',
                'resourceUrl' => 'https://symfony.com/doc/current/security/csrf.html',
                'answers' => [
                    ['text' => 'A $tokenSource parameter that allows reading the CSRF token from query strings or headers.', 'correct' => true],
                    ['text' => 'Automatic CSRF token generation without a form.', 'correct' => false],
                    ['text' => 'Support for encrypted CSRF tokens.', 'correct' => false],
                    ['text' => 'Automatic CSRF validation for all POST requests.', 'correct' => false],
                ],
            ],

            // QUESTION 186 - access_decision() Twig functions (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Symfony 8 added new Twig functions for access decisions. Which of the following are they?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 introduced access_decision() and access_decision_for_user() Twig functions. These provide detailed information about access decisions (which voters voted and how), unlike is_granted() which only returns a boolean.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html',
                'answers' => [
                    ['text' => 'access_decision() — returns detailed access decision information.', 'correct' => true],
                    ['text' => 'access_decision_for_user() — returns access decision for a specific user.', 'correct' => true],
                    ['text' => 'check_permission() — checks a specific permission.', 'correct' => false],
                    ['text' => 'voter_result() — returns the result of a specific voter.', 'correct' => false],
                ],
            ],

            // QUESTION 187 - Callable firewall listeners removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony 8, callable firewall listeners have been removed. What must be used instead?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, callable firewall listeners support was removed. You must extend AbstractListener or implement FirewallListenerInterface instead. The __invoke() method was also removed from AbstractListener and LazyFirewallContext.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Extend AbstractListener or implement FirewallListenerInterface.', 'correct' => true],
                    ['text' => 'Use #[AsFirewallListener] attribute on any class.', 'correct' => false],
                    ['text' => 'Register services tagged with security.firewall.listener.', 'correct' => false],
                    ['text' => 'Use EventSubscriberInterface with security events.', 'correct' => false],
                ],
            ],

            // QUESTION 188 - BadCredentialsException for empty $userIdentifier
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony 8, what happens when you pass an empty string as <code>$userIdentifier</code> to the <code>UserBadge</code> constructor?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, passing an empty string as $userIdentifier to UserBadge throws a BadCredentialsException immediately, instead of proceeding with an empty identifier.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'A BadCredentialsException is thrown.', 'correct' => true],
                    ['text' => 'An InvalidArgumentException is thrown.', 'correct' => false],
                    ['text' => 'The user is treated as anonymous.', 'correct' => false],
                    ['text' => 'The string is silently accepted and user lookup proceeds.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 7: ROUTING (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 189 - XML routes removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'In Symfony 8, which route configuration format has been removed from the Routing component?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 removed XML configuration support from the Routing component. Routes can be defined using PHP attributes, YAML, or PHP configuration files.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'XML format — routes can no longer be defined in XML files.', 'correct' => true],
                    ['text' => 'YAML format — routes must now use PHP attributes.', 'correct' => false],
                    ['text' => 'PHP attribute format — routes must use configuration files.', 'correct' => false],
                    ['text' => 'Annotation format (only) — PHP 8 attributes are still supported.', 'correct' => false],
                ],
            ],

            // QUESTION 190 - Public properties on route attributes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'In Symfony 8, how do route attribute classes (<code>#[Route]</code>) expose their properties?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, getters and setters on route attribute classes were removed. Properties are now public and accessed directly.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Through public properties — getters and setters have been removed.', 'correct' => true],
                    ['text' => 'Through getter methods only — setters have been removed.', 'correct' => false],
                    ['text' => 'Through a toArray() method that returns all configuration.', 'correct' => false],
                    ['text' => 'Through the same getters and setters as in Symfony 7.', 'correct' => false],
                ],
            ],

            // QUESTION 191 - Auto-register routes from controller service attributes (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Symfony 8 introduced automatic route registration from attributes on controller services. What does this mean?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 8 can automatically register routes from #[Route] attributes on controller services without needing explicit route configuration in YAML or PHP files. The framework discovers routes directly from the controller class attributes.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html',
                'answers' => [
                    ['text' => 'Routes defined via #[Route] attributes on controller classes are automatically discovered without manual configuration.', 'correct' => true],
                    ['text' => 'Controllers are automatically created as services when #[Route] is present.', 'correct' => false],
                    ['text' => 'Routes are dynamically generated based on controller method names.', 'correct' => false],
                    ['text' => 'Routes are cached in a shared database across environments.', 'correct' => false],
                ],
            ],

            // QUESTION 192 - Multiple envs in #[Route] (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Symfony 8 enhanced the <code>#[Route]</code> attribute. What new environment feature was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 allows specifying multiple environments in the #[Route] attribute. A route can be restricted to only work in specific environments, e.g., only in dev and test.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html',
                'answers' => [
                    ['text' => 'Multiple environments can be specified — the route is only active in listed envs.', 'correct' => true],
                    ['text' => 'Routes automatically adapt their path based on the environment.', 'correct' => false],
                    ['text' => 'Routes can define different controllers per environment.', 'correct' => false],
                    ['text' => 'Environment-specific route caching was introduced.', 'correct' => false],
                ],
            ],

            // QUESTION 193 - Non-array _query throws InvalidParameterException
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'In Symfony 8, what happens when a non-array <code>_query</code> parameter is passed to <code>UrlGenerator</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, providing a non-array _query parameter to UrlGenerator causes an InvalidParameterException. Previously this was handled silently or with a deprecation.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'An InvalidParameterException is thrown.', 'correct' => true],
                    ['text' => 'The parameter is silently cast to an array.', 'correct' => false],
                    ['text' => 'The parameter is appended as a string to the URL.', 'correct' => false],
                    ['text' => 'A deprecation warning is logged.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 8: SERIALIZER (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 194 - CsvEncoder escape character removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'In Symfony 8, what change was made to <code>CsvEncoder</code> regarding the escape character?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the escape character functionality was completely removed from CsvEncoder. The ESCAPE_CHAR_KEY context option and the withEscapeChar() method on CsvEncoderContextBuilder no longer exist. This aligns with PHP 8.4 which deprecated the escape parameter in CSV functions.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'The escape character support was entirely removed from CsvEncoder.', 'correct' => true],
                    ['text' => 'The default escape character was changed from \'\\\\\' to \'"\'.', 'correct' => false],
                    ['text' => 'The escape character is now mandatory and must be set explicitly.', 'correct' => false],
                    ['text' => 'CsvEncoder was deprecated in favor of a new CsvSerializer.', 'correct' => false],
                ],
            ],

            // QUESTION 195 - NameConverterInterface signature changed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'In Symfony 8, the <code>NameConverterInterface</code> signature was changed. What are the new method signatures?
<pre><code class="language-php">// Before:
public function normalize(string $propertyName): string;
public function denormalize(string $propertyName): string;

// After (Symfony 8): ?</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, NameConverterInterface::normalize() and denormalize() gained three optional parameters: ?string $class, ?string $format, and array $context. This merger absorbed AdvancedNameConverterInterface which was removed.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'normalize(string $propertyName, ?string $class = null, ?string $format = null, array $context = []): string', 'correct' => true],
                    ['text' => 'normalize(string $propertyName, object $object): string', 'correct' => false],
                    ['text' => 'normalize(string $propertyName, array $options = []): string', 'correct' => false],
                    ['text' => 'normalize(string|object $property): string', 'correct' => false],
                ],
            ],

            // QUESTION 196 - AdvancedNameConverterInterface removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'In Symfony 8, <code>AdvancedNameConverterInterface</code> has been removed. Why?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'AdvancedNameConverterInterface was removed because its extra parameters ($class, $format, $context) were merged into the base NameConverterInterface. There is no longer a need for a separate "advanced" interface.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Its extra parameters were merged into NameConverterInterface, making it redundant.', 'correct' => true],
                    ['text' => 'Name conversion is no longer supported in the Serializer component.', 'correct' => false],
                    ['text' => 'It was replaced by a new ContextAwareNameConverterInterface.', 'correct' => false],
                    ['text' => 'Name converters are now configured via attributes instead of interfaces.', 'correct' => false],
                ],
            ],

            // QUESTION 197 - #[ExtendsSerializationFor] attribute (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Symfony 8 introduced the <code>#[ExtendsSerializationFor]</code> attribute. What is its purpose?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The #[ExtendsSerializationFor] attribute allows you to define serialization configuration for a class from an external class. This is useful when you cannot modify the original class (e.g., a vendor class) but need to customize its serialization behavior.',
                'resourceUrl' => 'https://symfony.com/doc/current/serializer.html',
                'answers' => [
                    ['text' => 'It allows defining serialization rules for a class from outside that class (e.g., for vendor classes).', 'correct' => true],
                    ['text' => 'It extends the serialization format to support additional encoders.', 'correct' => false],
                    ['text' => 'It creates a child serializer that inherits parent configuration.', 'correct' => false],
                    ['text' => 'It marks a class as extending another class\'s serialization groups.', 'correct' => false],
                ],
            ],

            // QUESTION 198 - DateTimeNormalizer timezone forcing (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Symfony 8 enhanced the <code>DateTimeNormalizer</code>. What new timezone feature was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added the ability to force a specific timezone in DateTimeNormalizer. When set, all DateTime objects are converted to the specified timezone during normalization, ensuring consistent timezone output.',
                'resourceUrl' => 'https://symfony.com/doc/current/serializer.html',
                'answers' => [
                    ['text' => 'The ability to force a specific timezone — all dates are converted to that timezone during normalization.', 'correct' => true],
                    ['text' => 'Automatic UTC conversion for all DateTime objects.', 'correct' => false],
                    ['text' => 'Timezone information is now stripped from all serialized dates.', 'correct' => false],
                    ['text' => 'Support for multiple timezone formats (offset, IANA, abbreviation).', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 9: VALIDATOR (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 199 - Url constraint requireTld defaults to true
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'In Symfony 8, the <code>Url</code> validator constraint changed its default for <code>requireTld</code>. What is the new default?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the requireTld option of the Url constraint now defaults to true. This means URLs like http://localhost will be rejected unless you explicitly set requireTld to false.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'requireTld now defaults to true — URLs without a TLD (like http://localhost) are rejected.', 'correct' => true],
                    ['text' => 'requireTld now defaults to false — all URLs are accepted.', 'correct' => false],
                    ['text' => 'requireTld was removed — URLs always require a TLD.', 'correct' => false],
                    ['text' => 'requireTld was renamed to validateDomain.', 'correct' => false],
                ],
            ],

            // QUESTION 200 - GroupSequence no longer accepts associative arrays
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'In Symfony 8, which change was made to <code>GroupSequence</code>?
<pre><code class="language-php">// Before:
$sequence = new GroupSequence([\'value\' => [\'Group1\', \'Group2\']]);

// After (Symfony 8):
$sequence = new GroupSequence([\'Group1\', \'Group2\']);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, GroupSequence no longer accepts associative arrays. Only indexed arrays of group names are accepted.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Associative arrays are no longer accepted — only indexed arrays of group names work.', 'correct' => true],
                    ['text' => 'GroupSequence now requires at least two groups.', 'correct' => false],
                    ['text' => 'GroupSequence was replaced by ValidationSequence.', 'correct' => false],
                    ['text' => 'GroupSequence now accepts only string arguments, not arrays.', 'correct' => false],
                ],
            ],

            // QUESTION 201 - getRequiredOptions()/getDefaultOption() removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'In Symfony 8, <code>getRequiredOptions()</code> and <code>getDefaultOption()</code> have been removed from many constraints. What is the replacement?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, the getRequiredOptions() and getDefaultOption() methods were removed. Instead, constraints should use promoted constructor parameters with PHP 8 syntax. Required options become mandatory constructor parameters, and the default option is replaced by the first constructor parameter.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Use mandatory constructor parameters for required options and promoted properties.', 'correct' => true],
                    ['text' => 'Use #[Required] attributes on constraint properties.', 'correct' => false],
                    ['text' => 'Use a static configure() method on the constraint class.', 'correct' => false],
                    ['text' => 'Use a separate ConstraintOptionsBuilder class.', 'correct' => false],
                ],
            ],

            // QUESTION 202 - Custom constraint constructor pattern in Symfony 8
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'What is the correct way to create a custom constraint in Symfony 8?
<pre><code class="language-php">// Which pattern is valid?

// A)
class MyConstraint extends Constraint
{
    public $min;
    public function __construct(?array $options = null) {
        parent::__construct($options);
    }
    public function getRequiredOptions() {
        return [\'min\'];
    }
}

// B)
class MyConstraint extends Constraint
{
    public function __construct(
        public int $min,
        public ?int $max = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, the options-array pattern (A) is removed. Custom constraints must use constructor property promotion (B). Required options become mandatory constructor parameters. The parent::__construct() is called with null for options.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Only pattern B — use constructor property promotion with mandatory parameters.', 'correct' => true],
                    ['text' => 'Only pattern A — the options array pattern is still required.', 'correct' => false],
                    ['text' => 'Both patterns are valid in Symfony 8.', 'correct' => false],
                    ['text' => 'Neither — constraints must use attribute-only configuration.', 'correct' => false],
                ],
            ],

            // QUESTION 203 - #[ExtendsValidationFor] attribute (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Symfony 8 introduced the <code>#[ExtendsValidationFor]</code> attribute. What is its purpose?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The #[ExtendsValidationFor] attribute allows you to define validation constraints for a class from an external class. This is useful for adding validation to vendor classes you cannot modify directly.',
                'resourceUrl' => 'https://symfony.com/doc/current/validation.html',
                'answers' => [
                    ['text' => 'It allows defining validation constraints for a class from outside that class (e.g., for vendor classes).', 'correct' => true],
                    ['text' => 'It extends validation groups across class hierarchies.', 'correct' => false],
                    ['text' => 'It makes one constraint inherit validation rules from another.', 'correct' => false],
                    ['text' => 'It creates an alias for a validation constraint.', 'correct' => false],
                ],
            ],

            // QUESTION 204 - Video constraint (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Symfony 8 added a new validation constraint for media files. Which one?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Symfony 8 added the Video constraint, which validates video files (similar to how the Image constraint validates image files). It can check file type, max size, and other video-specific properties.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/Video.html',
                'answers' => [
                    ['text' => 'Video — validates video files similar to how Image validates images.', 'correct' => true],
                    ['text' => 'Audio — validates audio file formats.', 'correct' => false],
                    ['text' => 'Media — validates any media file type.', 'correct' => false],
                    ['text' => 'FileFormat — validates file MIME types.', 'correct' => false],
                ],
            ],

            // QUESTION 205 - Implicit constraint options removed from YAML/XML
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'In Symfony 8, implicit constraint options in YAML configuration have been removed. What is the correct syntax?
<pre><code class="language-yaml"># Before (implicit — removed):
App\Entity\User:
  constraints:
    - Callback: validateMe

# After (Symfony 8):
?</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, implicit constraint options are removed. You must explicitly name the option. For Callback, you write: - Callback: { callback: validateMe }.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '- Callback: { callback: validateMe } — options must be explicitly named.', 'correct' => true],
                    ['text' => '- Callback: [validateMe] — options must be in array format.', 'correct' => false],
                    ['text' => '- Callback(validateMe) — function call syntax is used.', 'correct' => false],
                    ['text' => 'YAML validation configuration is no longer supported.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 10: MESSENGER (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 206 - messenger:stats text format removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'In Symfony 8, the <code>messenger:stats</code> command no longer supports which output format?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the text format was removed from the messenger:stats command. The available formats are now json and txt (table format).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'The "text" format has been removed.', 'correct' => true],
                    ['text' => 'The "json" format has been removed.', 'correct' => false],
                    ['text' => 'The "xml" format has been removed.', 'correct' => false],
                    ['text' => 'The "table" format has been removed.', 'correct' => false],
                ],
            ],

            // QUESTION 207 - getRetryDelay() on RecoverableExceptionInterface
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Symfony 8 added a new method to <code>RecoverableExceptionInterface</code>. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony 8 added getRetryDelay() to RecoverableExceptionInterface. This allows recoverable exceptions to specify a custom delay (in milliseconds) before the message is retried, overriding the default retry strategy.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'getRetryDelay() — allows the exception to specify a custom retry delay in milliseconds.', 'correct' => true],
                    ['text' => 'getMaxRetries() — allows the exception to set a max retry count.', 'correct' => false],
                    ['text' => 'shouldRetry() — allows the exception to decide if retrying is possible.', 'correct' => false],
                    ['text' => 'getRetryTransport() — allows the exception to specify an alternative transport.', 'correct' => false],
                ],
            ],

            // QUESTION 208 - MessageSentToTransportsEvent (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Symfony 8 introduced a new event in the Messenger component. What is <code>MessageSentToTransportsEvent</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'MessageSentToTransportsEvent is dispatched after a message has been sent to one or more transports. It allows listeners to react after the message is dispatched (e.g., for logging, metrics, or post-dispatch processing).',
                'resourceUrl' => 'https://symfony.com/doc/current/messenger.html',
                'answers' => [
                    ['text' => 'An event dispatched after a message is sent to its transport(s), useful for logging or metrics.', 'correct' => true],
                    ['text' => 'An event dispatched before a message is sent, allowing modification.', 'correct' => false],
                    ['text' => 'An event dispatched when a transport fails to send a message.', 'correct' => false],
                    ['text' => 'An event dispatched when all transports have been initialized.', 'correct' => false],
                ],
            ],

            // QUESTION 209 - --exclude-receivers in messenger:consume (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'],
                'text' => 'Symfony 8 added a new option to the <code>messenger:consume</code> command. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added the --exclude-receivers option to the messenger:consume command. This allows you to consume from all transports except the specified ones, which is useful in multi-worker setups.',
                'resourceUrl' => 'https://symfony.com/doc/current/messenger.html',
                'answers' => [
                    ['text' => '--exclude-receivers — consume from all transports except the specified ones.', 'correct' => true],
                    ['text' => '--priority — set the processing priority for messages.', 'correct' => false],
                    ['text' => '--batch-size — process multiple messages at once.', 'correct' => false],
                    ['text' => '--circuit-breaker — stop consuming after repeated failures.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 11: TRANSLATION (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 210 - TranslatableMessage::__toString() removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'In Symfony 8, <code>TranslatableMessage::__toString()</code> has been removed. How should you convert a TranslatableMessage to a string?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, __toString() was removed from TranslatableMessage because it could only return the message ID without proper translation. Use trans() (with a translator) or getMessage() (for the raw message ID) instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Use trans($translator) for the translated string, or getMessage() for the raw message ID.', 'correct' => true],
                    ['text' => 'Use toString() as the new replacement method.', 'correct' => false],
                    ['text' => 'Cast it explicitly: (string) $message.', 'correct' => false],
                    ['text' => 'Use $message->render() to get the translated string.', 'correct' => false],
                ],
            ],

            // QUESTION 211 - TranslationExtractCommand replaces TranslationUpdateCommand
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'In Symfony 8, the <code>translation:update</code> command has been removed. What is the replacement?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'In Symfony 8, the TranslationUpdateCommand was removed and replaced by TranslationExtractCommand (translation:extract). The command extracts translation keys from templates and source code.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'translation:extract (TranslationExtractCommand).', 'correct' => true],
                    ['text' => 'translation:sync (TranslationSyncCommand).', 'correct' => false],
                    ['text' => 'translation:generate (TranslationGenerateCommand).', 'correct' => false],
                    ['text' => 'translation:compile (TranslationCompileCommand).', 'correct' => false],
                ],
            ],

            // QUESTION 212 - StaticMessage (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'Symfony 8 introduced <code>StaticMessage</code> in the Translation component. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'StaticMessage is a TranslatableInterface implementation that wraps a pre-translated string. Unlike TranslatableMessage which defers translation, StaticMessage is used when you already have the final, translated string but need it to implement TranslatableInterface.',
                'resourceUrl' => 'https://symfony.com/doc/current/translation.html',
                'answers' => [
                    ['text' => 'A TranslatableInterface implementation wrapping an already-translated string.', 'correct' => true],
                    ['text' => 'A caching layer for frequently used translations.', 'correct' => false],
                    ['text' => 'A constraint that validates translation keys exist.', 'correct' => false],
                    ['text' => 'A static helper for translating strings outside services.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 12: TWIG (UPGRADE-8.0)
            // =====================================================

            // QUESTION 213 - debug:twig text format removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In Symfony 8, the <code>debug:twig</code> command\'s output format changed. What happened?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'In Symfony 8, the "text" format was removed from the debug:twig command. Use the "txt" format instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'The "text" format was removed — use "txt" instead.', 'correct' => true],
                    ['text' => 'The "json" format was removed — use "yaml" instead.', 'correct' => false],
                    ['text' => 'All output formats were removed — only table format remains.', 'correct' => false],
                    ['text' => 'The command itself was removed.', 'correct' => false],
                ],
            ],

            // QUESTION 214 - base_template_class config removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In Symfony 8, the <code>base_template_class</code> configuration option for TwigBundle has been removed. What does this mean?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The base_template_class option allowed customizing the base class for compiled Twig templates. In Symfony 8, this option was removed, meaning all templates use the default Twig template class.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Custom base classes for compiled templates are no longer configurable — the default Twig class is always used.', 'correct' => true],
                    ['text' => 'Template inheritance is no longer supported.', 'correct' => false],
                    ['text' => 'You must now set the base class in the Twig environment directly.', 'correct' => false],
                    ['text' => 'The option was renamed to template_class.', 'correct' => false],
                ],
            ],

            // QUESTION 215 - TemplateCacheWarmer made final
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'In Symfony 8, the <code>TemplateCacheWarmer</code> class was made <code>final</code>. What impact does this have?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Making TemplateCacheWarmer final means it can no longer be extended. If you had a custom cache warmer that extended TemplateCacheWarmer, you must create your own implementation from scratch or use decoration.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'It can no longer be extended — use decoration or a custom implementation instead.', 'correct' => true],
                    ['text' => 'It can no longer be overridden in the service container.', 'correct' => false],
                    ['text' => 'It is now a singleton and cannot be instantiated multiple times.', 'correct' => false],
                    ['text' => 'It is now lazy-loaded by default.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 13: CONFIGURATION (UPGRADE-8.0)
            // =====================================================

            // QUESTION 216 - isRequired() + defaultValue() conflict
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'In Symfony 8, configuration tree builder nodes enforce a new rule. What is it?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, a configuration node cannot have both isRequired() and defaultValue() set at the same time. This was previously allowed but is now strictly enforced — a required node should not have a default since the user must provide the value.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'A node cannot have both isRequired() and defaultValue() — they are mutually exclusive.', 'correct' => true],
                    ['text' => 'A node must always have either isRequired() or defaultValue().', 'correct' => false],
                    ['text' => 'isRequired() now accepts a default value as its parameter.', 'correct' => false],
                    ['text' => 'defaultValue() has been renamed to fallbackValue().', 'correct' => false],
                ],
            ],

            // QUESTION 217 - $singular in arrayNode()
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'Symfony 8 added a <code>$singular</code> parameter to <code>NodeBuilder::arrayNode()</code>. What does it do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The $singular parameter allows specifying the singular name for the array node\'s children. This is used for normalizing configuration keys (e.g., \'handler\' as singular for \'handlers\' array).',
                'resourceUrl' => 'https://symfony.com/doc/current/components/config/definition.html',
                'answers' => [
                    ['text' => 'It specifies the singular name for the node\'s children used in configuration normalization.', 'correct' => true],
                    ['text' => 'It enforces that the array can contain only one element.', 'correct' => false],
                    ['text' => 'It enables a single-value shortcut for the array node.', 'correct' => false],
                    ['text' => 'It marks the array as containing only scalar values.', 'correct' => false],
                ],
            ],

            // QUESTION 218 - debug:container --show-arguments removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'In Symfony 8, which option of the <code>debug:container</code> command has been removed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, the --show-arguments option was removed from the debug:container command. Service arguments are now always shown by default when displaying service details.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => '--show-arguments — service arguments are now always displayed.', 'correct' => true],
                    ['text' => '--format — only the default table format is supported.', 'correct' => false],
                    ['text' => '--tag — tag filtering is no longer supported.', 'correct' => false],
                    ['text' => '--show-private — private services are always hidden.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 14: EXPRESSION LANGUAGE (UPGRADE-8.0)
            // =====================================================

            // QUESTION 219 - IGNORE_UNKNOWN_VARIABLES flag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'],
                'text' => 'In Symfony 8, how do you lint an expression without validating variable names?
<pre><code class="language-php">// Before (Symfony 7):
$expressionLanguage->lint($expr, null); // null = ignore unknown variables

// Symfony 8: ?</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, passing null for allowed variable names in lint() was removed. You must use the IGNORE_UNKNOWN_VARIABLES flag constant instead to skip variable name validation.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Use the IGNORE_UNKNOWN_VARIABLES flag: $el->lint($expr, ExpressionLanguage::IGNORE_UNKNOWN_VARIABLES).', 'correct' => true],
                    ['text' => 'Pass an empty array: $el->lint($expr, []).', 'correct' => false],
                    ['text' => 'Pass false: $el->lint($expr, false).', 'correct' => false],
                    ['text' => 'Use a second method: $el->lintWithoutVariables($expr).', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 15: WORKFLOW (UPGRADE-8.0)
            // =====================================================

            // QUESTION 220 - Event::getWorkflow() removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'In Symfony 8, <code>Event::getWorkflow()</code> was removed from Workflow events. How should you access the workflow instance?
<pre><code class="language-php">// Before:
public function onCompleted(CompletedEvent $event): void
{
    $workflow = $event->getWorkflow();
    $workflow->apply($event->getSubject(), \'next\');
}

// After (Symfony 8): ?</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, Event::getWorkflow() was removed. You should inject the WorkflowInterface into your listener via the constructor, using the #[Target] attribute to specify which workflow to inject.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Inject WorkflowInterface via the constructor using #[Target(\'workflow_name\')].', 'correct' => true],
                    ['text' => 'Use $event->getContext()->getWorkflow().', 'correct' => false],
                    ['text' => 'Use the Registry service to look up the workflow at runtime.', 'correct' => false],
                    ['text' => 'The workflow is available via the request attributes.', 'correct' => false],
                ],
            ],

            // =====================================================
            // SECTION 16: MISCELLANEOUS (UPGRADE-8.0 & CHANGELOG)
            // =====================================================

            // QUESTION 221 - OptionsResolver::setOptions() replaces nested setDefault()
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'In Symfony 8, <code>OptionsResolver::setDefault()</code> can no longer be used for nested options. What is the replacement?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'In Symfony 8, using setDefault() with a closure for defining nested options was removed. You must use setOptions() instead, which provides a dedicated API for configuring nested option resolvers.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Use setOptions() for defining nested option configurations.', 'correct' => true],
                    ['text' => 'Use setNested() for defining nested options.', 'correct' => false],
                    ['text' => 'Use addNestedDefaults() for nested configurations.', 'correct' => false],
                    ['text' => 'Nested options are no longer supported.', 'correct' => false],
                ],
            ],

            // QUESTION 222 - VarExporter: LazyGhostTrait/LazyProxyTrait removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'In Symfony 8, <code>LazyGhostTrait</code> and <code>LazyProxyTrait</code> have been removed from the VarExporter component. What is the replacement?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'PHP 8.4 introduced native lazy objects (via ReflectionClass::newLazyProxy() and ReflectionClass::newLazyGhost()). Symfony 8 removed its polyfill implementations (LazyGhostTrait and LazyProxyTrait) in favor of the native PHP 8.4 feature.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Use PHP 8.4 native lazy objects (ReflectionClass::newLazyProxy()/newLazyGhost()).', 'correct' => true],
                    ['text' => 'Use Symfony\'s new LazyObjectFactory service.', 'correct' => false],
                    ['text' => 'Use #[Lazy] attributes on service classes.', 'correct' => false],
                    ['text' => 'Lazy loading of services is no longer supported.', 'correct' => false],
                ],
            ],

            // QUESTION 223 - Router/Translator/etc made final
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'In Symfony 8, several core FrameworkBundle classes were made <code>final</code>. Which of the following classes are now final?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, Router, Translator, and SerializerCacheWarmer classes in the FrameworkBundle were made final. This prevents custom subclassing — use decoration instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Router', 'correct' => true],
                    ['text' => 'Translator', 'correct' => true],
                    ['text' => 'SerializerCacheWarmer', 'correct' => true],
                    ['text' => 'EventDispatcher', 'correct' => false],
                    ['text' => 'ContainerBuilder', 'correct' => false],
                ],
            ],

            // QUESTION 224 - FrankenPHP auto-detection (CHANGELOG)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'Symfony 8 improved the Runtime component with FrankenPHP support. What was added?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony 8 added FrankenPHP runner auto-detection in the Runtime component. When running under FrankenPHP (a modern PHP application server), Symfony automatically uses the appropriate runtime adapter without manual configuration.',
                'resourceUrl' => 'https://symfony.com/doc/current/deployment/frankenphp.html',
                'answers' => [
                    ['text' => 'FrankenPHP runner auto-detection — Symfony automatically uses the FrankenPHP runtime when detected.', 'correct' => true],
                    ['text' => 'FrankenPHP is now the default application server bundled with Symfony.', 'correct' => false],
                    ['text' => 'A FrankenPHP deployment recipe was added to symfony/flex.', 'correct' => false],
                    ['text' => 'FrankenPHP support for the WebProfiler toolbar.', 'correct' => false],
                ],
            ],

            // QUESTION 225 - Yaml: duplicate keys with null removed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'In Symfony 8, the Yaml component enforces stricter parsing. What change was made regarding duplicate mapping keys?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 8, parsing duplicate mapping keys whose value is null is no longer supported. Previously, duplicate keys with null values were silently ignored. Now this throws a parse error.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/8.0/UPGRADE-8.0.md',
                'answers' => [
                    ['text' => 'Duplicate mapping keys with null values now throw a parse error instead of being silently ignored.', 'correct' => true],
                    ['text' => 'All duplicate mapping keys are now allowed regardless of their value.', 'correct' => false],
                    ['text' => 'Duplicate keys now merge their values into an array.', 'correct' => false],
                    ['text' => 'Duplicate keys trigger a deprecation warning but are still accepted.', 'correct' => false],
                ],
            ],
        ];
    }
}
