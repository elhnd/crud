<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Certification-style questions - Batch 24
 */
class CertificationQuestionsFixtures24 extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function getDependencies(): array
    {
        return [CertificationQuestionsFixtures23::class];
    }

    public function load(ObjectManager $manager): void
    {
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);
        $php = $categoryRepo->findOneBy(['name' => 'PHP']);

        if (!$symfony || !$php) {
            throw new \Exception('Required categories not found');
        }

        // Load existing subcategories from AppFixtures
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

            // Q2 - SecurityBundle - Authenticating an user in services
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What are valid ways to authenticate a user in a service, using the <code>Security</code> service?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Depuis Symfony 6.2, la méthode login() du service Security accepte soit un UserInterface, soit un UserInterface avec authenticator et firewall.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/6.2/src/Symfony/Bundle/SecurityBundle/Security.php',
                'answers' => [
                    ['text' => '<code>$this->security->login($userIdentifier, $password, $firewall);</code>', 'correct' => false],
                    ['text' => '<code>$this->security->login($userIdentifier);</code>', 'correct' => false],
                    ['text' => '<code>$this->security->login($userEntity);</code>', 'correct' => true],
                    ['text' => '<code>$this->security->login($userEntity, $authenticator, $firewall);</code>', 'correct' => true],
                ],
            ],

            // Q3 - PHP - Running PHP
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'PHP is run on...?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'PHP est un langage côté serveur (server-side), il s\'exécute sur le serveur web et non dans le navigateur client.',
                'resourceUrl' => 'http://php.net/',
                'answers' => [
                    ['text' => 'Client browser and Web server', 'correct' => false],
                    ['text' => 'Client browser', 'correct' => false],
                    ['text' => 'Web server', 'correct' => true],
                ],
            ],

            // Q4 - DI - Definition visibility
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'When creating a service definition using PHP, could the visibility of the service be changed using <code>Symfony\Component\DependencyInjection\Definition::setPublic($boolean)</code> ?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, la méthode setPublic() permet de définir si un service est public ou privé.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/DependencyInjection/Definition.php#L527',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q5 - FrameworkBundle - Configuration per env
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'Is it possible to configure multiple environments in a single file?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, depuis Symfony 5.3, il est possible de configurer plusieurs environnements dans un seul fichier en utilisant la directive "when@env".',
                'resourceUrl' => 'https://symfony.com/blog/new-in-symfony-5-3-configure-multiple-environments-in-a-single-file',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q7 - DI - Service visibility auto_alias
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Dependency Injection'],
                'text' => 'Is it mandatory to define services that uses the <code>auto_alias</code> tag as <code>private</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, ce n\'est pas obligatoire. Les services avec le tag auto_alias peuvent être publics ou privés selon les besoins.',
                'resourceUrl' => 'https://symfony.com/doc/2.7/reference/dic_tags.html#auto-alias, https://github.com/symfony/symfony/blob/2.7/src/Symfony/Component/DependencyInjection/Compiler/AutoAliasServicePass.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => false],
                    ['text' => 'No', 'correct' => true],
                ],
            ],

            // Q8 - HTTP - Status code Unauthorized
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HTTP'],
                'text' => 'What is the status code for Unauthorized ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Le code HTTP 401 signifie "Unauthorized" - l\'utilisateur n\'est pas authentifié. À ne pas confondre avec 403 (Forbidden) qui indique que l\'utilisateur est authentifié mais n\'a pas les permissions.',
                'resourceUrl' => 'https://developer.mozilla.org/fr/docs/Web/HTTP/Status/401',
                'answers' => [
                    ['text' => '401', 'correct' => true],
                    ['text' => '403', 'correct' => false],
                    ['text' => '402', 'correct' => false],
                    ['text' => '405', 'correct' => false],
                ],
            ],

            // Q9 - Security - Voters voteOnAttribute signature
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'What is the signature of the <code>voteOnAttribute()</code> method from <code>Symfony\Component\Security\Core\Authorization\Voter\Voter</code> abstract class?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La signature correcte est voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token). L\'ordre des paramètres est important.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/v6.0.0/src/Symfony/Component/Security/Core/Authorization/Voter/Voter.php',
                'answers' => [
                    ['text' => '<pre><code class="language-php">voteOnAttribute(string $attribute, TokenInterface $token, mixed $subject)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token)</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">voteOnAttribute(TokenInterface $token, string $attribute, mixed $subject)</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">voteOnAttribute(TokenInterface $token, mixed $subject, string $attribute)</code></pre>', 'correct' => false],
                ],
            ],

            // Q10 - Event Dispatcher - ImmutableEventDispatcher usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Event Dispatcher'],
                'text' => 'Could the event listeners related to an event be retrieved from the <code>ImmutableEventDispatcher</code>?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ImmutableEventDispatcher permet de récupérer les listeners d\'un événement via getListeners(), mais ne permet pas de les modifier.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/EventDispatcher/ImmutableEventDispatcher.php',
                'answers' => [
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes', 'correct' => true],
                ],
            ],

            // Q11 - HttpFoundation - Request namespace
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'In which namespace does the <code>Request</code> object live ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La classe Request se trouve dans le namespace Symfony\Component\HttpFoundation.',
                'resourceUrl' => 'https://symfony.com/doc/current/components/http_foundation.html',
                'answers' => [
                    ['text' => '<code>Symfony\Component\HttpClient</code>', 'correct' => false],
                    ['text' => '<code>\</code> (built in class)', 'correct' => false],
                    ['text' => '<code>Symfony\Component\HttpKernel</code>', 'correct' => false],
                    ['text' => '<code>Symfony\Component\HttpFoundation</code>', 'correct' => true],
                ],
            ],

            // Q14 - Filesystem - mkdir behavior
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Filesystem'],
                'text' => 'Will the following code trigger an error if the <code>/tmp/photos</code> directory already exists ?
<pre><code class="language-php">$fs = new Filesystem();
$fs->mkdir(\'/tmp/photos\', 0700);</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, la méthode mkdir() du composant Filesystem ne déclenche pas d\'erreur si le répertoire existe déjà, contrairement à la fonction PHP native mkdir().',
                'resourceUrl' => 'http://symfony.com/doc/current/components/filesystem.html#mkdir',
                'answers' => [
                    ['text' => 'No', 'correct' => true],
                    ['text' => 'Yes', 'correct' => false],
                ],
            ],

            // Q15 - HttpFoundation - RequestMatcher usage
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Could a custom request matcher be created?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, il est possible de créer un request matcher personnalisé en implémentant l\'interface RequestMatcherInterface.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/HttpFoundation/RequestMatcherInterface.php',
                'answers' => [
                    ['text' => 'No', 'correct' => false],
                    ['text' => 'Yes', 'correct' => true],
                ],
            ],

            // Q16 - FrameworkBundle - Business Logic
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:FrameworkBundle'],
                'text' => 'Where should your business logic live ?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Selon les meilleures pratiques Symfony, la logique métier doit être placée dans les services, pas dans les contrôleurs.',
                'resourceUrl' => 'https://symfony.com/doc/current/best_practices.html',
                'answers' => [
                    ['text' => 'In Services.', 'correct' => true],
                    ['text' => 'In Controllers.', 'correct' => false],
                    ['text' => 'In BusinessStrategies.', 'correct' => false],
                ],
            ],

            // Q17 - Doctrine - Basic Mapping
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Doctrine'] ?? null,
                'text' => 'Is this mapping correct?
<pre><code class="language-php">/**
 * @Id
 * @GeneratedValue
 * @Column(type="integer")
 */
private $id;

/**
 * @Column(type="string")
 */
private $name;

/**
 * @Column(name="`number`", type="integer")
 */
private $number;</code></pre>',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, ce mapping est correct. Les backticks dans name="`number`" permettent d\'échapper le mot réservé "number" en SQL. @Id, @GeneratedValue et @Column sont correctement utilisés.',
                'resourceUrl' => 'https://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#identifiers-primary-keys, https://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#quoting-reserved-words',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q18 - Mailer - Mailer events
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mailer'],
                'text' => 'What events can be sent by the Mailer component when sending an email?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le composant Mailer peut envoyer les événements MessageEvent, FailedMessageEvent et SentMessageEvent. Il n\'y a pas de BouncedMessageEvent dans Symfony.',
                'resourceUrl' => 'https://github.com/symfony/symfony/tree/6.2/src/Symfony/Component/Mailer/Event',
                'answers' => [
                    ['text' => '<code>PostSentMessageEvent</code>', 'correct' => false],
                    ['text' => '<code>FailedMessageEvent</code>', 'correct' => true],
                    ['text' => '<code>BouncedMessageEvent</code>', 'correct' => false],
                    ['text' => '<code>SentMessageEvent</code>', 'correct' => true],
                    ['text' => '<code>PreSentMessageEvent</code>', 'correct' => false],
                ],
            ],

            // Q20 - Security - Access Decision Manager
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Security'],
                'text' => 'How does the <code>AccessDecisionManager</code> behave when configured with a <code>consensus</code> voting strategy?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Avec la stratégie "consensus", l\'accès est accordé si une majorité (plus de la moitié) des voters accorde l\'accès.',
                'resourceUrl' => 'https://symfony.com/doc/2.x/security/voters.html#changing-the-access-decision-strategy',
                'answers' => [
                    ['text' => 'It grants access if there is at least a majority of all the voters granting access.', 'correct' => true],
                    ['text' => 'It only grants access if none of the voters deny access.', 'correct' => false],
                    ['text' => 'It grants access if over two-thirds of voters answer the access is granted.', 'correct' => false],
                    ['text' => 'It grants access as soon as there is one voter granting access.', 'correct' => false],
                ],
            ],

            // Q22 - PHP - Session destroy variable
            [
                'category' => $php,
                'subcategory' => $subcategories['PHP:PHP Basics'],
                'text' => 'What is the way to destroy a variable in a PHP session?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour détruire une variable spécifique dans une session PHP, il faut utiliser unset() sur la variable dans $_SESSION. session_destroy() détruit toute la session, pas juste une variable.',
                'resourceUrl' => 'http://php.net/manual/en/function.unset.php, http://php.net/manual/en/function.session-destroy.php, http://php.net/manual/en/function.session-unset.php, http://php.net/manual/en/reserved.variables.session.php',
                'answers' => [
                    ['text' => '<code>unset()</code> on the variable in <code>$_SESSION</code>', 'correct' => true],
                    ['text' => '<code>session_destroy()</code>', 'correct' => false],
                    ['text' => '<code>session_unset()</code>', 'correct' => false],
                    ['text' => '<code>unset()</code> on the variable in <code>$HTTP_SESSION_VARS</code>', 'correct' => false],
                ],
            ],

            // Q24 - Console - Process handling
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'Could a running <code>Process</code> current state be displayed using a helper?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Oui, le DebugFormatterHelper permet d\'afficher l\'état actuel d\'un processus en cours d\'exécution.',
                'resourceUrl' => 'https://symfony.com/doc/2.6/components/console/helpers/debug_formatter.html#output-progress-information, https://github.com/symfony/symfony/blob/2.6/src/Symfony/Component/Console/Helper/DebugFormatterHelper.php',
                'answers' => [
                    ['text' => 'Yes', 'correct' => true],
                    ['text' => 'No', 'correct' => false],
                ],
            ],

            // Q27 - Console - Application events
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Console'],
                'text' => 'The Application class of the Console component allows you to optionally hook into the lifecycle of a console application via events.
<p>How many events are dispatched?</p>',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le composant Console dispatche 5 événements : COMMAND, TERMINATE, ERROR, SIGNAL, et depuis 5.2 CONSOLE_ERROR. En Symfony 5.2+, il y a effectivement 5 événements.',
                'resourceUrl' => 'https://symfony.com/doc/5.x/components/console/events.html',
                'answers' => [
                    ['text' => '1', 'correct' => false],
                    ['text' => '6', 'correct' => false],
                    ['text' => '3', 'correct' => false],
                    ['text' => '4', 'correct' => true],
                    ['text' => '5', 'correct' => false],
                    ['text' => '2', 'correct' => false],
                ],
            ],

            // Q29 - HttpFoundation - Accessing Request Data
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'How to access <code>$_POST</code> data when using a <code>Symfony\Component\HttpFoundation\Request</code> <code>$request</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'Les données POST sont accessibles via $request->request qui est un ParameterBag contenant les données de $_POST.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/http_foundation.html#accessing-request-data',
                'answers' => [
                    ['text' => '<pre><code>$request->post</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getPostData()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->getPost()</code></pre>', 'correct' => false],
                    ['text' => '<pre><code>$request->request</code></pre>', 'correct' => true],
                ],
            ],

            // Q30 - HttpFoundation - Cache Control
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:HttpFoundation'],
                'text' => 'Which of theses are the way to add the <code>Cache-Control: public,s-maxage=900</code> HTTP response header on a <code>Symfony\Component\HttpFoundation\Response</code> object?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La méthode correcte est setSharedMaxAge(). Elle définit le s-maxage du Cache-Control. setShareMaxAge() n\'existe pas (attention à la différence).',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/2.3/src/Symfony/Component/HttpFoundation/Response.php#L693',
                'answers' => [
                    ['text' => '<pre><code class="language-php">$response->setSharedMaxAge(900);</code></pre>', 'correct' => true],
                    ['text' => '<pre><code class="language-php">$response->setShareMaxAge(900);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$response->setSMaxAge(900);</code></pre>', 'correct' => false],
                    ['text' => '<pre><code class="language-php">$response->setMaxAge(900, true);</code></pre>', 'correct' => false],
                ],
            ],

            // Q31 - Mime - Email priority
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Mailer'],
                'text' => 'What are the available priorities for an <code>Email</code>?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Les priorités disponibles pour un Email sont : PRIORITY_HIGHEST, PRIORITY_HIGH, PRIORITY_NORMAL, PRIORITY_LOW, PRIORITY_LOWEST. Il n\'y a pas de PRIORITY_UNDEFINED ni PRIORITY_MEDIUM.',
                'resourceUrl' => 'https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Mime/Email.php#L29',
                'answers' => [
                    ['text' => '<code>PRIORITY_HIGH</code>', 'correct' => true],
                    ['text' => '<code>PRIORITY_LOWEST</code>', 'correct' => true],
                    ['text' => '<code>PRIORITY_LOW</code>', 'correct' => true],
                    ['text' => '<code>PRIORITY_UNDEFINED</code>', 'correct' => false],
                    ['text' => '<code>PRIORITY_NORMAL</code>', 'correct' => true],
                    ['text' => '<code>PRIORITY_HIGHEST</code>', 'correct' => true],
                    ['text' => '<code>PRIORITY_MEDIUM</code>', 'correct' => false],
                ],
            ],

            // Q32 - Config - Normalization fixXmlConfig
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Configuration'],
                'text' => 'What is the purpose of <code>Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition::fixXmlConfig</code> ?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'fixXmlConfig() normalise les noms d\'éléments XML (par exemple en pluralisant les clés) et s\'assure que les éléments XML uniques sont transformés en tableau.',
                'resourceUrl' => 'http://symfony.com/doc/current/components/config/definition.html#normalization',
                'answers' => [
                    ['text' => 'It applies a custom function to an XML element if an error occurs', 'correct' => false],
                    ['text' => 'It ensures that single XML elements are turned into an array', 'correct' => true],
                    ['text' => 'It normalizes XML element name (e.g. pluralizing the key used in XML)', 'correct' => true],
                    ['text' => 'It always applies a custom function to an XML element', 'correct' => false],
                ],
            ],

            // Q34 - Twig - Environment render method
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Twig'],
                'text' => 'Which method is used to render the desired template?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La méthode render() de la classe Environment de Twig est utilisée pour rendre un template et retourner le résultat sous forme de chaîne.',
                'resourceUrl' => 'https://twig.symfony.com/doc/1.x/api.html#rendering-templates, https://github.com/twigphp/Twig/blob/1.x/src/Environment.php#L370',
                'answers' => [
                    ['text' => '<code>Environment::resolveTemplate()</code>', 'correct' => false],
                    ['text' => '<code>Environment::showTemplate()</code>', 'correct' => false],
                    ['text' => '<code>Environment::display()</code>', 'correct' => false],
                    ['text' => '<code>Environment::render()</code>', 'correct' => true],
                ],
            ],
        ];
    }
}
