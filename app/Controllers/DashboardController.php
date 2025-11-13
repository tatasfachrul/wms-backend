<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ProductModel;
use App\Models\TransactionModel;
use CodeIgniter\I18n\Time;

class DashboardController extends ResourceController
{
    protected $format = 'json';

    public function summary()
    {
        $productModel = new ProductModel();
        $transactionModel = new TransactionModel();

        $totalProducts = $productModel->countAllResults();
        $lowStockItems = $productModel->where('stock < minimum_stock')->findAll();
        $totalTransactions = $transactionModel->countAllResults();

        return $this->respond([
            'success' => true,
            'data' => [
                'totalProducts' => $totalProducts,
                'lowStockItems' => $lowStockItems,
                'totalTransactions' => $totalTransactions,
            ]
        ]);
    }

    public function transactionsChart()
    {
        $type = $this->request->getGet('type') ?? 'daily';
        $transactionModel = new TransactionModel();
        $now = Time::now();
        $endOfMonth = Time::parse($now->format('Y-m-t 23:59:59'));

        $labels = [];
        $inboundStock = [];
        $outboundStock = [];

        if ($type === 'daily') {
            $startOfWeek = Time::now()->modify('monday this week')->setTime(0, 0, 0);
            $endOfWeek = Time::now()->modify('sunday this week')->setTime(23, 59, 59);

            $period = new \DatePeriod($startOfWeek, new \DateInterval('P1D'), $endOfWeek->add(new \DateInterval('P1D')));
            foreach ($period as $date) {
                $label = $date->format('D'); // Mon, Tue, ...
                $labels[] = $label;

                // Get inbound & outbound for each day
                $inbound = $transactionModel
                    ->where('type', 'IN')
                    ->where('DATE(created_at)', $date->format('Y-m-d'))
                    ->selectSum('quantity')
                    ->get()
                    ->getRow()
                    ->quantity ?? 0;

                $outbound = $transactionModel
                    ->where('type', 'OUT')
                    ->where('DATE(created_at)', $date->format('Y-m-d'))
                    ->selectSum('quantity')
                    ->get()
                    ->getRow()
                    ->quantity ?? 0;

                $inboundStock[] = (int) $inbound;
                $outboundStock[] = (int) $outbound;
            }
        } elseif ($type === 'weekly') {
            // Weekly for current month
            $year = $now->getYear();
            $month = $now->getMonth();
            $weeks = [];

            // Split current month into weeks
            $start = new Time("$year-$month-01");
            $end = $endOfMonth;
            $current = clone $start;

            while ($current < $end) {
                $weekStart = clone $current;
                $weekEnd = $weekStart->add(new \DateInterval('P6D'));
                if ($weekEnd > $end) { 
                    $weekEnd = clone $end;
                }

                $weeks[] = [$weekStart, $weekEnd];
                $current = $weekEnd->add(new \DateInterval('P1D'));
            }

            foreach ($weeks as $index => [$weekStart, $weekEnd]) {
                $labels[] = "Week " . ($index + 1);

                $inbound = $transactionModel
                    ->where('type', 'IN')
                    ->where('created_at >=', $weekStart->toDateTimeString())
                    ->where('created_at <=', $weekEnd->toDateTimeString())
                    ->selectSum('quantity')
                    ->get()
                    ->getRow()
                    ->quantity ?? 0;

                $outbound = $transactionModel
                    ->where('type', 'OUT')
                    ->where('created_at >=', $weekStart->toDateTimeString())
                    ->where('created_at <=', $weekEnd->toDateTimeString())
                    ->selectSum('quantity')
                    ->get()
                    ->getRow()
                    ->quantity ?? 0;

                $inboundStock[] = (int) $inbound;
                $outboundStock[] = (int) $outbound;
            }
        } elseif ($type === 'monthly') {
            // Monthly for current year
            $year = $now->getYear();
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = date('M', mktime(0, 0, 0, $m, 1));

                $start = new Time("$year-$m-01");
                $end = Time::parse("$year-$m-" . (new \DateTime("$year-$m-01"))->format('t') . " 23:59:59");

                $inbound = $transactionModel
                    ->where('type', 'IN')
                    ->where('created_at >=', $start->format('Y-m-d H:i:s'))
                    ->where('created_at <=', $end->format('Y-m-d H:i:s'))
                    ->selectSum('quantity')
                    ->get()
                    ->getRow()
                    ->quantity ?? 0;

                $outbound = $transactionModel
                    ->where('type', 'OUT')
                    ->where('created_at >=', $start->format('Y-m-d H:i:s'))
                    ->where('created_at <=', $end->format('Y-m-d H:i:s'))
                    ->selectSum('quantity')
                    ->get()
                    ->getRow()
                    ->quantity ?? 0;

                $inboundStock[] = (int) $inbound;
                $outboundStock[] = (int) $outbound;
            }
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'inboundStock' => $inboundStock,
                'outboundStock' => $outboundStock,
            ]
        ]);
    }
}