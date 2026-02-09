<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Real Certification Exam Questions - Part 2 (Questions 31-75)
 */
class CertificationExamFixtures2 extends Fixture implements FixtureGroupInterface
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

        // Load existing subcategories from AppFixtures
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
            // QUESTION 31 - Validation error placement
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Consider the following code that ensures that the user\'s password does not contain their username:
<pre><code class="language-php">class User
{
    #[Assert\IsTrue]
    public function isPasswordSafe(): bool
    {
        return !str_contains($this->password, $this->username);
    }
}</code></pre>

Where will Symfony report any validation errors in this case?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'IsTrue on a getterXXX/isXXX method places the error on the corresponding property. If the method does not correspond to a getter, the error is placed at the object level (displayed at the top of the form, not associated with any field).',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/IsTrue.html',
                'answers' => [
                    ['text' => 'At the top of the form (not associated with any field) (not found)', 'correct' => true],
                    ['text' => 'Next to the username field', 'correct' => false],
                    ['text' => 'Next to the password field', 'correct' => false],
                ],
            ],

            // QUESTION 32 - LessThanOrEqual date constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Consider the following model class:
<pre><code class="language-php">namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Actor
{
    ???
    private \DateTime $dateOfDeath;

    // ...

    public function getDateOfDeath(): \DateTime
    {
        return $this->dateOfDeath;
    }
}</code></pre>

Which of the following constraints does ??? successfully replace in order to validate the date of death is not greater than the current day?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The LessThanOrEqual constraint verifies that a date is less than or equal to another date. "today" is a special value recognized by this constraint that represents the current date. This ensures that dateOfDeath <= today (not in the future).',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/LessThanOrEqual.html',
                'answers' => [
                    ['text' => '#[Assert\Range(min: "today")]', 'correct' => false],
                    ['text' => '#[Assert\LessThanOrEqual("today")]', 'correct' => true],
                    ['text' => '#[Assert\Expression("this.getDateOfDeath().format(\'U\') > strtotime(\'today\')")]', 'correct' => false],
                    ['text' => '#[Assert\Date("now")]', 'correct' => false],
                    ['text' => '#[Assert\LessThan("current day")]', 'correct' => false],
                ],
            ],

            // QUESTION 33 - Birthday validation constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Consider the following code:
<pre><code class="language-php">namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class User
{
    ???
    public \DateTime $birthday;
}</code></pre>

Which Symfony Validator constraint does ??? successfully replace to ensure the user is under 18 years old?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'None of the proposed constraints are valid or appropriate for checking age. To verify that a user is under 18 years old, you would need to use #[Assert\LessThan(\'-18 years\')] or #[Assert\GreaterThan(\'today - 18 years\')] or create a custom constraint with Expression or Callback.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints.html',
                'answers' => [
                    ['text' => '#[Assert\Regex(\'/^\\d+$/\')]', 'correct' => false],
                    ['text' => '#[Assert\Date(\'18 years\')]', 'correct' => false],
                    ['text' => '#[Assert\Birthday(18)]', 'correct' => false],
                    ['text' => '#[Assert\Range(limit: 18)]', 'correct' => false],
                    ['text' => 'None of the above', 'correct' => true],
                ],
            ],

            // QUESTION 34 - Image constraint validation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Consider the following model class:
<pre><code class="language-php">namespace App\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class Picture
{
    #[Assert\Image(
        mimeTypes: [\'image/png\'],
        maxSize: \'2M\',
        orientations: [\'landscape\']
    )]
    public UploadedFile $uploadedFile;
}</code></pre>

Is the above validation configuration valid?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'The #[Assert\Image] constraint does not have an \'orientations\' parameter. Valid parameters include mimeTypes, maxSize, minWidth, maxWidth, minHeight, maxHeight, maxRatio, minRatio, allowSquare, allowLandscape, allowPortrait (booleans, not an array), and detectCorrupted.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/Image.html',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],

            // QUESTION 35 - Form type guessing
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Consider the following form defined in a class:
<pre><code class="language-php">use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(\'field1\', TextType::class)
            ->add(\'field2\', null)
            ->add(\'field3\');
    }
    // ...
}</code></pre>

What fields will Symfony apply the "form type guessing" mechanism to?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Form type guessing applies when the field type is not explicitly specified. field1 has an explicit TextType::class, so no guessing occurs. field2 and field3 do not have explicit types, so Symfony will guess the type from metadata (Doctrine mappings, validation constraints, etc.).',
                'resourceUrl' => 'https://symfony.com/doc/current/forms.html#field-type-guessing',
                'answers' => [
                    ['text' => 'field1 only.', 'correct' => false],
                    ['text' => 'field2 and field3 only.', 'correct' => true],
                    ['text' => 'field1 and field2 only.', 'correct' => false],
                    ['text' => 'field2 only.', 'correct' => false],
                    ['text' => 'field3 only.', 'correct' => false],
                ],
            ],

            // QUESTION 36 - FloatType not a built-in form type
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which of the following is not a built-in form field type class provided by the Symfony Form component?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'FloatType does not exist as a Symfony form type. For decimal numbers, Symfony uses NumberType with the \'scale\' or \'html5\' => false option. The other types (CurrencyType, CountryType, LocaleType, LanguageType) all exist.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/forms/types.html',
                'answers' => [
                    ['text' => 'CurrencyType', 'correct' => false],
                    ['text' => 'CountryType', 'correct' => false],
                    ['text' => 'LocaleType', 'correct' => false],
                    ['text' => 'LanguageType', 'correct' => false],
                    ['text' => 'FloatType', 'correct' => true],
                ],
            ],

            // QUESTION 37 - Override getBlockPrefix method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Inside a custom form type definition class, which of the following statements is the right solution to customize the name of the twig block used to render the form?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The getBlockPrefix() method in a custom form type determines the prefix used for Twig blocks. For example, if getBlockPrefix() returns \'my_custom\', Symfony will look for the blocks my_custom_widget, my_custom_row, my_custom_label, etc.',
                'resourceUrl' => 'https://symfony.com/doc/current/form/create_custom_field_type.html#defining-the-form-type',
                'answers' => [
                    ['text' => 'Implement a getName() method.', 'correct' => false],
                    ['text' => 'Register a block_name option in the OptionsResolver object.', 'correct' => false],
                    ['text' => 'Call the setRenderingBlockName() method on the FormBuilder object.', 'correct' => false],
                    ['text' => 'Override the getBlockPrefix() method from the base AbstractType class.', 'correct' => true],
                    ['text' => 'It\'s not possible to change the Twig block name from the form type definition class.', 'correct' => false],
                ],
            ],

            // QUESTION 38 - Form POST_SUBMIT event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'How can you dynamically change the submitted data of a form object just after they are normalized and written into the mapped object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The FormEvents::POST_SUBMIT event is triggered after the data has been normalized and written into the mapped object. This is the appropriate place to dynamically modify the submitted data.',
                'resourceUrl' => 'https://symfony.com/doc/current/form/events.html',
                'answers' => [
                    ['text' => 'Creating a listener that listens to the kernel.request main event.', 'correct' => false],
                    ['text' => 'Overriding the submit() method of the form type class.', 'correct' => false],
                    ['text' => 'Attaching a listener to the form type object that listens to the form.post_submit form event.', 'correct' => true],
                    ['text' => 'Declaring a new postSubmitData() method in the form type class.', 'correct' => false],
                    ['text' => 'It\'s not possible to dynamically change the submitted data of a form object.', 'correct' => false],
                ],
            ],

            // QUESTION 39 - row_attr variable in form theme
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Consider the following snippet of a form theme:
<pre><code class="language-twig">{% block form_row %}
    {% set row_attr = row_attr|merge({
        class: ???.class|default(\'\') ~ \'some_custom_class\'
    }) %}

    {{ parent() }}
{% endblock %}</code></pre>

Which statement does ??? successfully replace in order to add a CSS class to the form_row block?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The row_attr variable contains the HTML attributes for the form row. To access the existing class and add a new one, you use row_attr.class.',
                'resourceUrl' => 'https://symfony.com/doc/current/form/form_themes.html',
                'answers' => [
                    ['text' => 'attributes', 'correct' => false],
                    ['text' => 'widget_attr', 'correct' => false],
                    ['text' => 'parent_attr', 'correct' => false],
                    ['text' => '_attr', 'correct' => false],
                    ['text' => 'row_attr', 'correct' => true],
                ],
            ],

            // QUESTION 40 - HTTP POST not idempotent
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following methods is NOT considered idempotent by the HTTP specification?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'POST is not idempotent because each call can create a new resource. GET, PUT, DELETE, and HEAD are idempotent according to the HTTP specification.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Glossary/Idempotent',
                'answers' => [
                    ['text' => 'PUT', 'correct' => false],
                    ['text' => 'DELETE', 'correct' => false],
                    ['text' => 'POST', 'correct' => true],
                    ['text' => 'GET', 'correct' => false],
                    ['text' => 'HEAD', 'correct' => false],
                ],
            ],

            // QUESTION 41 - Invalid HTTP status code
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following is NOT a valid HTTP status code?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Status code 505 exists but means "HTTP Version Not Supported", not "Locale Not Available". "505 Locale Not Available" is not a standard HTTP status code.',
                'answers' => [
                    ['text' => '204 No Content', 'correct' => false],
                    ['text' => '307 Temporary Redirect', 'correct' => false],
                    ['text' => '451 Unavailable For Legal Reasons', 'correct' => false],
                    ['text' => '505 Locale Not Available', 'correct' => true],
                ],
            ],

            // QUESTION 42 - HTTP 405 Method Not Allowed
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following messages is mostly associated with the 405 HTTP status code?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'HTTP status code 405 means "Method Not Allowed". It is returned when the HTTP method used (GET, POST, PUT, DELETE, etc.) is not allowed for the requested resource.',
                'answers' => [
                    ['text' => 'Unauthorized', 'correct' => false],
                    ['text' => 'Method Not Allowed', 'correct' => true],
                    ['text' => 'Forbidden', 'correct' => false],
                    ['text' => 'Not Found', 'correct' => false],
                    ['text' => 'Payment Required', 'correct' => false],
                ],
            ],

            // QUESTION 43 - 400 is client error not server error
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => '400 is one of the server error HTTP status codes.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => '400 is a CLIENT error code (4xx), not a SERVER error code (5xx). Status code 400 means "Bad Request" — the client\'s request is malformed. 5xx codes are server errors.',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],

            // QUESTION 44 - HTTP 503 Service Unavailable
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following HTTP status codes is mostly associated with the Service Unavailable message?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'HTTP status code 503 means "Service Unavailable". It indicates that the server is temporarily unavailable (maintenance, overload, etc.).',
                'answers' => [
                    ['text' => '500', 'correct' => false],
                    ['text' => '501', 'correct' => false],
                    ['text' => '502', 'correct' => false],
                    ['text' => '503', 'correct' => true],
                    ['text' => '504', 'correct' => false],
                ],
            ],

            // QUESTION 45 - Cache expires attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Consider the following controller code:
<pre><code class="language-php">#[Route(\'/time\')]
#[Cache(expires: \'+1 hour\')]
public function time(): Response
{
    return $this->render(\'date/time.html.twig\', [
        \'date\' => new \\DateTime(),
    ]);
}</code></pre>

The template just displays the date passed from the controller.

A user accesses the /time page twice using the same browser:
1. date/time of the first request: Wed, March 2nd 16:00:00.
2. date/time of the second request: Wed, March 2nd 16:50:00.

What date/time will the page display on the second request?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The #[Cache(expires: \'+1 hour\')] attribute configures HTTP caching for 1 hour. The first request at 16:00:00 generates the page and caches it until 17:00:00. The second request at 16:50:00 is still within the cache period, so the browser uses its cached version which displays 16:00:00.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_cache.html',
                'answers' => [
                    ['text' => 'Wed, March 2nd 16:00:00', 'correct' => true],
                    ['text' => 'Wed, March 2nd 16:50:00', 'correct' => false],
                    ['text' => 'It depends on the user\'s timezone.', 'correct' => false],
                    ['text' => 'The date when the page was requested by any other user.', 'correct' => false],
                ],
            ],

            // QUESTION 46 - ESI cache with smaxage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Consider the following controller code with ESI:
<pre><code class="language-php">#[Route(\'/about\')]
#[Cache(smaxage: 3600)]
public function about(): Response
{
    return $this->render(\'page/about.html.twig\', [
        \'updated_at\' => new \\DateTime(),
    ]);
}

#[Cache(smaxage: 600)]
public function news(): Response
{
    return $this->render(\'page/news.html.twig\', [
        \'updated_at\' => new \\DateTime(),
    ]);
}</code></pre>

And the templates:
<pre><code class="language-twig">{# page/about.html.twig #}
Clock1: {{ updated_at|date(\'H:i:s T\') }}
{{ render_esi(controller(\'App\\Controller\\PageController::news\')) }}

{# page/news.html.twig #}
Clock2: {{ updated_at|date(\'H:i:s T\') }}</code></pre>

If ESI caching is enabled and the first user requests the /about page at 16:00:00 GMT, what date will each clock display when the second user requests the same page at 17:30:00 GMT?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'With ESI (Edge Side Includes), fragments are cached and invalidated independently. The /about page (Clock1) has an smaxage of 3600 (1h), and the news fragment (Clock2) has an smaxage of 600 (10 min). At 17:30:00 GMT, since 1.5h have passed, the main page cache has also expired, so both clocks would be regenerated. Note: the expected answer assumes the main page is still cached, which would only be true if the second request happened within the 1-hour window.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_cache/esi.html',
                'answers' => [
                    ['text' => 'Clock1: 17:00:00 GMT / Clock2: 17:30:00 GMT', 'correct' => false],
                    ['text' => 'Clock1: 16:00:00 GMT / Clock2: 17:30:00 GMT', 'correct' => true],
                    ['text' => 'Clock1: 17:30:00 GMT / Clock2: 16:00:00 GMT', 'correct' => false],
                    ['text' => 'Clock1: 17:30:00 GMT / Clock2: 17:30:00 GMT', 'correct' => false],
                    ['text' => 'Clock1: 17:00:00 GMT / Clock2: 16:10:00 GMT', 'correct' => false],
                ],
            ],

            // QUESTION 47 - ETag alone not cacheable
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Consider the following controller code:
<pre><code class="language-php">public function termsOfUse(): Response
{
    $response = $this->render(\'page/tos.html.twig\');
    $response->setETag(\'abcdef\');

    return $response;
}</code></pre>

Is this response cacheable either on the client (web browser) or on a shared reverse proxy cache like Varnish?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Setting an ETag alone does not make a response cacheable. The ETag is used for cache validation (conditional validation), but it does not specify that the response CAN be cached. To make a response cacheable, you must also set Cache-Control: public or private, or define s-maxage/max-age.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_cache/validation.html',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],

            // QUESTION 48 - Non-cacheable HTTP methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following HTTP methods are not cacheable?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'According to the HTTP specification (RFC 7231), the cacheable methods are GET and HEAD. The DELETE, PUT, and POST methods are not cacheable because they modify the server state.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Glossary/Cacheable',
                'answers' => [
                    ['text' => 'DELETE', 'correct' => true],
                    ['text' => 'PUT', 'correct' => true],
                    ['text' => 'GET', 'correct' => false],
                    ['text' => 'POST', 'correct' => true],
                    ['text' => 'HEAD', 'correct' => false],
                ],
            ],

            // QUESTION 49 - Console command must return int
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Consider the following Symfony command:
<pre><code class="language-php">#[AsCommand(name: \'app:some-command\')]
class SomeCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(\'Hello world\');
    }
}</code></pre>

If all the code of the execute() method is just the $output->writeln(\'...\') shown above, will this command run successfully when executing it as php bin/console app:some-command in the console terminal?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The execute() method must return a status code (int): Command::SUCCESS (0) on success, Command::FAILURE (1) on failure, or Command::INVALID (2) for incorrect usage. In the code shown, the execute() method does not return any value after writeln(). This will cause a TypeError because the method signature declares an int return type.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html',
                'answers' => [
                    ['text' => 'Yes, it will print the "Hello world" message.', 'correct' => false],
                    ['text' => 'No, it will throw an exception.', 'correct' => true],
                ],
            ],

            // QUESTION 50 - Console --profile debug mode
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'When running the following command in a default Symfony application, will Symfony always collect debug information during the command execution?
<pre><code>php bin/console --profile app:my-command</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The --profile option enables command profiling, but this requires the Profiler to be active. The Symfony Profiler is only active when the application is in "debug mode" (APP_DEBUG=1). In production environment (APP_DEBUG=0), the Profiler is not loaded, so the --profile option will have no effect.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html#profiling-commands',
                'answers' => [
                    ['text' => 'Yes.', 'correct' => false],
                    ['text' => 'Yes, but only if the command is run in the dev environment.', 'correct' => false],
                    ['text' => 'Yes, but only if the application is running in "debug mode".', 'correct' => true],
                    ['text' => 'No.', 'correct' => false],
                ],
            ],

            // QUESTION 51 - Command setHidden method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Consider the following command code:
<pre><code class="language-php">#[AsCommand(name: \'app:foo\')]
class FooCommand extends Command
{
    protected function configure()
    {
        $this
            ->xxx
            // ...
        ;
    }
}</code></pre>

Which statement does xxx successfully replace in order to avoid showing this command in the list of commands displayed when executing php bin/console list?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The setHidden(true) method hides a command from the list displayed by php bin/console list. The command remains directly executable (php bin/console app:foo), but it does not appear in the list. This is useful for internal or maintenance commands.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html#hiding-commands',
                'answers' => [
                    ['text' => 'setPublic(false)', 'correct' => false],
                    ['text' => 'setHidden(true)', 'correct' => true],
                    ['text' => 'hidden()', 'correct' => false],
                    ['text' => 'show(false)', 'correct' => false],
                    ['text' => 'private()', 'correct' => false],
                ],
            ],

            // QUESTION 52 - Console command arguments and options
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Consider the code of the following Symfony console command:
<pre><code class="language-php">#[AsCommand(name: \'app:greet\', description: \'Greet someone\')]
class GreetCommand extends Command
{
    protected function configure()
    {
        $this
            ->addArgument(\'name\', InputArgument::OPTIONAL, \'Who do you want to greet?\')
            ->addOption(\'yell\', null, InputOption::VALUE_NONE, \'Yell in uppercase letters\')
        ;
    }
    // ...
}</code></pre>

Which of the following will execute the command correctly?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The correct commands are: php bin/console app:greet --yell (option without value), php bin/console app:greet "Jane Smith" --yell (argument + option), php bin/console app:greet "Jane Smith" (argument only). The incorrect ones: php bin/console app:greet --yell=yes (VALUE_NONE does not accept a value), php bin/console app:greet --yell --name="Jane Smith" (\'name\' is an argument, not an option).',
                'resourceUrl' => 'https://symfony.com/doc/current/console/input.html',
                'answers' => [
                    ['text' => 'php bin/console app:greet --yell=yes', 'correct' => false],
                    ['text' => 'php bin/console app:greet --yell --name="Jane Smith"', 'correct' => false],
                    ['text' => 'php bin/console app:greet --yell', 'correct' => true],
                    ['text' => 'php bin/console app:greet "Jane Smith" --yell', 'correct' => true],
                    ['text' => 'php bin/console app:greet "Jane Smith"', 'correct' => true],
                ],
            ],

            // QUESTION 53 - Anonymous users and roles
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which of the following built-in security roles/attributes would you use to check if the user is an anonymous user browsing your web site?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In Symfony 5.1+, the concept of anonymous users changed. Anonymous users do not have any specific roles. To check if a user is anonymous (not authenticated), use $this->isGranted(\'IS_AUTHENTICATED_FULLY\') which returns false for unauthenticated users, or $this->getUser() === null.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html#checking-to-see-if-a-user-is-logged-in',
                'answers' => [
                    ['text' => 'IS_ANONYMOUSLY_AUTHENTICATED', 'correct' => false],
                    ['text' => 'IS_ANONYMOUS', 'correct' => false],
                    ['text' => 'IS_NOT_AUTHENTICATED', 'correct' => false],
                    ['text' => 'ROLE_ANONYMOUS', 'correct' => false],
                    ['text' => 'None. Anonymous users don\'t have any roles.', 'correct' => true],
                ],
            ],

            // QUESTION 54 - Trusted proxies configuration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Consider the following configuration applied to a default Symfony application:
<pre><code class="language-yaml">framework:
    trusted_proxies: \'192.0.0.1,10.0.0.0/8\'
    trusted_headers: [\'x-forwarded-for\', \'x-forwarded-host\', \'x-forwarded-proto\', \'x-forwarded-port\', \'x-forwarded-prefix\']</code></pre>

How will this configuration change the application behavior?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The trusted_proxies and trusted_headers configuration defines which proxies are trusted and which headers they can send. Symfony will only trust X-Forwarded-* headers if the request comes from an IP listed in trusted_proxies. This is important for security: without this configuration, an attacker could send fake headers to spoof their IP address.',
                'resourceUrl' => 'https://symfony.com/doc/current/deployment/proxies.html',
                'answers' => [
                    ['text' => 'ESI caching will only work for reverse proxies located at those IP addresses.', 'correct' => false],
                    ['text' => 'Symfony will balance HTTP Caching between those proxies using a round-robin scheduling algorithm.', 'correct' => false],
                    ['text' => 'The content of the x-forwarded-* headers (if any) will only be trusted for proxies located at those IP addresses.', 'correct' => true],
                    ['text' => 'Authentication with X-509 certificates will only work for requests originating at those IP addresses.', 'correct' => false],
                    ['text' => 'None of the above.', 'correct' => false],
                ],
            ],

            // QUESTION 55 - denyAccessUnlessGranted method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Consider the following controller code:
<pre><code class="language-php">class AdminController extends AbstractController
{
    public function editComment(Comment $comment)
    {
        ???

        // ...
    }
}</code></pre>

Which statement does ??? successfully replace in order to throw an AccessDeniedException exception if the current authenticated user has not been granted the EDIT_COMMENT permission on the $comment object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The denyAccessUnlessGranted() method is the standard method in AbstractController for checking permissions. It verifies whether the current user has the specified permission and automatically throws an AccessDeniedException if the permission is denied.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html#securing-controllers-and-other-code',
                'answers' => [
                    ['text' => '$this->throwAccessDeniedExceptionUnless(\'EDIT_COMMENT\', $comment);', 'correct' => false],
                    ['text' => '$this->grantIf(\'EDIT_COMMENT\', $comment);', 'correct' => false],
                    ['text' => '$this->disallowIfNotGranted(\'EDIT_COMMENT\', $comment);', 'correct' => false],
                    ['text' => '$this->denyAccessUnlessGranted(\'EDIT_COMMENT\', $comment);', 'correct' => true],
                    ['text' => '$this->forbidAccessIfNotGranted(\'EDIT_COMMENT\', $comment);', 'correct' => false],
                ],
            ],

            // QUESTION 56 - Access control features
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which of the following features are provided by Symfony\'s access_control security mechanism?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The access_control mechanism allows: restricting access by role (roles), by IP address (ip/ips), and enforcing HTTPS (requires_channel). It does NOT handle: security certificates (configured via firewalls) or geolocation (requires a custom implementation).',
                'resourceUrl' => 'https://symfony.com/doc/current/security/access_control.html',
                'answers' => [
                    ['text' => 'Restrict access by role, ensuring that the user has the required roles to access the resource.', 'correct' => true],
                    ['text' => 'Restrict access by the type of security certificate used to access the site.', 'correct' => false],
                    ['text' => 'Restrict access by location, ensuring that the user accesses from an allowed country or region.', 'correct' => false],
                    ['text' => 'Restrict access by user IP address.', 'correct' => true],
                    ['text' => 'Restrict access for requests not using HTTPS.', 'correct' => true],
                ],
            ],

            // QUESTION 57 - AuthenticationUtils service
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which of the following information can you retrieve thanks to the Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationUtils service?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The AuthenticationUtils service provides: getLastUsername() which returns the last username entered during a failed attempt, and getLastAuthenticationError() which returns the authentication exception. It does NOT provide the current user, password hashes, or the list of roles.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html#fetching-the-user-object',
                'answers' => [
                    ['text' => 'The current authenticated user.', 'correct' => false],
                    ['text' => 'The last username tried on the last unsuccessful authentication attempt.', 'correct' => true],
                    ['text' => 'The last five user password hashes and the last authentication error exception.', 'correct' => false],
                    ['text' => 'The list of the current authenticated user\'s granted roles.', 'correct' => false],
                    ['text' => 'The last authentication error exception.', 'correct' => true],
                ],
            ],

            // QUESTION 58 - Crawler only works with HTML/XML
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:DomCrawler'],
                'text' => 'The crawler object used in functional tests only works when the response is an XML or an HTML document.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Symfony\'s Crawler uses the DomCrawler component which parses XML or HTML. It does NOT work for JSON, plain text, or binaries. For JSON responses, use json_decode() on the response content.',
                'resourceUrl' => 'https://symfony.com/doc/current/testing.html#testing-application-with-the-test-client',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // QUESTION 59 - Profiler token identification
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Testing'],
                'text' => 'Consider the following functional test code:
<pre><code class="language-php">$client = static::createClient();
$client->enableProfiler();
$client->request(\'GET\', \'/\');

$token = $client->getProfile()->getToken();</code></pre>

Which value is held by the $token variable in this functional test?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'In the context of the Symfony Profiler, getToken() returns a unique identifier for the profiled request. It is a unique string generated for each request, used to identify the profile in the Web Debug Toolbar and accessible via the URL /_profiler/{token}. It is NOT a security token, a CSRF token, or the APP_SECRET.',
                'resourceUrl' => 'https://symfony.com/doc/current/testing/profiling.html',
                'answers' => [
                    ['text' => 'The current security authentication token (ie: AnonymousToken, UsernamePasswordToken etc.).', 'correct' => false],
                    ['text' => 'The CSRF token if there is any form in the page, null otherwise.', 'correct' => false],
                    ['text' => 'The value of the secret configuration option (set in APP_SECRET env var).', 'correct' => false],
                    ['text' => 'A string that uniquely identifies each performed request inside the Symfony Profile and Web Debug Toolbar.', 'correct' => true],
                ],
            ],

            // QUESTION 60 - Crawler cannot parse CSS
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:DomCrawler'],
                'text' => 'Supposing that the /assets/style.css URL is generated by the Symfony application and serves the following CSS code:
<pre><code>body { font-family: sans-serif; }
p { font-size: 16px; color: #000; }</code></pre>

Will the following test pass?
<pre><code class="language-php">public function testStyles()
{
    $client = static::createClient();
    $crawler = $client->request(\'GET\', \'/assets/style.css\');

    $color = $crawler->filter(\'p\')->attr(\'color\');
    $this->assertEquals(\'#000\', $color);
}</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'The Symfony Crawler (DomCrawler) is designed to parse HTML or XML, not raw CSS. The test will fail because the Crawler cannot parse CSS text as DOM. filter(\'p\') looks for an HTML <p> element, not a CSS rule "p { }". To test CSS, use $response->getContent() and assertStringContainsString().',
                'resourceUrl' => 'https://symfony.com/doc/current/testing.html#testing-application-with-the-test-client',
                'answers' => [
                    ['text' => 'No, because the test execution results in an error.', 'correct' => true],
                    ['text' => 'No. The variable $color will be null because the filter should be $crawler->filter(\'p\')->getNode(0)->attr(\'color\');', 'correct' => false],
                    ['text' => 'Yes, because the value of $color variable will be #000.', 'correct' => false],
                    ['text' => 'Yes, if there is an Internet connection.', 'correct' => false],
                ],
            ],

            // QUESTION 61 - Functional test file path
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Testing'],
                'text' => 'What is the recommended file path for the functional test of a controller called UserController in a default Symfony application?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'In a standard Symfony application, tests are in tests/ (plural). For a UserController located in src/Controller/UserController.php, the functional test should be tests/Controller/UserControllerTest.php. The structure under tests/ mirrors the one under src/.',
                'resourceUrl' => 'https://symfony.com/doc/current/testing.html#application-tests',
                'answers' => [
                    ['text' => 'src/Tests/Functional/UserControllerTest.php', 'correct' => false],
                    ['text' => '%kernel.tests_dir%/Controllers/UserController.php', 'correct' => false],
                    ['text' => 'test/Security/Controller/UserTest.php', 'correct' => false],
                    ['text' => 'tests/Controller/UserControllerTest.php', 'correct' => true],
                    ['text' => 'src/Controller/Tests/UserController.php', 'correct' => false],
                ],
            ],

            // QUESTION 62 - Test clients with different environments
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Testing'],
                'text' => 'Consider the following functional test snippet:
<pre><code class="language-php">$client1 = static::createClient();
$client2 = static::createClient([\'environment\' => \'prod\']);

$client1->insulate();
$client2->insulate();</code></pre>

In which environment does each HTTP client run?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Each client can be configured to run in a specific environment. $client1 = static::createClient() creates a client in the default test environment: \'test\'. $client2 = static::createClient([\'environment\' => \'prod\']) creates a client explicitly in the \'prod\' environment. The insulate() method isolates each request in a separate PHP process but does NOT affect the environment.',
                'resourceUrl' => 'https://symfony.com/doc/current/testing.html#testing-application-with-the-test-client',
                'answers' => [
                    ['text' => 'You cannot create two different clients in the same test.', 'correct' => false],
                    ['text' => 'Both clients will run in the test environment.', 'correct' => false],
                    ['text' => 'Both clients will run in the prod environment.', 'correct' => false],
                    ['text' => '$client1 will run in the test environment and $client2 will run in the prod environment.', 'correct' => true],
                    ['text' => 'None of the above answers is correct.', 'correct' => false],
                ],
            ],

            // QUESTION 63 - HttpClient parallel requests
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Consider the following code snippet that uses Symfony\'s HttpClient to make 30 HTTP requests to the same web site:
<pre><code class="language-php">use Symfony\Component\HttpClient\CurlHttpClient;

$client = new CurlHttpClient();
$responses = [];

for ($i = 0; $i < 30; ++$i) {
    $uri = "https://http2.akamai.com/demo/tile-$i.png";
    $responses[] = $client->request(\'GET\', $uri);
}</code></pre>

How will these requests be performed by the application?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony\'s HttpClient performs requests asynchronously and in parallel by default. $client->request() returns a ResponseInterface object immediately without blocking. All 30 requests are launched quickly and execute in parallel via HTTP/2 multiplexing or multiple connections.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_client.html#concurrent-requests',
                'answers' => [
                    ['text' => 'Sequentially (each request waits until the previous one is finished).', 'correct' => false],
                    ['text' => 'In parallel, all the 30 requests at the same time.', 'correct' => true],
                ],
            ],

            // QUESTION 64 - Filesystem copy does not copy directories
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Miscellaneous'],
                'text' => 'Consider the following code snippet related to the Symfony Filesystem component:
<pre><code class="language-php">use Symfony\Component\Filesystem\Filesystem;

$filesystem = new Filesystem();
$filesystem->copy(\'/path/to/dir1\', \'/path/to/dir2\');</code></pre>

If dir1 exists and Symfony has permission to create dir2, will this code copy the contents of dir1 into dir2?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The copy() method of Symfony\'s Filesystem component only copies files, not directories. If you pass a directory, an exception will be thrown. To copy an entire directory, use mirror(): $filesystem->mirror(\'/path/to/source\', \'/path/to/destination\');',
                'resourceUrl' => 'https://symfony.com/doc/current/components/filesystem.html',
                'answers' => [
                    ['text' => 'Yes.', 'correct' => false],
                    ['text' => 'No, because when copying directories, you need to pass true as the third argument of copy().', 'correct' => false],
                    ['text' => 'No, because copy() does not copy directories, only files.', 'correct' => true],
                ],
            ],

            // QUESTION 65 - app/ directory doesn't exist in Symfony 7
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Architecture'],
                'text' => 'Which one of the following directories doesn\'t belong to a Symfony 7 application using the default directory structure?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'The app/ directory no longer exists in Symfony 4+ (including Symfony 7). Symfony 2 and 3 used app/ for configuration, resources, and the kernel. Symfony 4+ simplified the structure: config/ replaces app/config/, templates/ replaces app/Resources/views/, and src/Kernel.php replaces app/AppKernel.php.',
                'resourceUrl' => 'https://symfony.com/doc/current/setup/file_structure.html',
                'answers' => [
                    ['text' => 'app/', 'correct' => true],
                    ['text' => 'config/', 'correct' => false],
                    ['text' => 'public/', 'correct' => false],
                    ['text' => 'src/', 'correct' => false],
                    ['text' => 'vendor/', 'correct' => false],
                ],
            ],

            // QUESTION 66 - Lock acquire method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Miscellaneous'],
                'text' => 'Which of the following methods should you use to check if some lock (stored in the $lock variable) has already been acquired by some process?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony\'s Lock component does not have an isAcquired() method. The correct way is to use acquire(): if acquire() returns false, the lock is already held by another process. Example: if (!$lock->acquire(false)) { echo "Was acquired by someone else"; }',
                'resourceUrl' => 'https://symfony.com/doc/current/lock.html',
                'answers' => [
                    ['text' => 'if ($lock->isAcquired()) { echo "Was acquired by someone else"; }', 'correct' => false],
                    ['text' => 'if ( ! $lock->acquire()) { echo "Was acquired by someone else"; }', 'correct' => true],
                ],
            ],

            // QUESTION 67 - .env.local has priority over .env
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'In your computer, the two following files are part of your application:
<pre><code># .env
APP_ENV=prod

# .env.local
APP_ENV=dev</code></pre>

If the application doesn\'t define its execution environment in any other way, in which environment will the application run?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony loads environment files in a specific order, and .env.local takes priority over .env. Loading order: .env, .env.local, .env.{APP_ENV}, .env.{APP_ENV}.local. In this case, .env sets APP_ENV=prod, then .env.local sets APP_ENV=dev and OVERRIDES the value. Final result: APP_ENV=dev',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration.html#configuring-environment-variables-in-env-files',
                'answers' => [
                    ['text' => 'prod', 'correct' => false],
                    ['text' => 'dev', 'correct' => true],
                ],
            ],

            // QUESTION 68 - when@prod configuration syntax
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'Consider the following configuration used in a default Symfony application:
<pre><code class="language-yaml">twig:
    strict_variables: true

???:
    twig:
        strict_variables: false</code></pre>

Which statement does ??? successfully replace to make the strict_variables option to be false in the prod environment?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The when@{environment} syntax allows defining environment-specific configurations. Correct example: when@prod: to override the configuration in production. This is a Symfony 5.3+ feature that allows putting everything in a single file instead of creating separate files.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration.html#configuration-environments',
                'answers' => [
                    ['text' => 'config@prod', 'correct' => false],
                    ['text' => '@prod', 'correct' => false],
                    ['text' => 'prod', 'correct' => false],
                    ['text' => 'when@prod', 'correct' => true],
                    ['text' => 'env(\'prod\')', 'correct' => false],
                ],
            ],

            // QUESTION 69 - Cache pool autowiring
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Consider the following cache configuration:
<pre><code class="language-yaml">framework:
    cache:
        pools:
            my_cache_pool:
                adapter: cache.adapter.array</code></pre>

In a default Symfony application which uses autowiring, which constructor argument do you have to use to get the my_cache_pool cache pool injected in your services?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'With Symfony autowiring, cache pools are injected via the CacheInterface type-hint + the parameter name in camelCase. The pool my_cache_pool becomes $myCachePool. Example: public function __construct(CacheInterface $myCachePool) {}',
                'resourceUrl' => 'https://symfony.com/doc/current/cache.html#creating-a-cache-pool',
                'answers' => [
                    ['text' => 'CacheInterface $myCachePool', 'correct' => true],
                    ['text' => 'MyCachePool $cache', 'correct' => false],
                    ['text' => 'ArrayCacheInterface $myCachePool', 'correct' => false],
                    ['text' => 'CacheInterface $cacheAdapterArray', 'correct' => false],
                    ['text' => 'CachePoolInterface $cacheAdapterArray', 'correct' => false],
                ],
            ],

            // QUESTION 70 - Error page templates
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following templates are valid for customizing 404 error pages in a Symfony web application that has installed and configured Twig?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Symfony allows customizing error pages by creating templates in templates/bundles/TwigBundle/Exception/. The valid templates are: templates/bundles/TwigBundle/Exception/error404.html.twig (specific to 404 status code) and templates/bundles/TwigBundle/Exception/error.html.twig (generic fallback error page).',
                'resourceUrl' => 'https://symfony.com/doc/current/controller/error_pages.html',
                'answers' => [
                    ['text' => 'templates/Resources/TwigBundle/views/error/404.html.twig', 'correct' => false],
                    ['text' => 'templates/bundles/TwigBundle/Exception/error404.html.twig', 'correct' => true],
                    ['text' => 'templates/bundles/TwigBundle/Exception/error.html.twig', 'correct' => true],
                    ['text' => 'bundles/TwigBundle/Exception/404.html.twig', 'correct' => false],
                    ['text' => 'templates/TwigBundle/error.404.twig', 'correct' => false],
                ],
            ],

            // QUESTION 71 - PropertyInfo component
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Miscellaneous'],
                'text' => 'Which of the following Symfony components is responsible for getting information about class properties by using different sources of metadata?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The PropertyInfo component extracts metadata about class properties. It detects property types, identifies whether a property is accessible (readable/writable), and extracts information from multiple sources: PHP reflection, PHPDoc, accessors, and PHP 8+ attributes.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/property_info.html',
                'answers' => [
                    ['text' => 'PropertyAccess', 'correct' => false],
                    ['text' => 'Validator', 'correct' => false],
                    ['text' => 'PropertyInfo', 'correct' => true],
                    ['text' => 'Finder', 'correct' => false],
                    ['text' => 'VarDumper', 'correct' => false],
                ],
            ],

            // QUESTION 72 - Event listeners with same priority
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'If two listeners are associated with the same event and they have exactly the same priority, Symfony only executes the listener which was first defined.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'When two listeners have exactly the same priority, Symfony executes BOTH of them, in the order they were registered. Symfony does not execute "only" the first one. Both listeners are executed, and the execution order is determined by the registration order.',
                'resourceUrl' => 'https://symfony.com/doc/current/event_dispatcher.html#event-listener-priority',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],

            // QUESTION 73 - composer dump-env prod command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'Which command can you run in production to improve performance when using env vars for configuration in a default Symfony application?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The composer dump-env prod command optimizes environment variable usage. It parses all .env* files and generates an optimized .env.local.php file. This PHP file is faster to load than .env text files and significantly improves performance in production.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration.html#configuring-environment-variables-in-production',
                'answers' => [
                    ['text' => 'composer dump-env prod', 'correct' => true],
                    ['text' => 'composer create:env-prod', 'correct' => false],
                    ['text' => 'composer generate:env-file --prod', 'correct' => false],
                    ['text' => 'composer dump --optimize', 'correct' => false],
                    ['text' => 'composer dump env-var', 'correct' => false],
                ],
            ],

            // QUESTION 74 - stopPropagation method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Consider the following code from an event listener:
<pre><code class="language-php">use App\Events\Blog\CommentPublishedEvent;

class BlogListener
{
    public function onBlogComment(CommentPublishedEvent $event): void
    {
        // ...

        $event->???();
    }
}</code></pre>

Which method does ??? successfully replace in order to prevent other listeners from responding to this same event?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'The stopPropagation() method prevents subsequent listeners from being executed for the same event. All Symfony events extend Event or implement StoppableEventInterface. stopPropagation() marks the event as "stopped". Subsequent listeners (with lower priority) are not executed.',
                'resourceUrl' => 'https://symfony.com/doc/current/event_dispatcher.html#stopping-event-flow-propagation',
                'answers' => [
                    ['text' => 'cancelPropagation()', 'correct' => false],
                    ['text' => 'stop()', 'correct' => false],
                    ['text' => 'skip()', 'correct' => false],
                    ['text' => 'stopPropagation()', 'correct' => true],
                    ['text' => 'cancelNext()', 'correct' => false],
                ],
            ],

            // QUESTION 75 - Intl component classes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Miscellaneous'],
                'text' => 'Which of the following is not a class defined in the Intl component to provide access to ICU (International Components for Unicode) data?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Symfony\'s Intl component provides several classes for accessing ICU data: Currencies, Countries, Languages, Locales. But "NumberFormats" is not one of them. To format numbers, use NumberFormatter (a native PHP class from the intl extension), not a class from the Intl component.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/forms/types/country.html',
                'answers' => [
                    ['text' => 'Currencies', 'correct' => false],
                    ['text' => 'NumberFormats', 'correct' => true],
                    ['text' => 'Locales', 'correct' => false],
                    ['text' => 'Countries', 'correct' => false],
                    ['text' => 'Languages', 'correct' => false],
                ],
            ],
        ];
    }
}
