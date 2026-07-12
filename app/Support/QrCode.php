<?php

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;

class QrCode
{
    /**
     * Render the data as an inline SVG. Server side only — there is no JS QR library.
     */
    public static function svg(string $data, int $size = 200): string
    {
        $builder = new Builder(
            writer: new SvgWriter,
            writerOptions: [
                SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
                SvgWriter::WRITER_OPTION_EXCLUDE_SVG_WIDTH_AND_HEIGHT => true,
            ],
            data: $data,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: $size,
            margin: 0,
            foregroundColor: new Color(23, 59, 69),   // ink
            backgroundColor: new Color(255, 255, 255),
        );

        return $builder->build()->getString();
    }
}
