<?php


namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ReposExport implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents
{
    protected $repos;

    public function __construct(array $repos)
    {
        $this->repos = $repos;
    }

    public function collection()
    {
        return collect($this->repos)->map(function ($repo) {
            return [
                'Name' => $repo['name'],
                'Stars' => $repo['stars'],
                'Language' => $repo['language'],
                'Created At' => $repo['created_at'],
                'URL' => $repo['url'],
            ];
        });
    }

    public function headings(): array
    {
        return ['Name', 'Stars', 'Language', 'Created At', 'URL'];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:E1')
                    ->getFont()
                    ->setBold(true);
            },
        ];
    }
}
