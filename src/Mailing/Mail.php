<?php namespace MapGuesser\Mailing;

use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    private array $recipients = [];

    public string $subject = '';

    public string $body = '';

    public function addRecipient(string $mail, ?string $name = null): void
    {
        $this->recipients[] = [$mail, $name];
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function setBodyFromTemplate(string $template, array $params = []): void
    {
        $this->body = file_get_contents(ROOT . '/mail/' . $template . '.html');

        $baseParameters = [
            'APP_NAME' => $_ENV['APP_NAME'],
            'BASE_URL' => \Container::$request->getBase(),
        ];

        $params = array_merge($baseParameters, $params);

        foreach ($params as $key => $param) {
            $this->body = str_replace('{{' . $key . '}}', $param, $this->body);
        }
    }

    public function send(): void
    {
        $mailer = new PHPMailer(true);

        $mailer->CharSet = 'utf-8';
        $mailer->Hostname = substr($_ENV['MAIL_FROM'], strpos($_ENV['MAIL_FROM'], '@') + 1);

        if (!empty($_ENV['MAIL_HOST'])) {
            $mailer->Mailer = 'smtp';
            $mailer->Host = $_ENV['MAIL_HOST'];
            $mailer->Port = !empty($_ENV['MAIL_PORT']) ? $_ENV['MAIL_PORT'] : 25;
            $mailer->SMTPSecure = !empty($_ENV['MAIL_SECURE']) ? $_ENV['MAIL_SECURE'] : '';

            if (!empty($_ENV['MAIL_USER'])) {
                $mailer->SMTPAuth = true;
                $mailer->Username = $_ENV['MAIL_USER'];
                $mailer->Password = $_ENV['MAIL_PASSWORD'];
            } else {
                $mailer->SMTPAuth = false;
            }
        } else {
            $mailer->Mailer = 'mail';
        }

        $mailer->setFrom($_ENV['MAIL_FROM'], $_ENV['APP_NAME']);
        $mailer->addReplyTo($_ENV['MAIL_FROM'], $_ENV['APP_NAME']);

        $mailer->Sender = !empty($_ENV['MAIL_BOUNCE']) ? $_ENV['MAIL_BOUNCE'] : $_ENV['MAIL_FROM'];
        $mailer->Subject = $this->subject;
        $mailer->msgHTML($this->body);

        foreach ($this->recipients as $recipient) {
            $this->sendMail($mailer, $recipient);
        }
    }

    private function sendMail(PHPMailer $mailer, array $recipient)
    {
        $mailer->clearAddresses();
        $mailer->addAddress($recipient[0], $recipient[1]);

        $mailer->send();
    }
}
