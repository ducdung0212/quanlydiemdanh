<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiExamSchedulesExport implements WithMultipleSheets
{
    protected $examSchedules;

    public function __construct($examSchedules)
    {
        $this->examSchedules = $examSchedules;
    }

    /**
     * Mỗi ca thi sẽ là một sheet riêng
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->examSchedules as $index => $examSchedule) {
            $sheets[] = new ExamScheduleSheet($examSchedule, $index + 1);
        }

        return $sheets;
    }
}
