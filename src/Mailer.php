<?php

namespace Aszone\Avenger;

class Mailer
{
    private $transporterMail;

    //function __construct(Swift_Mailer $swiftMailer, Swift_Message $swiftMessage)
    public function __construct()
    {
        $config = parse_ini_file('config/data.ini', true);
        $configMail = $config['email'];

        $this->transporterMail = \Swift_SmtpTransport::newInstance($configMail['host'], $configMail['port'], $configMail['security'])
            ->setUsername($configMail['username'])
            ->setPassword($configMail['password']);
    }

    public function sendMessage($to, $body)
    {
        $mailer = \Swift_Mailer::newInstance($this->transporterMail);
        $message = \Swift_Message::newInstance('Result of Avenger')
            ->setFrom($to)
            ->setTo($to)
            ->setBody(strip_tags($body))
            ->addPart($body, 'text/html');
        $numSent = $mailer->send($message);

        return $numSent;
    }
}
