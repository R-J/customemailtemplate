<?php

class CustomEmailTemplatePlugin extends Gdn_Plugin {
    public function settingsController_customEmailTemplate_handler($sender) {
        $sender->permission('Garden.Settings.Manage');
        $sender->addSideMenu('dashboard/settings/plugins');

        $files = new RegexIterator(
            new DirectoryIterator(__DIR__.'/views'),
            '/\\.tpl\$/i'
        );
decho($files);
die;
        $sender->setData('Title', t('Custom Email Template'));

        $configurationModule = new ConfigurationModule($sender);
        $configurationModule->initialize([
            'CustomEmailTemplatePlugin.Template' => [
                'Control' => 'DropDown',
                'Items' => $files,
                /*
                [
                    'i1' => 'One Item',
                    'i2' => 'Another one',
                    'i3' => 'The Third'
                ],
                */
                'LabelCode' => 'Choose Email Template',
                'Options' => ['IncludeNull' => true]
            ]
        ]);

        // The configuration takes our instructions and renders a nice form
        // from it. It also handles saving our values to the config.
        $configurationModule->renderAll();
    }

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
