<?php
namespace Blotto;

require __DIR__.'/config.php'; // defines nothing

require MAILCHIMP_MARKETING_PHP;
require MAILCHIMP_TRANSACTIONAL_PHP;

class Mailchimp {
    private $mm;
    private $mt;
    public $errorLast;

    public function __construct ( ) {
        $this->mm = new \MailchimpMarketing\ApiClient();
        $this->mt = new \MailchimpTransactional\ApiClient();
    }

    public function keySet ($keys) {
        $pieces = explode(' ', $keys);
        $this->mm->setConfig([
          'apiKey' => $pieces[0],
          'server' => $pieces[1]
        ]);
        $this->mt->setApiKey($pieces[2]);
    }

    public function ping() {
        $mm_response = $this->mm->ping->get();
        $mt_response = $this->mt->users->ping2(); // ping() also exists
        return "Mailchimp Marketing API\n".print_r($mm_response, true)."\nMailchimp Transactional API\n".print_r($mt_response,true)."\n";
    }

    public function received ($template_name,$message_ref) {
        //return true if hasn't bounced
        $response = $this->mt->messages->info(["id" => $message_ref]);
print_r($response);

        if (property_exists($response,'state')) {
            return ($response->state == 'sent'); // "sent", "bounced", or "rejected".
        }
        return false;

    }

    public function send ($campaign_ref,$email_to,$data) {
        // $data is a simple associative array [ template_var_name_1 => value_1, ... ]
        // template_name  slug of a template that exists in the user's account.
        // message - includes subject, mail from, mail to etc see list here
        // https://mailchimp.com/developer/transactional/api/messages/send-using-message-template/
        // https://mailchimp.com/developer/transactional/docs/templates-dynamic-content/#editable-content-areas
        // template_content is required and refers to the "original" merge method - essentially named <divs> 
        // which can now contain merge_vars
        // merge_vars can be either {{handlebars}} or *|MC_STYLE|*
        // also see https://gist.github.com/jonathansanchez/5daf5c1db024c9d32c546f11b4db9cb6
        try {
            $campaign = $this->mm->campaigns->get($campaign_ref); 

            $subj = $campaign->settings->subject_line;
            $template_id = $campaign->settings->template_id;
            $template = $this->mm->templates->getTemplate($template_id);

            $template_name = $template->name;
        }
        catch (\Exception $e) {
            $this->errorLast = $e->getMessage();
            return false;
        }
        // handlebars or mailchimp or empty for default; however the only way of supporting both 
        // types of Mailchimp template is to override to handlebars.  When templates are exported
        // in to Mandrill, if the default merge language (in settings) is handlebars, then Mailchimp 
        // tags are converted, and handlebars tags are escaped (!).  If default is Mailchimp, no
        // changes are made.  So you set the default to the *opposite* of the one you want to use!
        $merge_language = 'handlebars'; // Don't change unless quite sure.

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
            
            /*'merge_vars' => array(array(
                'rcpt' => $email_to,
                'vars' => [["name"=>'','content'=>'']] //$merge_vars_vars,
                )),*/
            );

        $message['auto_text']      = true;
        $message['inline_css']     = true;
        $message['merge']          = true;
        if ($merge_language) {
            $message['merge_language'] = $merge_language; 
        }
        $this->errorLast = null;
        try {
            $response = $this->mt->messages->sendTemplate([
                "template_name" => $template_name,
                "template_content" => [["name"=>'','content'=>'']], //daft, can't be null or empty array
                "message" => $message,

            ]);
        }
        catch (\Exception $e) {
            $this->errorLast = print_r($e, true);
            return false;
        }
        catch (\Error $e) {
            $this->errorLast = print_r($e, true);
        }

        if ($response[0]->status == 'sent') {
            return $response[0]->_id;
        }
        $this->errorLast = "CMID=$campaign_ref Email=$email_to\ndata=".print_r($data,true)."\nreturn=".print_r($response,true);
        return false;
    }
}