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
 * Real Certification Exam Questions - Assets Focus
 * Questions about AssetMapper and Asset Component
 * Marked with isCertification = true
 */
class CertificationQuestionsFixtures18 extends Fixture implements FixtureGroupInterface
{
    use UpsertQuestionTrait;

    public static function getGroups(): array
    {
        return ['questions'];
    }

    public function load(ObjectManager $manager): void
    {
        // Get existing categories
        $categoryRepo = $manager->getRepository(Category::class);
        $symfony = $categoryRepo->findOneBy(['name' => 'Symfony']);

        if (!$symfony) {
            throw new \Exception('Symfony category must exist. Run AppFixtures first.');
        }

        // Get/create subcategories
        $subcategories = $this->getOrCreateSubcategories($manager, $symfony);

        // Define certification exam questions
        $questions = $this->getCertificationQuestions($symfony, $subcategories);

        // Persist all questions using upsert
        foreach ($questions as $q) {
            $this->upsertQuestion($manager, $q);
        }

        $manager->flush();
    }

    private function getOrCreateSubcategories(ObjectManager $manager, Category $symfony): array
    {
        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        $subcategories = [];

        // Get existing subcategories
        foreach ($subcategoryRepo->findAll() as $sub) {
            $key = $sub->getCategory()->getName() . ':' . $sub->getName();
            $subcategories[$key] = $sub;
        }

        // Create additional subcategories if needed
        $additionalSubcategories = [
            'Symfony' => ['Assets' => '', 'AssetMapper' => ''],
        ];

        foreach ($additionalSubcategories as $catName => $subs) {
            $category = $catName === 'Symfony' ? $symfony : null;
            if (!$category) continue;

            foreach ($subs as $subName => $description) {
                $key = $catName . ':' . $subName;
                if (!isset($subcategories[$key])) {
                    $sub = new Subcategory();
                    $sub->setName($subName);
                    $sub->setCategory($category);
                    $sub->setDescription($description);
                    $manager->persist($sub);
                    $subcategories[$key] = $sub;
                }
            }
        }

        $manager->flush();
        return $subcategories;
    }

    private function getCertificationQuestions(Category $symfony, array $subcategories): array
    {
        return [
            // QUESTION 1 - AssetMapper versioning
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:AssetMapper'],
                'text' => 'Consider the following Twig template code in a Symfony application using AssetMapper:
<pre><code class="language-twig">{{ asset(\'images/logo.png\') }}</code></pre>

If the logo.png file is located at assets/images/logo.png, what will be the generated URL?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'AssetMapper ajoute automatiquement un hash de version au nom du fichier pour gérer le cache. Le format est: /assets/[path]/[filename]-[hash].[extension]',
                'resourceUrl' => 'https://symfony.com/doc/7.0/frontend/asset_mapper.html',
                'answers' => [
                    ['text' => '/images/logo.png', 'correct' => false],
                    ['text' => '/assets/images/logo.png', 'correct' => false],
                    ['text' => '/assets/images/logo-3c16d9220694c0e56d8648f25e6035e9.png', 'correct' => true],
                    ['text' => '/assets/images/logo.png?v=3c16d922', 'correct' => false],
                ],
            ],

            // QUESTION 2 - importmap:require command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:AssetMapper'],
                'text' => 'Which command should you use to add a third-party npm package like "bootstrap" to your AssetMapper application?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La commande importmap:require permet d\'ajouter des packages npm à l\'importmap.php sans utiliser npm.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/frontend/asset_mapper.html#importing-3rd-party-javascript-packages',
                'answers' => [
                    ['text' => 'php bin/console assets:install bootstrap', 'correct' => false],
                    ['text' => 'php bin/console importmap:require bootstrap', 'correct' => true],
                    ['text' => 'php bin/console asset-map:add bootstrap', 'correct' => false],
                    ['text' => 'php bin/console npm:install bootstrap', 'correct' => false],
                ],
            ],

            // QUESTION 3 - JavaScript import extension
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:AssetMapper'],
                'text' => 'When importing a relative JavaScript file in AssetMapper, which statement is valid?
<pre><code class="language-javascript">// assets/app.js
import Duck from ???;</code></pre>

Assuming the file is located at assets/duck.js:',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Contrairement à Node.js, les imports relatifs en environnement navigateur DOIVENT inclure l\'extension .js',
                'resourceUrl' => 'https://symfony.com/doc/7.0/frontend/asset_mapper.html#importmaps-writing-javascript',
                'answers' => [
                    ['text' => '\'./duck\'', 'correct' => false],
                    ['text' => '\'./duck.js\'', 'correct' => true],
                    ['text' => '\'duck\'', 'correct' => false],
                    ['text' => 'Both \'./duck\' and \'./duck.js\' work equally', 'correct' => false],
                ],
            ],

            // QUESTION 4 - AssetMapper deployment
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:AssetMapper'],
                'text' => 'When deploying a Symfony application using AssetMapper to production, which actions are required or recommended?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Pour le déploiement avec AssetMapper: asset-map:compile copie les assets versionnés dans public/assets/, importmap:install télécharge les dépendances tierces manquantes, et il est recommandé d\'activer HTTP/2 et la compression gzip/brotli sur le serveur web.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/frontend/asset_mapper.html#deploying-with-the-assetmapper-component',
                'answers' => [
                    ['text' => 'Run php bin/console asset-map:compile to copy assets to public/assets/', 'correct' => true],
                    ['text' => 'Run php bin/console importmap:install if assets/vendor/ is missing', 'correct' => true],
                    ['text' => 'Enable HTTP/2 on the web server for better performance', 'correct' => true],
                    ['text' => 'Enable gzip or brotli compression on the web server', 'correct' => true],
                    ['text' => 'Run npm build to compile JavaScript files', 'correct' => false],
                ],
            ],

            // QUESTION 5 - CSS import from JavaScript
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:AssetMapper'],
                'text' => 'In AssetMapper, you can import CSS files directly from JavaScript using the following syntax:
<pre><code class="language-javascript">// assets/app.js
import \'./styles/app.css\';</code></pre>

Is this statement true?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Vrai. AssetMapper permet d\'importer des fichiers CSS depuis JavaScript. Ce n\'est pas natif au navigateur, mais AssetMapper crée des entrées spéciales dans l\'importmap et ajoute automatiquement des balises <link> pour charger le CSS.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/frontend/asset_mapper.html#handling-css',
                'answers' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],

            // QUESTION 6 - debug:asset-map command
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:AssetMapper'],
                'text' => 'Which command allows you to see all mapped assets and their logical paths in your AssetMapper application?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 1,
                'explanation' => 'La commande debug:asset-map affiche tous les chemins mappés et les assets disponibles avec leurs chemins logiques.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/frontend/asset_mapper.html#debugging-seeing-all-mapped-assets',
                'answers' => [
                    ['text' => 'php bin/console debug:assets', 'correct' => false],
                    ['text' => 'php bin/console debug:asset-map', 'correct' => true],
                    ['text' => 'php bin/console list:assets', 'correct' => false],
                    ['text' => 'php bin/console asset-map:list', 'correct' => false],
                ],
            ],

            // QUESTION 7 - importmap entrypoint
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:AssetMapper'],
                'text' => 'In the importmap.php file, what are the characteristics of an "entrypoint"?
<pre><code class="language-php">return [
    \'app\' => [
        \'path\' => \'./assets/app.js\',
        \'entrypoint\' => true,
    ],
];</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Un entrypoint est un fichier JavaScript principal qui sera automatiquement chargé et exécuté par le navigateur via <script type="module">import \'app\';</script>. Il génère également des preload links pour optimiser les performances.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/frontend/asset_mapper.html#the-app-entrypoint-preloading',
                'answers' => [
                    ['text' => 'This file will be automatically loaded and executed by the browser', 'correct' => true],
                    ['text' => 'AssetMapper will generate preload links for better performance', 'correct' => true],
                    ['text' => 'A <script type="module"> tag will be generated to import this file', 'correct' => true],
                    ['text' => 'This file is only available in development environment', 'correct' => false],
                    ['text' => 'This file cannot import other JavaScript files', 'correct' => false],
                ],
            ],

            // QUESTION 8 - Asset versioning strategies
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Assets'],
                'text' => 'Which of the following is NOT a valid version strategy in Symfony\'s Asset component?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'Le composant Asset de Symfony fournit trois stratégies principales: EmptyVersionStrategy, StaticVersionStrategy, et JsonManifestVersionStrategy. TimestampVersionStrategy n\'existe pas dans Symfony.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/components/asset.html#versioned-assets',
                'answers' => [
                    ['text' => 'EmptyVersionStrategy', 'correct' => false],
                    ['text' => 'StaticVersionStrategy', 'correct' => false],
                    ['text' => 'JsonManifestVersionStrategy', 'correct' => false],
                    ['text' => 'TimestampVersionStrategy', 'correct' => true],
                ],
            ],

            // QUESTION 9 - StaticVersionStrategy format
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Assets'],
                'text' => 'Consider the following Asset component code:
<pre><code class="language-php">use Symfony\\Component\\Asset\\Package;
use Symfony\\Component\\Asset\\VersionStrategy\\StaticVersionStrategy;

$package = new Package(new StaticVersionStrategy(\'v1\', \'%2$s/%1$s\'));
echo $package->getUrl(\'/image.png\');</code></pre>

What will be the output?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Le format \'%2$s/%1$s\' place le chemin (%2$s) avant la version (%1$s). Résultat: /v1/image.png',
                'resourceUrl' => 'https://symfony.com/doc/7.0/components/asset.html#versioned-assets',
                'answers' => [
                    ['text' => '/image.png?v1', 'correct' => false],
                    ['text' => '/v1/image.png', 'correct' => true],
                    ['text' => '/image.png?v=v1', 'correct' => false],
                    ['text' => '/image-v1.png', 'correct' => false],
                ],
            ],

            // QUESTION 10 - JsonManifestVersionStrategy
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Assets'],
                'text' => 'Which statements are correct about JsonManifestVersionStrategy in Symfony\'s Asset component?
<pre><code class="language-php">$package = new Package(
    new JsonManifestVersionStrategy(__DIR__.\'/manifest.json\', null, $strictMode)
);</code></pre>',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'JsonManifestVersionStrategy lit un fichier JSON qui mappe les noms de fichiers sources vers les versions compilées. En mode strict, une exception est lancée pour les assets manquants. Sans mode strict, le chemin original est retourné. La stratégie peut aussi charger des manifests via HTTP.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/components/asset.html#json-file-manifest',
                'answers' => [
                    ['text' => 'In strict mode, an exception is thrown if an asset is not found in the manifest', 'correct' => true],
                    ['text' => 'Without strict mode, the original unmodified path is returned for missing assets', 'correct' => true],
                    ['text' => 'The manifest file can be loaded via HTTP using HttpClient component', 'correct' => true],
                    ['text' => 'The manifest file is automatically regenerated when assets change', 'correct' => false],
                    ['text' => 'This strategy requires Node.js to be installed', 'correct' => false],
                ],
            ],

            // QUESTION 11 - UrlPackage for CDN
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Assets'],
                'text' => 'Consider the following Asset component configuration:
<pre><code class="language-php">use Symfony\\Component\\Asset\\UrlPackage;
use Symfony\\Component\\Asset\\VersionStrategy\\StaticVersionStrategy;

$package = new UrlPackage(
    [\'https://cdn1.example.com/\', \'https://cdn2.example.com/\'],
    new StaticVersionStrategy(\'v1\')
);

echo $package->getUrl(\'/logo.png\');
echo $package->getUrl(\'/logo.png\');</code></pre>

What will happen when the same asset is requested twice?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 3,
                'explanation' => 'Quand plusieurs URLs CDN sont fournies, UrlPackage sélectionne une URL de manière DETERMINISTE (basée sur un hash du chemin). Le même asset utilisera toujours le même CDN, mais différents assets peuvent utiliser des CDNs différents.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/components/asset.html#absolute-assets-and-cdns',
                'answers' => [
                    ['text' => 'Both calls will randomly select between cdn1 and cdn2', 'correct' => false],
                    ['text' => 'Both calls will always use the same CDN URL for the same asset', 'correct' => true],
                    ['text' => 'The first call uses cdn1, the second uses cdn2 (round-robin)', 'correct' => false],
                    ['text' => 'Both calls will always use cdn1 (the first one)', 'correct' => false],
                ],
            ],

            // QUESTION 12 - AssetMapper commands
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:AssetMapper'],
                'text' => 'Which of the following is NOT a valid AssetMapper console command in Symfony?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'AssetMapper ne fournit pas de commande importmap:minify. La minification n\'est pas gérée par AssetMapper et doit être effectuée par le serveur web (gzip/brotli) ou des outils externes.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/frontend/asset_mapper.html',
                'answers' => [
                    ['text' => 'php bin/console importmap:require', 'correct' => false],
                    ['text' => 'php bin/console asset-map:compile', 'correct' => false],
                    ['text' => 'php bin/console importmap:minify', 'correct' => true],
                    ['text' => 'php bin/console debug:asset-map', 'correct' => false],
                ],
            ],

            // QUESTION 13 - AssetMapper does not minify
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:AssetMapper'],
                'text' => 'Does the AssetMapper component automatically minify JavaScript and CSS assets for production?',
                'type' => QuestionType::TRUE_FALSE,
                'difficulty' => 2,
                'explanation' => 'Non, AssetMapper ne minifie PAS les assets. La minification/compression doit être gérée par le serveur web (gzip, brotli) ou par des outils externes. AssetMapper se concentre sur le mapping et le versioning.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/frontend/asset_mapper.html#does-the-assetmapper-component-minify-assets',
                'answers' => [
                    ['text' => 'True', 'correct' => false],
                    ['text' => 'False', 'correct' => true],
                ],
            ],

            // QUESTION 14 - PathPackage with base path
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:Assets'],
                'text' => 'Consider the following Asset component code:
<pre><code class="language-php">use Symfony\\Component\\Asset\\PathPackage;
use Symfony\\Component\\Asset\\VersionStrategy\\StaticVersionStrategy;

$package = new PathPackage(\'/static/images\', new StaticVersionStrategy(\'v1\'));

echo $package->getUrl(\'logo.png\');
echo $package->getUrl(\'/logo.png\');</code></pre>

What will be the output of both echo statements?',
                'type' => QuestionType::SINGLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'PathPackage ajoute le base path uniquement aux chemins relatifs. Les chemins absolus (commençant par /) ignorent le base path.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/components/asset.html#grouped-assets',
                'answers' => [
                    ['text' => '/static/images/logo.png?v1 and /static/images/logo.png?v1', 'correct' => false],
                    ['text' => '/static/images/logo.png?v1 and /logo.png?v1', 'correct' => true],
                    ['text' => 'logo.png?v1 and /logo.png?v1', 'correct' => false],
                    ['text' => '/static/images/logo.png and /static/images/logo.png', 'correct' => false],
                ],
            ],

            // QUESTION 15 - importmap() Twig function
            [
                'category' => $symfony,
                'subcategory' => $subcategories['Symfony:AssetMapper'],
                'text' => 'What does the {{ importmap(\'app\') }} Twig function output in an AssetMapper application?',
                'type' => QuestionType::MULTIPLE_CHOICE,
                'difficulty' => 2,
                'explanation' => 'La fonction importmap() génère: 1) Une balise <script type="importmap"> avec les mappings, 2) Des balises <link rel="modulepreload"> pour la performance, 3) Une balise <script type="module"> qui importe l\'entrypoint, 4) Le ES module shim pour les anciens navigateurs.',
                'resourceUrl' => 'https://symfony.com/doc/7.0/frontend/asset_mapper.html#how-does-the-importmap-work',
                'answers' => [
                    ['text' => 'A <script type="importmap"> tag with all import mappings', 'correct' => true],
                    ['text' => 'Preload <link> tags for performance optimization', 'correct' => true],
                    ['text' => 'A <script type="module"> tag that loads the entrypoint', 'correct' => true],
                    ['text' => 'Inline JavaScript that minifies the code', 'correct' => false],
                    ['text' => 'A polyfill for older browsers (ES module shim)', 'correct' => true],
                ],
            ],
        ];
    }
}
