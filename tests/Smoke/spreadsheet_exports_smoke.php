<?php

use App\Exports\Procurement\GeographicTemplateExport;
use App\Exports\Procurement\MethodPlannedTemplateExport;
use App\Exports\Procurement\StageTemplateExport;
use App\Exports\Procurement\StatusTemplateExport;
use App\Exports\Procurement\StepApprovalTemplateExport;
use App\Exports\Procurement\StepStageTemplateExport;
use App\Exports\Procurement\VendorDeliverableTemplateExport;
use App\Exports\VendorTemplateExport;
use Illuminate\Contracts\Console\Kernel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$exports = [
    VendorTemplateExport::class,
    GeographicTemplateExport::class,
    MethodPlannedTemplateExport::class,
    StageTemplateExport::class,
    StatusTemplateExport::class,
    StepApprovalTemplateExport::class,
    StepStageTemplateExport::class,
    VendorDeliverableTemplateExport::class,
];

foreach ($exports as $exportClass) {
    $workbook = Excel::raw(new $exportClass, ExcelFormat::XLSX);

    if (! str_starts_with($workbook, 'PK')) {
        throw new RuntimeException("{$exportClass} did not generate a valid XLSX archive.");
    }
}

echo 'Spreadsheet export smoke passed: '.count($exports)." workbooks generated.\n";
