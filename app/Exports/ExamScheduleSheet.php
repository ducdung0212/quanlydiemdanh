<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class ExamScheduleSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    protected $examSchedule;
    protected $sheetNumber;

    public function __construct($examSchedule, $sheetNumber)
    {
        $this->examSchedule = $examSchedule;
        $this->sheetNumber = $sheetNumber;
    }

    /**
     * Tên sheet theo định dạng: Ca [số] - [Mã MH]
     */
    public function title(): string
    {
        $subjectCode = $this->examSchedule->subject_code;
        $examDate = Carbon::parse($this->examSchedule->exam_date)->format('d/m');
        
        // Giới hạn độ dài tên sheet (Excel limit 31 ký tự)
        $title = "Ca {$this->sheetNumber} - {$subjectCode} - {$examDate}";
        return substr($title, 0, 31);
    }

    /**
     * Tiêu đề các cột
     */
    public function headings(): array
    {
        return [
            'STT',
            'Mã SV',
            'Họ và tên',
            'Lớp',
            'Trạng thái',
            'Thời gian điểm danh',
            'Ghi chú'
        ];
    }

    /**
     * Dữ liệu sinh viên của ca thi
     */
    public function collection()
    {
        // Load attendance records với student
        $examSchedule = $this->examSchedule->load(['attendanceRecords.student']);
        
        // Lấy tất cả attendance records, không sắp xếp để giữ nguyên thứ tự ban đầu
        $records = $examSchedule->attendanceRecords;
        
        $students = collect([]);
        $stt = 1;
        
        foreach ($records as $record) {
            $student = $record->student;
            
            // Format thời gian điểm danh
            $checkInTime = '';
            if ($record->attendance_time) {
                try {
                    $checkInTime = Carbon::parse($record->attendance_time)->format('H:i:s d/m/Y');
                } catch (\Exception $e) {
                    $checkInTime = $record->attendance_time;
                }
            }
            
            // Debug: Log dữ liệu
            \Log::info('Export record', [
                'stt' => $stt,
                'student_code' => $student ? $student->student_code : 'N/A',
                'status' => $record->status,
                'check_in_time' => $record->attendance_time,
                'formatted_time' => $checkInTime
            ]);
            
            $students->push([
                $stt,  // STT
                $student ? $student->student_code : 'N/A',  // Mã SV
                $student ? $student->full_name : 'N/A',  // Họ và tên
                $student ? ($student->class_code) : '',  // Lớp
                $this->getStatusText($record->rekognition_result),  // Trạng thái
                $checkInTime,  // Thời gian điểm danh
                $record->note ?? ''  // Ghi chú
            ]);
            
            $stt++;
        }

        return $students;
    }

    /**
     * Định dạng trạng thái
     */
    private function getStatusText($status)
    {
        $statuses = [
            'match' => 'Có mặt',
            null => 'Vắng mặt'
        ];
        return $statuses[$status] ?? 'Vắng mặt';
    }

    /**
     * Định dạng style cho sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Thêm thông tin ca thi ở đầu sheet
        $sheet->insertNewRowBefore(1, 5);
        
        // Thông tin ca thi
        $sheet->setCellValue('A1', 'DANH SÁCH ĐIỂM DANH CA THI');
        $sheet->setCellValue('A2', 'Môn học: ' . ($this->examSchedule->subject->name ?? $this->examSchedule->subject_code));
        $sheet->setCellValue('A3', 'Mã môn: ' . $this->examSchedule->subject_code);
        $sheet->setCellValue('E2', 'Ngày thi: ' . Carbon::parse($this->examSchedule->exam_date)->format('d/m/Y'));
        $sheet->setCellValue('E3', 'Giờ thi: ' . $this->examSchedule->exam_time);
        $sheet->setCellValue('A4', 'Phòng thi: ' . $this->examSchedule->room);
        $sheet->setCellValue('E4', 'Thời gian: ' . $this->examSchedule->duration . ' phút');

        // Merge cells cho tiêu đề
        $sheet->mergeCells('A1:G1');
        
        // Style cho tiêu đề chính
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style cho thông tin ca thi
        $sheet->getStyle('A2:G4')->applyFromArray([
            'font' => [
                'size' => 11,
            ],
        ]);

        // Style cho header (hàng 6)
        $sheet->getStyle('A6:G6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Đếm số hàng có dữ liệu
        $lastRow = $sheet->getHighestRow();
        
        // Style cho bảng dữ liệu (từ hàng 7 trở đi)
        if ($lastRow > 6) {
            $sheet->getStyle('A7:G' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Căn giữa cho cột STT và Trạng thái
            $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E7:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return $sheet;
    }

    /**
     * Độ rộng các cột
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // STT
            'B' => 15,  // Mã SV
            'C' => 30,  // Họ và tên
            'D' => 15,  // Lớp
            'E' => 15,  // Trạng thái
            'F' => 20,  // Thời gian
            'G' => 25,  // Ghi chú
        ];
    }
}
