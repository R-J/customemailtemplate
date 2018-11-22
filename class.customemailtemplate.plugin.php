<?php

class CustomEmailTemplatePlugin extends Gdn_Plugin {
    /**
     * Add user info to template data.
     *
     * @param object $sender Instance of the calling class.
     * @param mixed $args Event arguments
     *
     * @return void.
     */
    public function base_beforeSendNotification_handler($sender, $args) {
        $emailTemplate = $args['Email']->getEmailTemplate();
        $user = (array)$args['User'];
        unset($user['Password'], $user['HashMethod']);
        if (!empty($user['Attributes']) && is_array($user['Attributes'])) {
            unset($user['Attributes']['PasswordResetKey']);
            unset($user['Attributes']['PasswordResetExpires']);
        }
        $emailTemplate->customEmailTemplate['user'] = $user;
    }

    public function gdn_email_beforeSendMail_handler($sender) {
        $emailTemplate = $sender->getEmailTemplate();

        // Tweak the view path to our custom view.
        $customViewLocation = Gdn::Controller()->fetchViewLocation(
            'email-basic',
            '',
            'plugins/customEmailTemplate'
        );
        $emailTemplate->setView($customViewLocation);

        // Re-render the mail.
        $sender->formatMessage($emailTemplate->toString());
    }
}
