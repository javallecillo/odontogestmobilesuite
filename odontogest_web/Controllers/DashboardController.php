<?php
/**
 * DashboardController
 */
class DashboardController {

    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Dashboard';
        $m         = DashboardModel::metricas();
        require_once VIEW_PATH . 'Dashboard/index.php';
    }
}
