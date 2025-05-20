<?php

/**
 * Base class of Emails
 */
/**
 * Requires
 */
require_once 'phpmailer.class.php';
require_once 'smtp.class.php';

/**
 * Email repository used by the application
 * @filename: emails.class.php
 * Location: _app/_models/_class
 * @Creator: J. Raya (JRM) <info@novaigrup.com>
 * 	20150105 RBM Created
 * 	20160520 RBM Modified
 */
class Emails
{

    /**
     * Email server host
     * @var string
     * @access private
     */
    public $host = '';

    /**
     * Sender address
     * @var string
     * @access private
     */
    public $from_address = '';

    /**
     * Sender name
     * @var string
     * @access private
     */
    public $from_name = '';

    /**
     * SMTP auth is required or not
     * @var bool
     * @access private
     */
    public $smtp_auth = '';

    /**
     * User account for auth send
     * @var string
     * @access private
     */
    public $auth_user = '';

    /**
     * Password account for auth send
     * @var string
     * @access private
     */
    public $auth_password = '';

    /**
     * Errors array
     * @var array
     */
    public $errors = array();
    
    /**
     * Obj email
     * @var object 
     */
    private $obj_email;

    /**
     * Class constructor
     *
     * @param string $host
     * @param string $from_address
     * @param string $from_name
     * @param bool $smtp_auth Optional, default false
     * @param string $auth_user Optional
     * @param string $auth_password Optional		
     */
    public function __construct($host, $from_address, $from_name, $smtp_auth = false, $auth_user = '', $auth_password = '')
    {
        $this->host = $host;
        $this->from_address = $from_address;
        $this->from_name = $from_name;
        $this->smtp_auth = $smtp_auth;
        $this->auth_user = $auth_user;
        $this->auth_password = $auth_password;

        $this->obj_email = new PHPMailer;
    }

    /**
     * General send email function
     * @param array $array_recipients
     * @param string $subject
     * @param string $body
     * @param boolean $is_html
     * @return boolean
     */
    public function sendEmail(array $array_recipients, $subject, $body, $is_html = false)
    {

        $this->obj_email->CharSet = 'UTF-8';
        $this->obj_email->SMTPKeepAlive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead
        $this->obj_email->Port = 25;
        $this->obj_email->setFrom($this->from_address, $this->from_name);
        $this->obj_email->addReplyTo($this->from_address, $this->from_name);

        $this->obj_email->Subject = $subject;

        if ($is_html)
        {
            $this->obj_email->msgHTML($body);/** Attach AltBody inherit */
        }
        else
        {
            $this->obj_email->Body = $body;
        }


        foreach ($array_recipients as $full_name => $email)
        {
            if ($this->smtp_auth)
            {
                $this->obj_email->isSMTP();
                $this->obj_email->Host = $this->host;
                $this->obj_email->SMTPAuth = true;
                $this->obj_email->Username = $this->auth_user;
                $this->obj_email->Password = $this->auth_password;
            }
            else
            {
                $this->obj_email->isMail();
                $this->obj_email->Host = 'localhost';
                $this->obj_email->SMTPAuth = false;
            }

            $this->obj_email->addAddress($email, $full_name);

            if (!$this->obj_email->send())
            {
                /** On error try to send vía localhost */
                $this->obj_email->isMail();
                $this->obj_email->Host = 'localhost';
                $this->obj_email->SMTPAuth = false;
                if (!$this->obj_email->send())
                {
                    $this->errors[] = array($email, $this->obj_email->ErrorInfo);
                }
            }

            // Clear all addresses and attachments for next loop
            $this->obj_email->clearAddresses();
        }

        // Once the email is sended to all the addresses, we clear the attachments
        $this->obj_email->clearAttachments();		
		
        if (count($this->errors) == 0)
        {
            return true;
        }

        return false;
    }
    
    public function attachFile( $binary_file, $name = 'attach.file' )
    {
        $this->obj_email->addStringAttachment($binary_file, $name);
    }

}

?>