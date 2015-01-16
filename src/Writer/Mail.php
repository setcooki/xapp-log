<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Log.Writer');
xapp_import('xapp.Log.Writer.Exception');

/**
 * Log Writer Mail class
 *
 * @package Log
 * @subpackage Log_Writer
 * @class Xapp_Log_Writer_Mail
 * @error 119
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Log_Writer_Mail extends Xapp_Log_Writer
{
    /**
     * contains either a string with single email address, a string with
     * multiple email addresses separated by comma, an array of email addresses
     * or instance of Xapp_Mail
     *
     * @var null|string|array|Xapp_Mail
     */
    public $mail = null;

    /**
     * contains the emails subject
     *
     * @var null|string
     */
    protected $_subject = null;

    /**
     * contains the emails optional from email address or array with email
     * addresses or array with address => name pairs.
     *
     * @var null|mixed
     */
    protected $_from = null;

    /**
     * contains mail priority by default 3 = normal
     *
     * @var int
     */
    protected $_priority = 3;

    /**
     * contains mail encoding by default utf-8. this value has only affect
     * if this class is used with phps native mail() class
     *
     * @var string
     */
    protected $_encoding = 'utf-8';

    /**
     * array of additional headers. these values have only affect if this
     * class is used with phps native mail() class
     *
     * @var array
     */
    protected $_headers = array();


    /**
     * class constructor receives all necessary instance parameters and sets them to be
     * used at later stage in write action.
     *
     * @error 11901
     * @param string|array|Xapp_Mail $mail expects valid mail value all explained in property description
     * @param string $subject expects the mails subject
     * @param null|string|array $from expects optional mails from value(s)
     * @param int $priority expects optional priority
     * @param string $encoding expects optional encoding
     * @param array $headers expects optional additional headers
     */
    public function __construct($mail, $subject, $from = null, $priority = 3, $encoding = 'utf-8', Array $headers = array())
    {
        $this->mail = $mail;
        $this->_subject = trim($subject);
        $this->_from = $from;
        $this->_priority = (int)$priority;
        $this->_encoding = strtolower(trim((string)$encoding));
        $this->_headers = $headers;
    }


    /**
     * write message by sending email to recipients which can be multiple. if the mail parameter
     * in class constructor is passed with an instance of Xapp_Mail will compose a message from
     * the class constructors parameters and the incoming message and send the mail to the recipients.
     * if the first parameter is a string of comma separated email addresses or array of email addresses
     * will use phps native mail() function to send mail.
     *
     * @error 11902
     * @param string|array|object $message expects the message object
     * @param null|mixed $params expects optional parameters
     * @return bool|mixed
     */
    public function write($message, $params = null)
    {
        $message = $this->format($message, PHP_EOL, PHP_EOL . PHP_EOL);
        if(is_object($this->mail) && $this->mail instanceof Xapp_Mail)
        {
            $msg = $this->mail->compose
            (
                $message,
                $this->_subject,
                null,
                $this->_from,
                $this->_priority
            );
            $res = $this->mail->dispatch($msg);
            return ((int)$res > 0) ? true : false;
        }else{
            $err = 0;
            if(!is_array($this->mail))
            {
                $this->mail = strtolower(trim($this->mail));
                if(strpos($this->mail, ',') !== false || strpos($this->mail, ';') !== false)
                {
                    $this->mail = implode(',', str_replace(array(';'), ',', $this->mail));
                }else{
                    $this->mail = (array)$this->mail;
                }
            }
            $headers   = array();
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-type: text/plain; charset=" . trim($this->_encoding);
            $headers[] = "X-Mailer: PHP/".phpversion();
            if($this->_from !== null)
            {
                $this->_from = (array)$this->_from;
                $headers[] = "From: " . trim(array_shift($this->_from));
            }
            if((int)$this->_priority === 1)
            {
                $headers[] = "X-Priority: 1 (Highest)";
                $headers[] = "X-MSMail-Priority: High";
                $headers[] = "Importance: High";
            }
            $headers = array_merge($headers, (array)$this->_headers);
            foreach($this->mail as $m)
            {
                if(!@mail($m, trim($this->_subject), $message, implode("\r\n", $headers)))
                {
                    $err++;
                }
            }
            return ($err === 0) ? true : false;
        }
    }
}