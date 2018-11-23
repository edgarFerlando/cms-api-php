@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.portofolio') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/portofolio') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.portofolio') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( getLang() . '.admin.portofolio.update', $portofolio->id), 'method' => 'PATCH', 'files'=>true)) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('portofolio_name') ? 'has-error' : '' !!}">
                {!! Form::label('portofolio_name', trans('app.portofolio_name').' *') !!}
                {!! Form::text('portofolio_name', $portofolio->portofolio_name, [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('portofolio_name'))
                    <span class="help-block">{!! $errors->first('portofolio_name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('keterangan') ? 'has-error' : '' !!}">
                {!! Form::label('keterangan', trans('app.keterangan')) !!}
                {!! Form::text('keterangan', $portofolio->keterangan, [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('keterangan'))
                    <span class="help-block">{!! $errors->first('keterangan') !!}</span>
                @endif
            </div>
            <!-- portofolio detail -->
            <div class="form-group">
                {!! Form::label('variants', trans('app.portofolio_detail').' *') !!}
                <div class="conrows-scope product-image-wrap" id="add-variant-detail-form">
                    <div class="form-only">
                        <?php
                            $ff_variants = [];
                        ?>
                        {!! Form::hidden('ff_variants', rawurlencode(json_encode($ff_variants))) !!} 
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    {!! Form::label('detail_name', trans('app.detail_name').' *') !!}
                                    {!! Form::text('detail_name', Input::old('detail_name'), [ 'class'=>'form-control', 'autocomplete' => 'off']) !!}
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    {!! Form::label('detail_keterangan', trans('app.detail_keterangan')) !!}
                                    {!! Form::text('detail_keterangan', Input::old('detail_keterangan'), [ 'class'=>'form-control', 'autocomplete' => 'off']) !!}
                                    <span class="help-block"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    {!! HTML::link('#', trans('app.add'), [ 'class' => 'btn btn-primary btn-addto_conrows' ]) !!}
                                    {!! HTML::link('#', trans('app.cancel'), [ 'class' => 'btn btn-default btn-addto_conrows-reset' ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <table class="table table-bordered table-condensed table-white table-striped conrows">
                                <thead>
                                    <tr>
                                        <th>{{{ trans('app.detail_name') }}}</th>
                                        <!-- <th>{{{ trans('app.weekend_price') }}}</th> -->
                                        <th>{{{ trans('app.detail_keterangan') }}}</th>
                                        <th>?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $variant_datarow = Input::old('variant_datarow');
                                    if (!is_null($variant_datarow)) {

                                        $variant_datarow = buildPOST_fromJS($variant_datarow); 
                                        //dd($variant_datarow);
                                        foreach ($variant_datarow as $idx => $variant) { 
                                            $hidden_data = mapping_fieldType('variant', $variant);
                                            //dd($hidden_data);
                                            echo '<tr>';
                                            
                                            echo '<td class="text-left">' . $variant['detail_name']['val'] . '</td>';
                                            // echo '<td class="text-right">Rp ' . money($variant['weekend_price']['val'], 2) . '</td>';
                                            echo '<td class="text-left">' . $variant['detail_keterangan']['val'] . '</td>';
                                            
                                            echo '<td width="65">
                                            <input type="hidden" value="' . $hidden_data . '" name="variant_datarow[' . $idx . ']">
                                            <button type="button" class="btn btn-danger btn-xs edit_conrow">
                                            <span class="fa fa-pencil"></span>
                                            </button>&nbsp;
                                            <button type="button" class="btn btn-danger btn-xs clear_conrow">
                                            <span class="fa fa-times"></span>
                                            </button>
                                            </td>';
                                            echo '</tr>';
                                        }
                                    }
                                    //dd($product);
                                      if (is_null($variant_datarow) && isset($portofolio)) { 
                                        $variant_datarow = [];
                                        //dd($product);
                                        foreach($portofolio['variations'] as $key => $variation){ 
                                          $variant_datarow[$key]['id']['val'] = $variation['id']; 
                                          $variant_datarow[$key]['id']['text'] = $variation['id'];
                                          $variant_datarow[$key]['detail_name']['val'] = $variation['detail_name']; 
                                          $variant_datarow[$key]['detail_name']['text'] = $variation['detail_name'];
                                          $variant_datarow[$key]['detail_keterangan']['val'] = $variation['detail_keterangan']; 
                                          $variant_datarow[$key]['detail_keterangan']['text'] = $variation['detail_keterangan']; 
                                        }
                                        $idx = 0;
                                        foreach ($variant_datarow as $variant) {
                                          $hidden_data = mapping_fieldType('variant', $variant);
                                          echo '<tr>';
                                          echo '<td class="text-left">' . $variant['detail_name']['val'] . '</td>';
                                          echo '<td class="text-left">' . $variant['detail_keterangan']['val'] . '</td>';
                                          echo '<td width="65">
                                          <input type="hidden" value="' . $hidden_data . '" name="portofolio_detail_datarow[' . $idx . ']">
                                          <button type="button" class="btn btn-danger btn-xs edit_conrow">
                                          <span class="fa fa-pencil"></span>
                                          </button>&nbsp;
                                          <button type="button" class="btn btn-danger btn-xs clear_conrow">
                                          <span class="fa fa-times"></span>
                                          </button>
                                          </td>';
                                          echo '</tr>';

                                          $idx++;
                                        }
                                      }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
          <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.portofolio.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
