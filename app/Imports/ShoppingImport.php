<?php

namespace App\Imports;

use App\Shopping;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Carbon\Carbon;

class ShoppingImport implements ToModel, WithValidation, WithCustomCsvSettings
{
    use Importable;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Shopping([
            'date' => Carbon::createFromFormat('d/m/Y', $row[0])->toDateString(),
            'expiry_date' => $row[1],
            'cost_code' => $row[2],
            'voucher' => $row[3],
            'provider_ruc' => $row[4],
            'provider_name' => $row[5],
            'base_with_fiscal_credit_right' => str_replace(',', '', $row[6]),
            'base_without_fiscal_credit_right' => str_replace(',', '', $row[7]),
            'base_operation_mixed' => str_replace(',', '', $row[8]),
            'non_task_adquisitions' => str_replace(',', '', $row[9]),
            'igv_with_fiscal_credit_right' => str_replace(',', '', $row[10]),
            'igv_without_fiscal_credit_right' => str_replace(',', '', $row[11]),
            'igv_task_mixed' => str_replace(',', '', $row[12]),
            'other_charges' => str_replace(',', '', $row[13]),
            'total' => str_replace(',', '', $row[14]),
            'glosa' => $row[15],
            'doc_ref' => $row[16],
            'doc_ref_date' => $row[17],
            'exchange_rate' => $row[18],
            'comp_con' => $row[19],
            'base_ref' => str_replace(',', '', $row[20]),
            'number' => isset($row[21]) ? $row[21] : '',
        ]);
    }

    public function rules(): array
    {
        return [
            '0' => ['date_format:d/m/Y'],
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ","
        ];
    }
}
