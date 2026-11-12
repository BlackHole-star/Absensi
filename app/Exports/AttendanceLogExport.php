<?php

namespace App\Exports;

use App\Http\Controllers\Admin\AttendanceLogController;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceLogExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $logs)
    {
    }

    public function collection()
    {
        return $this->logs->map(fn ($log) => AttendanceLogController::exportRow($log));
    }

    public function headings(): array
    {
        return AttendanceLogController::exportHeadings();
    }
}
