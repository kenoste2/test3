<?php

require_once 'application/controllers/BaseController.php';

class CronController extends BaseController
{

    public function trainAction()
    {
        global $config;

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        /*
         * 1.  actie_id, file_id en template_id ophalen volgens train logica uit train.php van Final
         */

        $train = new Application_Model_Train();
        $modules = $train->getMappedTrainModules();

        $print = new Application_Model_Print();
        $filesActions = new Application_Model_FilesActions();

        foreach ($modules as $module) {
            /* 2. fetch content from template */
            $content = $print->getTemplateContent($module->FILE_ID, $module->TEMPLATE_ID);
            $content = json_decode($content);
            $content = $content->CONTENT;
            $to = $print->getToContent($module->FILE_ID,$module->TEMPLATE_ID);

            $now = new DateTime();
            $action = array(
                'FILE_ID' => $module->FILE_ID,
                'ACTION_ID' => $module->ACTION_ID,
                'REMARKS' => '',
                'VIA' => 'POST',
                'ADDRESS' => "{$to['NAME']}\n{$to['ADDRESS']}\n{$to['ZIP_CODE']} {$to['CITY']}",
                'EMAIL' => $to['E_MAIL'],
                'PRINTED' => 0,
                'ACTION_DATE' => $now->format('Y-m-d'),
                'TEMPLATE_ID' => $module->TEMPLATE_ID,
                'FILE_STATE_ID' => $modules->STATE_ID,
                'CONTENT' => $content
            );
            /* 3. model Application_Model_FilesActions -> add */
            $fileActionId = $filesActions->add($action);


            $interestCostsAccess = $this->moduleAccess('intrestCosts');

            $pdfDoc = new Application_Model_PdfDocument($interestCostsAccess);

            $pdfDoc->_initPdf();
            $pdfDoc->_loadContentToPdf($fileActionId);
            $fileName = $config->rootFileActionDocuments . "/{$fileActionId}.pdf";
            if (!file_exists($fileName)) {
                $pdfDoc->pdf->Output($fileName);
            }
        }

        die("train has run");
    }

    public function intrestsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $obj = new Application_Model_FilesReferences();

        $referencesToUpdate = $obj->getOpenReferencesWithAutoCalculate();

        if (!empty($referencesToUpdate)) {
            foreach($referencesToUpdate as $reference) {
                $data = array(
                    'AMOUNT' => $reference->AMOUNT,
                    'INTEREST_PERCENT' => $reference->INTEREST,
                    'INTEREST_PERCENT' => $reference->INTEREST_PERCENT,
                    'AUTO_CALCULATE' => 1,
                    'START_DATE' => $reference->START_DATE,
                    'END_DATE' => date("Y-m-d"),
                    'REFERENCE_ID' => $reference->REFERENCE_ID
                );
                $obj->update($data);
            }
        }
        die("intrests have been calculated");
    }


    public function saldoReportAction()
    {
        $obj = new Application_Model_Dso();

        $clientsModel = new Application_Model_Clients();

        $activeClients = $clientsModel->getAllClients();
        foreach($activeClients[0] as $client) {

            $openAmount = $obj->getOpenAmounts($client->CLIENT_ID);
            $interCompany = $obj->getIntercompany($client->CLIENT_ID);
            $data = array(
                'AMOUNT' => $openAmount ? $openAmount : 0,
                'INTERCOMPANY' => $interCompany ? $interCompany : 0,
                'CLIENT_ID' => $client->CLIENT_ID,
                'CREATION_DATE' => date("Y-m-d"),
                'CREATION_USER' => 'CRON',
            );

            $this->saveData('REPORTS$SALDO', $data);
        }

        die("saldo saved");
    }

    public function paymentdelayAction()
    {
        $obj = new Application_Model_Debtors();
        $results = $obj->getCurrentPaymentDelays();

        if ($results) {
            foreach ($results as $row) {
                if (!empty($row->DELAY))
                $data = array (
                    'DEBTOR_ID' => $row->DEBTOR_ID,
                    'PAYMENT_DELAY' => $row->DELAY,
                    'CREATION_USER' => 'CRON',
                    'CREATION_DATE' => date("Y-m-d"),
                );
                $this->saveData('DEBTORS$PAYMENT_DELAY', $data);
            }
        }
        die("delays saved");
    }



    public function commissionAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $obj = new Application_Model_CalculateCommission();
        $obj->getList();
        die("commissions");
    }

    public function fetchMailsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->saveMailsFromMailbox();
        die("mails fetched");
    }

    private function saveMailsFromMailbox()
    {
        $obj = new Application_Model_MailFetch();
        $mails = $obj->getInbox();


        $remarksObj = new Application_Model_FilesRemarks();

        if (!empty($mails)) {
            foreach ($mails as $mail) {
                $this->handleMail($mail, $remarksObj);
            }
        }
    }

    /**
     * @param $mail
     * @param $remarksObj
     */
    private function handleMail($mail, $remarksObj)
    {
        global $config;
        $importedMails = new Application_Model_ImportedMails();
        $matches = array();
        $match = preg_match("/#(.+?)-(.+?)#/", $mail['subject'], $matches);
        if ($match) {
            $clientCode = $matches[1];
            $reference = $matches[2];
            $escClientCode = $this->db->escape($clientCode);
            $escReference = $this->db->escape($reference);

            $fileId = $this->db->get_var("SELECT FILE_ID FROM FILES\$FILES_ALL_INFO WHERE CLIENT_CODE = '{$escClientCode}' AND REFERENCE = '{$escReference}'");
            if ($fileId) {

                $mail['from'] = str_replace("<", "(", $mail['from']);
                $mail['from'] = str_replace(">", ")", $mail['from']);
                $mail['subject'] = str_replace("#", " ", $mail['subject']);
                $remark = "{$mail['subject']} --- From : {$mail['from']}";
                $remark = str_replace("\"", "`", $remark);

                $remark = utf8_encode($remark);




                $data = array(
                    'FILE_ID' => $fileId,
                    'CREATION_DATE' => $mail['date'],
                    'FROM_EMAIL' => $mail['from'],
                    'TO_EMAIL' => $mail['to'],
                    'MAIL_BODY' => $mail['plainContent'],
                    'MAIL_SUBJECT' => $mail['subject']
                );

                $importedMailId = $importedMails->add($data);

                if(count($mail['attachments']) > 0) {

                    foreach($mail['attachments'] as $attachment) {

                        $splitName = pathinfo($attachment['file_name']);
                        $now = new DateTime();
                        $nowStr = $now->format('Y-m-d-H-i-s');

                        $serverFileName = "{$splitName['filename']}_{$nowStr}.{$splitName['extension']}";
                        $filePath = "{$config->rootMailAttachmentsDocuments}/{$serverFileName}";
                        $fileSystem = new Application_Model_FileSystem();
                        $fileSystem->createFileFromContent($filePath, $attachment['content']);

                        $attachmentData = array(
                            'IMPORTED_MAIL_ID' => $importedMailId,
                            'ORIGINAL_FILENAME' => $attachment['file_name'],
                            'SERVER_FILENAME' => $serverFileName,
                            'MIME_TYPE' => $attachment['type'],
                            'CREATION_DATE' => $mail['date']
                        );
                        $importedMails->addAttachment($attachmentData);
                    }
                }
            }
        }
    }
}

