<?php

namespace App\Exports;

use App\Models\ReconcileResult;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReconcileExport implements FromCollection, WithHeadings, WithMapping
{
    protected $token_applicant;

    public function __construct($token_applicant)
    {
        $this->token_applicant = $token_applicant;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {        
        $query = ReconcileResult::with('merchant', 'bank_account')->where('token_applicant', $this->token_applicant)->get();
        return $query;
    }

    public function map($data): array
    {
        return [
            $data->settlement_date,
            $data->batch_fk,
            $data->merchant->reference_code,
            $data->mid,
            $data->merchant->name,
            $data->processor_payment,
            '-',
            $data->total_sales,
            $data->merchant_payment,
            $data->transfer_amount,
            " ".$data->bank_account->account_number,
            $data->bank_account->bank_code,
            $data->bank_account->bank_name,
            $data->bank_account->account_holder,
            $data->merchant->email,
            $data->processor_payment
        ];
    }

    public function headings(): array
    {
        return [
            'Settlement Date',
            'Sequence Batch Number',
            'Merchant Reference Code',
            'MID',
            'Merchant Name',
            'Bank Type',
            'Trx Status',
            'Total Sales',
            'Bank Statement',
            'Transfer Amount',
            'Account Number',
            'Bank Code',
            'Bank Name',
            'Account Holder',
            'Email',
            'Bank Type',
            'Others',
            // 'Id',
            // 'Transaction Id',
        ];
    }
}
