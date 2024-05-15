<?php

namespace App\Exports;

use App\Models\ReconcileResult;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReconcileExport implements FromCollection, WithHeadings, WithMapping
{
    protected $token_applicant, $status, $startDate, $endDate, $channel;

    public function __construct($token_applicant, $status, $startDate, $endDate, $channel)
    {
        $this->token_applicant = $token_applicant;
        $this->status = $status;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->channel = $channel;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = ReconcileResult::with('merchant', 'bank_account');
        if ($this->token_applicant) {
            $query->where('token_applicant', $this->token_applicant);
        }
        if ($this->status != 'all') {
            if ($this->status) {
                if ($this->status == "match") {
                    $query->where('status', 'MATCH');
                } elseif ($this->status == "dispute") {
                    $query->whereIn('status', ['NOT_MATCH', 'NOT_FOUND']);
                }
            }
        }

        $query->where(DB::raw('DATE(settlement_date)'), '>=', $this->startDate);
        $query->where(DB::raw('DATE(settlement_date)'), '<=', $this->endDate);
        $query->where('processor_payment', $this->channel);

        return $query->get();
    }

    public function map($data): array
    {
        if ($data->status == 'MATCH') {
            $stt = 'MATCH';
        } elseif($data->status == 'NOT_MATCH' || $data->status == 'NOT_FOUND'){
            $stt = 'DISPUTE';
        } else{
            $stt = 'ONHOLD';
        }
        return [
            $data->settlement_date,
            $data->batch_fk,
            $data->merchant->reference_code,
            $data->mid,
            $data->merchant->name,
            $data->processor_payment,
            $stt,
            $data->internal_payment,
            $data->bank_settlement_amount,
            $data->dispute_amount,
            $data->total_sales,
            $data->transfer_amount,
            " " . $data->bank_account->account_number,
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
            // 'Trx Status',
            'Reconcile Status',
            'BO Settlement Amount',
            'BANK Settlement Amount',
            'Dispute Amount',
            'Total Sales',
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
