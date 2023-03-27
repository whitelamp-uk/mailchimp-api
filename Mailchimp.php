<?php
namespace Blotto;

require __DIR__.'/config.php'; // defines nothing
require MAILCHIMP_PHP;

class Mailchimp {
    private $mc;

    public function __construct ( ) {
        $this->mc = new \MailchimpTransactional\ApiClient();
    }

    public function keySet ($key) {
        $this->mc->setApiKey( $key);
    }

    public function ping() {
        $response = $this->mc->users->ping();
        return $response;
    }

    public function ping2() {
        $response = $this->mc->users->ping2();
        return $response;
    }

    public function received ($template_name,$message_ref) {
        // return true if hasn't bounced
        //$response = $this->mc->messages->info(["id" => $message_ref]);
        //print_r($response);
    }

    public function send ($template_name,$email_to,$data) {
        // campaign id 
        // $data is a simple associative array [ template_var_name_1 => value_1, ... ]
        // template_name  slug of a template that exists in the user's account.
        // message - includes subject, mail from, mail to etc see list here
        // https://mailchimp.com/developer/transactional/api/messages/send-using-message-template/
        // https://mailchimp.com/developer/transactional/docs/templates-dynamic-content/#editable-content-areas
        // template_content is required and refers to the "original" merge method - essentially named <divs> 
        // which can now contain merge_vars
        // merge_vars can be either {{handlebars}} or *|MC_STYLE|*
        // also see https://gist.github.com/jonathansanchez/5daf5c1db024c9d32c546f11b4db9cb6

        // handlebars or mailchimp or empty for default; however the only way of supporting both 
        // types of Mailchimp template is to override to handlebars.  When templates are exported
        // in to Mandrill, if the default merge language (in settings) is handlebars, then Mailchimp 
        // tags are converted, and handlebars tags are escaped (!).  If default is Mailchimp, no
        // changes are made.  So you set the default to the *opposite* of the one you want to use!
        $merge_language = 'handlebars'; 

        // take sane array of merge variables and make insane:
        $merge_vars_vars = [];
        foreach ($data as $template_var => $value) {
            $merge_vars_vars[] = ['name' => $template_var, 'content' => $value];
        }

        $merge_vars_vars[] = ['name' => 'mergelanguage', 'content' => $merge_language];

        $message = array(
            'subject' => 'Mailchimp test',
            'from_email' => 'me@thefundraisingfoundry.com',
            'to' => array(
                array('email' => $email_to, 'name' => 'Me', 'type'  => 'to')
                ),
            'global_merge_vars' => $merge_vars_vars, //
            
            'merge_vars' => array(array(
                'rcpt' => $email_to,
                'vars' => [["name"=>'','content'=>'']] //$merge_vars_vars,
                )),
            );


        $message['auto_text']      = true;
        $message['inline_css']     = true;
        $message['merge']          = true;
        if ($merge_language) {
            $message['merge_language'] = $merge_language; 
        }

        //print_r($message);
        $response = $this->mc->messages->sendTemplate([
            "template_name" => $template_name,
            "template_content" => [["name"=>'','content'=>'']], //daft, can't be null or empty array
            "message" => $message,

        ]);
        print_r($response);

    }

}