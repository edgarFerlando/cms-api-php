@extends('backend.user.history-analysis')
@section('history_data')
    
<?php 
    if($data){ 
        $a_plans = isset($data['a_plans'])?$data['a_plans']:[];
        $b_plans = isset($data['b_plans'])?$data['b_plans']:[];
        $plan_balances = $data['plan_balances'];
        $incomes = $data_cashflow->incomes;
        $plan_analysis = $data['plan_analysis'];

        $plans = [];
        $random_idx = 999;
        $active_plans = [];
        $inactive_plans = [];
        $active_plans_total = 0;
        $inactive_plans_total = 0;
        foreach($a_plans as $a_plan){
            $plan = $a_plan->plan_a[0];
            $plan_number = $plan->plan_number;
            $plan_idx = $plan_number;
            if(isset($plans[$plan_number])){
                $plan_idx = $random_idx;
                $random_idx++;
            }
            $income_simulation = $plan->income_simulation;
            $income_simulation_rate = $income_simulation->bunga_investasi_pertahun;
            $income_simulation_produk = $income_simulation->produk;
            $income_simulation_total_inv = $income_simulation->total_investasi;

            $plan_protections = $a_plan->insurance_coverages;

            $plan_details = [
                'plan_type' => 'a',
                'plan_type_alias' => 'Safe Retirement',
                'plan_name' => 'Plan '.$plan->plan_number,
                'plan_perbulan' => $plan->plan_perbulan
            ];
            
            if($plan->status == 1){
                $active_plans_total += $plan->plan_perbulan;
                $active_plans[$plan_idx]['plans'] = $plan_details;
            }else{
                $inactive_plans_total += $plan->plan_perbulan;
                $active_plans[$plan_idx]['plans'] = $plan_details;
            }

            $plans[$plan_idx] = '<h4>Plan '.$plan_number.' &nbsp;<small>( Safe Retirement )</small></h4>
            <dl>
                <dt>Investasi yang anda pilih :</dt>
                <dd>
                    <table class="table no-margin">
                        <thead>
                            <tr>
                                <th>Investasi</th>
                                <th>Rate</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>'.$income_simulation_produk.'</td>
                                <td>'.$income_simulation_rate.' %</td>
                                <td>Rp '.money($income_simulation_total_inv, 2).'</td>
                            </tr>
                        </tbody>
                    </table>
                </dd>
                <dt>Nilai pertanggungan asuransi Anda :</dt>
                <dd>
                    <table class="table no-margin">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Premi /bulan</th>
                                <th>Nilai pertanggungan</th>
                            </tr>
                        </thead>
                        <tbody>';
                            foreach($plan_protections as $plan_protection){
                                $plans[$plan_idx] .= '<tr>
                                    <td>'.$plan_protection->taxo_insurance_type_name.'</td>
                                    <td>Rp '.money($plan_protection->premi_perbulan, 2).'</td>
                                    <td>Rp '.money($plan_protection->nilai_pertanggungan, 2).'</td>
                                </tr>';
                            }
            $plans[$plan_idx] .= '</tbody>
                    </table>
                </dd>
                <dt>Total</dt>
                <dd>Rp '.money($plan->plan_perbulan, 2).' /bulan</dd>
            </dl><br />';
        }

        foreach($b_plans as $b_plan){
            $plan = $b_plan->plan_b[0];
            $plan_number = $plan->plan_number;
            $plan_idx = $plan_number;
            if(isset($plans[$plan_number])){
                $plan_idx = $random_idx;
                $random_idx++;
            }

            $plan_details = [
                'plan_type' => 'b',
                'plan_type_alias' => 'Comfort plan',
                'plan_name' => 'Plan '.$plan->plan_number,
                'plan_perbulan' => $plan->plan_perbulan
            ];

            if($plan->status == 1){
                $active_plans_total += $plan->plan_perbulan;
                $active_plans[$plan_idx]['plans'] = $plan_details;
            }else{
                $inactive_plans_total += $plan->plan_perbulan;
                $inactive_plans[$plan_idx]['plans'] = $plan_details;
            }
        
            $plans[$plan_idx] = '<h4>Plan '.$plan_number.' &nbsp;<small>( Comfort plan )</small></h4>
            <dl>
                <dd>
                    <table class="table no-margin">
                        <thead>
                            <tr>
                                <th>Asset Repayment name</th>
                                <th>Plan Name</th>
                                <th>Durasi cicilan</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Property</td>
                                <td>'.$plan->plan_name.'</td>
                                <td>'.$plan->durasi_cicilan.' bulan</td>
                                <td>Rp '.money($plan->kebutuhan_dana, 2).'</td>
                            </tr>
                        </tbody>
                    </table>
                </dd>
                <dt>Total</dt>
                <dd>Rp '.money($plan->plan_perbulan, 2).' /bulan</dd>
            </dl><br />';
        }
    }
?>
<h4 class="text-center">Plans</h4>
@foreach($plans as $plan)
    {!! $plan !!}
@endforeach
<!--
<h4>Plan 1 <small>( Safe Retirement )</small></h4>
<dl>
    <dt>Investasi yang anda pilih :</dt>
    <dd>
        <table class="table no-margin">
            <thead>
                <tr>
                    <th>Investasi</th>
                    <th>Rate</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Money market</td>
                    <td>18%</td>
                    <td>Rp 1.000.000,00</td>
                </tr>
            </tbody>
        </table>
    </dd>
    <dt>Nilai pertanggungan investasi Anda :</dt>
    <dd>
        <table class="table no-margin">
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>Premi /bulan</th>
                    <th>Nilai pertanggungan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Jiwa</td>
                    <td>Rp 1.000.000,00</td>
                    <td>Rp 12.000.000,00</td>
                </tr>
                <tr>
                    <td>Kesehatan</td>
                    <td>Rp 1.000.000,00</td>
                    <td>Rp 12.000.000,00</td>
                </tr>
            </tbody>
        </table>
    </dd>
    <dt>Total</dt>
    <dd>Rp 459.000,00 /bulan</dd>
</dl>

<h4>Plan 2 <small>( Comfort plan )</small></h4>
<dl>
    <dd>
        <table class="table no-margin">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Name</th>
                    <th>Sisa durasi</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Property</td>
                    <td>Rumah doggy</td>
                    <td>30 bulan</td>
                    <td>Rp 1.000.000,00</td>
                </tr>
            </tbody>
        </table>
    </dd>
    <dt>Total</dt>
    <dd>Rp 459.000,00 /bulan</dd>
</dl>-->

<h4 class="text-center">Plan Summary</h4>
<dl>
@foreach($incomes as $idx => $income)
        <dt>Pendapatan lain-lain</dt>
        <dd>Rp {{ money($income->pendapatan_lain, 2) }} /tahun</dd>
@endforeach
@foreach($plan_balances as $idx => $plan_balance)
    @if($plan_balance->name = 'free_cashflow')
        <?php
            $sisa_free_cashflow = $plan_balance->balance - $active_plans_total;
        ?>
        <dt>{{ trans('app.'.$plan_balance->name) }}</dt>
        <dd>Rp {{ money($plan_balance->balance, 2) }}</dd>
    @endif
@endforeach
<dt>Total plan tidak akfif</dt>
<dd>Rp {{ money($inactive_plans_total, 2) }}</dd>
<dt>Sisa free cashflow</dt>
<dd>Rp {{ money($sisa_free_cashflow, 2) }}</dd>
</dl>
<h4>Daftar plan tidak aktif</h4>
<table class="table no-margin">
    <thead>
        <tr>
            <th>Plan</th>
            <th>Cicilan /bulan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($inactive_plans as $inactive_plan)
            <tr>
                <td>{{ $inactive_plan['plans']['plan_name'] }}</td>
                <td>Rp {{ money($inactive_plan['plans']['plan_perbulan'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h4>Daftar plan aktif</h4>
<table class="table no-margin">
    <thead>
        <tr>
            <th>Plan</th>
            <th>Cicilan /bulan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($active_plans as $active_plan)
            <tr>
                <td>{{ $active_plan['plans']['plan_name'] }}</td>
                <td>Rp {{ money($active_plan['plans']['plan_perbulan'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h4>Note Executive planner</h4>
<dl>
<dd>{{ $plan_analysis[0]['note'] }}</dd>
</dl>
@stop
