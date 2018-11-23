@extends('backend.user.history-analysis')
@section('history_data')
    
<?php 
    if($data){
        $asset_repayments_paid = $data->asset_repayments_paid;
        $asset_repayments_paidoff = $data->asset_repayments_paidoff;
    }
?>

<h4>Asset repayments paid</h4>
<dl>
    <table class="table no-margin">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Cicilan terbayar</th>
            </tr>
        </thead>
        <tbody>
            @if($asset_repayments_paid)
                @foreach($asset_repayments_paid as $asset_repayment_paid)
                    <tr>
                        <td>{{ $asset_repayment_paid->asset_repayment_nama }}</td>
                        <td>Rp {{ money($asset_repayment_paid->cicilan_terbayar, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="2">data not available</td></tr>
            @endif
        </tbody>
    </table>
</dl>
<h4>Asset repayments paidoff</h4>
<dl>
    <table class="table no-margin">
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Nama</th>
                <th>Cicilan /bulan</th>
                <th>Sisa durasi</th>
            </tr>
        </thead>
        <tbody>
            @if($asset_repayments_paidoff)
                @foreach($asset_repayments_paidoff as $asset_repayment_paidoff)
                    <tr>
                        <td>{{ $asset_repayment_paidoff->taxo_wallet_name }}</td>
                        <td>{{ $asset_repayment_paidoff->nama }}</td>
                        <td>Rp {{ money($asset_repayment_paidoff->nilai_aset, 2) }}</td>
                        <td>{{ $asset_repayment_paidoff->sisa_durasi }} bulan</td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="3">data not available</td></tr>
            @endif
        </tbody>
    </table>
</dl>
@stop
