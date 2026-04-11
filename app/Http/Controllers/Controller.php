<?php

/**
 * Controller.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use Barryvdh\DomPDF\PDF;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base application controller that all other controllers extend.
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Register a centered page footer on rendered PDFs without enabling embedded PHP in templates.
     */
    protected function addCenteredPdfPageFooter(PDF $pdf, string $textTemplate, int $fontSize = 9, float $bottomOffset = 30): void
    {
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();

        $canvas->page_script(function (int $pageNumber, int $pageCount, $canvas, $fontMetrics) use ($textTemplate, $fontSize, $bottomOffset): void {
            if ($pageCount <= 1) {
                return;
            }

            $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
            $text = str_replace([':current', ':total'], [(string) $pageNumber, (string) $pageCount], $textTemplate);
            $width = $fontMetrics->getTextWidth($text, $font, $fontSize);
            $x = ($canvas->get_width() - $width) / 2;
            $y = $canvas->get_height() - $bottomOffset;

            $canvas->text($x, $y, $text, $font, $fontSize);
        });
    }
}