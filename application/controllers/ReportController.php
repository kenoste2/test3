<?php

require_once 'application/controllers/BaseController.php';

class ReportController extends BaseController
{

    public function historyAction() {

        $this->checkAccessAndRedirect(array('report/history'));

        $this->view->bread = $this->functions->T("menu_reports") . "->" . $this->functions->T("menu_report_history")  ;

        $statisticsForClientModel = new Application_Model_StatisticsForClient();
        $clientModel = new Application_Model_Clients();

        list($clients) = $clientModel->getAllClients();

        if ($this->isClient()) {
            $clientId = $this->auth->online_client_id;
        } else {
            $clientId = $this->getRequest()->getParam('clientId');
        }


        $stats = $statisticsForClientModel->getStats($clientId);
        $statsTotal = $statisticsForClientModel->getStatsTotal($clientId);

        $this->view->stats = $stats;
        $this->view->statsTotal = $statsTotal;
        $this->view->clients = $clients;
        $this->view->clientId = $clientId;
        $this->view->isClient = $this->isClient();
    }

    public function agingAction()
    {
        $this->checkAccessAndRedirect(array('report/aging'));

        $this->view->bread = $this->functions->T("menu_reports") . "->" . $this->functions->T("menu_report_aging");


        $selectedCollectorId = false;
        if (!empty($this->auth->online_collector_id) && !$this->getParam('showall')) {
            $selectedCollectorId  = $this->auth->online_collector_id;
            $this->view->showCollectorSelector = true;
            $this->view->bread .= " - " . $this->auth->online_collector_name . " <a href=" . $this->config->rootLocation. "/report/realtime/showall/1><li class='fa fa-search-minus fa-fw'></li></a>" ;
        }
        $this->view->selectedCollector = $selectedCollectorId;


        $form = new Application_Form_SearchReport();

        $statisticsForClientModel = new Application_Model_StatisticsForClient();
        $clientModel = new Application_Model_Clients();

        list($clients) = $clientModel->getAllClients();

        if ($this->isClient()) {
            $clientId = $this->auth->online_client_id;
        } else {
            $formArray = array
            (
                'CONTRACT_LINEOFBUSINESS' => $this->getRequest()->getParam('CONTRACT_LINEOFBUSINESS'),
                'CONTRACT_UNDERWRITER' => $this->getRequest()->getParam('CONTRACT_UNDERWRITER'),
                'COLLECTOR_ID' => $this->getRequest()->getParam('COLLECTOR_ID'),
                'GROUP_BY' => $this->getRequest()->getParam('GROUP_BY'),
            );

            $this->view->selectionData = $formArray;

            $form->populate($formArray);
            $this->view->form = $form;
        }

        $aging = $statisticsForClientModel->getAging($this->getRequest()->getParam('CONTRACT_UNDERWRITER'),$this->getRequest()->getParam('COLLECTOR_ID'), $this->getRequest()->getParam('CONTRACT_LINEOFBUSINESS'),$this->getRequest()->getParam('GROUP_BY'));

        $getCurrencyRatings = new Application_Model_CommonFunctions();
        $ratings = $getCurrencyRatings->getCurrencyRates();
        $this->view->ratings = $ratings;

        $this->view->aging = $aging;
        $this->view->isClient = $this->isClient();
    }

    public function exportAgingAction() {

        $limitResults = 500;

        $period = $this->getParam('period');
        $url = "/period/".$period;

        if ($this->getParam("XLS")) {
            $xls = $this->getParam("XLS");
            $date = date("Ymd");
            $fileName = $date."_export_aging_".$period.".xls";
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            $this->_helper->layout->disableLayout();
        } else { $xls = false; }
        
        if (!empty($this->getParam('type'))) {
            $type = $this->getParam('type');
            $url.="/type/".$type;
        } else { $type = false; }

        if (!empty($this->getParam('groupby'))) {
            $groupby = $this->getParam('groupby');
            $url.= "/groupby/".$groupby;
        } else { $groupby = false; }

        if (!empty($this->getParam('collector'))) {
            $collector = $this->getParam('collector');
            $url.= "/collector/".$collector;
        } else { $collector = false; }

        if (!empty($this->getParam('underwriter'))) {
            $underwriter = $this->getParam('underwriter');
            $url.="/underwriter/".$underwriter;
        } else { $underwriter = false; }

        if (!empty($this->getParam('lob'))) {
            $lob = $this->getParam('lob');
            $url.="/lob/".$lob;
        } else { $lob = false; }

        $ExportData = new Application_Model_StatisticsForClient();
        $getExportData = $ExportData->getAgingExport($period, $limitResults, $type, $groupby, $collector, $underwriter, $lob, $xls);

        $this->view->exportUrl = "/report/export-aging".$url."/XLS/1";
        $this->view->data = $getExportData;
        $this->view->xls = $xls;
        $this->view->limitResults = $limitResults;
    }

    public function dsoAction() {

        $this->checkAccessAndRedirect(array('report/dso'));

        $this->view->bread = $this->functions->T("menu_reports") . "->" . $this->functions->T("menu_report_dso")  ;
        $dsoObj = new Application_Model_Dso();
        $clientModel = new Application_Model_Clients();

        list($clients) = $clientModel->getAllClients();

        if ($this->isClient()) {
            $clientId = $this->auth->online_client_id;
        } else {
            $clientId = $this->getRequest()->getParam('clientId', $this->getSelectedClientId($clients));
        }

        $dso = $dsoObj->getDsoList($clientId);
        $this->view->dso = $dso;
        $this->view->clients = $clients;
        $this->view->clientId = $clientId;
        $this->view->isClient = $this->isClient();
    }

    public function realtimeAction() {

        $this->checkAccessAndRedirect(array('report/realtime'));

        $this->view->bread = $this->functions->T("menu_reports") . "->" . $this->functions->T("menu_report_realtime")  ;

        $selectedCollectorId = false;
        if (!empty($this->auth->online_collector_id) && !$this->getParam('showall')) {
            $selectedCollectorId  = $this->auth->online_collector_id;
            $this->view->showCollectorSelector = true;
            $this->view->bread .= " - " . $this->auth->online_collector_name . " <a href=" . $this->config->rootLocation. "/report/realtime/showall/1><li class='fa fa-search-minus fa-fw'></li></a>" ;
        }
        $this->view->selectedCollector = $selectedCollectorId;


        $filesAllInfoModel = new Application_Model_FilesAllInfo();


        $realtimeSummary = $filesAllInfoModel->getRealtimeSummary($selectedCollectorId);
        $realtimeSummaryTotal = $filesAllInfoModel->getRealtimeSummaryTotal($selectedCollectorId);

        $this->view->realtimeSummary = $realtimeSummary;
        $this->view->realtimeSummaryTotal = $realtimeSummaryTotal;

    }


    public function doubtfullDebtsAction() {

        $this->checkAccessAndRedirect(array('report/doubtfull-debts'));

        $this->view->exportButton = true;
        $this->view->addButton = "/report/doubtfull-debts-detail";

        $obj = new Application_Model_DoubtfullDebts();
        $results  = $obj->getDueClientFileList();
        $this->view->results = $results;

        $this->export->sql = $obj->getDueClientFileSql();

    }

    public function doubtfullDebtsDetailAction()
    {
        $this->_helper->layout->disableLayout();
        $fileName = "export_doubtfullDebts".rand(0,999999).".xls";

        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');


        $obj = new Application_Model_DoubtfullDebts();

        $sql = $obj->getDueClientInvoicesSql();
        $results = $this->db->get_results($sql);
        $this->view->results = $results;
    }

    public function paymentForecastAction() {

        $this->checkAccessAndRedirect(array('report/payment-forecast'));

        $this->view->bread = $this->functions->T("menu_reports") . "->" . $this->functions->T("forecastHistogram_c")  ;

        $paymentDelayAverageObj = new Application_Model_PaymentDelayAverage();

        $clientModel = new Application_Model_Clients();

        if ($this->isClient()) {
            $clients = array($this->auth->online_client_id);
            $clientId = $rawClientId = $this->auth->online_client_id;
            $this->view->allowChoice = false;
        } else {
            list($clients) = $clientModel->getAllClients();
            $rawClientId = $this->getRequest()->getParam('clientId');
            if(!$rawClientId || $rawClientId == 'all') {
                $clientId = null;
            } else {
                $clientId = $rawClientId;
            }
            $this->view->allowChoice = true;
        }

        $this->view->paymentForecast = $paymentDelayAverageObj->getPaymentForecast($clientId);

        $this->view->clients = $clients;
        $this->view->clientId = $clientId;
    }

    public function clientHistoryAction()
    {
        $this->_helper->layout()->disableLayout();

        $historyNamespace = new Zend_Session_Namespace('history');

        $this->view->historyNamespace = $historyNamespace;
    }

    public function clientHistoryBedragenAction()
    {
        $this->_helper->layout()->disableLayout();

        $historyNamespace = new Zend_Session_Namespace('history');

        $this->view->historyNamespace = $historyNamespace;
    }

    protected function getSelectedClientId(array $clients)
    {
        if ($this->isClient()) {
            return $this->auth->online_user_id;
        }

        $firstClient = reset($clients);
        return $firstClient->CLIENT_ID;
    }

    protected function isClient()
    {
        if ($this->auth->online_rights == 5) {
            return true;
        }

        return false;
    }

}

