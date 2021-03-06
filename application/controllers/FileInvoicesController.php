<?php

require_once 'application/controllers/BaseFileController.php';

class FileInvoicesController extends BaseFileController
{

    public function viewAction()
    {
        $obj = new Application_Model_FilesReferences();

        $this->_helper->_layout->setLayout('file-layout');

        if ($this->hasAccess('manageInvoices')) {
            //$this->view->addButton = "/file-invoices/add/fileId/" . $this->fileId;
            $this->view->manageInvoices = true;
        }
        $this->view->printButton = true;


        if ($this->moduleAccess('intrestCosts')) {
            $this->view->showIntrestCosts = true;
        }

        if ($this->getParam("delete") && $this->hasAccess('manageInvoices')) {
            //$this->delete($this->getParam("delete"));
            $this->view->deleted = true;
        }

        $results = $obj->getReferencesByFileId($this->fileId);
        $this->view->results = $results;
    }

    public function addAction()
    {
        $form = new Application_Form_FileAddInvoice();

        $fileReferenceObj = new Application_Model_FilesReferences();

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();
                $update['FILE_ID'] = $this->fileId;
                $update['START_DATE'] = $this->functions->date_dbformat($update['START_DATE']);
                $update['INVOICE_DATE'] = $this->functions->date_dbformat($update['INVOICE_DATE']);
                $update['END_DATE'] = date("Y-m-d");
                $update['AMOUNT'] = $this->functions->dbBedrag($update['AMOUNT']);
                $update['DISPUTE_AMOUNT'] = $this->functions->dbBedrag($update['DISPUTE_AMOUNT']);
                $update['COSTS'] = $this->functions->dbBedrag($update['COSTS']);
                $update['INTEREST'] = $this->functions->dbBedrag($update['INTEREST']);
                $update['REFERENCE_TYPE'] = "FACTUUR";
                $fileReferenceObj->create($update);
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        } else {
            $data = array();
        }
// Populating form
        $form->populate($data);

        $this->view->form = $form;
    }

    public function editAction()
    {
        $form = new Application_Form_FileEditInvoice();
        $fileReferenceObj = new Application_Model_FilesReferences();

        if (!$this->hasAccess('manageInvoices')) {
            $this->_redirect('/error/noaccess');
            return;
        }

        if (!$this->hasAccess('manageInvoices')) {
            $form->removeElement('submit');
        }

        if ($this->auth->online_rights == 9) {
            $form->removeElement('submit');
            $form->DISPUTE->setAttrib('disabled', true);
            $form->DISPUTE_COMMENT->setAttrib('disabled', true);
            $form->DISPUTE_ASSIGNEE->setAttrib('disabled', true);
            $form->DISPUTE_STATUS->setAttrib('disabled', true);
            $form->DISPUTE_DATE->setAttrib('disabled', true);
            $form->DISPUTE_DUEDATE->setAttrib('disabled', true);
            $form->DISPUTE_ENDED_DATE->setAttrib('disabled', true);
            $form->DISPUTE_AMOUNT->setAttrib('disabled', true);
        }

        $referenceId = $this->getParam('id');
        $reference = $this->db->get_row("SELECT * FROM FILES\$REFERENCES WHERE REFERENCE_ID = {$referenceId}");

        $data = array();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $update = $form->getValues();
                $update['FILE_ID'] = $this->fileId;
                $update['START_DATE'] = $this->functions->date_dbformat($update['START_DATE']);
                $update['END_DATE'] = $this->functions->date_dbformat($update['END_DATE']);
                $update['INVOICE_DATE'] = $this->functions->date_dbformat($update['INVOICE_DATE']);
                $update['AMOUNT'] = $this->functions->dbBedrag($update['AMOUNT']);
                $update['COSTS'] = $this->functions->dbBedrag($update['COSTS']);
                $update['INTEREST'] = $this->functions->dbBedrag($update['INTEREST']);
                $update['INTEREST_PERCENT'] = $this->functions->dbBedrag($update['INTEREST_PERCENT']);
                $update['INTEREST_MINIMUM'] = $this->functions->dbBedrag($update['INTEREST_MINIMUM']);
                $update['COST_PERCENT'] = $this->functions->dbBedrag($update['COST_PERCENT']);
                $update['COST_MINIMUM'] = $this->functions->dbBedrag($update['COST_MINIMUM']);
                $update['DISPUTE_DATE'] = $this->functions->date_dbformat($update['DISPUTE_DATE']);
                $update['DISPUTE_DUEDATE'] = $this->functions->date_dbformat($update['DISPUTE_DUEDATE']);
                $update['DISPUTE_ENDED_DATE'] = $this->functions->date_dbformat($update['DISPUTE_ENDED_DATE']);
                $update['DISPUTE_AMOUNT'] = $this->functions->dbBedrag($update['DISPUTE_AMOUNT']);
                $fileReferenceObj->update($update);
                $reference = $this->db->get_row("SELECT * FROM FILES\$REFERENCES WHERE REFERENCE_ID = {$referenceId}");
                $this->view->formSaved = true;
            } else {
                $this->view->formError = true;
                $this->view->errors = $form->getErrors();
            }
        }

        $data = array(
            'REFERENCE_TYPE' => $reference->REFERENCE_TYPE,
            'REFERENCE_ID' => $reference->REFERENCE_ID,
            'REFERENCE' => $reference->INVOICE_DOCCODE."/".$reference->REFERENCE."/".$reference->INVOICE_DOCLINENUM,
            'TRAIN_TYPE' => $reference->TRAIN_TYPE,
            'REFUND_STATEMENT' => $reference->REFUND_STATEMENT,
            'INVOICE_DATE' => $this->functions->dateformat($reference->INVOICE_DATE),
            'START_DATE' => $this->functions->dateformat($reference->START_DATE),
            'INVOICE_DATE' => $this->functions->dateformat($reference->INVOICE_DATE),
            'END_DATE' => $this->functions->dateformat($reference->END_DATE),
            'AMOUNT' => $this->functions->amount($reference->AMOUNT),
            'AUTO_CALCULATE' => $reference->AUTO_CALCULATE,
            'INTEREST' => $this->functions->amount($reference->INTEREST),
            'COSTS' => $this->functions->amount($reference->COSTS),
            'INTEREST_PERCENT' => $this->functions->amount($reference->INTEREST_PERCENT),
            'COST_PERCENT' => $this->functions->amount($reference->COST_PERCENT),
            'INTEREST_MINIMUM' => $this->functions->amount($reference->INTEREST_MINIMUM),
            'COST_MINIMUM' => $this->functions->amount($reference->COST_MINIMUM),
            'STATE_ID' => $reference->STATE_ID,
            'DISPUTE' => $reference->DISPUTE,
            'DISPUTE_DATE' => $this->functions->dateformat($reference->DISPUTE_DATE),
            'DISPUTE_DUEDATE' => $this->functions->dateformat($reference->DISPUTE_DUEDATE),
            'DISPUTE_ENDED_DATE' => $this->functions->dateformat($reference->DISPUTE_ENDED_DATE),
            'DISPUTE_STATUS' => $reference->DISPUTE_STATUS,
            'DISPUTE_ASSIGNEE' => $reference->DISPUTE_ASSIGNEE,
            'DISPUTE_COMMENT' => $reference->DISPUTE_COMMENT,
            'DISPUTE_AMOUNT' => $this->functions->amount($reference->DISPUTE_AMOUNT),
            'CONTRACT_NUMBER' => $reference->CONTRACT_NUMBER,
            'CONTRACT_UNDERWRITER' => $reference->CONTRACT_UNDERWRITER,
            'CONTRACT_UY' => $reference->CONTRACT_UY,
            'CONTRACT_INCEPTIONDATE' => $reference->CONTRACT_INCEPTIONDATE,
            'CONTRACT_LINEOFBUSINESS' => $reference->CONTRACT_LINEOFBUSINESS,
            'CONTRACT_INSURED' => $reference->CONTRACT_INSURED,
            'LEDGER_ACCOUNT' => $reference->LEDGER_ACCOUNT,
            'VALUTA' => $reference->VALUTA,
            'INVOICE_FROMDATE' => $this->functions->dateformat($reference->INVOICE_FROMDATE),
            'INVOICE_TODATE' => $this->functions->dateformat($reference->INVOICE_TODATE),
        );

        $form->populate($data);

        $fileDocumentsObj = new Application_Model_FilesDocuments();

        $this->view->documents = $fileDocumentsObj->getByReferenceId($referenceId);
        if ($reference->DISPUTE == 1) {
            $this->view->showDisputeContent = true;
        }
        $this->view->debtorDisputeComment = $reference->DEBTOR_DISPUTE_COMMENT;
        $this->view->debtorDisputePhone = $reference->DEBTOR_DISPUTE_PHONE;
        $this->view->debtorDisputeEmail = $reference->DEBTOR_DISPUTE_EMAIL;
        $this->view->form = $form;
    }

    private function delete($id)
    {
        $Obj = new Application_Model_FilesReferences();
        $Obj->delete($id);
    }

}

