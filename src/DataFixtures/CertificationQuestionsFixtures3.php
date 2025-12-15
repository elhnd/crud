<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 3
 */
class CertificationQuestionsFixtures3 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;
    public static function getGroups(): array
    {
        return ['certification', 'questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures2::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found.');
        }

        // Create additional subcategories
        $newSubcategories = [
            'Symfony' => [
                'PropertyAccess' => 'PropertyAccess component for reading/writing object properties',
                'Filesystem' => 'Filesystem component for file operations',
                'Mailer' => 'Mailer component for sending emails',
            ],
            'PHP' => [
                'I/O' => 'File input/output operations',
            ],
        ];

        $subcategories = [];

        // Get all existing subcategories
        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        foreach ($subcategoryRepo->findAll() as $sub) {
            $subcategories[$sub->getCategory()->getName() . ':' . $sub->getName()] = $sub;
        }

        // Create new subcategories
        foreach ($newSubcategories['Symfony'] as $name => $description) {
            $key = 'Symfony:' . $name;
            if (!isset($subcategories[$key])) {
                $sub = new Subcategory();
                $sub->setName($name);
                $sub->setDescription($description);
                $sub->setCategory($symfony);
                $manager->persist($sub);
                $subcategories[$key] = $sub;
            }
        }

        foreach ($newSubcategories['PHP'] as $name => $description) {
            $key = 'PHP:' . $name;
            if (!isset($subcategories[$key])) {
                $sub = new Subcategory();
                $sub->setName($name);
                $sub->setDescription($description);
                $sub->setCategory($php);
                $manager->persist($sub);
                $subcategories[$key] = $sub;
            }
        }

        $manager->flush();

        $questions = [
            // Q1: PSR-0 and PSR-4
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PSR'],
                'text' => 'What are PSR-0 and PSR-4?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'PSR-0 and PSR-4 are PHP-FIG standards that define specifications for autoloading classes from file paths.',
                'resourceUrl' => 'https://www.php-fig.org/psr/psr-4/',
                'answers' => [
                    ['text' => 'A specification for autoloading classes from file paths.', 'correct' => true],
                    ['text' => 'A common logger interface.', 'correct' => false],
                    ['text' => 'A coding style guide.', 'correct' => false],
                    ['text' => 'A utility to convert non-namespaced PHP classes into namespaced ones.', 'correct' => false],
                ],
            ],
            // Q2: Route compilation exception
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Which exception is thrown when a <code>Route</code> defined with <code>/page/{foo}/{foo}</code> cannot be compiled (duplicate placeholder)?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'A LogicException is thrown when a route has duplicate variable names.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/Routing/RouteCompiler.php#L39',
                'answers' => [
                    ['text' => '<code>LogicException</code>', 'correct' => true],
                    ['text' => '<code>RuntimeException</code>', 'correct' => false],
                    ['text' => '<code>InvalidArgumentException</code>', 'correct' => false],
                    ['text' => '<code>RouteCompilationException</code>', 'correct' => false],
                    ['text' => '<code>InvalidRouteCompilationContextException</code>', 'correct' => false],
                ],
            ],
            // Q3: ChoiceType preferred_choices duplication
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'With the <code>preferred_choices</code> option, is it possible to render the preferred choices both at the top of the list and in the full list of choices?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, by combining the duplicate_preferred_choices option with the preferred_choices option (added in Symfony 6.4).',
                'resourceUrl' => 'https://symfony.com/doc/6.4/reference/forms/types/choice.html#duplicate-preferred-choices',
                'answers' => [
                    ['text' => 'Yes by combining the <code>duplicate_preferred_choices</code> option with the <code>preferred_choices</code> one', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],
            // Q4: PHP fseek/rewind
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:I/O'],
                'text' => 'Given the following code:<pre><code class="language-php">&lt;?php
$fp = fopen(\'file.txt\', \'r\');
$string1 = fgets($fp, 512);
fseek($fp, 0);</code></pre>
Which function will give the same output as <code>fseek($fp, 0)</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'rewind() is equivalent to fseek($fp, 0) - both reset the file pointer to the beginning.',
                'resourceUrl' => 'https://www.php.net/filesystem',
                'answers' => [
                    ['text' => 'rewind()', 'correct' => true],
                    ['text' => 'fgetss()', 'correct' => false],
                    ['text' => 'file()', 'correct' => false],
                    ['text' => 'fgets()', 'correct' => false],
                ],
            ],
            // Q5: Service id case-sensitive
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Are service ids considered as case-sensitive?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, since Symfony 4.0, service ids are case-sensitive.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-3-3-dependency-injection-deprecations#deprecated-the-case-insensitivity-of-service-identifiers',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Q6: Twig set is not a function
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following is NOT a Twig function?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'set is a Twig tag, not a function. template_from_string, source, parent, and range are all functions.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/',
                'answers' => [
                    ['text' => '<code>set</code>', 'correct' => true],
                    ['text' => '<code>template_from_string</code>', 'correct' => false],
                    ['text' => '<code>source</code>', 'correct' => false],
                    ['text' => '<code>parent</code>', 'correct' => false],
                    ['text' => '<code>range</code>', 'correct' => false],
                ],
            ],
            // Q7: Environment variables usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Given the following configuration and the fact that the <code>.env</code> file exists with a key <code>APP_SECRET=bar</code>, which value will be used in <code>framework.secret</code>?<pre><code class="language-yaml"># config/packages/framework.yaml
parameters:
    env(SECRET): \'foo\'

framework:
    secret: \'%env(APP_SECRET)%\'</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The .env file value takes precedence. Since APP_SECRET=bar is defined, that value is used. The env(SECRET) default is for a different variable.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/configuration/env_var_processors.html#built-in-environment-variable-processors',
                'answers' => [
                    ['text' => '<code>bar</code>', 'correct' => true],
                    ['text' => '<code>foo</code>', 'correct' => false],
                    ['text' => 'An error will be thrown', 'correct' => false],
                ],
            ],
            // Q8: Compiler pass registration steps
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which sentences are true about compiler pass registration?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Compiler passes can be registered in the Kernel build() method. There are 5 compilation steps (BEFORE_OPTIMIZATION, OPTIMIZATION, BEFORE_REMOVING, REMOVING, AFTER_REMOVING). With autoconfigure, they are automatically registered.',
                'resourceUrl' => 'https://symfony.com/doc/3.3/service_container/compiler_passes.html',
                'answers' => [
                    ['text' => 'Compiler pass can be registered in the <code>build</code> method of the Kernel', 'correct' => true],
                    ['text' => 'When a compiler pass is registered, you can chose the step where it will be executed. 5 steps are available', 'correct' => true],
                    ['text' => 'Compiler pass are automatically registered if they implement <code>CompilerPassInterface</code> and <code>autoconfigure</code> is set to true', 'correct' => true],
                    ['text' => 'When a compiler pass is registered, you can chose the step where it will be executed. 4 steps are available', 'correct' => false],
                    ['text' => 'When a compiler pass is registered, you can chose the step where it will be executed. 6 steps are available', 'correct' => false],
                ],
            ],
            // Q9: Clock ClockInterface only
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'Is <code>ClockInterface</code> the only interface available in the <code>Clock</code> Component?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Yes, ClockInterface is the only interface in the Clock component.',
                'resourceUrl' => 'https://github.com/symfony/clock/blob/6.2/ClockInterface.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Q10: PropertyAccess reading missing key
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'What will be the result of the following code?<pre><code class="language-php">use Symfony\Component\PropertyAccess\PropertyAccess;

$accessor = PropertyAccess::createPropertyAccessor();

$person = array(
    \'first_name\' => \'Wouter\',
);

$age = $accessor->getValue($person, \'[age]\');</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'By default, PropertyAccess returns null for non-existent array keys instead of throwing an exception.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/property_access/introduction.html#reading-from-arrays',
                'answers' => [
                    ['text' => 'The value of <code>$age</code> will be <code>null</code>.', 'correct' => true],
                    ['text' => 'A <code>Symfony\\Component\\PropertyAccess\\Exception\\NoSuchPropertyException</code> will be thrown.', 'correct' => false],
                    ['text' => 'A <code>Symfony\\Component\\PropertyAccess\\Exception\\NoSuchIndexException</code> will be thrown.', 'correct' => false],
                    ['text' => 'The value of <code>$age</code> will be <code>0</code>.', 'correct' => false],
                ],
            ],
            // Q11: Translator first argument
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Translation'],
                'text' => 'What is the first argument of the constructor of <code>Symfony\\Component\\Translation\\Translator</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The first argument of the Translator constructor is the locale string.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Translation/Translator.php#L72',
                'answers' => [
                    ['text' => 'The locale', 'correct' => true],
                    ['text' => 'A translator provider', 'correct' => false],
                    ['text' => 'A translator loader', 'correct' => false],
                    ['text' => 'The translation directory', 'correct' => false],
                ],
            ],
            // Q12: Filesystem mirror
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Is the following code valid?<pre><code class="language-php">&lt;?php

$fs = new Filesystem();
$fs->mirror(\'/srv/app\', \'/srv/bar\', null, [\'copy_on_windows\' => true]);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Yes, the mirror() method accepts an options array including copy_on_windows.',
                'resourceUrl' => 'https://symfony.com/doc/2.1/components/filesystem.html#mirror',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
            // Q13: Constraint class purpose
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'In validation, what is the purpose of the Constraint classes?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Constraint classes define the rules to validate (e.g., NotBlank, Length). The actual validation logic is in ConstraintValidator classes.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/validation/custom_constraint.html',
                'answers' => [
                    ['text' => 'To define the rules to validate.', 'correct' => true],
                    ['text' => 'To define the validation groups.', 'correct' => false],
                    ['text' => 'To define the validation logic.', 'correct' => false],
                ],
            ],
            // Q14: Twig raw filter escaping
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Given <code>var</code> and <code>bar</code> are existing variables, among the following, which expressions are ESCAPED (not raw)?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'raw must be the last filter to prevent escaping. {{ var|raw|upper }} is escaped because upper comes after raw. {{ var|raw~bar }} concatenates, so bar is escaped.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/filters/raw.html',
                'answers' => [
                    ['text' => '<code>{{ var|raw|upper }}</code>', 'correct' => true],
                    ['text' => '<code>{{ var|upper|raw }}</code>', 'correct' => false],
                    ['text' => '<code>{{ var|raw~bar }}</code>', 'correct' => false],
                ],
            ],
            // Q15: HTTP Basic authentication
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'When using HTTP basic authentication, how does the server start the authentication process?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'HTTP Basic authentication starts with the server sending a WWW-Authenticate header with a 401 Unauthorized status code.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc2617#section-2',
                'answers' => [
                    ['text' => 'Sending the WWW-Authenticate HTTP header with the HTTP 401 Not Authorized status code.', 'correct' => true],
                    ['text' => 'Redirecting the request to the port 443.', 'correct' => false],
                    ['text' => 'Rendering a login form with the fields _user and _password.', 'correct' => false],
                    ['text' => 'Sending the status code HTTP 418 Authentication Required.', 'correct' => false],
                ],
            ],
            // Q16: Mailer EnvelopeListener priority
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mailer'],
                'text' => 'What is the priority of the <code>EnvelopeListener->onMessage()</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The EnvelopeListener onMessage method has a priority of -255.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/Mailer/EventListener/EnvelopeListener.php#L59',
                'answers' => [
                    ['text' => '<code>-255</code>', 'correct' => true],
                    ['text' => '<code>0</code>', 'correct' => false],
                    ['text' => '<code>-100</code>', 'correct' => false],
                    ['text' => '<code>100</code>', 'correct' => false],
                    ['text' => '<code>255</code>', 'correct' => false],
                ],
            ],
            // Q17: Session mock classes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Which classes exist to help you test code that is using a <code>Symfony\\Component\\HttpFoundation\\Session\\Session</code> object?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'MockArraySessionStorage and MockFileSessionStorage are the available mock storage classes for testing.',
                'resourceUrl' => 'https://symfony.com/doc/2.3/components/http_foundation/session_testing.html',
                'answers' => [
                    ['text' => '<code>MockArraySessionStorage</code>', 'correct' => true],
                    ['text' => '<code>MockFileSessionStorage</code>', 'correct' => true],
                    ['text' => '<code>MockSessionStorage</code>', 'correct' => false],
                    ['text' => '<code>MockMemorySessionStorage</code>', 'correct' => false],
                    ['text' => '<code>MockDatabaseSessionStorage</code>', 'correct' => false],
                ],
            ],
            // Q18: Console events not built-in
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Which of these console events are NOT built-in?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The built-in console events are COMMAND, SIGNAL, TERMINATE, and ERROR. VIEW and HANDLE_COMMAND do not exist.',
                'resourceUrl' => 'https://symfony.com/doc/4.0/components/console/events.html',
                'answers' => [
                    ['text' => '<code>ConsoleEvents::VIEW</code>', 'correct' => true],
                    ['text' => '<code>ConsoleEvents::HANDLE_COMMAND</code>', 'correct' => true],
                    ['text' => '<code>ConsoleEvents::TERMINATE</code>', 'correct' => false],
                    ['text' => '<code>ConsoleEvents::ERROR</code>', 'correct' => false],
                    ['text' => '<code>ConsoleEvents::COMMAND</code>', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $q) {
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }
}
