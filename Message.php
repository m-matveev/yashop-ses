<?php

namespace mamatveev\yii2AmazonSesMailer;

use yii\mail\BaseMessage;

/**
 * Message implements a message class based on Amazon Simple Email Service
 *
 * @author Mikhail Matveev <m.matveev114@gmail.com>
 * @author Vitaliy Ofat <ofatv22@gmail.com>
 */
class Message extends BaseMessage
{
    /**
     * @var \SimpleEmailServiceMessage Simple Email Service message instance.
     */
    private $_sesMessage;

    /**
     * @var string Text content
     */
    private $messageText;

    /**
     * @var string Html content
     */
    private $messageHtml = null;

    /**
     * @var string Message charset
     */
    private $charset;

    /**
     * @var string Message sender
     */
    private $from;

    /**
     * @var string replyTo
     */
    private $replyTo;

    /**
     * @var string To
     */
    private $to;

    /**
     * @var string CC
     */
    private $cc;

    /**
     * @var string BCC
     */
    private $bcc;

    /**
     * @var string Subject
     */
    private $subject;

    /**
     * @var integer Sending time for debugging
     */
    private $time;

    /**
     * In Yii2 dev panel some bug and this method have to return information about result of sending
     * @return \mamatveev\yii2AmazonSesMailer\Message Message class instance.
     */
    public function getSwiftMessage()
    {
        return $this;
    }

    /**
     * @return \SimpleEmailServiceMessage Simple Email Service message instance.
     */
    public function getSesMessage()
    {
        if (!is_object($this->_sesMessage)) {
            $this->_sesMessage = new \SimpleEmailServiceMessage();
        }

        return $this->_sesMessage;
    }

    /**
     * @inheritdoc
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @inheritdoc
     */
    public function setCharset($charset)
    {
        $this->getSesMessage()->setMessageCharset($charset);
        $this->getSesMessage()->setSubjectCharset($charset);

        $this->charset = $charset;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @inheritdoc
     * @todo name не обязательно и hostname вставлять не надо
     */
    public function setFrom($from, $name = null)
    {
        if (!isset($name)) {
            $name = gethostname();
        }
        if (!is_array($from) && isset($name)) {
            $from = array($from => $name);
        }
        list($address) = array_keys($from);
        $name = $from[$address];
        $this->from = '"'.$name.'" <'.$address.'>';
        $this->getSesMessage()->setFrom($this->from);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($replyTo)
    {
        $this->getSesMessage()->addReplyTo($replyTo);
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @inheritdoc
     */
    public function setTo($to)
    {
        $this->to = $to;
        $sesMessage = $this->getSesMessage();
        $sesMessage->to = [];

        if (is_array($this->to)) {
            $sesMessage->addTo(array_flip($this->to));
        } else {
            $sesMessage->addTo($this->to);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @inheritdoc
     */
    public function setCc($cc)
    {
        $this->getSesMessage()->addCC($cc);
        $this->cc = $cc;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @inheritdoc
     */
    public function setBcc($bcc)
    {
        $this->getSesMessage()->addBCC($bcc);
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject)
    {
        $this->getSesMessage()->setSubject($subject);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTextBody($text)
    {
        $this->messageText = $text;
        $this->setBody($this->messageText, $this->messageHtml);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHtmlBody($html)
    {
        $this->messageHtml = $html;
        $this->setBody($this->messageText, $this->messageHtml);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBody()
    {
        return $this->messageText;
    }

    /**
     * @inheritdoc
     */
    public function setBody($text, $html = null)
    {
        $this->getSesMessage()->setMessageFromString($text, $html);
    }

    /**
     * @inheritdoc
     */
    public function attach($fileName, array $options = [])
    {
        $name = $fileName;
        $mimeType = 'application/octet-stream';
        $contentId = null;
        $attachmentType = 'attachment';

        if (!empty($options['fileName'])) {
            $name = $options['fileName'];
        }
        if (!empty($options['contentType'])) {
            $mimeType = $options['contentType'];
        }
        if (!empty($options['contentId'])) {
            $contentId = $options['contentId'];
        }
        if (!empty($options['attachmentType'])) {
            $attachmentType = $options['attachmentType'];
        }

        $this->getSesMessage()->addAttachmentFromFile($name, $fileName, $mimeType, $contentId, $attachmentType);

        return $this;
    }

    /**
     * @inheritdoc
     * @todo допилить
     */
    public function attachContent($content, array $options = [])
    {
        $name = 'file 1';
        $mimeType = 'application/octet-stream';

        if (!empty($options['fileName'])) {
            $name = $options['fileName'];
        }
        if (!empty($options['contentType'])) {
            $mimeType = $options['contentType'];
        }
        $this->getSesMessage()->addAttachmentFromData($name, $content, $mimeType);

        return $this;
    }

    /**
     * @inheritdoc
     * @todo допилить
     */
    public function embed($fileName, array $options = [])
    {
//        $embed = [
//            'File' => $fileName
//        ];
//        if (!empty($options['fileName'])) {
//            $embed['Name'] = $options['fileName'];
//        } else {
//            $embed['Name'] = pathinfo($fileName, PATHINFO_BASENAME);
//        }
//        $embed['ContentID'] = 'cid:' . uniqid();
//        $this->attachments[] = $embed;
//        return $embed['ContentID'];

        return $this->attach($fileName, $options);
    }

    /**
     * @inheritdoc
     */
    public function embedContent($content, array $options = [])
    {
//        должно быть по идее так
//        return $this->attachContent($content, $options);

        if (isset($options['fileName']) === false || empty($options['fileName'])) {
            throw new InvalidParamException('fileName is missing');
        }

        $contentId = uniqid('attach_', true);
        $mimeType = 'application/octet-stream';
        $attachmentType = 'attachment';

        if (!empty($options['contentType'])) {
            $mimeType = $options['contentType'];
        }

        if (!empty($options['attachmentType'])) {
            $attachmentType = $options['attachmentType'];
        }

        $this->getSesMessage()->addAttachmentFromData($options['fileName'],
            $content,
            $mimeType,
            "<{$contentId}>");

        return $contentId;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return $this->getSesMessage()->getRawMessage();
    }

    public function setDate($time)
    {
        $this->time = $time;

        return $this;
    }

    public function getDate()
    {
        return $this->time;
    }

    public function getHeaders()
    {
        //todo: make headers for debug
        return '';
    }

    public function setHeader($key, $value)
    {
        $this->getSesMessage()->addTextHeader($key, $value);

        return $this;
    }
}
