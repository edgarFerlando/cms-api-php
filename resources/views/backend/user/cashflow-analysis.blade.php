@extends('backend.user.history-analysis')
@section('history_data')
    
<?php
    if($data){
        $incomes = $data->incomes;
        $expenses = $data->expenses;
        $debt_repayments = $data->debt_repayments;
        $asset_repayments = $data->asset_repayments;
        $insurances = $data->insurances;
        $plan_balances = $data->plan_balances;
    }
?>

@if(count($incomes))
<h4>Incomes</h4>
<dl>
    @if($incomes)
    <table class="table no-margin">
    <thead>
                <tr>
                    <th>Pendapatan bulanan</th>
                    <th>Pendapatan lain</th>
                </tr>
            </thead>
            <tbody>
        @foreach($incomes as $idx => $income)
            
                <tr>
                    <td>Rp {{ money($income->pendapatan_bulanan, 2) }}</td>
                    <td>Rp {{ money($income->pendapatan_lain, 2) }}</td>
                </tr>
            
        @endforeach
    @else
        <tr><td colspan="2">data not available</td></tr>
    @endif
    </tbody>
        </table>
</dl>

<h4>Expenses</h4>
<dl>
    <table class="table no-margin">
        <thead>
            <tr>
                <th>Name</th>
                <th>Budget</th>
            </tr>
        </thead>
        <tbody>
            @if($expenses)
                @foreach($expenses as $expense)
                    <tr>
                        <td>{{ $expense->taxo_wallet_name }}</td>
                        <td>Rp {{ money($expense->anggaran_perbulan, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="2">data not available</td></tr>
            @endif
        </tbody>
    </table>
</dl>

<h4>Debt repayments</h4>
<dl>
    <table class="table no-margin">
        <thead>
            <tr>
                <th>Name</th>
                <th>Cicilan /bulan</th>
                <th>Sisa durasi</th>
            </tr>
        </thead>
        <tbody>
            @if($debt_repayments)
                @foreach($debt_repayments as $debt_repayment)
                    <tr>
                        <td>{{ $debt_repayment->taxo_wallet_name }}</td>
                        <td>Rp {{ money($debt_repayment->cicilan_perbulan, 2) }}</td>
                        <td>{{ $debt_repayment->sisa_durasi }} bulan</td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="3">data not available</td></tr>
            @endif
        </tbody>
    </table>
</dl>

<h4>Asset repayments</h4>
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
            @if($asset_repayments)
                @foreach($asset_repayments as $asset_repayment)
                    <tr>
                        <td>{{ $asset_repayment->taxo_wallet_name }}</td>
                        <td>{{ $asset_repayment->nama }}</td>
                        <td>Rp {{ money($asset_repayment->cicilan_perbulan, 2) }}</td>
                        <td>{{ $asset_repayment->sisa_durasi }} bulan</td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="3">data not available</td></tr>
            @endif
        </tbody>
    </table>
</dl>

<h4>Insurances</h4>
<dl>
    <table class="table no-margin">
        <thead>
            <tr>
                <th>Agen Asuransi</th>
                <th>Jenis</th>
                <th>No. Polis</th>
                <th>Premi /bulan</th>
                <th>Nilai Pertanggungan</th>
            </tr>
        </thead>
        <tbody>
            @if($insurances)
                @foreach($insurances as $insurance)
                    <tr>
                        <td>{{ $insurance->taxo_wallet_name }}</td>
                        <td>{{  $insurance->taxo_insurance_type_name }}</td>
                        <td>{{  $insurance->no_polis }}</td>
                        <td>Rp {{ money($insurance->premi_perbulan, 2) }}</td>
                        <td>Rp {{ money($insurance->nilai_pertanggungan, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="5">data not available</td></tr>
            @endif
        </tbody>
    </table>
</dl>

<h4>Plan balances</h4>
<dl>
    @if($plan_balances)
    <table class="table no-margin">
    <thead>
                <tr>
                    <th>Name</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
        @foreach($plan_balances as $idx => $plan_balance)
            
                <tr>
                    <td>{{ trans('app.'.$plan_balance->name) }}</td>
                    <td>Rp {{ money($plan_balance->balance, 2) }}</td>
                </tr>
            
        @endforeach
    @else
        <tr><td colspan="2">data not available</td></tr>
    @endif
    </tbody>
        </table>
</dl>
@else
    Data not available
@endif
@stop
