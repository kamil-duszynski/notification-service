<?php
declare(strict_types=1);

namespace App;

use App\Service\Notification\NotificationManager;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->runNotificationManager($container);
    }

    private function runNotificationManager(ContainerBuilder $container): void
    {
        $notificationManager = $container
            ->getDefinition(NotificationManager::class)
            ->setPublic(true)
        ;

        $notificationProviders = $container->findTaggedServiceIds('notification.provider');

        if (true === empty($notificationProviders)) {
            return;
        }

        $configPath = 'config/notifications/channels.yaml';
        $config     = Yaml::parseFile($configPath, Yaml::PARSE_CONSTANT);
        $resolver   = new OptionsResolver();

        $resolver
            ->setRequired(
                [
                    'sms', 'email'
                ]
            )
            ->setAllowedTypes('sms', 'bool')
            ->setAllowedTypes('email', 'bool')
        ;

        $channels = $resolver->resolve($config);

        foreach (array_keys($notificationProviders) as $id) {
            $notificationManager->addMethodCall('addProvider', [ new Reference($id) ]);
        }

        $notificationManager->addMethodCall('prepareChannels', [ $channels ]);
    }
}
