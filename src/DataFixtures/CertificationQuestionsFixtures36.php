<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 36
 * Extracted from certification exam practice questions
 */
class CertificationQuestionsFixtures36 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures35::class];
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
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }

    private function getCertificationQuestions(Category $symfony, Category $php, array $subcategories): array
    {
        return [
            // Q1 - HttpFoundation - $request->get() deprecated
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Does using <code>$request->get(\'key\')</code> still a recommended approach when fetching input data?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Using $request->get(\'key\') is deprecated, consider using explicit input sources instead.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/HttpFoundation/Request.php#L694',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q2 - Clock - DatePoint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Clock'],
                'text' => 'What is the purpose of the <code>Symfony\Component\Clock\DatePoint</code> class?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'DatePoint is a drop-in replacement of PHP date/time classes to provide full integration with the Clock component.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-4-datepoint',
                'answers' => [
                    ['text' => 'It\'s a drop-in replacement of PHP date/time classes to provide full integration with the <code>Clock</code> component', 'correct' => true],
                    ['text' => 'It\'s a wrapper to better handle PHP date/time objects in statistic and probability computing', 'correct' => false],
                    ['text' => 'It adds a convenient widget for <code>DateTimeImmutable</code> data in forms', 'correct' => false],
                ],
            ],

            // Q3 - HttpFoundation - Accessing Session
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What is the way to access the session from the <code>$request</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The getSession() method is used to access the session from the Request object.',
                'resourceUrl' => 'https://symfony.com/doc/2.8/components/http_foundation.html#accessing-the-session',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$request->getSession()</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$request->getPhpSession()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$request->fetchSession()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$request->session</code></pre>', 'correct' => false],
                ],
            ],

            // Q4 - Validator - NoSuspiciousCharacters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'How does the NoSuspiciousCharactersValidator checks for suspicious characters?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The NoSuspiciousCharactersValidator uses the Spoofchecker PHP class from the intl extension.',
                'resourceUrl' => 'https://symfony.com/doc/6.3/reference/constraints/NoSuspiciousCharacters.html',
                'answers' => [
                    ['text' => 'It uses the <code>Spoofchecker</code> PHP class', 'correct' => true],
                    ['text' => 'Is matches against Unicode\'s Spoof database', 'correct' => false],
                    ['text' => 'Is uses a set of regex', 'correct' => false],
                ],
            ],

            // Q5 - HTTP - Safe method caching
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Could a cache performance optimization (such as pre-fetching) be used with a safe method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, safe methods like GET and HEAD can benefit from cache optimizations like pre-fetching.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc7231#section-4.2.1',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - PHP - strtok for parsing
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Which function would best parse the following string by the tab (\t) and newline (\n) characters?
<pre><code class="language-php">$string = "John\tMark\nTed\tLarry";</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'strtok() is designed to tokenize strings by multiple delimiter characters.',
                'resourceUrl' => 'http://php.net/manual/en/function.strtok.php',
                'answers' => [
                    ['text' => '<code>strtok($string, "\t\n");</code>', 'correct' => true],
                    ['text' => '<code>explode($string, "\t\n");</code>', 'correct' => false],
                    ['text' => '<code>str_split($string, "\t\n");</code>', 'correct' => false],
                    ['text' => '<code>strstr($string, "\t\n");</code>', 'correct' => false],
                ],
            ],

            // Q7 - PHP - Variable function call
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Does the following code valid?
<pre><code class="language-php">&lt;?php

function foo(): void
{
  echo \'Hello !\';
}

$func = \'foo\';

$func();</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, PHP supports variable functions where a variable containing a function name can be called with parentheses.',
                'resourceUrl' => 'https://www.php.net/manual/en/functions.first_class_callable_syntax.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q8 - PHP - func_get_arg
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">function bake()
{
    $third = ??? ;
    // ...
}

bake(\'flour\', \'spinach\', \'egg\', \'tomato\', \'salt\');</code></pre>
Which statement does the <code>???</code> placeholder replace in order to store the third passed arguments in the <code>$third</code> variable?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'func_get_arg(2) and func_get_args()[2] both retrieve the third argument (index 2).',
                'resourceUrl' => 'http://php.net/manual/en/function.func-get-arg.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">func_get_arg(2)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">func_get_args()[2]</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$argv[3]</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$_ARGS[2]</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">func_get_arg(3)</code></pre>', 'correct' => false],
                ],
            ],

            // Q9 - Cache - Pools list
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Could the available pools list be displayed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, using the cache:pool:list command or through the profiler.',
                'resourceUrl' => 'https://symfony.com/doc/4.3/cache.html#clearing-the-cache',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q10 - DI - Container parameters with dot prefix
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => '<pre><code class="language-php">$containerBuilder->setParameter(\'foo\', \'bar\');
$containerBuilder->setParameter(\'_foo\', \'bar\');
$containerBuilder->setParameter(\'.foo\', \'bar\');</code></pre>
Which of the following container parameters will be accessible after the compilation?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Parameters starting with a dot (.) are "build parameters" and are removed after compilation. Regular and underscore-prefixed parameters remain accessible.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-dx-improvements-part-2#build-parameters-in-service-container',
                'answers' => [
                    ['text' => 'foo', 'correct' => true],
                    ['text' => '_foo', 'correct' => true],
                    ['text' => '.foo', 'correct' => false],
                ],
            ],

            // Q11 - Security - Voter priority
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which priority has a voter by default?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'By default, voters have a priority of 0.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Bundle/SecurityBundle/DependencyInjection/Compiler/AddSecurityVotersPass.php',
                'answers' => [
                    ['text' => '<code>0</code>', 'correct' => true],
                    ['text' => '<code>1</code>', 'correct' => false],
                    ['text' => '<code>-255</code>', 'correct' => false],
                    ['text' => '<code>100</code>', 'correct' => false],
                    ['text' => '<code>255</code>', 'correct' => false],
                ],
            ],

            // Q12 - Doctrine - Unit of Work queries
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'Considering the following code, which assertions are true?
<pre><code class="language-php">$repository = $this->entityManager->getRepository(\'User\');
$user = $repository->findOneBy([\'name\' => \'Toto\']);
$user2 = $repository->findOneBy([\'name\' => \'Toto\']);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Doctrine returns the same object reference but executes 2 queries because it only tracks by ID, not by other criteria.',
                'resourceUrl' => 'http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/unitofwork.html#how-doctrine-keeps-track-of-objects',
                'answers' => [
                    ['text' => '<code>$user === $user2</code> and 2 sql queries have been executed.', 'correct' => true],
                    ['text' => '<code>$user === $user2</code> and 1 sql query has been executed.', 'correct' => false],
                    ['text' => '<code>$user !== $user2</code> and 1 sql query has been executed.', 'correct' => false],
                    ['text' => '<code>$user !== $user2</code> and 2 sql queries have been executed.', 'correct' => false],
                ],
            ],

            // Q13 - Forms - BirthdayType parent
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which of the following form types is the parent of the <code>Symfony\Component\Form\Extension\Core\Type\BirthdayType</code> form type?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'BirthdayType extends DateType, adding years option defaulting to 120 years back.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/forms/types/birthday.html',
                'answers' => [
                    ['text' => '<code>Symfony\Component\Form\Extension\Core\Type\DateType</code>', 'correct' => true],
                    ['text' => '<code>Symfony\Component\Form\Extension\Core\Type\TimeType</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\Form\Extension\Core\Type\DateTimeType</code>', 'correct' => false],
                ],
            ],

            // Q14 - Best Practices - Controllers
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'According to the Symfony <em>Best Practices Guide</em>, which of the following assertions is true about Symfony controllers?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Controllers must be thin whereas models must be fat - business logic should be in services, not controllers.',
                'resourceUrl' => 'https://symfony.com/doc/3.0/best_practices/controllers.html',
                'answers' => [
                    ['text' => 'Controllers must be thin whereas models must be fat.', 'correct' => true],
                    ['text' => 'Controllers have to extend a base class from <code>symfony/framework-bundle</code>.', 'correct' => false],
                    ['text' => 'In the MVC architecture, the Controller layer is responsible for encapsulating the business logic.', 'correct' => false],
                    ['text' => 'Controllers are responsible for building and executing SQL queries against a database.', 'correct' => false],
                    ['text' => 'Controllers must always return an array.', 'correct' => false],
                ],
            ],

            // Q15 - Best Practices - Constants
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'According to the official Symfony <em>Best Practices Guide</em>, is it recommended to store global settings that rarely change in raw PHP constants instead of storing them under a configuration dedicated file?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, constants are recommended for values that rarely change to avoid configuration overhead.',
                'resourceUrl' => 'https://symfony.com/doc/current/best_practices.html#use-constants-to-define-options-that-rarely-change',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q16 - HttpKernel - NO_AUTO_CACHE_CONTROL_HEADER private response
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'Given the <code>AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER</code> header directive is set, is the response marked as private?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'No, when NO_AUTO_CACHE_CONTROL_HEADER is set, Symfony does not automatically mark the response as private.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/http_cache.html#http-caching-and-user-sessions',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q17 - HTTP - 308 Location header
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Does a response using the <code>308</code> status code should contain a <code>Location</code> header?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, according to RFC 7538, a 308 Permanent Redirect response SHOULD contain a Location header.',
                'resourceUrl' => 'https://tools.ietf.org/html/rfc7538#section-3',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q18 - Twig - block() with is defined
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following Twig snippet:
<pre><code class="language-twig">{% if block(\'footer\', \'common_blocks.html.twig\') is defined %}
    ...
{% endif %}</code></pre>
Which of the following statements are true?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The block() function with a second argument checks if a block exists in another template.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/functions/block.html',
                'answers' => [
                    ['text' => 'The code checks if the <code>common_blocks.html.twig</code> template contains a Twig block called <code>footer</code>', 'correct' => true],
                    ['text' => 'The code is wrong because the <code>is defined</code> test cannot be used with the <code>block()</code> function.', 'correct' => false],
                    ['text' => 'The code is wrong because the <code>block()</code> function doesn\'t allow to pass a second argument.', 'correct' => false],
                    ['text' => 'The <code>if</code> condition will be <code>false</code> if the <code>footer</code> block exists in the <code>common_blocks.html.twig</code> template but it\'s empty (it doesn\'t have any content inside).', 'correct' => false],
                ],
            ],

            // Q19 - Twig - strict_variables off with valid variable
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following Twig code snippet:
<pre><code class="language-twig">The {{ color }} car!</code></pre>
What will be the result of evaluating this template when passing it the <code>blue</code> value for the <code>color</code> variable and when the <code>strict_variables</code> global setting is off?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'When the variable is passed and exists, the template evaluates successfully regardless of strict_variables setting.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/templates.html#variables',
                'answers' => [
                    ['text' => 'The template will be succesfully evaluated and the string <code>The blue car!</code> will be displayed in the web browser.', 'correct' => true],
                    ['text' => 'Twig will raise a <code>Twig_Error_Runtime</code> exception preventing the template from being evaluated.', 'correct' => false],
                ],
            ],

            // Q20 - Security - security.authentication_utils (duplicate)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the name of the service that allows to retrieve an authentication error when a user doesn\'t manage to be authenticated by the application?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The security.authentication_utils service provides methods to get the last authentication error and last username.',
                'resourceUrl' => 'https://symfony.com/doc/5.4/security.html#form-login',
                'answers' => [
                    ['text' => '<code>security.authentication_utils</code>', 'correct' => true],
                    ['text' => '<code>security.authentication_errors</code>', 'correct' => false],
                    ['text' => '<code>security.helper</code>', 'correct' => false],
                    ['text' => '<code>security.authentication_error_manager</code>', 'correct' => false],
                    ['text' => '<code>security.auth_errors_utils</code>', 'correct' => false],
                ],
            ],

            // Q21 - PHP Arrays - array_map
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'],
                'text' => 'Consider the following PHP script.
<pre><code class="language-php">&lt;?php

function square($val)
{
    return $val ** 2;
}

$arr = [1, 2, 3, 4];

/** line **/

$i = 0;
foreach ($squares as $value) {
    if ($i++ > 0) {
        echo ".";
    }

    echo $value;
}</code></pre>
What <code>/** line **/</code> should be used to apply a callback function to every element of an array?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'array_map applies a callback to each element and returns a new array. array_walk modifies in place and returns bool.',
                'resourceUrl' => 'https://php.net/manual/en/function.array-map.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$squares = array_map(\'square\', $arr);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$squares = array_walk($arr, \'square\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$squares = call_user_func_array($arr, \'square\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$squares = call_user_func_array(\'square\', $arr);</code></pre>', 'correct' => false],
                ],
            ],

            // Q22 - Routing - No route found /book/123
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Given the following routes, what controller will be executed for <code>/book/123</code>?
<pre><code class="language-yml"># config/routes.yaml
book_list:
    path:       /books
    controller: \'App\Controller\BookController::list\'
book_detail:
    path:       /books/{slug}
    controller: \'App\Controller\BookController::detail\'
book_download:
    path:       /books/{slug}/download
    controller: \'App\Controller\BookController::download\'</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The URL /book/123 does not match any route because all routes start with /books (plural).',
                'resourceUrl' => 'https://symfony.com/doc/2.x/routing.html',
                'answers' => [
                    ['text' => 'Error: No route found', 'correct' => true],
                    ['text' => 'App\Controller\BookController::list', 'correct' => false],
                    ['text' => 'App\Controller\BookController::detail', 'correct' => false],
                    ['text' => 'App\Controller\BookController::download', 'correct' => false],
                ],
            ],

            // Q23 - HttpFoundation - Generator in JsonResponse
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'What will be the output of this controller action?
<pre><code class="language-php">use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController
{
    #[Route(\'/\', name: \'default\')]
    public function default(Request $request)
    {
        return new JsonResponse([
            \'data\' => $this->getData(),
        ]);
    }

    private function getData(): \Generator
    {
        yield \'foo\';
        yield \'bar\';
        yield \'baz\';
    }
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Generators are not automatically iterated by JsonResponse. To use generators, use StreamedJsonResponse from Symfony 6.3+.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-dx-improvements-part-2#streamed-json-responses',
                'answers' => [
                    ['text' => 'It will return <code>{"data":{}}</code>', 'correct' => true],
                    ['text' => 'It will return <code>{"data":["foo","bar","baz"]}</code>', 'correct' => false],
                    ['text' => 'It will throw an <code>\InvalidArgumentException</code>', 'correct' => false],
                ],
            ],

            // Q24 - Twig - Apply tag
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What\'s the aim of the <code>apply</code> tag?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The apply tag allows applying one or multiple filters to a block of template code.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/tags/apply.html',
                'answers' => [
                    ['text' => 'Apply one or multiple filters on a block', 'correct' => true],
                    ['text' => 'Apply one and only one filter on a block', 'correct' => false],
                    ['text' => 'Apply a camelCase transformation to a text', 'correct' => false],
                    ['text' => 'Define a new tag', 'correct' => false],
                ],
            ],

            // Q25 - PHP - extract function
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">$data = [\'bar\' => \'foo\'];
???
echo $bar;</code></pre>
Which statement does the <code>???</code> placeholder replace in order to make the script print the string <code>foo</code> on the standard output?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The extract() function imports variables from an array into the current symbol table.',
                'resourceUrl' => 'http://php.net/manual/en/function.extract.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">extract($data);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">parse_array($data);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">http_extract_data($data);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">import_variables();</code></pre>', 'correct' => false],
                ],
            ],

            // Q26 - DI - Custom attributes autoconfigure
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could custom attributes be registered for autoconfiguring annotated classes?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, using registerAttributeForAutoconfiguration() you can register custom attributes.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/DependencyInjection/ContainerBuilder.php#L1309',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q27 - Templating - lint:twig deprecations
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Could deprecations be displayed when using <code>bin/console lint:twig</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, the lint:twig command can show deprecations with the --show-deprecations option.',
                'resourceUrl' => 'https://symfony.com/doc/4.4/templates.html#linting-twig-templates',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q28 - PropertyAccess - Enable magic call
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'What is the way to enable magic __call method?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Use PropertyAccessorBuilder with enableMagicCall() to enable magic __call method support.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/property_access/introduction.html#magic-call-method',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$accessor = PropertyAccess::createPropertyAccessorBuilder()
    ->enableMagicCall()
    ->getPropertyAccessor()
;</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$accessor = PropertyAccess::createPropertyAccessor(true);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$accessor = PropertyAccess::createPropertyAccessorBuilder()
    ->getPropertyAccessor(true)
;</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$accessor = PropertyAccess::createPropertyAccessor()
    ->enableMagicCall()
;</code></pre>', 'correct' => false],
                ],
            ],

            // Q29 - VarDumper - HtmlDumper theme
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:VarDumper'],
                'text' => 'Could the theme used by <code>HtmlDumper</code> be changed?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, HtmlDumper supports custom themes via the setTheme() method.',
                'resourceUrl' => 'https://symfony.com/doc/4.2/components/var_dumper/advanced.html#dumpers',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q30 - DI - imports with parameters invalid
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is this code valid?
<pre><code class="language-yaml"># config/services.yaml
imports:
    - "%kernel.project_dir%/config/services/"</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'No, parameters cannot be used in imports. The resource key must use a literal path or glob pattern.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration.html#configuration-parameters',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q31 - DI - Service tags
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Which mechanism allows to aggregate services by domain in the service container?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Tags are used to group services that belong to a specific domain or category.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/dic_tags.html',
                'answers' => [
                    ['text' => 'Tag', 'correct' => true],
                    ['text' => 'Abstraction', 'correct' => false],
                    ['text' => 'Scope', 'correct' => false],
                    ['text' => 'Listener', 'correct' => false],
                ],
            ],

            // Q32 - PropertyAccess - Reading from arrays with brackets
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'],
                'text' => 'What is the way to get the value of the <code>first_name</code> index of the <code>$person</code> array?
<pre><code class="language-php">$person = array(
    \'first_name\' => \'Wouter\',
);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'When accessing array indices with PropertyAccessor, square brackets must be used.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/property_access/introduction.html#reading-from-arrays',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->getValue($person, \'[first_name]\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->getValue($person, \'first_name\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->readIndex($person, \'first_name\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->readProperty($person, \'first_name\');</code></pre>', 'correct' => false],
                ],
            ],

            // Q33 - DI - Autowire attribute extendable
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Can the <code>#[Autowire]</code> attribute be extended?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, since Symfony 6.3, the Autowire attribute can be extended to create custom autowiring attributes.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-3-dependency-injection-improvements#allow-extending-the-autowire-attribute',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No, it is marked as <code>final</code>', 'correct' => false],
                ],
            ],

            // Q34 - OptionsResolver - Dependent defaults
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:OptionsResolver'],
                'text' => 'Is it possible to define default values that depend on another option?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Yes, using setDefault() with a closure that receives the OptionsResolver.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/options_resolver.html#default-values-that-depend-on-another-option',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q35 - Forms - ChoiceType choice_attr
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which types are allowed for the <code>choice_attr</code> option of the <code>Symfony\Component\Form\Extension\Core\Type\ChoiceType</code> form type?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The choice_attr option accepts array, string (property path), or callable.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/reference/forms/types/choice.html#choice-attr',
                'answers' => [
                    ['text' => '<code>array</code>', 'correct' => true],
                    ['text' => '<code>string</code>', 'correct' => true],
                    ['text' => '<code>callable</code>', 'correct' => true],
                    ['text' => '<code>boolean</code>', 'correct' => false],
                    ['text' => '<code>integer</code>', 'correct' => false],
                ],
            ],

            // Q36 - Mime - MessageConverter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mime'],
                'text' => 'Which type of <code>Message</code> can be used to <code>MessageConverter::toEmail()</code> in order to create an <code>Email</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'MessageConverter::toEmail() accepts a Message object.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/Mime/MessageConverter.php#L31',
                'answers' => [
                    ['text' => '<code>Message</code>', 'correct' => true],
                    ['text' => '<code>RawMessage</code>', 'correct' => false],
                    ['text' => '<code>string</code>', 'correct' => false],
                ],
            ],

            // Q37 - Twig - strict_variables on missing variable
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Consider the following Twig code snippet:
<pre><code class="language-twig">The {{ color }} car!</code></pre>
What will be the result of evaluating this template without passing it a <code>color</code> variable when the <code>strict_variables</code> global setting is on?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'With strict_variables on, accessing an undefined variable throws a Twig_Error_Runtime exception.',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/api.html#environment-options',
                'answers' => [
                    ['text' => 'Twig will raise a <code>Twig_Error_Runtime</code> exception preventing the template from being evaluated.', 'correct' => true],
                    ['text' => 'The template will be succesfully evaluated and the string <code>The empty car!</code> will be displayed in the web browser.', 'correct' => false],
                    ['text' => 'The template will be partially evaluated and the string <code>The</code> will be displayed in the web browser.', 'correct' => false],
                    ['text' => 'The template will be succesfully evaluated and the string <code>The  car!</code> will be displayed in the web browser.', 'correct' => false],
                ],
            ],

            // Q38 - Twig - is_safe filter option (duplicate)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which <code>$options</code> allow a <code>Twig_Filter</code> decide how to escape data by itself?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The is_safe option must be an array of contexts like [\'html\'].',
                'resourceUrl' => 'https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping',
                'answers' => [
                    ['text' => '<pre><code class="language-php">[\'is_safe\' => [\'html\']]</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">[\'is_safe\' => \'html\']</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">[\'is_safe\' => true]</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">[\'is_safe\']</code></pre>', 'correct' => false],
                ],
            ],

            // Q39 - PHP - PHP_FLOAT_EPSILON (duplicate)
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Data Format & Types'],
                'text' => 'How is called the PHP constant representing the smallest possible number <code>n</code>, so that <code>1.0 + n != 1.0</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PHP_FLOAT_EPSILON represents machine epsilon for floating point calculations.',
                'resourceUrl' => 'https://www.php.net/manual/en/reserved.constants.php',
                'answers' => [
                    ['text' => '<code>PHP_FLOAT_EPSILON</code>', 'correct' => true],
                    ['text' => '<code>PHP_FLOAT_DIG</code>', 'correct' => false],
                    ['text' => '<code>PHP_FLOAT_SMALLEST</code>', 'correct' => false],
                    ['text' => '<code>PHP_FLOAT_MIN</code>', 'correct' => false],
                ],
            ],

            // Q40 - DI - ContainerBuilder env var count (duplicate)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could the number of time each environment variables has been resolved be obtained when using <code>ContainerBuilder</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Yes, the ContainerBuilder tracks environment variable resolution count.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/DependencyInjection/ContainerBuilder.php#L1077',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
        ];
    }
}
