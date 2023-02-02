<?php
declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\Notification\NotificationManager;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'notification:send',
    description: 'Sending specified notification type to given recipient',
)]
class SendNotificationCommand extends Command
{
    private const DEFAULT_CHANNEL = 'email';

    private array $allowedTypes = [
        'test'
    ];

    private array $allowedChannels = [
        'sms', 'email'
    ];

    public function __construct(
        private readonly NotificationManager $notificationManager,
        private readonly UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $allowedTypes    = implode(', ', $this->allowedTypes);
        $allowedChannels = implode(', ', $this->allowedChannels);

        $this
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                "Type of notification - one of given: $allowedTypes"
            )
            ->addArgument(
                'recipient',
                InputArgument::REQUIRED,
                'Recipient of notification (find by email)'
            )
            ->addOption(
                'channel',
                'c',
                InputOption::VALUE_REQUIRED,
                "Communication channel - one of given: $allowedChannels",
                self::DEFAULT_CHANNEL
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type           = $input->getArgument('type');
        $recipientEmail = $input->getArgument('recipient');
        $channel        = $input->getOption('channel');
        $allowedTypes   = implode(', ', $this->allowedTypes);

        if (true === empty($type)) {
            throw new InvalidArgumentException('Please specify *type* of notification');
        }

        if (false === in_array($type, $this->allowedTypes)) {
            throw new InvalidArgumentException("Type $type is not allowed. Allowed types are: $allowedTypes");
        }

        if (true === empty($recipientEmail)) {
            throw new InvalidArgumentException('Please specify *recipient* of notification');
        }

        if (true === filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                "Recipient email provided ($recipientEmail) is not a valid email address"
            );
        }

        if (true === empty($channel)) {
            throw new InvalidArgumentException('Please specify *channel* of notification');
        }

        $recipient = $this->userRepository->findByEmail($recipientEmail);

        if (null === $recipient) {
            throw new InvalidArgumentException("Recipient for specified email $recipientEmail not exists");
        }

        $this->notificationManager
            ->create(
                $type,
                $recipient,
                $channel
            )
            ->send()
        ;

        return Command::SUCCESS;
    }
}
