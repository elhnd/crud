<?php

namespace App\DataFixtures;

use App\Entity\Subcategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Adds documentation URLs to subcategories for revision strategy links
 */
class SubcategoryDocumentationFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['documentation'];
    }

    // Documentation URLs mapping for each subcategory
    private const DOCUMENTATION_URLS = [
        // Symfony subcategories
        'Architecture' => 'https://symfony.com/doc/current/introduction/symfony_architecture.html',
        'Controllers' => 'https://symfony.com/doc/current/controller.html',
        'Routing' => 'https://symfony.com/doc/current/routing.html',
        'Twig' => 'https://twig.symfony.com/doc/3.x/',
        'Forms' => 'https://symfony.com/doc/current/forms.html',
        'Validation' => 'https://symfony.com/doc/current/validation.html',
        'Dependency Injection' => 'https://symfony.com/doc/current/service_container.html',
        'Services' => 'https://symfony.com/doc/current/service_container.html',
        'Security' => 'https://symfony.com/doc/current/security.html',
        'PasswordHasher' => 'https://symfony.com/doc/current/security/passwords.html',
        'Console' => 'https://symfony.com/doc/current/console.html',
        'Testing' => 'https://symfony.com/doc/current/testing.html',
        'Event Dispatcher' => 'https://symfony.com/doc/current/event_dispatcher.html',
        'Serializer' => 'https://symfony.com/doc/current/serializer.html',
        'Messenger' => 'https://symfony.com/doc/current/messenger.html',
        'Mailer' => 'https://symfony.com/doc/current/mailer.html',
        'Translation' => 'https://symfony.com/doc/current/translation.html',
        'Cache' => 'https://symfony.com/doc/current/cache.html',
        'HTTP' => 'https://symfony.com/doc/current/introduction/http_fundamentals.html',
        'HttpFoundation' => 'https://symfony.com/doc/current/components/http_foundation.html',
        'HttpKernel' => 'https://symfony.com/doc/current/components/http_kernel.html',
        'Configuration' => 'https://symfony.com/doc/current/configuration.html',
        'PropertyAccess' => 'https://symfony.com/doc/current/components/property_access.html',
        'Filesystem' => 'https://symfony.com/doc/current/components/filesystem.html',
        'Clock' => 'https://symfony.com/doc/current/components/clock.html',
        'Assets' => 'https://symfony.com/doc/current/frontend/asset_mapper.html',
        'Doctrine' => 'https://symfony.com/doc/current/doctrine.html',
        'Migrations' => 'https://symfony.com/doc/current/doctrine.html#migrations-creating-the-database-tables-columns',
        'Sessions' => 'https://symfony.com/doc/current/session.html',
        'Cookies' => 'https://symfony.com/doc/current/components/http_foundation.html#setting-cookies',
        'Request Handling' => 'https://symfony.com/doc/current/introduction/http_fundamentals.html',
        'Response' => 'https://symfony.com/doc/current/components/http_foundation.html#response',
        'Attributes' => 'https://symfony.com/doc/current/routing.html#creating-routes-as-attributes',
        'String' => 'https://symfony.com/doc/current/components/string.html',
        'Uid' => 'https://symfony.com/doc/current/components/uid.html',
        'Workflow' => 'https://symfony.com/doc/current/workflow.html',
        'Lock' => 'https://symfony.com/doc/current/lock.html',
        'RateLimiter' => 'https://symfony.com/doc/current/rate_limiter.html',
        'Notifier' => 'https://symfony.com/doc/current/notifier.html',
        'Scheduler' => 'https://symfony.com/doc/current/scheduler.html',
        'Process' => 'https://symfony.com/doc/current/components/process.html',
        'Expression Language' => 'https://symfony.com/doc/current/components/expression_language.html',
        'Finder' => 'https://symfony.com/doc/current/components/finder.html',
        'OptionsResolver' => 'https://symfony.com/doc/current/components/options_resolver.html',
        'Yaml' => 'https://symfony.com/doc/current/components/yaml.html',
        
        // PHP subcategories
        'OOP' => 'https://www.php.net/manual/en/language.oop5.php',
        'PHP Basics' => 'https://www.php.net/manual/en/langref.php',
        'Interfaces & Traits' => 'https://www.php.net/manual/en/language.oop5.interfaces.php',
        'PSR' => 'https://www.php-fig.org/psr/',
        'Namespaces' => 'https://www.php.net/manual/en/language.namespaces.php',
        'I/O' => 'https://www.php.net/manual/en/book.filesystem.php',
        'Arrays' => 'https://www.php.net/manual/en/book.array.php',
        'Strings' => 'https://www.php.net/manual/en/ref.strings.php',
        'Dates' => 'https://www.php.net/manual/en/book.datetime.php',
        'Exceptions' => 'https://www.php.net/manual/en/language.exceptions.php',
        'SPL' => 'https://www.php.net/manual/en/book.spl.php',
        'Generators' => 'https://www.php.net/manual/en/language.generators.php',
        'Closures' => 'https://www.php.net/manual/en/functions.anonymous.php',
        'Attributes PHP' => 'https://www.php.net/manual/en/language.attributes.php',
        'Enums' => 'https://www.php.net/manual/en/language.enumerations.php',
        'Fibers' => 'https://www.php.net/manual/en/language.fibers.php',
        'Reflection' => 'https://www.php.net/manual/en/book.reflection.php',
    ];

    public function load(ObjectManager $manager): void
    {
        $subcategoryRepo = $manager->getRepository(Subcategory::class);
        $subcategories = $subcategoryRepo->findAll();

        $updated = 0;
        foreach ($subcategories as $subcategory) {
            $name = $subcategory->getName();
            if (isset(self::DOCUMENTATION_URLS[$name])) {
                $subcategory->setDocumentationUrl(self::DOCUMENTATION_URLS[$name]);
                $updated++;
            }
        }

        $manager->flush();
        
        echo "Updated documentation URLs for {$updated} subcategories.\n";
    }
}
