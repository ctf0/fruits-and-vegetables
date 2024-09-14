<?php

declare(strict_types = 1);

use App\Service\FileDataLoader;
use App\DataFixtures\ProductFixtures;
use App\EventListener\ExceptionEventSubscriber;
use Symfony\Component\DependencyInjection\Reference;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services
        ->defaults()
        ->bind('string $environment', env('APP_ENV'))
        ->autowire(true)
        ->autoconfigure(true);

    $services
        ->load('App\\', __DIR__ . '/../src/')
        ->exclude([
            __DIR__ . '/../src/DependencyInjection/',
            __DIR__ . '/../src/Entity/',
            __DIR__ . '/../src/Kernel.php',
        ]);

    $services->set(FileDataLoader::class)->args(['%kernel.project_dir%/request.json']);
    $services->set(ProductFixtures::class)->args([new Reference(FileDataLoader::class)]);
    $services->set(ExceptionEventSubscriber::class)->tag('kernel.event_listener');
};
