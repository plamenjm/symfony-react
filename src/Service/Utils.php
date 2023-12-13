<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Utils
{
    private LoggerInterface $logger;
    private array $happyMessages = ['Amazing!', 'You did it!', 'Great work!', 'Keep going!'];

    public function __construct(#[Autowire(lazy: true)] LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function happyMessage(): string
    {
        $msg = $this->happyMessages[array_rand($this->happyMessages)];
        $this->logger->debug($msg, ['Utils', 'happyMessage']);
        return $msg;
    }
}