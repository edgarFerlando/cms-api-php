<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use App\Exceptions\Validation\ValidationException;
use Config;
use App\Repositories\Role\RoleRepository as Role;
use Entrust;
use Cache;


class ToolController extends Controller {

    protected $role;

    public function __construct(Role $role) {
        $this->role = $role;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function priceComparison() {
        $attr = [ 
                'title' => trans('app.tool_price_comparison')
            ];
        if(!Entrust::can(['read_price_comparison_tool'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }

        return view('backend.tool.price-comparison');
    }

    public function simulasi() {
        return view('backend.tool.simulasi');
    }

    public function simulasiPhp(){ // ini digunakan sama persis tapi dengan update variable dinamis ada di API/PlanController.php
        $usia = 30;
        $usia_pensiun = 60;
        $lama_tahun_investasi = $usia_pensiun-$usia;
        $lama_bulan_investasi = $lama_tahun_investasi*12;
        

        $inflasi = config_db_cached('settings::rate_inflation');
        $pv = 7500000;
        $pv_raw = $pv;

        //dd(calc_inf_fv('ori', $pv, $inflasi, $lama_tahun_investasi, true));

        $deposito_rate = config_db_cached('settings::rate_deposit');
        $suffix_data_name = 'needinv';
        $need_inv_pv = $pv_raw/(($deposito_rate/100)/12);
        
        $res['lama_tahun_investasi'] = $lama_tahun_investasi;
        $res['lama_bulan_investasi'] = $lama_bulan_investasi;
        $res += calc_inf_fv($suffix_data_name, $need_inv_pv, $inflasi, $lama_tahun_investasi, false);//inflasi seharusnya dihitung dalam tahun
        $res[$suffix_data_name]['rate_inv_'.$suffix_data_name] = $deposito_rate;
        //dd($res);
        $res += calc_ins('planprotection', $res);
        $res += add_ins_html('planprotection', $res);
        //dd($res);

        //pilih investasi
        $inv_rate_options = [
            0 => [
                'bgcolor' => '#EED1E9',
                'product' => 'Deposito',
                'rates' => [
                    5, 6,7,8
                ]
            ],
            1 => [
                'bgcolor' => '#D1E1EE',
                'product' => 'Government Bond',
                'rates' => [
                    9
                ]
            ],
            2 => [
                'bgcolor' => '#D9EFD2',
                'product' => 'Corporate Bond',
                'rates' => [
                    10,11,12
                ]
            ],
            3 => [
                'bgcolor' => '#F4F2D6',
                'product' => 'Money Market',
                'rates' => [
                    13
                ]
            ],
            4 => [
                'bgcolor' => '#F2D4D8',
                'product' => 'Money Market',
                'rates' => [
                    14
                ]
            ],
            5 => [
                'bgcolor' => '#DFD1EE',
                'product' => 'Money Market',
                'rates' => [
                    15,16,17,18
                ]
            ]
        ];
        $arr_rate_options = [];
        $arr_rate_idx = 0;
        foreach ($inv_rate_options as $inv_rate_option) {
            foreach ($inv_rate_option['rates'] as $idx_rate => $rate) {
                $arr_rate_options[$arr_rate_idx]['product'] = $inv_rate_option['product'];
                $arr_rate_options[$arr_rate_idx]['bgcolor'] = $inv_rate_option['bgcolor'];
                $arr_rate_options[$arr_rate_idx]['rate'] = $rate;
                $arr_rate_options[$arr_rate_idx]['details'] = calc_inv_pv_getPayment(slugify($inv_rate_option['product'], '_').'_'.$rate, $rate, $res);
                $arr_rate_idx++;
            }
        }

        $res['income_simulation'] = $arr_rate_options;
        $res['plan_growth'] = calc_inv_dynamicRate(10000000, [18,18,18,18,18,18,18,18,18,18,18,18], ['lama_bulan_investasi' => 12]);
        $res['actual_growth'] = calc_inv_dynamicRate(10000000, [18,17,16,15,14,13,12,11,10,9,8,7], ['lama_bulan_investasi' => 12]);
        dd($res);
        
        //add_reverse_inv_html($name);

        return view('backend.tool.simulasi-php', compact('res'));
    }

}