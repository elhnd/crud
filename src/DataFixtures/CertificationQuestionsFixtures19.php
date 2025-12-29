<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Subcategory;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 19
 */
class CertificationQuestionsFixtures19 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures18::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \RuntimeException('Categories not found. Run AppFixtures first.');
        }

        $subcategories = $this->getSubcategories($manager, $symfony, $php);
        $questions = $this->getCertificationQuestions($symfony, $php, $subcategories);

        foreach ($questions as $q) {
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }

    private function getSubcategories(ObjectManager $manager, Category $symfony, Category $php): array
    {
        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        $subcategories = [];

        foreach ($subcategoryRepo->findAll() as $sub) {
            $subcategories[$sub->getCategory()->getName() . ':' . $sub->getName()] = $sub;
        }

        $additional = [
            'Symfony' => [
                'Expression Language' => 'Expression Language component for evaluating expressions',
                'Messenger' => 'Messenger component for message-based applications',
            ],
        ];

        foreach ($additional as $catName => $subs) {
            $category = $catName === 'Symfony' ? $symfony : $php;
            foreach ($subs as $name => $description) {
                $key = $catName . ':' . $name;
                if (!isset($subcategories[$key])) {
                    $sub = new Subcategory();
                    $sub->setName($name);
                    $sub->setDescription($description);
                    $sub->setCategory($category);
                    $manager->persist($sub);
                    $subcategories[$key] = $sub;
                }
            }
        }

        $manager->flush();
        return $subcategories;
    }

    private function getCertificationQuestions(Category $symfony, Category $php, array $subcategories): array
    {
        return [
            // Q1 - PHP - Generator usage
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Could a generator contains a <code>return</code> statement?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, un générateur peut contenir une instruction return. Depuis PHP 7.0, les générateurs peuvent retourner une valeur finale via return, accessible avec Generator::getReturn().',
                'resourceUrl' => 'https://www.php.net/manual/en/language.generators.syntax.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q2 - Expression Language - Parser configuration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'] ?? $subcategories['Symfony:Services'],
                'text' => 'Could the parser cache be changed?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le cache du parser peut être changé. On peut passer une instance de cache PSR-6 au constructeur d\'ExpressionLanguage pour personnaliser le cache des expressions parsées.',
                'resourceUrl' => 'https://symfony.com/doc/2.4/components/expression_language/caching.html#the-workflow',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q3 - PHP - Type casts
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What should replace the <code>/* ... */</code> in order to echo <code>foo</code>?
<pre><code class="language-php">$t = \'foo\';
$o = (object)$t;
echo $o->/* ... */;</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Quand on cast une valeur scalaire en objet, PHP crée un objet stdClass avec une propriété nommée "scalar" contenant la valeur. Donc $o->scalar retourne "foo".',
                'resourceUrl' => 'http://php.net/manual/en/language.types.type-juggling.php, http://php.net/manual/en/language.types.object.php#language.types.object.casting',
                'answers' => [
                    ['text' => '<code>text</code>', 'correct' => false],
                    ['text' => '<code>foo</code>', 'correct' => false],
                    ['text' => '<code>object</code>', 'correct' => false],
                    ['text' => 'This is not possible.', 'correct' => false],
                    ['text' => '<code>scalar</code>', 'correct' => true],
                ],
            ],

            // Q4 - HttpKernel - Sub Requests
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is a sub request?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Une sous-requête sert à rendre une petite portion d\'une page au lieu d\'une page complète. C\'est utile pour inclure des contrôleurs dans des templates.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_kernel.html#sub-requests',
                'answers' => [
                    ['text' => 'A sub request is a request used in tests.', 'correct' => false],
                    ['text' => 'A sub request is a request from a HTTP reverse proxy.', 'correct' => false],
                    ['text' => 'A sub request serves to create HTTP cache headers.', 'correct' => false],
                    ['text' => 'A sub request serves to render just one small portion of a page instead of a full page.', 'correct' => true],
                ],
            ],

            // Q5 - Routing - Route attributes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'It is possible to specify a default value for an attribute in a route?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => 'Oui, il est possible de spécifier une valeur par défaut pour un paramètre de route en utilisant la section "defaults" ou directement dans le path avec la syntaxe {param?default}.',
                'resourceUrl' => 'http://symfony.com/doc/current/create_framework/routing.html, http://symfony.com/doc/current/routing.html#giving-placeholders-a-default-value',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q6 - Messenger - Handlers configuration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Messenger'] ?? $subcategories['Symfony:Services'],
                'text' => 'Could handlers be restricted per bus?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, les handlers peuvent être restreints par bus en utilisant l\'attribut #[AsMessageHandler] avec l\'option "bus" ou via la configuration du tag messenger.message_handler.',
                'resourceUrl' => 'https://symfony.com/doc/4.1/messenger/multiple_buses.html#restrict-handlers-per-bus',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q8 - DI - ContainerConfigurator parameters
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could parameters be configured using <code>ContainerConfigurator</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, les paramètres peuvent être configurés avec ContainerConfigurator en utilisant la méthode parameters() qui retourne un ParametersConfigurator.',
                'resourceUrl' => 'https://github.com/symfony/dependency-injection/blob/3.4/Loader/Configurator/ContainerConfigurator.php#L63',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q9 - DI - Enumerations usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Could enumerations be used with <code>!php/const</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 5.4, les énumérations PHP 8.1 peuvent être utilisées avec !php/const dans les fichiers de configuration YAML pour référencer des cas d\'énumération.',
                'resourceUrl' => 'https://github.com/symfony/symfony/pull/40857',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q10 - Security - Access Decision Manager
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What does the <code>affirmative</code> strategy of <code>AccessDecisionManager</code> do?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La stratégie "affirmative" accorde l\'accès dès qu\'un voter accorde l\'accès. C\'est la stratégie par défaut et la plus permissive.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/security/voters.html#changing-the-access-decision-strategy',
                'answers' => [
                    ['text' => 'Grant access if there are more voters granting access than there are denying.', 'correct' => false],
                    ['text' => 'Grant access as soon as there is one voter granting access.', 'correct' => true],
                    ['text' => 'Only grant access if none of the voters has denied access.', 'correct' => false],
                ],
            ],

            // Q11 - Serializer - Normalizers
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'Which of the followings are built-in normalizers?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les normalizers intégrés incluent: DateTimeNormalizer, GetSetMethodNormalizer, DataUriNormalizer, CustomNormalizer, et ObjectNormalizer. JsonNormalizer et XmlNormalizer n\'existent pas (ce sont des encoders, pas des normalizers).',
                'resourceUrl' => 'https://github.com/symfony/symfony/tree/3.1/src/Symfony/Component/Serializer/Normalizer',
                'answers' => [
                    ['text' => '<code>DateNormalizer</code>', 'correct' => false],
                    ['text' => '<code>CustomNormalizer</code>', 'correct' => true],
                    ['text' => '<code>TimeNormalizer</code>', 'correct' => false],
                    ['text' => '<code>GetSetMethodNormalizer</code>', 'correct' => true],
                    ['text' => '<code>XmlNormalizer</code>', 'correct' => false],
                    ['text' => '<code>DataUriNormalizer</code>', 'correct' => true],
                    ['text' => '<code>DateTimeNormalizer</code>', 'correct' => true],
                    ['text' => '<code>JsonNormalizer</code>', 'correct' => false],
                    ['text' => '<code>ObjectNormalizer</code>', 'correct' => true],
                ],
            ],

            // Q12 - Validator - Constraint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validator'],
                'text' => 'Which of the following are Symfony built-in validation constraint?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les contraintes intégrées sont: IsNull, NotNull, NotBlank et Blank. Les contraintes "Null" n\'existe pas (c\'est IsNull).',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/constraints.html',
                'answers' => [
                    ['text' => 'Blank', 'correct' => true],
                    ['text' => 'IsNull', 'correct' => true],
                    ['text' => 'NotNull', 'correct' => true],
                    ['text' => 'NotBlank', 'correct' => true],
                    ['text' => 'Null', 'correct' => false],
                    ['text' => 'IsBlank', 'correct' => false],
                ],
            ],

            // Q13 - Form - RangeType options
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which of the following snippets is valid to set the maximum and minimum value for a <code>Symfony\Component\Form\Extension\Core\Type\RangeType</code> form type?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour RangeType, les valeurs min et max doivent être définies dans l\'option "attr" car elles correspondent aux attributs HTML min et max de l\'élément input range.',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/forms/types/range.html',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$formBuilder->add(\'name\', RangeType::class, [
    \'attr\' => [
        \'minimum\' => 5,
        \'maximum\' => 50
    ]
]);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$formBuilder->add(\'name\', RangeType::class, [
    \'minimum\' => 5,
    \'maximum\' => 50
]);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$formBuilder->add(\'name\', RangeType::class, [
    \'min\' => 5,
    \'max\' => 50
]);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$formBuilder->add(\'name\', RangeType::class, [
    \'attr\' => [
        \'min\' => 5,
        \'max\' => 50
    ]
]);</code></pre>', 'correct' => true],
                ],
            ],

            // Q15 - PHP - Extract usage
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'] ?? $subcategories['PHP:PHP Basics'],
                'text' => 'Could variables be extracted as references when using <code>extract</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, extract() supporte le flag EXTR_REFS qui extrait les variables comme références, ce qui permet de modifier les valeurs du tableau d\'origine.',
                'resourceUrl' => 'https://www.php.net/manual/en/function.extract.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q16 - DI - Auto_alias usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

use AppBundle\Lock\MysqlLock;
use AppBundle\Lock\PostgresqlLock;
use AppBundle\Lock\SqliteLock;

// Given $container is an instance of ContainerBuilder

$container->register(\'app.mysql_lock\', MysqlLock::class)->setPublic(false);
$container->register(\'app.postgresql_lock\', PostgresqlLock::class)->setPublic(false);
$container->register(\'app.sqlite_lock\', SqliteLock::class)->setPublic(false);

$container->register(\'app.lock\')->addTag(\'auto_alias\', [
  \'format\' => \'app.%database_type%_lock\',
]);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le tag auto_alias permet de créer automatiquement un alias vers un service basé sur un paramètre. Le format spécifie le pattern du nom de service avec le placeholder %database_type%.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/reference/dic_tags.html#auto-alias, https://github.com/symfony/symfony/blob/2.7/src/Symfony/Component/DependencyInjection/Compiler/AutoAliasServicePass.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q18 - HttpKernel - LocaleListener
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What is the aim of the <code>Symfony\Component\HttpKernel\EventListener\LocaleListener::onKernelRequest()</code> listener on <code>kernel.request</code> event?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'LocaleListener initialise la locale en fonction de la requête actuelle. Il vérifie d\'abord si un paramètre _locale existe dans la route, sinon utilise la locale par défaut.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/3f43534332046a141a179184eb58620b7e3d9ed3/src/Symfony/Component/HttpKernel/EventListener/LocaleListener.php#L56',
                'answers' => [
                    ['text' => 'Find the user locale based on the user session.', 'correct' => false],
                    ['text' => 'Save the current locale in a cookie', 'correct' => false],
                    ['text' => 'Initializes the locale based on the current request.', 'correct' => true],
                ],
            ],

            // Q19 - HttpClient - Response
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpClient'] ?? $subcategories['Symfony:Services'],
                'text' => 'Could you emulate chunked responses and/or timeouts in mocked responses?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, MockResponse permet d\'émuler des réponses chunked avec le paramètre body comme générateur, et des timeouts en spécifiant des délais dans les options.',
                'resourceUrl' => 'https://symfony.com/doc/5.2/http_client.html#testing-http-clients-and-responses',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q20 - PHP - PHP Iteration (Generators)
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'Consider the following code snippet:
<pre><code class="language-php">&lt;?php
function xrange($len) {
    $i = 0;
    while ($i < $len) {
        /* ??? */
        $i++;
    }
    return;
}

foreach (xrange(1000) as $i) {
    /* Do something ... */
}</code></pre>
<p>What should replace <code>/* ??? */</code> to make <code>$i</code> available to the <code>foreach</code> loop?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le mot-clé yield transforme la fonction en générateur, permettant de produire des valeurs une par une qui peuvent être itérées avec foreach.',
                'resourceUrl' => 'http://php.net/generators',
                'answers' => [
                    ['text' => '<pre><code class="language-php">yield $i;</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">new Iterator($i);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">return $i;</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">push $i;</code></pre>', 'correct' => false],
                ],
            ],

            // Q23 - Form - DateType HTML5 rendering
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What option should you use in order to have a <code>Symfony\Component\Form\Extension\Core\Type\DateType</code> form type rendered as an HTML5 <code>input type="date"</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'option "widget" avec la valeur "single_text" combinée à l\'option "html5" à true (par défaut) permet de rendre DateType comme un input HTML5 type="date".',
                'resourceUrl' => 'https://symfony.com/doc/2.x/reference/forms/types/date.html',
                'answers' => [
                    ['text' => '<code>no_javascript</code>', 'correct' => false],
                    ['text' => '<code>datepicker</code>', 'correct' => false],
                    ['text' => '<code>type_date</code>', 'correct' => false],
                    ['text' => '<code>html5</code>', 'correct' => true],
                ],
            ],

            // Q24 - Runtime - Configuration
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Runtime'] ?? $subcategories['Symfony:Services'],
                'text' => 'Is it possible to define environment variables only for the <code>prod</code> environment?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, en utilisant des fichiers .env.prod ou .env.prod.local, les variables ne seront chargées que dans l\'environnement de production.',
                'resourceUrl' => 'https://symfony.com/doc/5.3/components/runtime.html#using-options',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q27 - Security - new Authenticator-Based Security
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In the Authenticator-Based Security, Is it possible to create a custom entry_point?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, un entry_point personnalisé peut être créé en implémentant AuthenticationEntryPointInterface et en le configurant dans security.yaml.',
                'resourceUrl' => 'https://symfony.com/doc/5.1/security/experimental_authenticators.html#configuring-the-authentication-entry-point',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q28 - HttpKernel - Kernel class
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpKernel'],
                'text' => 'What are the arguments of the <code>Symfony\Component\HttpKernel\Kernel</code> constructor?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le constructeur du Kernel prend deux arguments: l\'environnement (string) et le flag de debug (bool). Il ne prend pas de paramètre pour le logging ou le caching.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/HttpKernel/Kernel.php',
                'answers' => [
                    ['text' => 'Whether to enable caching or not.', 'correct' => false],
                    ['text' => 'Whether to enable logging or not.', 'correct' => false],
                    ['text' => 'Whether to enable debugging or not.', 'correct' => true],
                    ['text' => 'The environment name of the application.', 'correct' => true],
                    ['text' => 'The name of the application.', 'correct' => false],
                ],
            ],

            // Q32 - HttpFoundation - Cookies partitioned
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could the fact that a cookie is partitioned be checked?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 6.4, la classe Cookie a une méthode isPartitioned() pour vérifier si un cookie utilise l\'attribut Partitioned (CHIPS).',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-4-chips-cookies, https://github.com/symfony/symfony/blob/6.4/src/Symfony/Component/HttpFoundation/Cookie.php, https://github.com/symfony/symfony/blob/6.4/src/Symfony/Component/HttpFoundation/Cookie.php#L246',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q33 - PropertyAccess - Reading from Arrays
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:PropertyAccess'] ?? $subcategories['Symfony:Services'],
                'text' => 'What is the way to get the value of the <code>first_name</code> index of the <code>$person</code> array?
<pre><code class="language-php">$person = array(
    \'first_name\' => \'Wouter\',
);</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour accéder à un index de tableau avec PropertyAccessor, il faut utiliser la notation entre crochets [first_name], pas seulement le nom de la clé.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/property_access/introduction.html#reading-from-arrays',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->readProperty($person, \'first_name\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->getValue($person, \'first_name\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->readIndex($person, \'first_name\');</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$firstName = $accessor->getValue($person, \'[first_name]\');</code></pre>', 'correct' => true],
                ],
            ],

            // Q34 - Form - Render
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'How can you render a form named <code>form</code> in a Twig template?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Un formulaire peut être rendu avec form(form) (raccourci) ou manuellement avec form_start(), form_widget() et form_end().',
                'resourceUrl' => 'http://symfony.com/doc/current/reference/forms/twig_reference.html, http://symfony.com/doc/current/book/forms.html',
                'answers' => [
                    ['text' => '<pre><code class="language-twig">{{ render_form(form) }}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% form(form) %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{{ form_start(form) }}
{{ form_widget(form) }}
{{ form_end(form) }}</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-twig">{{ form(form) }}</code></pre>', 'correct' => true],
                ],
            ],

            // Q38 - OptionsResolver - setAllowedTypes for null
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:OptionsResolver'] ?? $subcategories['Symfony:Services'],
                'text' => 'When using the <code>OptionsResolver</code>, what is the correct call to <code>setAllowedTypes</code> to allow the value <code>null</code> for the option named <code>my_option</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour autoriser null, il faut passer la chaîne \'null\' (ou un tableau contenant \'null\') à setAllowedTypes. Passer null directement ou [null] ne fonctionne pas.',
                'resourceUrl' => 'http://symfony.com/doc/2.6/components/options_resolver.html#type-validation, https://github.com/symfony/symfony/blob/240e9648af3daa5ed19580fdec74d768e30692a6/src/Symfony/Component/OptionsResolver/OptionsResolver.php#L572',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$resolver->setAllowedTypes(\'my_option\', [null]);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$resolver->setAllowedTypes(\'my_option\', null);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$resolver->setAllowedTypes(\'my_option\', \'null\');</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$resolver->setAllowedTypes(\'my_option\', [\'null\']);</code></pre>', 'correct' => true],
                ],
            ],

            // Q39 - Expression Language - contains operator
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Expression Language'] ?? $subcategories['Symfony:Services'],
                'text' => 'Is the following code valid?
<pre><code class="language-php">&lt;?php

# ...

$expressionLanguage = new ExpressionLanguage();

$expressionLanguage->evaluate(\'url contains "example.com"\', [
    \'url\' => \'https://example.com/api\',
]);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 6.1, l\'opérateur "contains" est disponible dans ExpressionLanguage pour vérifier si une chaîne contient une sous-chaîne.',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-6-1-improved-expressionlanguage-syntax#new-operators, https://symfony.com/doc/current/reference/formats/expression_language.html#comparison-operators',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q40 - Twig - Template inheritance error
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which of the following templates will throw an error <code>A template that extends another one cannot have a body</code>?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un template qui étend un autre ne peut pas avoir de contenu en dehors des blocs. {{ \'f\' ~ \'oo\' }} est du contenu hors bloc, donc provoque l\'erreur.',
                'resourceUrl' => 'http://twig.symfony.com/doc/tags/extends.html',
                'answers' => [
                    ['text' => '<pre><code class="language-twig">{% extends \'base.html.twig\' %}

{% block body \'foo\' %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% extends \'base.html.twig\' %}

{% block body %}
    foo
{% endblock %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% extends \'base.html.twig\' %}

{% block foo \'foo\' %}
{% block body block(\'foo\') %}</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-twig">{% extends \'base.html.twig\' %}

{{ \'f\' ~ \'oo\' }}</code></pre>', 'correct' => true],
                ],
            ],

            // Q42 - Form - Empty_data usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'Which type of argument is passed if a closure is used in <code>empty_data</code> option?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Quand une closure est utilisée comme empty_data, elle reçoit une instance de FormInterface en argument, permettant d\'accéder aux données et options du formulaire.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/form/use_empty_data.html#option-2-provide-a-closure',
                'answers' => [
                    ['text' => 'An instance of <code>FormInterface</code>', 'correct' => true],
                    ['text' => 'An instance of <code>Option</code>', 'correct' => false],
                    ['text' => 'An array', 'correct' => false],
                    ['text' => 'Nothing', 'correct' => false],
                ],
            ],

            // Q43 - Dotenv - Dotenv overriding
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dotenv'] ?? $subcategories['Symfony:Services'],
                'text' => 'Given you have an already register env var <code>MYVAR=foo</code> in your shell and you load <code>MYVAR=bar</code> from a <code>.env</code> file via the Dotenv component. What will happen?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Dotenv ne remplace pas les variables d\'environnement existantes. getenv() retourne la valeur du shell (foo), mais $_ENV peut contenir la valeur du fichier .env (bar).',
                'resourceUrl' => 'https://symfony.com/doc/3.3/components/dotenv.html',
                'answers' => [
                    ['text' => 'an exception will be thrown', 'correct' => false],
                    ['text' => '$_ENV[\'MYVAR\'] will be equal to <code>bar</code>', 'correct' => true],
                    ['text' => 'getenv(\'MYVAR\') will return <code>bar</code>', 'correct' => false],
                    ['text' => 'getenv(\'MYVAR\') will return <code>foo</code>', 'correct' => true],
                    ['text' => '$_ENV[\'MYVAR\'] will be equal to <code>foo</code>', 'correct' => false],
                ],
            ],

            // Q44 - PHP - Arrays sorting
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:Arrays'] ?? $subcategories['PHP:PHP Basics'],
                'text' => 'All array sorting functions take the array to sort as reference and modify it instead of returning the sorted array',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Vrai, toutes les fonctions de tri PHP (sort, rsort, asort, ksort, etc.) modifient le tableau passé par référence et retournent un booléen indiquant le succès.',
                'resourceUrl' => 'https://www.php.net/manual/en/array.sorting.php',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // Q45 - Routing - Host condition with placeholders
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'Is it possible to use placeholders in <code>host</code> section of a route definition?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, les placeholders peuvent être utilisés dans la section host d\'une route pour gérer le routing par sous-domaine, par exemple: host: "{subdomain}.example.com".',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#sub-domain-routing',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],
        ];
    }
}
