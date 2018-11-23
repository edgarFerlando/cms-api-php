@extends('frontend.emailWeb-layout')
@section('body')
<?php
    $incomes = [];
	$expenses = [];
	$debt_repayments = [];
	$asset_repayments = [];
	$insurances = [];
	$plan_balances = [];
    if($ca_item){
        $incomes = $ca_item->incomes;
        $expenses = $ca_item->expenses;
        $debt_repayments = $ca_item->debt_repayments;
        $asset_repayments = $ca_item->asset_repayments;
        $insurances = $ca_item->insurances;
        $plan_balances = $ca_item->plan_balances;
    }

    $asset_repayments_paid = [];
	$asset_repayments_paidoff = [];
    if($pa_item){
        $asset_repayments_paid = $pa_item->asset_repayments_paid;
        $asset_repayments_paidoff = $pa_item->asset_repayments_paidoff;
    }

    if($p_item){
        $a_plans = isset($p_item['a_plans'])?$p_item['a_plans']:[];
        $b_plans = isset($p_item['b_plans'])?$p_item['b_plans']:[];
        $plan_analysis = isset($p_item['plan_analysis'])?$p_item['plan_analysis']:[];
    }

    $html_income_expense = '';
    $income_expense_counter = 1;
    if($incomes){
        foreach($incomes as $idx => $income){
            $html_income_expense .= '<div class="grid-x">
                <div class="small-6 cell"><label for="right-label">'.$income_expense_counter.'. Penghasilan bulanan</label></div>
                <div class="small-6 cell"><div class="label-rounded label-input">Rp '.money($income->pendapatan_bulanan, 2).'</div></div>
            </div>';
            $income_expense_counter++;
            $html_income_expense .= '<div class="grid-x">
                <div class="small-6 cell"><label for="right-label">'.$income_expense_counter.'. Penghasilan lain</label></div>
                <div class="small-6 cell"><div class="label-rounded label-input">Rp '.money($income->pendapatan_lain, 2).'</div></div>
            </div>';
            $income_expense_counter++;
        }
    }

    if($expenses){
        foreach($expenses as $expense){
            $html_income_expense .= '<div class="grid-x">
                <div class="small-6 cell"><label for="right-label">'.$income_expense_counter.'. Dompet '.$expense->taxo_wallet_name.'</label></div>
                <div class="small-6 cell"><div class="label-rounded label-input">Rp '.money($expense->anggaran_perbulan, 2).'</div></div>
            </div>';
        }
    }

    $html_debt_repayment = '';
    $debt_repayment_counter = 1;
    if($debt_repayments){
        foreach($debt_repayments as $debt_repayment){
            $html_debt_repayment .= '<div class="grid-x">
                <div class="small-5 cell"><label for="right-label">'.$debt_repayment_counter.'. '.$debt_repayment->taxo_wallet_name.'</label></div>
                <div class="small-7 cell">
                    <div class="label-rounded label-input">
                        <div class="grid-x has-extra-form">
                            <div class="small-4 cell text-left">Cicilan</div>
                            <div class="small-8 cell text-right">Rp. '.money($debt_repayment->cicilan_perbulan, 2).'</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-x">
                <div class="small-5 cell">&nbsp;</div>
                <div class="small-7 cell">
                    <div class="label-rounded label-input">
                        <div class="grid-x has-extra-form">
                            <div class="small-4 cell text-left">Sisa Tenor</div>
                            <div class="small-8 cell text-right">'.$debt_repayment->sisa_durasi.' Bulan</div>
                        </div>
                    </div>
                </div>
            </div>';
            $debt_repayment_counter++;
        }
    }

    $html_asset_repayment = '';
    $asset_repayment_counter = 1;
    if($asset_repayments){
        foreach($asset_repayments as $asset_repayment){
            $html_asset_repayment .= '<div class="grid-x">
                <div class="small-5 cell"><label for="right-label">'.$asset_repayment_counter.'. '.$asset_repayment->taxo_wallet_name.'</label></div>
                <div class="small-7 cell">
                    <div class="label-rounded label-input">
                        <div class="grid-x has-extra-form">
                            <div class="small-4 cell text-left">Cicilan</div>
                            <div class="small-8 cell text-right">Rp. '.money($asset_repayment->cicilan_perbulan, 2).'</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-x">
                <div class="small-5 cell">&nbsp;</div>
                <div class="small-7 cell">
                    <div class="label-rounded label-input">
                        <div class="grid-x has-extra-form">
                            <div class="small-4 cell text-left">Sisa Tenor</div>
                            <div class="small-8 cell text-right">'.$asset_repayment->sisa_durasi.' Bulan</div>
                        </div>
                    </div>
                </div>
            </div>';
            $asset_repayment_counter++;
        }
    }

    $html_insurance = '';
    $insurance_counter = 1;
    if($insurances){
        foreach($insurances as $insurance){
            $html_insurance .= '<dt>'.$insurance_counter.'. '.$insurance->taxo_wallet_name.'</dt>
                <dd>No Polis : '.$insurance->no_polis.'</dd>
                <dd>Premi : Rp. '.money($insurance->premi_perbulan, 2).'</dd>
                <dd>Jenis : '.$insurance->taxo_insurance_type_name.'</dd>
                <dd>Nilai pertanggungan : Rp. '.money($insurance->nilai_pertanggungan, 2).'</dd>';
                $insurance_counter++;
        }
    }

    $html_asset_repayment_paid = '';
    $asset_repayment_paid_counter = 1;
    if($asset_repayments_paid){
        foreach($asset_repayments_paid as $asset_repayment_paid){
            $html_asset_repayment_paid .= '<dt>'.$asset_repayment_paid_counter.'. '.$asset_repayment_paid->asset_repayment_nama.'</dt>
                <dd>Cicilan Terbayar : Rp. '.money($asset_repayment_paid->cicilan_terbayar, 2).'</dd>';
            $asset_repayment_paid_counter++;
        }
    }

    $html_asset_repayment_paidoff = '';
    $asset_repayment_paidoff_counter = 1;
    if($asset_repayments_paidoff){
        foreach($asset_repayments_paidoff as $asset_repayment_paidoff){
            $html_asset_repayment_paidoff .= '<dt>'.$asset_repayment_paidoff_counter.'. '.$asset_repayment_paidoff->taxo_wallet_name.'</dt>
                <dd>'.$asset_repayment_paidoff->nama.'</dd>
                <dd>Nilai : Rp. '.money($asset_repayment_paidoff->nilai_aset, 2).'</dd>
                
                <!--
                <dd>Sisa tenor : '.$asset_repayment_paidoff->sisa_durasi.' bulan</dd> -->';
                
            $asset_repayment_paidoff_counter++;
        }
    }

    $html_plans = [];
    $html_actual_interest_rates = '';
    //$deposito_rate = config_db_cached('settings::rate_deposit');
    $activated = '';
    $not_activated = '';
    if(count($a_plans)){
        foreach($a_plans as $a_plan){
            

            $plan_a_item = isset($a_plan->plan_a)?$a_plan->plan_a[0]:[];
            $income_simulation = $plan_a_item->income_simulation;
            $insurance_coverages = isset($a_plan->insurance_coverages)?$a_plan->insurance_coverages:[];
            $plan_protections = isset($a_plan->plan_protections)?$a_plan->plan_protections[0]:[];
            $actual_interest_rates = isset($income_simulation->actual_interest_rates)?$income_simulation->actual_interest_rates:[];
            if(count($actual_interest_rates)){
                $html_actual_interest_rates = '<table class="email-border" width="100%"><tr><td>Periode</td><td>Rate</td></tr>';
                foreach($actual_interest_rates as $actual_interest_rate){
                    $html_actual_interest_rates .= '<tr><td>'.Carbon\Carbon::parse($actual_interest_rate->period)->format('M Y').'</td><td>'.money($actual_interest_rate->rate, 2).'%</td></tr>';
                }
                $html_actual_interest_rates .= '</table>';
            }

            if(count($insurance_coverages)){
                $html_insurance_coverages = '';
                foreach($insurance_coverages as $insurance_coverage){ 
                    $html_insurance_coverages .= '<dd>'.$insurance_coverage['taxo_insurance_type_name'].' – Rp '.money($insurance_coverage['nilai_pertanggungan'], 2).'</dd>';
                }
            }

            $html_plans[$plan_a_item->plan_number] = '<div class="cell small-12 rounded-box box-1011 turquoise-border">
            <div class="title-wrapper small right"><div class="label-rounded blue height30">RENCANA '.$plan_a_item->plan_number.'</div></div>
            <div class="grid-x">
                <div class="small-12 cell">
                    <dl>
                        <dt>1. Rencana Kebutuhan Anda ?</dt>
                        <dd>Nominal : Rp. '.money($plan_a_item->fv_kebutuhan_dana, 2).'</dd>
                        <dt>2. Kebutuhan Hidup Dasar</dt>
                        <dd>Saat ini : Rp. '.money($plan_a_item->pendapatan_pensiun, 2).'</dd>
                        <dd>Status : '.$plan_a_item->status_perkawinan.'</dd>
                        <dd>Umur : '.$plan_a_item->umur.' tahun</dd>
                        <dd>Umur Pensiun : '.$plan_a_item->umur_pensiun.' tahun</dd>
                        <dd>Dengan asumsi bunga deposito '.$plan_a_item->inflasi_pertahun.'% maka dibutuhkan</dd> 
                        <dd>uang Rp. '.money($plan_a_item->kebutuhan_dana, 2).'</dd>
                        <dd>Future value pada saat pensiun</dd> 
                        <dd>di umur N tahun : Rp. '.money($plan_a_item->fv_kebutuhan_dana, 2).'</dd>
                        <dt>3. Produk Mutual Fund yang dipilih</dt>
                        <dd>Nama : '.$income_simulation['produk'].'</dd>
                        <dd>Nominal : Rp. '.money($income_simulation['total_investasi'], 2).'</dd>
                        <dd>Tenor : '.($plan_a_item['durasi_tahun_investasi']*12).' Bulan</dd>
                        <dd>Rencana pertumbuhan : '.$income_simulation['bunga_investasi_pertahun'].'% / thn</dd>
                        <!--
                        <dd>Realisasi pertumbuhan : </dd>
                        <dd>'.$html_actual_interest_rates.'</dd>
                        -->
                        <dt>4. Asuransi yang dibutuhkan dalam waktu '.$plan_protections->durasi_proteksi.' tahun</dt> 
                        '.$html_insurance_coverages.'
                    </dl>
                </div>
            </div>
            </div>';

            if($plan_a_item->status == 1)
                $activated .= '<dd>RENCANA '.$plan_a_item->plan_number.'</dd>';
            else
                $not_activated .= '<dd>RENCANA '.$plan_a_item->plan_number.'</dd>';
        }
    }

    if(count($b_plans)){
        foreach($b_plans as $b_plan){
            $plan_b_item = isset($b_plan->plan_b)?$b_plan->plan_b[0]:[];

            $html_plans[$plan_b_item->plan_number] = '<div class="cell small-12 rounded-box box-1011 turquoise-border">
            <div class="title-wrapper small right"><div class="label-rounded blue height30">RENCANA '.$plan_b_item->plan_number.' - '.$plan_b_item->plan_name.'</div></div>
            <div class="grid-x">
                <div class="small-12 cell">
                    <dl>
                        <dt>1. Rencana Kebutuhan Anda ?</dt>
                        <dd>Nominal : Rp. '.money($plan_b_item->kebutuhan_dana, 2).'</dd>
                        <dt>2. Cicilan</dt>
                        <dd>Durasi : '.$plan_b_item->durasi_cicilan.' bulan</dd>
                        <dd>Bunga tahunan flat : '.money($plan_b_item->bunga_tahunan_flat, 2).'</dd>
                    </dl>
                </div>
            </div>
            </div>';

            if($plan_b_item->status == 1)
                $activated .= '<dd>RENCANA '.$plan_b_item->plan_number.' - '.$plan_b_item->plan_name.'</dd>';
            else
                $not_activated .= '<dd>RENCANA '.$plan_b_item->plan_number.' - '.$plan_b_item->plan_name.'</dd>';
        }
    }
    asort($html_plans);
    if(count($plan_analysis)){
        $html_action_plan = '';
        $action_plan_counter = 1;
        $plan_analysis = $plan_analysis[0];
        if(isset($plan_analysis->client_action_plan)){
            $action_plan = $plan_analysis->client_action_plan;
            if(isset($action_plan->details) && count($action_plan->details)){
                foreach($action_plan->details as $action){
                    $html_action_plan .='<tr>
                        <td>'.$action_plan_counter.'.</td>
                        <td>'.$action->note.'</td>
                        <td>'.Carbon\Carbon::parse($action->timeline)->format('d M Y').'</td>
                    </tr>';
                    $action_plan_counter++;
                }
            }
        }
    }


?>
<div class="outer-content-wrap email-plan-analysis">
<div class="content-wrap">
    <div class="grid-x email-head">
        <div class="cell small-12 collapse has-logo">
        <div class="title-wrapper">
                <div class="check-sm-ico"></div>
                <div class="title">
                    ringkasan kemajuan
                </div>
            </div>
            <img src="{!! asset('img/logo-fundtastic-color.png') !!}">
        </div>
        <div class="cell small-12 collapse has-title">
            <div class="user-wrapper">
                <div class="user-ico"></div>
                <div class="label-rounded blue height30 name">
                   {!! $user_dtl['user_code'] !!} 
                </div>
            </div>
        </div>
        <div class="cell small-12 collapse has-lg-title">
            DEVELOP<br />PLAN
        </div>
    </div>
    <div class="grid-x email-body">
        <!-- <div class="cell small-12 rounded-box box-1110 top-space">
            <div class="title-wrapper stick-left"><div class="label-rounded orange height30">MISSION AND GOAL</div></div>
            <div class="grid-x">
                <div class="small-12 cell">
                    Fondasi keuangan yang kuat
                </div>
            </div>
        </div> -->
        <div class="cell small-12 head-box orange-border">
            <div class="title-wrapper right"><div class="label-rounded orange height30">PEMERIKSAAN KEUANGAN</div></div>
        </div>
        <div class="cell small-12 rounded-box box-1011 turquoise-border">
            <div class="title-wrapper small right"><div class="label-rounded blue height30">PENDAPATAN DAN BEBAN</div></div>
            <!--<div class="grid-x">
                <div class="small-6 cell"><label for="right-label">1. Bulanan Rutin</label></div>
                <div class="small-6 cell"><div class="label-rounded label-input">Rp. 10.000.000,00</div></div>
            </div>
            <div class="grid-x">
                <div class="small-6 cell"><label for="right-label">2. Penghasilan tambahan</label></div>
                <div class="small-6 cell"><div class="label-rounded label-input">Rp. 2.000.000,00</div></div>
            </div>-->
            {!! $html_income_expense !!}
        </div>
        <div class="cell small-12 rounded-box box-1101">
            <div class="title-wrapper small right"><div class="label-rounded turquoise height30">CICILAN HUTANG</div></div>
            <!--<div class="grid-x">
                <div class="small-5 cell"><label for="right-label">1. HANDPHONE</label></div>
                <div class="small-7 cell">
                    <div class="label-rounded label-input">
                        <div class="grid-x has-extra-form">
                            <div class="small-4 cell text-left">Nilai</div>
                            <div class="small-8 cell text-right">Rp. 10.000.000,00</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-x">
                <div class="small-5 cell">&nbsp;</div>
                <div class="small-7 cell">
                    <div class="label-rounded label-input">
                        <div class="grid-x has-extra-form">
                            <div class="small-4 cell text-left">Cicilan</div>
                            <div class="small-8 cell text-right">Rp. 10.000.000,00</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-x">
                <div class="small-5 cell">&nbsp;</div>
                <div class="small-7 cell">
                    <div class="label-rounded label-input">
                        <div class="grid-x has-extra-form">
                            <div class="small-4 cell text-left">Sisa Tenor</div>
                            <div class="small-8 cell text-right">36 Bulan</div>
                        </div>
                    </div>
                </div>
            </div>
            -->
            {!! $html_debt_repayment !!}
        </div>
        <div class="cell small-12 rounded-box box-1101">
            <div class="title-wrapper small right"><div class="label-rounded turquoise height30">ASET HUTANG</div></div>
            {!! $html_asset_repayment !!}
        </div>
        <div class="cell small-12 rounded-box box-1110 blue-border">
            <div class="title-wrapper small right"><div class="label-rounded turquoise height30">ASURANSI</div></div>
            <div class="grid-x">
                <div class="small-12 cell">
                    <dl class="text-center">
                        <!-- <dt>1. PRUDENTIAL</dt>
                        <dd>No Polis : 7009393</dd>
                        <dd>Premi : Rp. 500.000,00</dd>
                        <dd>Jenis : JIWA</dd>
                        <dd>Nilai pertanggungan : Rp. 1.000.000.000,-</dd>
                        <dt>1. PRUDENTIAL</dt>
                        <dd>No Polis : 7009393</dd>
                        <dd>Premi : Rp. 500.000,00</dd>
                        <dd>Jenis : JIWA</dd>
                        <dd>Nilai pertanggungan : Rp. 1.000.000.000,-</dd>-->
                        {!! $html_insurance !!}
                    </dl>
                </div>
            </div>
        </div>
        <div class="cell small-12 head-box orange-border">
            <div class="title-wrapper right"><div class="label-rounded orange height30">PORTFOLIO ANALYSIS</div></div>
        </div>
        <div class="cell small-12 rounded-box box-1011 turquoise-border">
            <div class="title-wrapper small right"><div class="label-rounded blue height30">CICILAN ASET</div></div>
            <div class="grid-x">
                <div class="small-12 cell">
                    <dl class="text-center">
                        {!! $html_asset_repayment_paid !!}
                    </dl>
                </div>
            </div>
        </div>
        <div class="cell small-12 rounded-box box-1101">
            <div class="title-wrapper small"><div class="label-rounded turquoise height30">ASET LUNAS</div></div>
            <div class="grid-x">
                <div class="small-12 cell">
                    <dl class="text-center">
                        <!--<dt>1. RUMAH</dt>
                        <dd>Nilai : Rp. 600.000.000,00</dd>
                        <dt>2. APARTEMENT</dt>
                        <dd>Nilai : Rp. 300.000.000,00</dd>-->
                        {!! $html_asset_repayment_paidoff !!}
                    </dl>
                </div>
            </div>
        </div>
        <div class="cell small-12 head-box orange-border">
            <div class="title-wrapper right"><div class="label-rounded orange height30">ANALISA RENCANA</div></div>
        </div>
        
        {!! implode('', $html_plans) !!}
        <!--<div class="cell small-12 rounded-box box-1011 turquoise-border">
           <div class="title-wrapper small"><div class="label-rounded blue height30">PENSIUN DINI</div></div>
            <div class="grid-x">
                <div class="small-12 cell">
                    <dl>
                        <dt>1. Rencana Kebutuhan Anda ?</dt>
                        <dd>Nominal : Rp. 2.000.000.000,00</dd>
                        <dt>2. Kebutuhan Hidup Dasar</dt>
                        <dd>Saat ini : Rp. 10.000.000,00</dd>
                        <dd>Status : </dd>
                        <dd>Umur :</dd>
                        <dd>Umur Pensiun : </dd>
                        <dd>Dengan asumsi bunga deposito N% maka dibutuhkan</dd> 
                        <dd>uang Rp. 2.000.000.000,-</dd>
                        <dd>Future value pada saat pensiun</dd> 
                        <dd>di umur N tahun : Rp. 5.500.000.000,00</dd>
                        <dt>3. Produk Mutual Fund yang dipilih</dt>
                        <dd>Nama : Mutual Fund Equity</dd>
                        <dd>Nominal : Rp. 2.000.000,00</dd>
                        <dd>Tenor : 630 Bulan</dd>
                        <dd>Rencana pertumbuhan : N% / thn</dd>
                        <dd>Realisasi pertumbuhan : N% / thn</dd>
                        <dt>4. Asuransi yang dibutuhkan dalam waktu 5 tahun</dt> 
                        <dd>JIWA – Rp 2.500.000.000,00</dd>
                        <dd>KRITIS – Rp 500.000.000,00</dd>
                    </dl>
                </div>
            </div>
        </div>-->
        <div class="cell small-12 collapse has-lg-title what-to-do">
            APA<br />YANG HARUS DILAKUKAN
        </div>
        <div class="cell small-12 head-box turquoise-border">
            <div class="title-wrapper"><div class="label-rounded turquoise height30">RENCANA YANG DIJALANKAN</div></div>
        </div>
        <div class="cell small-12 collapse has-md-title">
            <dl>
                <!-- <dd>PENSIUN DINI</dd>
                <dd>PENSIUN DONO</dd>-->
                {!! $activated !!}
            </dl>
        </div>
        <div class="cell small-12 head-box turquoise-border">
            <div class="title-wrapper stick-right"><div class="label-rounded turquoise height30">RENCANA YANG TIDAK DIJALANKAN</div></div>
        </div>
        <div class="cell small-12 collapse has-md-title">
            <dl>
                {!! $not_activated !!}
            </dl>
        </div>
        <div class="cell small-12 head-box turquoise-border">
            <div class="title-wrapper"><div class="label-rounded turquoise height30">RENCANA AKSI</div></div>
        </div>

        <div class="cell small-12 has-table">
            <table class="email-border" width="100%">
                <thead>
                    <tr>
                        <th width="50px">No.</th>
                        <th>Tindakan</th>
                        <th width="120px">Tanggal jatuh tempo untuk selesai</th>
                    </tr>
                </thead>
                <tbody>
                    {!! $html_action_plan !!}
                    <!-- <tr>
                        <td>1.</td>
                        <td>Mendaftarkan rekening operasional ke CFP</td>
                        <td>16 april 2018</td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>Mendaftarkan rekening operasional ke CFP</td>
                        <td>16 april 2018</td>
                    </tr>
                    <tr>
                        <td>3.</td>
                        <td>Mendaftarkan rekening operasional ke CFP</td>
                        <td>16 april 2018</td>
                    </tr>-->
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@stop