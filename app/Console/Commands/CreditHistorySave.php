<?php

namespace App\Console\Commands;

use App\Company;
use App\CreditHistory;
use App\Sale;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreditHistorySave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credit_history:save';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info("Informacion guardada con exito");
        $company_ids = Company::pluck('id')->toArray();
        $initial_date = CarbonImmutable::now()->startOfYear()->format('Y-m-d');
        $final_date = CarbonImmutable::now()->format('Y-m-d');
        $date_type_id = 1;

        $elements = Sale::when($company_ids, function ($query, $company_ids) {
            return $query->whereIn('company_id', $company_ids);
        })
            ->when($date_type_id == 1, function ($query) use ($initial_date, $final_date) {
                return $query->where('sale_date', '>=', $initial_date)
                    ->where('sale_date', '<=', $final_date);
            })
            ->where('balance', '!=', 0)
            ->whereNotIn('client_id', [1031, 427, 13326, 14072, 13783, 14269, 14274, 14294, 14328, 14329, 14258])
            ->whereNotIn('warehouse_document_type_id', [1, 2, 3, 6, 9, 10, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32])
            ->select(
                'id',
                'total_perception',
                'balance',
                'paid'
            )
            ->get();

        foreach ($elements as $item) {
            $obj = new CreditHistory();
            $obj->sale_id = $item->id;
            $obj->total_perception = $item->total_perception;
            $obj->balance = $item->balance;
            $obj->paid = $item->paid;
            $obj->save();
        }
    }
}
