<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function calculate(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'oklad' => 'required|integer',
                'work_days' => 'required|integer',
                'work_days_done' => 'required|integer',
                'has_mzp' => 'required|boolean',
                'year' => 'nullable',
                'month' => 'nullable',
                'pensioner' => 'required|boolean',
                'disabled' => 'required|boolean',
                'disabled_group' => 'required_if:disabled,true|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 200);
            }
            DB::beginTransaction();
            //оклад в зависимости от отработанных дней
            $oklad = $request->oklad*$request->work_days_done/$request->work_days;
            //расчет налогов
            $mzp = 42500;
            $mrp = 2917;
            $opv = intval($oklad*0.1);
            $vosms = intval($oklad*0.02);
            $osms = intval($oklad*0.02);
            $so = intval(($oklad-$opv)*0.035);
            $ipn = $oklad-$opv-$vosms;
            if($request->has_mzp){
                $ipn -= $mzp;
            }
            if(25*$mrp > $oklad){
                $ipn -= $ipn*0.9;
            }
            $ipn = intval($ipn*0.1);
            $nalogi = [];
            $nalogi['ОПВ'] = $opv;
            $nalogi['ВОСМС'] = $vosms;
            $nalogi['ОСМС'] = $osms;
            $nalogi['СО'] = $so;
            $nalogi['ИПН'] = $ipn;
            if($request->pensioner){
                if($request->disabled){
                    $nalogi['ИПН'] = 0;
                }
                $nalogi['ОПВ'] = 0;
                $nalogi['ВОСМС'] = 0;
                $nalogi['ОСМС'] = 0;
                $nalogi['СО'] = 0;
            }else{
                if($request->disabled){
                    switch ($request->disabled_group) {
                        case 1:
                            $nalogi['ОПВ'] = 0;
                            $nalogi['ВОСМС'] = 0;
                            $nalogi['ОСМС'] = 0;
                            $nalogi['ИПН'] = 0;
                            break;
                        case 2:
                            $nalogi['ОПВ'] = 0;
                            $nalogi['ВОСМС'] = 0;
                            $nalogi['ОСМС'] = 0;
                            $nalogi['ИПН'] = 0;
                            break;
                        case 3:
                            $nalogi['ВОСМС'] = 0;
                            $nalogi['ОСМС'] = 0;
                            $nalogi['ИПН'] = 0;
                            break;
                        default:
                            break;
                    }
                    if($oklad > 882*$mrp){
                        $nalogi['ИПН'] = $ipn;
                    }
                }
            }

            //расчет зарплаты на руки
            $zp_na_ruki = $oklad;
            foreach ($nalogi as $nalog) {
                $zp_na_ruki -= $nalog;
            }

            //сохраняем в базу...
            DB::commit();
            return response()->json(['Начисленная зарплата' => $oklad, 'Зарплата на руки' => $zp_na_ruki, 'Налоги' => $nalogi], 200);
        }catch (Exception $e) {
            DB::rollBack();
        return response()->json(['errors' => $e->getMessage()], 500);
      }
    }
}
