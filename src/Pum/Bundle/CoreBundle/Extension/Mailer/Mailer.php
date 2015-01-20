<?php

namespace Pum\Bundle\CoreBundle\Extension\Mailer;

use Symfony\Component\Templating\EngineInterface;

class Mailer
{
    private $mailer;
    private $templating;
    private $transport;
    private $params;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer     = $mailer;
        $this->templating = $templating;
        $this->transport  = $mailer->getTransport();
        $this->$params    = array();

        if ($this->transport instanceof \Swift_Transport_SendmailTransport) {
            $this->transport->setCommand('/usr/sbin/sendmail -t');
        }
    }

    /**
     * Reset params
     */
    public function init($params)
    {
        $this->params = array();

        return $this;
    }

    /**
     * shortcut to send mail
     * @param  array $params
     */
    public function send($params = null, $reset = true)
    {
        if (null === $params) {
            $params = $this->params;

            if ($reset) {
                $this->init();
            }
        }

        // Compose mail from params
        $message     = \Swift_Message::newInstance();
        $bodyContent = '';
        $type        = 'text/html';

        foreach ($params as $key => $value) {
            switch ($key) {
                case 'subject':
                    $message->setSubject($value);
                    break;

                case 'from':
                    $message->setFrom($value);
                    break;

                case 'to':
                    $adrs = (array)$value;
                    foreach ($adrs as $adr) {
                        if (filter_var($adr, FILTER_VALIDATE_EMAIL)) {
                            $message->setTo($adr);
                        }
                    }
                    break;

                case 'cc':
                    $adrs = (array)$value;
                    foreach ($adrs as $adr) {
                        if (filter_var($adr, FILTER_VALIDATE_EMAIL)) {
                            $message->setCc($adr);
                        }
                    }
                    break;

                case 'body':
                    $bodyContent = $value;
                    break;

                case 'template':
                    if (isset($value['name']) && isset($value['vars'])) {
                        $bodyContent = $this->templating->render($value['name'], $value['vars']);
                    }
                    break;

                case 'type':
                    $type = $value;
                    break;

                case 'attachmentPath':
                    $message->attach(\Swift_Attachment::fromPath($value));
                    break;
            }
        }

        $message->setBody($bodyContent, $type);
        $this->mailer->send($message);
    }

    public function subject($value)
    {
        $this->params['subject'] = $value;

        return $this;
    }

    public function from($value)
    {
        $this->params['from'] = $value;

        return $this;
    }

    public function to($value)
    {
        $this->params['to'][] = $value;

        return $this;
    }

    public function cc($value)
    {
        $this->params['cc'][] = $value;

        return $this;
    }

    public function body($value)
    {
        $this->params['body'] = $value;

        return $this;
    }

    public function template($name, $vars = array())
    {
        $this->params['template']['name'] = $name;
        $this->params['template']['vars'] = $vars;

        return $this;
    }

    public function type($value)
    {
        $this->params['type'] = $value;

        return $this;
    }

    public function attachmentPath($value)
    {
        $this->params['attachmentPath'] = $value;

        return $this;
    }
}
