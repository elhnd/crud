<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Subcategory;
use App\Entity\User;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['base', 'questions'];
    }

    public function load(ObjectManager $manager): void
    {
        // Get or create default user
        $userRepo = $manager->getRepository(User::class);
        $user = $userRepo->findOneBy(['email' => 'user@quiz.local']);
        if (!$user) {
            $user = new User();
            $user->setEmail('user@quiz.local');
            $user->setUsername('Quiz User');
            $user->setPassword('$2y$13$dummy.password.hash');
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
        }

        // Get or create Symfony Category
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        if (!$symfony) {
            $symfony = new Category();
            $symfony->setName('Symfony');
            $symfony->setDescription('Symfony framework concepts and features');
            $symfony->setIcon('code');
            $symfony->setColor('#000000');
            $manager->persist($symfony);
        }

        // Get or create PHP Category
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);
        if (!$php) {
            $php = new Category();
            $php->setName('PHP');
            $php->setDescription('PHP language features and best practices');
            $php->setIcon('terminal');
            $php->setColor('#777BB4');
            $manager->persist($php);
        }

        // Symfony Subcategories
        $symfonySubcategories = [
            'Dependency Injection' => 'Service Container and DI concepts',
            'HttpKernel' => 'Request lifecycle and HTTP handling',
            'Security' => 'Authentication, authorization, and security features',
            'Forms' => 'Form building, validation, and handling',
            'Event Dispatcher' => 'Event-driven architecture',
            'Console' => 'CLI commands and console components',
            'Twig' => 'Templating engine features',
            'Routing' => 'URL routing and parameter handling',
            'Validation' => 'Data validation constraints',
        ];

        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        $symfonySubcategoryEntities = [];
        foreach ($symfonySubcategories as $name => $description) {
            $sub = $subcategoryRepo->findOneBy(['name' => $name, 'category' => $symfony]);
            if (!$sub) {
                $sub = new Subcategory();
                $sub->setName($name);
                $sub->setDescription($description);
                $sub->setCategory($symfony);
                $manager->persist($sub);
            }
            $symfonySubcategoryEntities[$name] = $sub;
        }

        // PHP Subcategories
        $phpSubcategories = [
            'OOP' => 'Object-Oriented Programming concepts',
            'Interfaces & Traits' => 'Interfaces, traits, and abstract classes',
            'Exceptions' => 'Error handling and exceptions',
            'PHP Basics' => 'PHP basics and syntax',
            'Typing & Strict Types' => 'Type declarations and strict mode',
            'Arrays & Collections' => 'Arrays, array functions and manipulation',
            'Functions' => 'Functions, closures, and callbacks',
            'Namespaces' => 'Namespace organization and autoloading',
        ];

        $phpSubcategoryEntities = [];
        foreach ($phpSubcategories as $name => $description) {
            $sub = $subcategoryRepo->findOneBy(['name' => $name, 'category' => $php]);
            if (!$sub) {
                $sub = new Subcategory();
                $sub->setName($name);
                $sub->setDescription($description);
                $sub->setCategory($php);
                $manager->persist($sub);
            }
            $phpSubcategoryEntities[$name] = $sub;
        }

        // Symfony Questions
        $this->createSymfonyQuestions($manager, $symfony, $symfonySubcategoryEntities);

        // PHP Questions
        $this->createPhpQuestions($manager, $php, $phpSubcategoryEntities);

        $manager->flush();
    }

    private function createSymfonyQuestions(ObjectManager $manager, Category $symfony, array $subcategories): void
    {
        $questions = [
            // Dependency Injection
            [
                'subcategory' => 'Dependency Injection',
                'text' => 'What is the primary purpose of the Symfony Service Container?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The Service Container manages object creation and dependencies, implementing the Dependency Injection pattern.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container.html',
                'answers' => [
                    ['text' => 'To manage object creation and handle dependencies automatically', 'correct' => true],
                    ['text' => 'To store session data', 'correct' => false],
                    ['text' => 'To handle HTTP requests', 'correct' => false],
                    ['text' => 'To manage database connections only', 'correct' => false],
                ],
            ]
        ];

        foreach ($questions as $q) {
            $q['category'] = $symfony;
            $q['subcategory'] = $subcategories[$q['subcategory']];
            $this->upsertQuestion($manager, $q);
        }
    }

    private function createPhpQuestions(ObjectManager $manager, Category $php, array $subcategories): void
    {
        $questions = [
            // OOP
            [
                'subcategory' => 'OOP',
                'text' => 'What is the main purpose of encapsulation in OOP?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Encapsulation hides internal implementation and protects data by restricting direct access to object internals.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.visibility.php',
                'answers' => [
                    ['text' => 'To hide internal implementation details and protect data', 'correct' => true],
                    ['text' => 'To make code run faster', 'correct' => false],
                    ['text' => 'To reduce file size', 'correct' => false],
                    ['text' => 'To allow global access to variables', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'OOP',
                'text' => 'Which keyword is used to prevent a class from being extended in PHP?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The final keyword prevents a class from being extended or a method from being overridden.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.final.php',
                'answers' => [
                    ['text' => 'final', 'correct' => true],
                    ['text' => 'static', 'correct' => false],
                    ['text' => 'sealed', 'correct' => false],
                    ['text' => 'const', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'OOP',
                'text' => 'PHP supports multiple inheritance through classes.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'PHP does not support multiple inheritance. A class can only extend one parent class.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.inheritance.php',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],
            [
                'subcategory' => 'OOP',
                'text' => 'Which visibility modifiers are available in PHP? (Select all that apply)',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'PHP has three visibility modifiers: public, protected, and private.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.visibility.php',
                'answers' => [
                    ['text' => 'public', 'correct' => true],
                    ['text' => 'protected', 'correct' => true],
                    ['text' => 'private', 'correct' => true],
                    ['text' => 'internal', 'correct' => false],
                ],
            ],
            // Interfaces & Traits
            [
                'subcategory' => 'Interfaces & Traits',
                'text' => 'What is the main difference between an interface and an abstract class?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Interfaces only declare method signatures, while abstract classes can contain implemented methods.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.interfaces.php',
                'answers' => [
                    ['text' => 'Interfaces cannot contain implementation, abstract classes can', 'correct' => true],
                    ['text' => 'There is no difference', 'correct' => false],
                    ['text' => 'Abstract classes are faster', 'correct' => false],
                    ['text' => 'Interfaces can have properties', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'Interfaces & Traits',
                'text' => 'A class can implement multiple interfaces in PHP.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'PHP allows a class to implement multiple interfaces using the implements keyword.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.interfaces.php',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'Interfaces & Traits',
                'text' => 'What is the purpose of traits in PHP?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Traits provide a mechanism for code reuse without inheritance.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.traits.php',
                'answers' => [
                    ['text' => 'To share methods between classes without inheritance', 'correct' => true],
                    ['text' => 'To define constants', 'correct' => false],
                    ['text' => 'To replace interfaces', 'correct' => false],
                    ['text' => 'To improve performance', 'correct' => false],
                ],
            ],
            // Exceptions
            [
                'subcategory' => 'Exceptions',
                'text' => 'What block is used to catch exceptions in PHP?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The try-catch block is used to handle exceptions in PHP.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.exceptions.php',
                'answers' => [
                    ['text' => 'try-catch', 'correct' => true],
                    ['text' => 'handle-error', 'correct' => false],
                    ['text' => 'error-handler', 'correct' => false],
                    ['text' => 'exception-block', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'Exceptions',
                'text' => 'The finally block always executes regardless of whether an exception was thrown.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'The finally block runs after try and catch, whether or not an exception occurred.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.exceptions.php#language.exceptions.finally',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'Exceptions',
                'text' => 'Which of the following are valid ways to throw an exception in PHP? (Select all that apply)',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'You can throw exceptions using throw new Exception or throw an exception from a variable.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.exceptions.php',
                'answers' => [
                    ['text' => 'throw new Exception("message")', 'correct' => true],
                    ['text' => 'throw new RuntimeException("message")', 'correct' => true],
                    ['text' => 'throw $exceptionVariable', 'correct' => true],
                    ['text' => 'exception("message")', 'correct' => false],
                ],
            ],
            // PHP Basics
            [
                'subcategory' => 'PHP Basics',
                'text' => 'What is the null coalescing operator in PHP?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The ?? operator returns the left operand if it exists and is not null, otherwise it returns the right operand.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.operators.comparison.php#language.operators.comparison.coalesce',
                'answers' => [
                    ['text' => '??', 'correct' => true],
                    ['text' => '?:', 'correct' => false],
                    ['text' => '||', 'correct' => false],
                    ['text' => '&&', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'PHP Basics',
                'text' => 'Which PHP 8 feature allows defining class properties directly in the constructor parameters?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Constructor property promotion allows declaring and initializing properties directly in the constructor.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion',
                'answers' => [
                    ['text' => 'Constructor Property Promotion', 'correct' => true],
                    ['text' => 'Auto Properties', 'correct' => false],
                    ['text' => 'Smart Constructors', 'correct' => false],
                    ['text' => 'Property Injection', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'PHP Basics',
                'text' => 'PHP 8 introduced the match expression as an improved alternative to switch.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Match expressions provide strict comparison and return values, unlike switch statements.',
                'resourceUrl' => 'https://www.php.net/manual/en/control-structures.match.php',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'PHP Basics',
                'text' => 'Which of the following are features introduced in PHP 8? (Select all that apply)',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP 8 introduced named arguments, union types, attributes, and many other features.',
                'resourceUrl' => 'https://www.php.net/releases/8.0/en.php',
                'answers' => [
                    ['text' => 'Named Arguments', 'correct' => true],
                    ['text' => 'Union Types', 'correct' => true],
                    ['text' => 'Attributes', 'correct' => true],
                    ['text' => 'Generics', 'correct' => false],
                ],
            ],
            // Typing & Strict Types
            [
                'subcategory' => 'Typing & Strict Types',
                'text' => 'What does declare(strict_types=1) do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Strict types mode enforces type checking for function arguments and return values.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict',
                'answers' => [
                    ['text' => 'Enforces strict type checking for scalar type declarations', 'correct' => true],
                    ['text' => 'Enables all PHP warnings', 'correct' => false],
                    ['text' => 'Makes all variables immutable', 'correct' => false],
                    ['text' => 'Enables debug mode', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'Typing & Strict Types',
                'text' => 'The mixed type hint in PHP 8 allows any type.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'The mixed type accepts any value type in PHP 8.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.types.mixed.php',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
            // Arrays & Collections
            [
                'subcategory' => 'Arrays & Collections',
                'text' => 'What function is used to check if a key exists in an array?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'array_key_exists() checks if a key exists in an array.',
                'resourceUrl' => 'https://www.php.net/manual/en/function.array-key-exists.php',
                'answers' => [
                    ['text' => 'array_key_exists()', 'correct' => true],
                    ['text' => 'key_exists()', 'correct' => false],
                    ['text' => 'has_key()', 'correct' => false],
                    ['text' => 'in_array()', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'Arrays & Collections',
                'text' => 'Which array functions transform array values? (Select all that apply)',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_map, array_filter, and array_reduce are functional-style array transformation functions.',
                'resourceUrl' => 'https://www.php.net/manual/en/function.array-map.php',
                'answers' => [
                    ['text' => 'array_map()', 'correct' => true],
                    ['text' => 'array_filter()', 'correct' => true],
                    ['text' => 'array_reduce()', 'correct' => true],
                    ['text' => 'array_push()', 'correct' => false],
                ],
            ],
            // Functions
            [
                'subcategory' => 'Functions',
                'text' => 'What is a closure in PHP?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'A closure is an anonymous function that can capture variables from its surrounding scope.',
                'resourceUrl' => 'https://www.php.net/manual/en/functions.anonymous.php',
                'answers' => [
                    ['text' => 'An anonymous function that can capture variables from surrounding scope', 'correct' => true],
                    ['text' => 'A function that closes files', 'correct' => false],
                    ['text' => 'A function that prevents memory leaks', 'correct' => false],
                    ['text' => 'A function defined inside a class', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'Functions',
                'text' => 'Arrow functions in PHP automatically capture variables from the parent scope.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Arrow functions (fn =>) automatically capture variables by value from the parent scope.',
                'resourceUrl' => 'https://www.php.net/manual/en/functions.arrow.php',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
            // Namespaces
            [
                'subcategory' => 'Namespaces',
                'text' => 'What keyword is used to import a class from another namespace?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The use keyword imports classes, functions, or constants from other namespaces.',
                'resourceUrl' => 'https://www.php.net/manual/en/language.namespaces.importing.php',
                'answers' => [
                    ['text' => 'use', 'correct' => true],
                    ['text' => 'import', 'correct' => false],
                    ['text' => 'include', 'correct' => false],
                    ['text' => 'require', 'correct' => false],
                ],
            ],
            [
                'subcategory' => 'Namespaces',
                'text' => 'PSR-4 autoloading maps namespace prefixes to directory paths.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'PSR-4 defines an autoloading standard that maps namespace prefixes to base directories.',
                'resourceUrl' => 'https://www.php-fig.org/psr/psr-4/',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
        ];

        foreach ($questions as $q) {
            $q['category'] = $php;
            $q['subcategory'] = $subcategories[$q['subcategory']];
            $this->upsertQuestion($manager, $q);
        }
    }
}
