@extends('backend.layout.layout')
@section('content')
<section class="content-header">    
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.cfp.schedule.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.schedule_cfp') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<br>
<div class="content">
    <div class="box"> 
        <div class="box-header with-border">
            <h3 class="box-title">CFP Schedule Day Off</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('cfp_name', trans('app.cfp_name')) !!}
                <div>{!! $data->name !!}</div>
            </div>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('cfp_schedule_day_off_start_date', trans('app.cfp_schedule_day_off_start_date')) !!}
                <div>{!! $data->cfp_schedule_day_off_start_date !!}</div>
            </div>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('cfp_schedule_day_off_end_date', trans('app.cfp_schedule_day_off_end_date')) !!}
                <div>{!! $data->cfp_schedule_day_off_end_date !!}</div>
            </div>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('is_approval', trans('app.is_approval')) !!}
                <div>
                    @if($data->is_approval == 1)
                        Cuti Disetujui
                      @elseif($data->is_approval == 2)
                        Cuti Ditolak
                      @else
                        Cuti Belum Disetujui
                      @endif
                </div>
            </div>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('description', trans('app.description')) !!}
                <div>{!! $data->description !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( URL::route('admin.cfp.schedule.dayoff'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>

@stop