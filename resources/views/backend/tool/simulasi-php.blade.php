@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>Simulasi</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url('admin') !!}"><i class="fa fa-bookmark"></i> Simulasi</a></li>
	</ol>
</section>
<div class="content" id="simulasi-page">
	<div class="box">  
		<div class="box-body">
			<div class="form-group">
				<div class="row">
					<div class="col-lg-2">
						<h3>Data</h3>
						<div class="form-group">
			                {!! Form::label('usia', 'Usia *') !!}
							{!! Form::text('usia', '', [ 'class' => 'form-control number triggerOnKeyup', 'autocomplete' => 'off' ]) !!}
			            </div>
			            <div class="form-group">
			                {!! Form::label('usia_pensiun', 'Pensiun *') !!}
							{!! Form::text('usia_pensiun', '', [ 'class' => 'form-control number triggerOnKeyup', 'autocomplete' => 'off' ]) !!}
			            </div>
			        </div>
			        <div class="col-lg-4">
						<h3>Target</h3>
			            <div class="form-group">
			                {!! Form::label('inflasi', 'Inflasi *') !!}
							{!! Form::text('inflasi', '', [ 'class' => 'form-control number triggerOnKeyup', 'autocomplete' => 'off', 'placeholder' => '% pertahun' ]) !!}
			            </div>
			            <div class="form-group">
			                {!! Form::label('pendapatan_saat_pensiun', 'Pendapatan pensiun *') !!}
							{!! Form::text('pendapatan_saat_pensiun', '', [ 'class' => 'form-control number triggerOnKeyup', 'autocomplete' => 'off', 'placeholder' => 'Pendapatan perbulan' ]) !!}
			            </div>
			        </div>
			        <!--<div class="col-lg-3">
			        	<h3>Investasi</h3>
			            <div class="form-group">
			            	<div class="row">
			            		<div class="col-lg-7">
									{!! Form::label('inv_name', 'Nama *') !!}
									{!! Form::text('inv_name', '', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
								</div>
								<div class="col-lg-5" style="padding-left:0;">
									{!! Form::label('inv_rate', 'Rate *') !!}
									{!! Form::text('inv_rate', '', [ 'class' => 'form-control number triggerOnKeyup', 'autocomplete' => 'off', 'placeholder' => '% pertahun' ]) !!}
								</div>
							</div>
			            </div>
			            <div class="form-group">
			            	<div class="row">
								<div class="col-lg-9">
									{!! Form::label('jumlah_investasi', 'Investasi *') !!}
							{!! Form::text('jumlah_investasi', '', [ 'class' => 'form-control number triggerOnKeyup', 'autocomplete' => 'off', 'placeholder' => 'Investasi perbulan' ]) !!}
								</div>
								<div class="col-lg-3 action-tool" style="padding-left:0;">
									<div class="btn btn-success btn-block add-inv"><i class="fa fa-plus"></i></div>
								</div>
							</div>
			            </div>
			        </div>
			        <div class="col-lg-3">
			        	<h3>Asuransi</h3>
			            <div class="form-group">
							{!! Form::label('ins_name', 'Nama *') !!}
							{!! Form::text('ins_name', '', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
			            </div>
			            <div class="form-group">
			            	<div class="row">
			            		<div class="col-lg-9">
									{!! Form::label('ins_inv_rate', 'Bunga Investasi *') !!}
									{!! Form::text('ins_inv_rate', '', [ 'class' => 'form-control number', 'autocomplete' => 'off', 'placeholder' => '% pertahun' ]) !!}
								</div>
								<div class="col-lg-3 action-tool" style="padding-left:0;">
									<div class="btn btn-success btn-block add-ins"><i class="fa fa-plus"></i></div>
								</div>
							</div>
			                
			            </div>
			        </div>-->
			        <div class="col-lg-6">
			        	<h3>Pilihan Investasi</h3>
			            <div class="form-group">
							{!! Form::label('reverse_inv_name', 'Nama *') !!}
							{!! Form::text('reverse_inv_name', '', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
			            </div>
			            <div class="form-group">
			            	<div class="row">
								<div class="col-lg-9">
									{!! Form::label('reverse_inv_rate', 'Rate *') !!}
							{!! Form::text('reverse_inv_rate', '', [ 'class' => 'form-control number', 'autocomplete' => 'off', 'placeholder' => '% pertahun' ]) !!}
								</div>
								<div class="col-lg-3 action-tool" style="padding-left:0;">
									<div class="btn btn-success btn-block add-reverse-inv"><i class="fa fa-plus"></i></div>
								</div>
							</div>
			                
			            </div>
			        </div>
					<div class="col-lg-2 simulasi-summary">
			            <h3>Summary</h3>
			            <h4>Target</h4><hr class="min"/>
			            <dl>
			            	<dt>PV</dt>
			            	<dd class="simul-text-pv">Rp 0,00</dd>
			            	<!--<dt>FV&nbsp;&nbsp;<a href="#" simulasi="html_simulasi_inf" class="load-simulasi"><i class="fa fa-list"></i></a></dt>
			            	<dd class="simul-text-money-fv">Rp 0,00</dd>-->
			            	<dt>Inv Depo <span class="simul-text-depo-rate">0</span>%</dt>
			            	<dt>PV</dt>
			            	<dd class="simul-text-money-needinv-pv">Rp 0,00</dd>
			            	<dt>FV Inf <span class="simul-text-inf-rate">0</span>%&nbsp;&nbsp;<a href="#" simulasi="html_simulasi_inf_needinv" class="load-simulasi"><i class="fa fa-list"></i></a></dt>
			            	<dd class="simul-text-money-needinv-fv">Rp 0,00</dd>
			            	<!--<dt>Invest required FV&nbsp;&nbsp;<a href="#" simulasi="html_simulasi_inf_ori" class="load-simulasi"><i class="fa fa-list"></i></a></dt>
			            	<dd class="simul-text-money-needinv-fv">Rp 0,00</dd>-->
			            	<!--<dt>Pencapaian</dt>
			            	<dd class="simul-text-money-diff">Rp 0,00</dd>-->
			            </dl>
			            <!--<h4>Investasi</h4><hr class="min"/>
			            <div class="inv-wrap">-->
				            <!--<dl>
				            	<dt>Deposito &nbsp;&nbsp;<a href="#"><i class="fa fa-list"></i></a></dt>
				            	<dd>5.000.000 /bulan</dd>
				            	<dt>FV</dt>
				            	<dd>50.000.000</dd>
				            </dl>
				            <dl>
				            	<dt>Stock &nbsp;&nbsp;<a href="#"><i class="fa fa-list"></i></a></dt>
				            	<dd>5.000.000 /bulan</dd>
				            	<dt>FV</dt>
				            	<dd>50.000.000</dd>
				            </dl>-->
				        <!--</div>-->
			            <h4>Asuransi</h4><hr class="min"/>
			            <div class="ins-wrap">
			            	{!! $res['ins_html'] !!}
				            <!--<dl>
				            	<dt>AXA &nbsp;&nbsp;<a href="#"><i class="fa fa-list"></i></a></dt>
				            	<dd>5.000.000 /bulan</dd>
				            	<dt>FV</dt>
				            	<dd>50.000.000</dd>
				            </dl>
				            <dl>
				            	<dt>BPJS &nbsp;&nbsp;<a href="#"><i class="fa fa-list"></i></a></dt>
				            	<dd>5.000.000 /bulan</dd>
				            	<dt>FV</dt>
				            	<dd>50.000.000</dd>
				            </dl>-->
				        </div>
				        <h4>Pilihan Investasi</h4><hr class="min"/>
			            <div class="inv-reverse-wrap">
				        </div>
					</div>
					<div class="col-lg-10">
						<div class="result-simulasi">
							<!--<div class="simul-text"></div>-->
							<div class="simul-future-value">

								<h3>Simulasi</h3>
								<table class="table table-bordered">
									<thead>
										{!! $res['html_simulasi_inf_needinv_head'] !!}
										<!--<tr class="simul-text-wrap"><th class="simul-text" colspan="6">
											Future Value Rp <span class="simul-text-pv">0,00</span> saat usia <span class="simul-text-usia-pensiun">0,00</span> tahun dengan inflasi <span class="simul-text-inflasi">0,00</span> % pertahun adalah Rp <span class="simul-text-money-fv" style="font-size:16px;">0,00</span><br />
											Pencapaian target investasi : Rp <span class="simul-text-money-inv" style="font-size:16px;">0,00</span> - Rp <span class="simul-text-money-fv">0,00</span> = Rp <span class="simul-text-money-diff" style="font-size:16px;">0,00</span>
										</th></tr>-->
										<!--<tr class="simul-text-wrap"><th class="simul-text" colspan="6">
											<span class="simul-text-type"></span>&nbsp;<span class="simul-text-name"></span>
										</th></tr>
										<tr><th class="text-center">Month</th><th class="text-center">Payment</th><th class="text-center">Monthly Interest [ % ]</th><th class="text-center">Interest</th><th class="text-center">FV</th></tr>-->
									</thead>
									<tbody>
										{!! $res['html_simulasi_inf_needinv'] !!}
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
	        </div>
		</div>
		</div>
</div>
@stop
