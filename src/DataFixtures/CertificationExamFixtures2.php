<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Subcategory;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Real Certification Exam Questions - Part 2 (Questions 31-75)
 */
class CertificationExamFixtures2 extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['certification', 'certification2'];
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
            $question = new Question();
            $question->setText($q['text']);
            $question->setTypeEnum($q['type']);
            $question->setDifficulty($q['difficulty']);
            $question->setExplanation($q['explanation']);
            $question->setResourceUrl($q['resourceUrl'] ?? null);
            $question->setCategory($q['category']);
            $question->setSubcategory($q['subcategory']);
            $question->setIsCertification(true);

            foreach ($q['answers'] as $a) {
                $answer = new Answer();
                $answer->setText($a['text']);
                $answer->setIsCorrect($a['correct']);
                $question->addAnswer($answer);
            }

            $manager->persist($question);
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
                'HTTP' => 'HTTP specification and status codes',
                'Cache' => 'HTTP caching and ESI',
                'Console' => 'Symfony Console commands',
                'Security' => 'Authentication and authorization',
                'Events' => 'Event dispatcher and listeners',
                'Serializer' => 'Symfony Serializer component',
                'Doctrine' => 'Doctrine ORM integration',
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
            // QUESTION 31 - getBlockPrefix (from Q37)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'In a Symfony application that uses a custom form type with custom theme blocks, how do you change the default name of the Twig blocks used to render that form type?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode getBlockPrefix() dans un custom form type détermine le préfixe utilisé pour les blocks Twig. Par exemple, si getBlockPrefix() retourne \'my_custom\', Symfony cherchera my_custom_widget, my_custom_row, etc.',
                'answers' => [
                    ['text' => 'Override the getName() method.', 'correct' => false],
                    ['text' => 'Add a new block_name option to the form type\'s optionsResolver.', 'correct' => false],
                    ['text' => 'Override the getBlockPrefix() method from the base AbstractType class.', 'correct' => true],
                    ['text' => 'Override the setRenderingBlockName() method.', 'correct' => false],
                    ['text' => 'It\'s not possible to change the Twig block name from the form type definition class.', 'correct' => false],
                ],
            ],

            // QUESTION 32 - Form POST_SUBMIT event (from Q38)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'How can you dynamically change the submitted data of a form object just after they are normalized and written into the mapped object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'L\'événement FormEvents::POST_SUBMIT est déclenché après que les données ont été normalisées et écrites dans l\'objet mappé. C\'est l\'endroit approprié pour modifier les données.',
                'resourceUrl' => 'https://symfony.com/doc/current/form/events.html',
                'answers' => [
                    ['text' => 'Creating a listener that listens to the kernel.request main event.', 'correct' => false],
                    ['text' => 'Overriding the submit() method of the form type class.', 'correct' => false],
                    ['text' => 'Attaching a listener to the form type object that listens to the form.post_submit form event.', 'correct' => true],
                    ['text' => 'Declaring a new postSubmitData() method in the form type class.', 'correct' => false],
                    ['text' => 'It\'s not possible to dynamically change the submitted data of a form object.', 'correct' => false],
                ],
            ],

            // QUESTION 33 - row_attr variable (from Q39)
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
                'explanation' => 'La variable row_attr contient les attributs HTML de la ligne de formulaire. Pour accéder à la classe existante, on utilise row_attr.class.',
                'answers' => [
                    ['text' => 'attributes', 'correct' => false],
                    ['text' => 'widget_attr', 'correct' => false],
                    ['text' => 'parent_attr', 'correct' => false],
                    ['text' => '_attr', 'correct' => false],
                    ['text' => 'row_attr', 'correct' => true],
                ],
            ],

            // QUESTION 34 - HTTP POST not idempotent (from Q40)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following methods is NOT considered idempotent by the HTTP specification?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'POST n\'est pas idempotent car chaque appel peut créer une nouvelle ressource. GET, PUT, DELETE et HEAD sont idempotents.',
                'resourceUrl' => 'https://developer.mozilla.org/en-US/docs/Glossary/Idempotent',
                'answers' => [
                    ['text' => 'PUT', 'correct' => false],
                    ['text' => 'DELETE', 'correct' => false],
                    ['text' => 'POST', 'correct' => true],
                    ['text' => 'GET', 'correct' => false],
                    ['text' => 'HEAD', 'correct' => false],
                ],
            ],

            // QUESTION 35 - Invalid HTTP status code (from Q41)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following is NOT a valid HTTP status code?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le code 505 existe mais signifie "HTTP Version Not Supported", pas "Locale Not Available". "505 Locale Not Available" n\'est pas un code standard.',
                'answers' => [
                    ['text' => '204 No Content', 'correct' => false],
                    ['text' => '307 Temporary Redirect', 'correct' => false],
                    ['text' => '451 Unavailable For Legal Reasons', 'correct' => false],
                    ['text' => '505 Locale Not Available', 'correct' => true],
                ],
            ],

            // QUESTION 36 - HTTP 405 (from Q42)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following messages is mostly associated with the 405 HTTP status code?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le code HTTP 405 signifie "Method Not Allowed". Il est retourné quand la méthode HTTP utilisée n\'est pas autorisée pour la ressource.',
                'answers' => [
                    ['text' => 'Unauthorized', 'correct' => false],
                    ['text' => 'Method Not Allowed', 'correct' => true],
                    ['text' => 'Forbidden', 'correct' => false],
                    ['text' => 'Not Found', 'correct' => false],
                    ['text' => 'Payment Required', 'correct' => false],
                ],
            ],

            // QUESTION 37 - 400 is client error (from Q43)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => '400 is one of the server error HTTP status codes.',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 1,
                'explanation' => '400 est un code d\'erreur CLIENT (4xx), pas un code d\'erreur SERVEUR (5xx). Les codes 5xx sont les erreurs serveur.',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],

            // QUESTION 38 - HTTP 503 (from Q44)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following HTTP status codes is mostly associated with the Service Unavailable message?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le code HTTP 503 signifie "Service Unavailable". Il indique que le serveur est temporairement indisponible.',
                'answers' => [
                    ['text' => '500', 'correct' => false],
                    ['text' => '501', 'correct' => false],
                    ['text' => '502', 'correct' => false],
                    ['text' => '503', 'correct' => true],
                    ['text' => '504', 'correct' => false],
                ],
            ],

            // QUESTION 39 - Cache expires (from Q45)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Consider the following controller with HTTP cache:
<pre><code class="language-php">#[Route(\'/time\')]
#[Cache(expires: \'+1 hour\')]
public function time(): Response
{
    return $this->render(\'date/time.html.twig\', [
        \'date\' => new \\DateTime(),
    ]);
}</code></pre>

A user accesses the /time page twice:
1. First request: Wed, March 2nd 16:00:00
2. Second request: Wed, March 2nd 16:50:00

What date/time will the page display on the second request?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le cache HTTP est valide pendant 1 heure. La 2ème requête à 16:50 utilise le cache qui expire à 17:00, donc affiche 16:00:00.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_cache.html',
                'answers' => [
                    ['text' => 'Wed, March 2nd 16:00:00', 'correct' => true],
                    ['text' => 'Wed, March 2nd 16:50:00', 'correct' => false],
                    ['text' => 'It depends on the user\'s timezone.', 'correct' => false],
                    ['text' => 'The date when the page was requested by any other user.', 'correct' => false],
                ],
            ],

            // QUESTION 40 - ESI cache (from Q46)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Consider two controllers with ESI caching:
- /about page with smaxage: 3600 (1 hour) displays Clock1
- news fragment with smaxage: 600 (10 min) displays Clock2

First user requests /about at 16:00:00 GMT.
Second user requests /about at 17:30:00 GMT.

What will each clock display for the second user?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Avec ESI, les fragments ont leur propre cache. À 17:30: Clock1 montre la valeur cachée (16:00:00), Clock2 est régénéré (17:30:00).',
                'resourceUrl' => 'https://symfony.com/doc/current/http_cache/esi.html',
                'answers' => [
                    ['text' => 'Clock1: 17:00:00 GMT / Clock2: 17:30:00 GMT', 'correct' => false],
                    ['text' => 'Clock1: 16:00:00 GMT / Clock2: 17:30:00 GMT', 'correct' => true],
                    ['text' => 'Clock1: 17:30:00 GMT / Clock2: 16:00:00 GMT', 'correct' => false],
                    ['text' => 'Clock1: 17:30:00 GMT / Clock2: 17:30:00 GMT', 'correct' => false],
                ],
            ],

            // QUESTION 41 - ETag alone not cacheable (from Q47)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Cache'],
                'text' => 'Consider a controller that sets an ETag on the response:
<pre><code class="language-php">public function termsOfUse(): Response
{
    $response = $this->render(\'page/tos.html.twig\');
    $response->setETag(\'abcdef\');
    return $response;
}</code></pre>

Is this response cacheable either on the client or on a shared reverse proxy cache?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Définir un ETag seul ne rend pas une réponse cacheable. Il faut aussi définir Cache-Control: public ou s-maxage/max-age.',
                'resourceUrl' => 'https://symfony.com/doc/current/http_cache/validation.html',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],

            // QUESTION 42 - Non-cacheable HTTP methods (from Q48)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'Which of the following HTTP methods are NOT cacheable? (Select all that apply)',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Seuls GET et HEAD sont cacheables. DELETE, PUT et POST modifient l\'état du serveur et ne sont pas mis en cache.',
                'answers' => [
                    ['text' => 'DELETE', 'correct' => true],
                    ['text' => 'PUT', 'correct' => true],
                    ['text' => 'GET', 'correct' => false],
                    ['text' => 'POST', 'correct' => true],
                    ['text' => 'HEAD', 'correct' => false],
                ],
            ],

            // QUESTION 43 - Command must return int (from Q49)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Consider the following command:
<pre><code class="language-php">#[AsCommand(name: \'app:some-command\')]
class SomeCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(\'Hello world\');
    }
}</code></pre>

Will this command run successfully?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode execute() doit retourner un int (Command::SUCCESS, FAILURE ou INVALID). Sans return, une TypeError sera lancée.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html',
                'answers' => [
                    ['text' => 'Yes, it will print the "Hello world" message.', 'correct' => false],
                    ['text' => 'No, it will throw an exception.', 'correct' => true],
                ],
            ],

            // QUESTION 44 - --profile debug mode (from Q50)
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'When running the following command, will Symfony always collect debug information?
<pre><code>php bin/console --profile app:my-command</code></pre>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'option --profile nécessite que le Profiler soit actif, ce qui requiert que l\'application soit en "debug mode" (APP_DEBUG=1).',
                'answers' => [
                    ['text' => 'Yes.', 'correct' => false],
                    ['text' => 'Yes, but only if the command is run in the dev environment.', 'correct' => false],
                    ['text' => 'Yes, but only if the application is running in "debug mode".', 'correct' => true],
                    ['text' => 'No.', 'correct' => false],
                ],
            ],

            // QUESTION 45 - Command return codes
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Which constant should a Symfony console command return to indicate successful execution?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Command::SUCCESS (valeur 0) indique une exécution réussie. Command::FAILURE (1) indique un échec, Command::INVALID (2) une utilisation incorrecte.',
                'resourceUrl' => 'https://symfony.com/doc/current/console.html#command-lifecycle',
                'answers' => [
                    ['text' => 'Command::OK', 'correct' => false],
                    ['text' => 'Command::SUCCESS', 'correct' => true],
                    ['text' => 'Command::DONE', 'correct' => false],
                    ['text' => 'Command::COMPLETED', 'correct' => false],
                    ['text' => 'return 0;', 'correct' => false],
                ],
            ],

            // QUESTION 46 - kernel.request event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Events'],
                'text' => 'Which event is the first one triggered during the handling of an HTTP request in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'kernel.request est le premier événement déclenché. Il permet de modifier la requête ou de retourner une réponse directement.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/events.html',
                'answers' => [
                    ['text' => 'kernel.controller', 'correct' => false],
                    ['text' => 'kernel.request', 'correct' => true],
                    ['text' => 'kernel.view', 'correct' => false],
                    ['text' => 'kernel.response', 'correct' => false],
                    ['text' => 'kernel.start', 'correct' => false],
                ],
            ],

            // QUESTION 47 - Security access_control
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'In Symfony\'s security.yaml, what is the purpose of access_control rules?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'access_control définit les règles d\'autorisation basées sur les patterns d\'URL et les rôles requis.',
                'resourceUrl' => 'https://symfony.com/doc/current/security/access_control.html',
                'answers' => [
                    ['text' => 'To define which users can access which URLs based on their roles.', 'correct' => true],
                    ['text' => 'To configure the authentication mechanism.', 'correct' => false],
                    ['text' => 'To define custom firewall rules.', 'correct' => false],
                    ['text' => 'To encrypt user passwords.', 'correct' => false],
                ],
            ],

            // QUESTION 48 - is_granted in Twig
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'How do you check if the current user has a specific role in a Twig template?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La fonction is_granted() vérifie si l\'utilisateur courant possède le rôle ou l\'attribut spécifié.',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html#access-control-in-templates',
                'answers' => [
                    ['text' => '{% if app.user.hasRole(\'ROLE_ADMIN\') %}', 'correct' => false],
                    ['text' => '{% if is_granted(\'ROLE_ADMIN\') %}', 'correct' => true],
                    ['text' => '{% if user_has_role(\'ROLE_ADMIN\') %}', 'correct' => false],
                    ['text' => '{% if app.security.isGranted(\'ROLE_ADMIN\') %}', 'correct' => false],
                ],
            ],

            // QUESTION 49 - Password hashing
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'Which service should you use to hash user passwords in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'UserPasswordHasherInterface est le service pour hasher les mots de passe dans Symfony 5.3+.',
                'resourceUrl' => 'https://symfony.com/doc/current/security/passwords.html',
                'answers' => [
                    ['text' => 'PasswordEncoderInterface', 'correct' => false],
                    ['text' => 'UserPasswordHasherInterface', 'correct' => true],
                    ['text' => 'SecurityHasher', 'correct' => false],
                    ['text' => 'PasswordService', 'correct' => false],
                ],
            ],

            // QUESTION 50 - Firewall stateless
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What does the "stateless: true" option do in a Symfony firewall configuration?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'stateless: true désactive la session pour ce firewall. Chaque requête doit s\'authentifier indépendamment (utile pour les API).',
                'resourceUrl' => 'https://symfony.com/doc/current/security.html#stateless-authentication',
                'answers' => [
                    ['text' => 'It disables session usage for that firewall.', 'correct' => true],
                    ['text' => 'It makes the firewall inactive.', 'correct' => false],
                    ['text' => 'It disables CSRF protection.', 'correct' => false],
                    ['text' => 'It enables anonymous access.', 'correct' => false],
                ],
            ],

            // QUESTION 51 - Serializer groups
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Serializer'],
                'text' => 'How do you specify which properties should be serialized when using the Symfony Serializer?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'L\'attribut #[Groups] permet de définir quelles propriétés sont incluses lors de la sérialisation.',
                'resourceUrl' => 'https://symfony.com/doc/current/serializer.html#using-serialization-groups-attributes',
                'answers' => [
                    ['text' => 'Using the #[Serialize] attribute on properties.', 'correct' => false],
                    ['text' => 'Using the #[Groups] attribute on properties.', 'correct' => true],
                    ['text' => 'Using the @Expose annotation.', 'correct' => false],
                    ['text' => 'Using getSerializableProperties() method.', 'correct' => false],
                ],
            ],

            // QUESTION 52 - Doctrine entity manager
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'Which method must be called to save a new entity to the database in Doctrine?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Pour une nouvelle entité, persist() puis flush() sont nécessaires. persist() prépare l\'entité, flush() exécute les requêtes SQL.',
                'resourceUrl' => 'https://symfony.com/doc/current/doctrine.html#persisting-objects-to-the-database',
                'answers' => [
                    ['text' => 'Only $em->save($entity);', 'correct' => false],
                    ['text' => '$em->persist($entity); followed by $em->flush();', 'correct' => true],
                    ['text' => 'Only $em->flush($entity);', 'correct' => false],
                    ['text' => '$em->insert($entity);', 'correct' => false],
                ],
            ],

            // QUESTION 53 - Repository custom method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'In a Doctrine repository, which method should you use to create custom queries?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'createQueryBuilder() permet de construire des requêtes personnalisées avec une syntaxe fluide.',
                'resourceUrl' => 'https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository',
                'answers' => [
                    ['text' => '$this->createQuery()', 'correct' => false],
                    ['text' => '$this->createQueryBuilder(\'alias\')', 'correct' => true],
                    ['text' => '$this->buildQuery()', 'correct' => false],
                    ['text' => '$this->getQuery()', 'correct' => false],
                ],
            ],

            // QUESTION 54 - Doctrine migrations
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'],
                'text' => 'Which command generates a new migration based on the differences between your entities and the database schema?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'doctrine:migrations:diff compare les entités avec le schéma actuel et génère une migration avec les différences.',
                'resourceUrl' => 'https://symfony.com/doc/current/doctrine.html#migrations-creating-the-database-tables-columns',
                'answers' => [
                    ['text' => 'php bin/console doctrine:migrations:generate', 'correct' => false],
                    ['text' => 'php bin/console doctrine:migrations:diff', 'correct' => true],
                    ['text' => 'php bin/console doctrine:schema:update', 'correct' => false],
                    ['text' => 'php bin/console make:migration', 'correct' => false],
                ],
            ],

            // QUESTION 55 - Event priority
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Events'],
                'text' => 'In Symfony\'s event system, which listener is called first: one with priority 10 or one with priority 100?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les listeners avec une priorité plus élevée sont appelés en premier. 100 > 10, donc le listener avec priorité 100 est appelé en premier.',
                'resourceUrl' => 'https://symfony.com/doc/current/event_dispatcher.html#connecting-listeners',
                'answers' => [
                    ['text' => 'The one with priority 10', 'correct' => false],
                    ['text' => 'The one with priority 100', 'correct' => true],
                    ['text' => 'They are called in random order', 'correct' => false],
                    ['text' => 'They are called at the same time', 'correct' => false],
                ],
            ],

            // QUESTION 56 - Stop propagation
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Events'],
                'text' => 'How can an event listener prevent other listeners from being called for an event?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode stopPropagation() sur l\'objet événement empêche les listeners suivants d\'être appelés.',
                'resourceUrl' => 'https://symfony.com/doc/current/event_dispatcher.html#stopping-event-flow-propagation',
                'answers' => [
                    ['text' => 'Return false from the listener', 'correct' => false],
                    ['text' => 'Call $event->stopPropagation()', 'correct' => true],
                    ['text' => 'Throw a StopPropagationException', 'correct' => false],
                    ['text' => 'Call $event->cancel()', 'correct' => false],
                ],
            ],

            // QUESTION 57 - Kernel exception event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Events'],
                'text' => 'Which event allows you to handle exceptions and return a custom error response?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'kernel.exception est déclenché quand une exception non attrapée se produit. Vous pouvez y définir une réponse personnalisée.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/events.html#kernel-exception',
                'answers' => [
                    ['text' => 'kernel.error', 'correct' => false],
                    ['text' => 'kernel.exception', 'correct' => true],
                    ['text' => 'kernel.terminate', 'correct' => false],
                    ['text' => 'kernel.failure', 'correct' => false],
                ],
            ],

            // QUESTION 58 - AsEventListener attribute
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Events'],
                'text' => 'Which attribute should you use to register a method as an event listener in Symfony 6.2+?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '#[AsEventListener] est l\'attribut pour enregistrer un listener directement sur une méthode.',
                'resourceUrl' => 'https://symfony.com/doc/current/event_dispatcher.html#defining-event-listeners-with-php-attributes',
                'answers' => [
                    ['text' => '#[EventSubscriber]', 'correct' => false],
                    ['text' => '#[AsEventListener]', 'correct' => true],
                    ['text' => '#[Listener]', 'correct' => false],
                    ['text' => '#[OnEvent]', 'correct' => false],
                ],
            ],

            // QUESTION 59 - Twig escape strategy
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'What is the default auto-escaping strategy in Twig for HTML templates?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Twig échappe automatiquement les variables pour HTML par défaut afin de prévenir les attaques XSS.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/api.html#escaper-extension',
                'answers' => [
                    ['text' => 'No escaping (raw output)', 'correct' => false],
                    ['text' => 'HTML escaping', 'correct' => true],
                    ['text' => 'JavaScript escaping', 'correct' => false],
                    ['text' => 'URL escaping', 'correct' => false],
                ],
            ],

            // QUESTION 60 - Twig raw filter
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which Twig filter should you use to output HTML without escaping?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le filtre |raw désactive l\'échappement automatique et affiche le contenu HTML tel quel.',
                'resourceUrl' => 'https://twig.symfony.com/doc/3.x/filters/raw.html',
                'answers' => [
                    ['text' => '|html', 'correct' => false],
                    ['text' => '|raw', 'correct' => true],
                    ['text' => '|unsafe', 'correct' => false],
                    ['text' => '|noescape', 'correct' => false],
                ],
            ],

            // QUESTION 61 - Environment parameter syntax
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'What is the correct syntax to use an environment variable as a parameter in services.yaml?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La syntaxe \'%env(VAR_NAME)%\' résout la variable d\'environnement au runtime.',
                'resourceUrl' => 'https://symfony.com/doc/current/configuration.html#accessing-configuration-parameters',
                'answers' => [
                    ['text' => '${DATABASE_URL}', 'correct' => false],
                    ['text' => '\'%env(DATABASE_URL)%\'', 'correct' => true],
                    ['text' => '\'%DATABASE_URL%\'', 'correct' => false],
                    ['text' => '\'%env.DATABASE_URL%\'', 'correct' => false],
                ],
            ],

            // QUESTION 62 - Service alias
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'How do you create an alias for a service in Symfony\'s services.yaml?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un alias est créé avec la syntaxe @service_id pour pointer vers un autre service.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/alias_private.html',
                'answers' => [
                    ['text' => 'alias_name: @real_service_id', 'correct' => false],
                    ['text' => 'App\\AliasInterface: \'@App\\RealService\'', 'correct' => true],
                    ['text' => 'alias: [alias_name, real_service_id]', 'correct' => false],
                    ['text' => 'alias_name.service: real_service_id', 'correct' => false],
                ],
            ],

            // QUESTION 63 - Service lazy loading
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'What is the purpose of marking a service as "lazy" in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un service lazy n\'est instancié que lorsqu\'il est réellement utilisé, ce qui améliore les performances.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/lazy_services.html',
                'answers' => [
                    ['text' => 'The service is only instantiated when first used.', 'correct' => true],
                    ['text' => 'The service is cached permanently.', 'correct' => false],
                    ['text' => 'The service is loaded asynchronously.', 'correct' => false],
                    ['text' => 'The service is deprecated.', 'correct' => false],
                ],
            ],

            // QUESTION 64 - Route requirements
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'How do you restrict a route parameter to only accept numeric values?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le paramètre requirements avec une regex \\d+ limite les valeurs à des chiffres.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#parameters-validation',
                'answers' => [
                    ['text' => '#[Route(\'/blog/{id}\', type: \'int\')]', 'correct' => false],
                    ['text' => '#[Route(\'/blog/{id}\', requirements: [\'id\' => \'\\d+\'])]', 'correct' => true],
                    ['text' => '#[Route(\'/blog/{id:int}\')]', 'correct' => false],
                    ['text' => '#[Route(\'/blog/{id}\', validate: [\'id\' => \'numeric\'])]', 'correct' => false],
                ],
            ],

            // QUESTION 65 - Multiple HTTP methods
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Routing'],
                'text' => 'How do you specify that a route should accept both GET and POST methods?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le paramètre methods accepte un tableau de méthodes HTTP autorisées.',
                'resourceUrl' => 'https://symfony.com/doc/current/routing.html#matching-http-methods',
                'answers' => [
                    ['text' => '#[Route(\'/form\', methods: \'GET|POST\')]', 'correct' => false],
                    ['text' => '#[Route(\'/form\', methods: [\'GET\', \'POST\'])]', 'correct' => true],
                    ['text' => '#[Route(\'/form\', http: [\'GET\', \'POST\'])]', 'correct' => false],
                    ['text' => '#[GetRoute(\'/form\')] #[PostRoute(\'/form\')]', 'correct' => false],
                ],
            ],

            // QUESTION 66 - Autowiring interface
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'When autowiring, how does Symfony know which implementation to inject for an interface?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Si une seule implémentation existe, Symfony l\'injecte automatiquement. Sinon, un alias doit être configuré.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/autowiring.html',
                'answers' => [
                    ['text' => 'It always uses the first implementation found.', 'correct' => false],
                    ['text' => 'It injects the only implementation or requires an alias if multiple exist.', 'correct' => true],
                    ['text' => 'It throws an error if an interface is type-hinted.', 'correct' => false],
                    ['text' => 'It creates a proxy for all implementations.', 'correct' => false],
                ],
            ],

            // QUESTION 67 - Form handleRequest
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'What does the handleRequest() method do when processing a form?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'handleRequest() remplit le formulaire avec les données de la requête et soumet le formulaire si applicable.',
                'resourceUrl' => 'https://symfony.com/doc/current/forms.html#processing-forms',
                'answers' => [
                    ['text' => 'It validates the form data.', 'correct' => false],
                    ['text' => 'It populates the form with request data and submits it.', 'correct' => true],
                    ['text' => 'It renders the form.', 'correct' => false],
                    ['text' => 'It persists the form data to the database.', 'correct' => false],
                ],
            ],

            // QUESTION 68 - CSRF token form
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Forms'],
                'text' => 'How is CSRF protection enabled by default in Symfony forms?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La protection CSRF est activée par défaut dans tous les formulaires Symfony. Un champ _token caché est ajouté automatiquement.',
                'resourceUrl' => 'https://symfony.com/doc/current/security/csrf.html',
                'answers' => [
                    ['text' => 'You must add a csrf_token field manually.', 'correct' => false],
                    ['text' => 'It is enabled automatically for all forms.', 'correct' => true],
                    ['text' => 'You must call enableCsrf() on the form.', 'correct' => false],
                    ['text' => 'It is only enabled in production environment.', 'correct' => false],
                ],
            ],

            // QUESTION 69 - Validation constraint target
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'Can you apply a validation constraint to an entire class (not just a property)?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Oui, les contraintes au niveau classe (comme UniqueEntity ou Callback) valident l\'objet entier.',
                'resourceUrl' => 'https://symfony.com/doc/current/validation.html#constraint-targets',
                'answers' => [
                    ['text' => 'No, constraints only work on properties.', 'correct' => false],
                    ['text' => 'Yes, using class-level constraints like UniqueEntity or Callback.', 'correct' => true],
                    ['text' => 'Only with custom validators.', 'correct' => false],
                    ['text' => 'Only in YAML configuration.', 'correct' => false],
                ],
            ],

            // QUESTION 70 - Assert\Valid
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'What is the purpose of the #[Assert\\Valid] constraint?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => '#[Assert\\Valid] indique que l\'objet imbriqué doit également être validé avec ses propres contraintes.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/constraints/Valid.html',
                'answers' => [
                    ['text' => 'It checks if the property is set.', 'correct' => false],
                    ['text' => 'It validates nested objects with their own constraints.', 'correct' => true],
                    ['text' => 'It validates that the value is a valid object instance.', 'correct' => false],
                    ['text' => 'It replaces all other constraints.', 'correct' => false],
                ],
            ],

            // QUESTION 71 - Validation groups
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Validation'],
                'text' => 'What are validation groups used for?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les groupes de validation permettent de valider seulement certaines contraintes selon le contexte.',
                'resourceUrl' => 'https://symfony.com/doc/current/validation/groups.html',
                'answers' => [
                    ['text' => 'To organize constraints by severity.', 'correct' => false],
                    ['text' => 'To apply different validation rules depending on context.', 'correct' => true],
                    ['text' => 'To validate multiple objects at once.', 'correct' => false],
                    ['text' => 'To group error messages together.', 'correct' => false],
                ],
            ],

            // QUESTION 72 - Debug autowiring command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'Which command displays all services that can be autowired?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'debug:autowiring liste tous les services disponibles pour l\'autowiring avec leurs alias.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/debug.html',
                'answers' => [
                    ['text' => 'php bin/console debug:container', 'correct' => false],
                    ['text' => 'php bin/console debug:autowiring', 'correct' => true],
                    ['text' => 'php bin/console debug:services', 'correct' => false],
                    ['text' => 'php bin/console list:autowiring', 'correct' => false],
                ],
            ],

            // QUESTION 73 - Make controller command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Controllers'],
                'text' => 'Which command generates a new controller class with basic boilerplate?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'make:controller crée un nouveau contrôleur avec le template et la route de base.',
                'resourceUrl' => 'https://symfony.com/doc/current/controller.html#generating-controllers',
                'answers' => [
                    ['text' => 'php bin/console generate:controller', 'correct' => false],
                    ['text' => 'php bin/console make:controller', 'correct' => true],
                    ['text' => 'php bin/console new:controller', 'correct' => false],
                    ['text' => 'php bin/console create:controller', 'correct' => false],
                ],
            ],

            // QUESTION 74 - Kernel terminate event
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Events'],
                'text' => 'When is the kernel.terminate event triggered?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'kernel.terminate est déclenché après l\'envoi de la réponse au client, idéal pour les tâches longues.',
                'resourceUrl' => 'https://symfony.com/doc/current/reference/events.html#kernel-terminate',
                'answers' => [
                    ['text' => 'When the application is shut down.', 'correct' => false],
                    ['text' => 'After the response has been sent to the client.', 'correct' => true],
                    ['text' => 'Before the controller is called.', 'correct' => false],
                    ['text' => 'When an exception is thrown.', 'correct' => false],
                ],
            ],

            // QUESTION 75 - Tagged services
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Services'],
                'text' => 'What is the purpose of tagging services in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les tags permettent de grouper des services pour qu\'un autre service puisse les collecter et les utiliser ensemble.',
                'resourceUrl' => 'https://symfony.com/doc/current/service_container/tags.html',
                'answers' => [
                    ['text' => 'To mark services for removal.', 'correct' => false],
                    ['text' => 'To group services so they can be collected and processed together.', 'correct' => true],
                    ['text' => 'To add metadata for debugging.', 'correct' => false],
                    ['text' => 'To create service aliases.', 'correct' => false],
                ],
            ],
        ];
    }
}
