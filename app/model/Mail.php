<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;

class Mail
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var IMailer
     */
    private $mailer;


    /**
     * @param array $config
     * @param IMailer $mailer
     */
    public function __construct(array $config, IMailer $mailer)
    {
        $this->config = $config;
        $this->mailer = $mailer;
    }


    /**
     * @param Message $message
     * @throws SendException
     */
    public function send(Message $message): void
    {
        $message->setFrom($this->getFrom(), $this->getFromName());

        $this->mailer->send($message);
    }


    /**
     * @return string
     */
    protected function getFrom(): string
    {
        return $this->config['from'];
    }


    /**
     * @return string|null
     */
    protected function getFromName(): ?string
    {
        return $this->config['fromName'];
    }

}
