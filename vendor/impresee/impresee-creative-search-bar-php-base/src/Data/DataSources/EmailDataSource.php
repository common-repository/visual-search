<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Data\Models\EmailModel;

interface EmailDataSource {
    public function sendErrorEmail(EmailModel $email_data);
    public function sendInformationEmail(EmailModel $email_data);
}