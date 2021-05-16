<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRepayment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

class LoanController extends Controller
{

    public function generateLoan(Request $request){
      
        $validator = Validator::make($request->all(), [
            'loan_amount' => 'required|integer',
            'tenure' => 'required|numeric|between:26,200',
            'interest_rate'  => 'required|numeric|between:10,16',
        ]);

        if($validator->fails()){
                return response()->json($validator->errors(), 400);
        }
        
        $exist = Loan::where('user_id', JWTAuth::user()->id)->where('status', 1)->count();

        if($exist){
            return response()->json(['You have one loan in progress, please complete it to apply new one'], 200);
        }
        

        //$loan_status = config('loan.loan_status');
        try{

            $processing_fee = 1;

            $total_amount_to_pay =  $request->loan_amount + (($request->loan_amount * $request->interest_rate)/100);
            $disbursement_amount = $request->loan_amount - (($request->loan_amount * $processing_fee)/100);

            $loan = new Loan();
            $loan->user_id = JWTAuth::user()->id;
            $loan->loan_amount = $request->loan_amount;
            $loan->tenure = (int)$request->tenure;
            $loan->total_amount_to_pay = $total_amount_to_pay;
            $loan->frequency = 'week';
            $loan->interest_rate = $request->interest_rate;
            $loan->disbursement_amount = $disbursement_amount;
            $loan->processing_fee = $processing_fee;
            $loan->status = 1;
            $loan->save();

            $response = ['Status' => 'Approved', 'Total Amount to Pay' => $total_amount_to_pay];

            return response()->json($response, 200);

        } catch(Exception $e){
            return response()->json('Something went wrong');
        }               

    }

    public function getLoanDetails(){

        try{
            $loans = Loan::where('user_id', JWTAuth::user()->id)->get();

            $loan_status = config('loan.loan_status');

            foreach($loans as $loan){
                $paid_count = $this->getPayemntCount($loan->id);
                
                $data[] = [
                            'Loan Id' => $loan->id,
                            'Loan Amount' => $loan->loan_amount,
                            'Tensure' => (int)$loan->tenure,
                            'Frequency' => $loan->frequency,
                            'Interest Rate' => $loan->interest_rate,
                            'Status' =>  $loan_status[$loan->status],
                            'Total Amount to pay' => $loan->total_amount_to_pay,
                            'Installment Amount' => number_format($loan->total_amount_to_pay / (int)$loan->tenure, 2),
                            'Pending Installments' => $loan->tenure - $paid_count,
                        ];
            }

            return response()->json($data, 200);

        } catch(Exception $e){
            return response()->json(['Something went wrong']);
        } 

    }


    public function loanRepayment(Request $request){

        $validator = Validator::make($request->all(), [
            'repayment_amount' => 'required|numeric',           
        ]);

        if($validator->fails()){
                return response()->json($validator->errors(), 400);
        }

        try {

            $loan = Loan::where('status', 1)->where('user_id', JWTAuth::user()->id)->first();

            if(!$loan){
                return response()->json(['No loan Found or Loan completed']);
            }

            $installment_amount =  number_format($loan->total_amount_to_pay/$loan->tenure, 2, '.', '');

            if($request->repayment_amount == $installment_amount){
                $payment = new LoanRepayment();
                $payment->user_id = JWTAuth::user()->id;
                $payment->loan_id = $loan->id; 
                $payment->repayment_amount = $request->repayment_amount;
                $payment->save();

                $count = $this->getPayemntCount($loan->id);
                $pending = (int)$loan->tenure - $count;
                if($pending == 0){
                    $loan->status = 2;
                    $loan->save();
                }

                return response()->json(['Success, Pending Installments - '.$pending]);

            }else{
                return response()->json(['Wrong amount, Your installment amount is '.$installment_amount]);
            }
            
        } catch (\Exception $e) {
            return response()->json(['Something went wrong']);
        }
        
    }

    protected function getPayemntCount($loan_id): int {
        $count = LoanRepayment::where('user_id', JWTAuth::user()->id)->where('loan_id', $loan_id)->count();
        return $count;
    }
}
