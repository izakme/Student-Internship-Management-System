<?php

require_once __DIR__ . "/../helpers/Language.php";

class Pdf
{
    public static function generate($title, $headers, $rows, $filename = 'report.pdf')
    {
        $pdf = new self();
        $pdf->render($title, $headers, $rows, $filename);
    }

    private function render($title, $headers, $rows, $filename)
    {
        $data = $this->build($title, $headers, $rows);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . strlen($data));
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $data;
    }

    private function build($title, $headers, $rows)
    {
        $w = 595.28;
        $h = 841.89;
        $ml = 50;
        $mr = 50;
        $aw = $w - $ml - $mr;

        $colCount = count($headers);
        $colW = $colCount > 0 ? $aw / $colCount : $aw;

        $body = '';

        // --- System Title Banner ---
        $body .= "0.05 0.32 0.71 rg\n";
        $body .= sprintf("%.2f %.2f %.2f %.2f re f\n", 0, $h - 60, $w, 60);

        $body .= "BT\n/F1 18 Tf\n1 1 1 rg\n";
        $body .= sprintf("%.2f %.2f Td\n", $ml, $h - 42);
        $body .= '(' . $this->escape(__('Student Internship Management System')) . ") Tj\n";
        $body .= "ET\n";

        $body .= "BT\n/F1 9 Tf\n1 1 1 rg\n";
        $body .= sprintf("%.2f %.2f Td\n", $ml, $h - 56);
        $body .= '(' . $this->escape(date('F j, Y')) . ") Tj\n";
        $body .= "ET\n";

        // --- Report Title ---
        $body .= "BT\n/F1 14 Tf\n0.05 0.32 0.71 rg\n";
        $body .= sprintf("%.2f %.2f Td\n", $ml, $h - 90);
        $body .= '(' . $this->escape($title) . ") Tj\n";
        $body .= "ET\n";

        // --- Divider ---
        $yDiv = $h - 100;
        $body .= "0.05 0.32 0.71 RG\n1 w\n";
        $body .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $ml, $yDiv, $ml + $aw, $yDiv);

        // --- Table ---
        if ($colCount > 0) {
            $ty = $h - 120;
            $th = 18;

            // Header background
            $body .= "0.05 0.32 0.71 rg\n";
            $body .= sprintf("%.2f %.2f %.2f %.2f re f\n", $ml, $ty - $th, $aw, $th);

            // Header grid - bottom line
            $body .= "0.04 0.25 0.56 RG\n0.5 w\n";
            $body .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $ml, $ty - $th, $ml + $aw, $ty - $th);

            // Header cells
            $hx = $ml;
            foreach ($headers as $hdr) {
                // Vertical separator
                if ($hx > $ml) {
                    $body .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $hx, $ty, $hx, $ty - $th);
                }
                $body .= "BT\n/F1 9 Tf\n1 1 1 rg\n";
                $body .= sprintf("%.2f %.2f Td\n", $hx + 4, $ty - 13);
                $body .= '(' . $this->escape($this->fitText($hdr, $colW, 9)) . ") Tj\n";
                $body .= "ET\n";
                $hx += $colW;
            }

            // Rows
            $rowH = 16;
            $y = $ty - $th;

            foreach ($rows as $i => $row) {
                // Alternating row background
                if ($i % 2 === 0) {
                    $body .= "0.95 0.95 0.97 rg\n";
                    $body .= sprintf("%.2f %.2f %.2f %.2f re f\n", $ml, $y - $rowH, $aw, $rowH);
                }

                // Row bottom border
                $body .= "0.85 0.85 0.85 RG\n0.3 w\n";
                $body .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $ml, $y - $rowH, $ml + $aw, $y - $rowH);

                // Cells
                $cx = $ml;
                $colIdx = 0;
                foreach ($headers as $hdr) {
                    $cellVal = isset($row[$hdr]) ? (string)$row[$hdr] : '';
                    // Vertical separator
                    if ($cx > $ml) {
                        $body .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $cx, $y, $cx, $y - $rowH);
                    }
                    $body .= "BT\n/F1 8 Tf\n0.2 0.2 0.2 rg\n";
                    $body .= sprintf("%.2f %.2f Td\n", $cx + 4, $y - 11);
                    $body .= '(' . $this->escape($this->fitText($cellVal, $colW, 8)) . ") Tj\n";
                    $body .= "ET\n";
                    $cx += $colW;
                    $colIdx++;
                }
                $y -= $rowH;
            }

            // Table outer border
            $body .= "0.04 0.25 0.56 RG\n0.8 w\n";
            $body .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $ml, $ty, $ml + $aw, $ty);
            $body .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $ml, $ty - $th, $ml + $aw, $ty - $th);
            // Left and right borders
            $body .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $ml, $ty, $ml, $y + $rowH);
            $body .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $ml + $aw, $ty, $ml + $aw, $y + $rowH);
        }

        // --- Footer ---
        $fy = 50;
        $body .= "0.7 0.7 0.7 RG\n0.5 w\n";
        $body .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $ml, $fy, $ml + $aw, $fy);

        $body .= "BT\n/F1 8 Tf\n0.5 0.5 0.5 rg\n";
        $body .= sprintf("%.2f %.2f Td\n", $ml, $fy - 14);
        $body .= '(' . $this->escape(__('Student Internship Management System') . ' | ' . __('Generated:') . ' ' . date('M d, Y H:i')) . ") Tj\n";
        $body .= "ET\n";

        $body .= "BT\n/F1 8 Tf\n0.5 0.5 0.5 rg\n";
        $body .= sprintf("%.2f %.2f Td\n", $ml + $aw - 40, $fy - 14);
        $body .= '(' . $this->escape(__('Page 1 of 1')) . ") Tj\n";
        $body .= "ET\n";

        // --- Build PDF structure ---
        $out = '%PDF-1.4' . "\n";
        $out .= "%\xE2\xE3\xCF\xD3\n";

        $offsets = [];

        $offsets[1] = strlen($out);
        $out .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        $offsets[2] = strlen($out);
        $out .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

        $offsets[3] = strlen($out);
        $out .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$w} {$h}] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> /ProcSet [/PDF /Text] >> >>\nendobj\n";

        $offsets[4] = strlen($out);
        $streamLen = strlen($body);
        $out .= "4 0 obj\n<< /Length {$streamLen} >>\nstream\n{$body}\nendstream\nendobj\n";

        $offsets[5] = strlen($out);
        $out .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";

        $offsets[0] = 0;
        $xrefOffset = strlen($out);
        $out .= "xref\n0 6\n";
        foreach ([0, 1, 2, 3, 4, 5] as $n) {
            $out .= sprintf("%010d %05d %s \n", $offsets[$n], ($n === 0 ? 65535 : 0), ($n === 0 ? 'f' : 'n'));
        }

        $out .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
        $out .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $out;
    }

    private function escape($str)
    {
        $str = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $str);
        $str = preg_replace('/[^\x20-\x7E\x0A\x0D]/', '?', $str);
        return $str;
    }

    private function fitText($text, $colWidth, $fontSize = 8)
    {
        $padding = 8;
        $availWidth = $colWidth - $padding;
        $charWidth = $fontSize * 0.56;
        $maxChars = floor($availWidth / $charWidth);

        if (strlen($text) <= $maxChars) {
            return $text;
        }

        return substr($text, 0, max(0, $maxChars - 3)) . '...';
    }
}
