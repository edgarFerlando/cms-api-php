<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DashboardController extends Controller {

    function index() {

        //$logger = new Logger();
        /*$chartData = $logger->getLogPercent();*/

        $chartData = array();
        //dd(getTest());
        return view('backend/layout/dashboard', compact('chartData'))->with('active', 'home');
    }

}
