<?php
/*
 * Name:
 * Description:
 */

namespace Swift\Events;

Class Mail {
    
    //Reset SMTP Transport
    public function onSending($message)
    {
        $swiftMailer = \Mail::getSwiftMailer();
        try {
            if ($swiftMailer->getTransport() instanceof \Swift_Transport_AbstractSmtpTransport)
            {
                $swiftMailer->getTransport()->reset(); // Send RSET to restart the smtp status
            }
        } catch ( Exception $e ) {
            try {
                $swiftMailer->getTransport()->stop(); // Stop (QUIT, will probably fail too)
            } catch ( Exception $e ) {
            }
            $swiftMailer->getTransport()->start(); // Start Back
        }
    }
    
    public function subscribe($events)
    {
        $events->listen('mailer.sending', '\Swift\Events\Mail@onSending');
    }
}