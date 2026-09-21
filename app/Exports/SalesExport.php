<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SalesExport implements FromView
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }


    public function view(): View
    {


        return view('exports.sale_order',[
            "orders" => $this->orders
        ]);
    }
}
