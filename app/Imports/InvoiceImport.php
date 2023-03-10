<?php

namespace App\Imports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class InvoiceImport implements ToModel, WithStartRow
{
    protected $id_pickup;

    public function __construct($id_pickup) {
        $this->id_pickup = $id_pickup;
    }

    public function startRow(): int {
        return 2;
    }

    public function model(array $row) {
        return new Invoice([
            'mnf_id'        => $this->id_pickup,
            'tujuan'        => $row[0],
            'barcode'       => $row[1],
            'koli'          => $row[2],
            'berat'         => $row[3],
            'harga'         => $row[4],
            'packing'       => $row[5],
            'total_kiriman' => $row[3] * $row[4] + $row[5],
            'keterangan'    => $row[6],
        ]);
    }
}
