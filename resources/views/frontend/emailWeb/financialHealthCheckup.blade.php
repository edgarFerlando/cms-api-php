@extends('frontend.emailWeb-layout')
@section('body')
<div class="outer-content-wrap">
<div class="content-wrap">
    <div class="grid-x email-head">
        <div class="cell small-12 collapse has-logo">
            <div class="user-wrapper">
                <div class="user-ico"></div>
                <div class="label-rounded blue height30 name">
                    {!! $item->mail_to !!}
                </div>
            </div>
            <img src="{!! asset('img/logo-fundtastic-color.png') !!}">
        </div>
        <div class="cell small-12 collapse has-title">
            <div class="title-wrapper">
                <div class="check-sm-ico"></div>
                <div class="title">
                    cek kesehatan financial
                </div>
            </div>
            <div class="date">
                {!! $item->created_at->format('d F Y') !!}
            </div>
        </div>
    </div>
    <div class="grid-x email-body">
        <div class="cell small-12 rounded-box box-1011">
            <div class="title-wrapper"><div class="label-rounded turquoise height30">PEMASUKAN</div></div>
            <!-- <div class="grid-x grid-padding-x">
                <div class="small-6 cell"><label for="right-label">1. Bulanan Rutin</label></div>
                <div class="small-6 cell"><div class="label-rounded label-input">Rp. 10.000.000,00</div></div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="small-6 cell"><label for="right-label">2. Penghasilan tambahan</label></div>
                <div class="small-6 cell"><div class="label-rounded label-input">Rp. 2.000.000,00</div></div>
            </div>-->
            {!! $html_pemasukan !!}
        </div>
        <div class="cell small-12 rounded-box box-1101 turquoise-border">
            <div class="title-wrapper right"><div class="label-rounded blue height30">PENGELUARAN</div></div>
            {!! $html_pengeluaran !!}
        </div>

        <div class="cell small-12 result-box">
            <ul class="list-vertical" style="display: inline-block;margin: 0 auto;overflow: hidden;padding: 0;">
                <li>
                    <img src="{!! asset('img/email/check-blue.png') !!}" style="float: left;margin-right:15px;">
                </li>
                <li class="lbl-text">
                    Hasil
                </li>
                <li>
                    <div class="label-rounded result">{!! $item->result !!}</div>
                </li>
            <li>
            <img src="{!! asset('img/email/'.str_slug($item->result, '-').'.png') !!}" style="float: left;margin-left:15px;">
            </li>
            </ul>
        </div>
        <div class="cell small-12 contact-us">
            <div class="grid-x">
                <div class="cell small-12">
                    HUBUNGI FINANCIAL PLANNER ANDA
                </div>
                <div class="cell small-12">
                    UNTUK INFORMASI LEBIH DETAIL
                </div>
                <div class="cell small-6 text-right">
                    MELALUI MENU
                </div>
                <div class="cell small-6 text-left">
                    <div class="label-rounded blue height30">
                        KONSULTASI
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@stop