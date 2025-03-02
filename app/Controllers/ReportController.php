<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\ReportService;

class ReportController extends Controller{
    public function generateReport(){
        $format = $this->request->getGet('format');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        if (!$startDate || !$endDate || !$format) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Datas de início, fim e formato são obrigatórios.'
            ])->setStatusCode(400);
        }

        $reportService = new ReportService();
        $reportService->generateReport($format, $startDate, $endDate);
    }
}
