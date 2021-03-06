<?php
/*
 * dienstplan
 *
 * Copyright (c) 2019 Christoph Fischer, https://christoph-fischer.org
 * Author: Christoph Fischer, chris@toph.de
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Reports;

use App\City;
use App\Day;
use App\Funeral;
use App\Liturgy;
use App\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpWord\Shared\Converter;

class FuneralsRelativesReport extends AbstractExcelDocumentReport
{
    public $title = 'Liste der Angehörigen';
    public $description = 'Adressliste von Angehörigen für die Beerdigungen eines Jahres';
    public $group = 'Kasualien';


    public function setup()
    {
        $minDate = Day::orderBy('date', 'ASC')->limit(1)->get()->first();
        $maxDate = Day::orderBy('date', 'DESC')->limit(1)->get()->first();
        $cities = Auth::user()->cities;
        return $this->renderSetupView(['minDate' => $minDate, 'maxDate' => $maxDate, 'cities' => $cities]);
    }

    public function render(Request $request)
    {
        $request->validate([
            'city' => 'required|integer',
            'start' => 'required|date|date_format:d.m.Y',
        ]);

        $start = Carbon::createFromFormat('d.m.Y H:i:s', $request->get('start') . ' 0:00:00');
        $city = City::find($request->get('city'));

        $funerals = Funeral::with('service')
            ->whereHas('service', function ($query) use ($city, $start) {
                $query->where('city_id', $city->id);
                $query->whereHas('day', function ($query2) use ($start) {
                    $query2->where('date', '>=', $start);
                });
            })
            ->get();

        $this->spreadsheet->getDefaultStyle()
            ->getFont()
            ->setName('Arial')
            ->setSize(8);
        $this->spreadsheet->setActiveSheetIndex(0);
        $sheet = $this->spreadsheet->getActiveSheet();

        // column width
        for ($i=65; $i<=76; $i++) {
            $sheet->getColumnDimension(chr($i))->setWidth(20);
        }
        // title row

        $headers = [
            'Datum',
            "Verstorben_Name",
            'Verstorben_Adresse',
            "Verstorben_PLZ",
            'Verstorben_Ort',
            'Predigttext',
            'Pfarrer',
            'Friedhof',
            'Hinterblieben_Name',
            'Hinterblieben_Adresse',
            "Hinterblieben_PLZ",
            'Hinterblieben_Ort',
        ];

        foreach ($headers as $index => $header) {
            $column = chr(65 + $index);
            $sheet->setCellValue("{$column}1", $header);
            $sheet->getStyle("{$column}1")->getFont()->setBold(true);
        }

        // content rows

        $row = 1;
        foreach ($funerals as $funeral) {
            $row++;
            $sheet->setCellValue("A{$row}", $funeral->service->day->date->format('d.m.Y'));
            $sheet->setCellValue("B{$row}", $funeral->buried_name);
            $sheet->setCellValue("C{$row}", $funeral->buried_address);
            $sheet->setCellValue("D{$row}", $funeral->buried_zip);
            $sheet->setCellValue("E{$row}", $funeral->buried_city);
            $sheet->setCellValue("F{$row}", $funeral->text);
            $sheet->setCellValue("G{$row}", $funeral->service->participantsText('P'));
            $sheet->setCellValue("H{$row}", $funeral->service->locationText());
            $sheet->setCellValue("I{$row}", $funeral->relative_name);
            $sheet->setCellValue("J{$row}", $funeral->relative_address);
            $sheet->setCellValue("K{$row}", $funeral->relative_zip);
            $sheet->setCellValue("L{$row}", $funeral->relative_city);
        }

        // output
        $filename = 'Beerdigungen ab '.$start->format('Y-m-d') . ', ' . $city->name;
        $this->sendToBrowser($filename);
    }


}
