<?php
require_once '../../helpers/jwt.php';
require_once '../../vendor/tcpdf/tcpdf.php';

$reportType = $_GET['report_type'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$token = $_GET['token'] ?? '';
$departmentName = $_GET['department'] ?? null;

$user = validate_jwt($token);
if (!$user) { die('Invalid Token'); }

require_once '../../src/config/Database.php';
$db = new Database();
$conn = $db->connect();

$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Church Fund Manager');
$pdf->SetAuthor('Church Fund Manager');
$pdf->SetTitle(ucfirst($reportType) . ' Report');
$pdf->SetMargins(10, 15, 10);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

// Header
$pdf->Cell(0, 10, ucfirst($reportType) . ' Report', 0, 1, 'C');
$pdf->Ln(3);

$headerBg = [220, 220, 220];
$totalBg = [200, 255, 200];

if ($reportType === 'contribution') {
    $sql = "SELECT date, tithe, hope_channel AS hope, clc_building AS clc, one_offering_clc AS clc_offering,
                   one_offering_church AS church_offering, cb, cf, pfm, local_church_others AS local_church 
            FROM contributions 
            WHERE date BETWEEN ? AND ? ORDER BY date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$from, $to]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $headers = ['Date', 'Tithe', 'Hope', 'CLC', '1O CLC', '1O Church', 'CB', 'CF', 'PFM', 'Local'];
    $widths = [25, 25, 25, 25, 25, 25, 25, 25, 25, 25];
    $startX = ($pdf->getPageWidth() - array_sum($widths)) / 2;

    $pdf->SetX($startX);
    foreach ($headers as $i => $col) {
        $pdf->SetFillColorArray($headerBg);
        $pdf->Cell($widths[$i], 8, $col, 1, 0, 'C', 1);
    }
    $pdf->Ln();

    $totals = ['tithe'=>0, 'hope'=>0, 'clc'=>0, 'clc_offering'=>0, 'church_offering'=>0, 'cb'=>0, 'cf'=>0, 'pfm'=>0, 'local_church'=>0];

    foreach ($data as $row) {
        $pdf->SetX($startX);
        $pdf->Cell($widths[0], 8, $row['date'], 1, 0, 'C');
        $pdf->Cell($widths[1], 8, number_format($row['tithe'], 2), 1, 0, 'C');
        $pdf->Cell($widths[2], 8, number_format($row['hope'], 2), 1, 0, 'C');
        $pdf->Cell($widths[3], 8, number_format($row['clc'], 2), 1, 0, 'C');
        $pdf->Cell($widths[4], 8, number_format($row['clc_offering'], 2), 1, 0, 'C');
        $pdf->Cell($widths[5], 8, number_format($row['church_offering'], 2), 1, 0, 'C');
        $pdf->Cell($widths[6], 8, number_format($row['cb'], 2), 1, 0, 'C');
        $pdf->Cell($widths[7], 8, number_format($row['cf'], 2), 1, 0, 'C');
        $pdf->Cell($widths[8], 8, number_format($row['pfm'], 2), 1, 0, 'C');
        $pdf->Cell($widths[9], 8, number_format($row['local_church'], 2), 1, 0, 'C');
        $pdf->Ln();

        foreach ($totals as $key => $val) {
            $totals[$key] += $row[$key];
        }
    }

    $pdf->SetX($startX);
    $pdf->SetFillColorArray($totalBg);
    $pdf->Cell($widths[0], 8, 'Total', 1, 0, 'C', 1);
    foreach (array_keys($totals) as $i => $key) {
        $pdf->Cell($widths[$i + 1], 8, number_format($totals[$key], 2), 1, 0, 'C', 1);
    }
    $pdf->Ln();

    $grandTotal = array_sum($totals);
    $pdf->SetX($startX);
    $pdf->Cell(array_sum(array_slice($widths, 0, 9)), 8, 'Grand Total:', 1, 0, 'C', 1);
    $pdf->Cell($widths[9], 8, number_format($grandTotal, 2), 1, 0, 'C', 1);

} elseif ($reportType === 'expenses') {
    $sql = "SELECT e.expense_date, m.full_name, e.purpose, e.amount, e.department_name
            FROM department_expenses e
            LEFT JOIN members m ON m.id = e.member_id
            WHERE e.expense_date BETWEEN ? AND ? ORDER BY e.expense_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$from, $to]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $headers = ['Date', 'Member', 'Purpose', 'Amount', 'Department'];
    $widths = [35, 50, 60, 30, 40];
    $startX = ($pdf->getPageWidth() - array_sum($widths)) / 2;

    $pdf->SetX($startX);
    foreach ($headers as $i => $col) {
        $pdf->SetFillColorArray($headerBg);
        $pdf->Cell($widths[$i], 8, $col, 1, 0, 'C', 1);
    }
    $pdf->Ln();

    $totalExpense = 0;

    foreach ($data as $row) {
        $pdf->SetX($startX);
        $purposeHeight = $pdf->getStringHeight($widths[2], $row['purpose']);
        $rowHeight = max(8, $purposeHeight);

        $pdf->Cell($widths[0], $rowHeight, $row['expense_date'], 1, 0, 'C');
        $pdf->Cell($widths[1], $rowHeight, $row['full_name'], 1, 0, 'C');
        $pdf->MultiCell($widths[2], $rowHeight, $row['purpose'], 1, 'C', false, 0);
        $pdf->Cell($widths[3], $rowHeight, number_format($row['amount'], 2) . ' php', 1, 0, 'C');
        $pdf->Cell($widths[4], $rowHeight, $row['department_name'], 1, 0, 'C');
        $pdf->Ln();

        $totalExpense += $row['amount'];
    }

    $pdf->SetX($startX);
    $pdf->SetFillColorArray($totalBg);
    $pdf->Cell($widths[0], 8, 'Total', 1, 0, 'C', 1);
    $pdf->Cell($widths[1] + $widths[2], 8, '', 1, 0, 'C', 1);
    $pdf->Cell($widths[3], 8, number_format($totalExpense, 2) . ' php', 1, 0, 'C', 1);
    $pdf->Cell($widths[4], 8, '', 1, 0, 'C', 1);

} elseif ($reportType === 'department' && $departmentName) {
    file_put_contents('../../debug.log', "Generating department report for {$departmentName} from {$from} to {$to}\n", FILE_APPEND);

$sql = "SELECT d.name, da.amount, da.percentage, da.year 
        FROM departments d 
        INNER JOIN department_allocations da ON d.id = da.department_id 
        WHERE d.name = ? AND da.year BETWEEN YEAR(?) AND YEAR(?)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$departmentName, $from, $to]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    file_put_contents('../../debug.log', "Fetched " . count($data) . " rows from departments\n", FILE_APPEND);

    $headers = ['Department', 'Amount', 'Percentage', 'Year'];
    $widths = [60, 40, 40, 30];
    $startX = ($pdf->getPageWidth() - array_sum($widths)) / 2;

    $pdf->SetX($startX);
    foreach ($headers as $i => $col) {
        $pdf->SetFillColorArray($headerBg);
        $pdf->Cell($widths[$i], 8, $col, 1, 0, 'C', 1);
    }
    $pdf->Ln();

    foreach ($data as $row) {
$amount = $row['amount'];
$percentage = $row['percentage'];
$year = $row['year'];


        $pdf->SetX($startX);
        $pdf->Cell($widths[0], 8, $departmentName, 1, 0, 'C');
        $pdf->Cell($widths[1], 8, number_format($amount, 2), 1, 0, 'C');
        $pdf->Cell($widths[2], 8, number_format($percentage, 2) . '%', 1, 0, 'C');
        $pdf->Cell($widths[3], 8, $year, 1, 0, 'C');
        $pdf->Ln();
    }
}


$pdf->Output(ucfirst($reportType) . '_Report.pdf', 'I');
