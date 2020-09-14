<?php
namespace FMUP\ErrorHandler\Plugin;

use FMUP\Sapi;

class Mail extends Abstraction
{
    public function canHandle()
    {
        $config = $this->getBootstrap()->getConfig();
        return (
            (!$this->getException() instanceof \FMUP\Exception\Status)
            && !$config->get('use_daily_alert') && !$this->iniGet('display_errors')
        );
    }

    /**
     * @param string $key
     * @return string
     * @codeCoverageIgnore
     */
    protected function iniGet($key)
    {
        return ini_get($key);
    }

    /**
     * @return $this
     */
    public function handle()
    {
        $this->sendMail($this->getBody());
        return $this;
    }

    /**
     * @param string $body
     * @return bool
     * @throws \Exception
     * @throws \FMUP\Config\Exception
     * @throws \FMUP\Exception
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function sendMail($body)
    {
        $config = $this->getBootstrap()->getConfig();
        /** @var \FMUP\Request\Http $request */
        $request = $this->getRequest();
        $serverName = $this->getBootstrap()->getSapi()->get() != Sapi::CLI
            ? $request->getServer(\FMUP\Request\Http::SERVER_NAME)
            : $config->get('erreur_mail_sujet');
        $mail = $this->createMail($config);
        $mail->From = $config->get('mail_robot');
        $mail->FromName = $config->get('mail_robot_name');
        $mail->Subject = '[Erreur] ' . $serverName;
        $mail->AltBody = (string)$body;
        $mail->WordWrap = 50; // set word wrap

        $mail->Body = (string)$body;

        $recipients = $config->get('mail_support');
        if (strpos($recipients, ',') === false) {
            $mail->AddAddress($recipients, "Support");
        } else {
            $tab_recipients = explode(',', $recipients);
            foreach ($tab_recipients as $recipient) {
                $mail->AddAddress($recipient);
            }
        }
        return $mail->Send();
    }

    /**
     * Creates a new mail for config
     * @param \FMUP\Config\ConfigInterface $config
     * @return \FMUP\Mail
     * @codeCoverageIgnore
     */
    protected function createMail(\FMUP\Config\ConfigInterface $config)
    {
        return new \FMUP\Mail($config);
    }

    /**
     * @todo clean this
     * @return string
     */
    protected function getBody()
    {
        $view = new \FMUP\View(array('exception' => $this->getException()));
        return $view->setViewPath($this->getViewPath())->render();
    }

    protected function getViewPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('Mail', 'render.phtml'));
    }
}
