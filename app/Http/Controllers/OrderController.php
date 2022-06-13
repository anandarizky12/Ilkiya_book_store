<?php

namespace App\Http\Controllers;
use App\Notifications\StatusNotification;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Shipping;
use Carbon\Carbon;
use App\Models\User;
use PDF;
use Notification;
use Helper;
use Illuminate\Support\Str;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders=Order::orderBy('id','DESC')->paginate(10);
        return view('admin.order.index')->with('orders',$orders);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */



     // midtrans in here yo 
    public function store(Request $request)
    {
        // $this->validate($request,[
        //     'first_name'=>'string|required',
        //     'last_name'=>'string|required',
        //     'address'=>'string|required',
        //     'coupon'=>'nullable|numeric',
        //     'phone'=>'numeric|required',
        //     'post_code'=>'string|nullable',
        //     'email'=>'string|required'
        // ]);
        // return $request->all();

             \Midtrans\Config::$serverKey = 'SB-Mid-server-Ld1CfyKdVgAqZjGR4-TUonaN';
            // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
            \Midtrans\Config::$isProduction = false;
            // Set sanitization on (default)
            \Midtrans\Config::$isSanitized = true;
            // Set 3DS transaction for credit card to true
            \Midtrans\Config::$is3ds = true;
            
            $params = array(
                'transaction_details' => array(
                    'order_id' => rand(),
                    'gross_amount' => 10000,
                ),
                'customer_details' => array(
                    'first_name' => 'budi',
                    'last_name' => 'pratama',
                    'email' => 'budi.pra@example.com',
                    'phone' => '08111222333',
                ),
            );
 
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return view('client.pages.checkout')->with('snap_token',$snapToken);

        // if(empty(Cart::where('user_id',auth()->user()->id)->where('order_id',null)->first())){
        //     request()->session()->flash('error','Cart is Empty !');
        //     return back();
        // }
     

        // $order=new Order();
        // $order_data=$request->all();
        // $order_data['order_number']='ORD-'.strtoupper(Str::random(10));
        // $order_data['user_id']=$request->user()->id;
        // $order_data['shipping_id']=$request->shipping;
        // $shipping=Shipping::where('id',$order_data['shipping_id'])->pluck('price');
        // // return session('coupon')['value'];
        // $order_data['sub_total']=Helper::totalCartPrice();
        // $order_data['quantity']=Helper::cartCount();
        // if(session('coupon')){
        //     $order_data['coupon']=session('coupon')['value'];
        // }
        // if($request->shipping){
        //     if(session('coupon')){
        //         $order_data['total_amount']=Helper::totalCartPrice()+$shipping[0]-session('coupon')['value'];
        //     }
        //     else{
        //         $order_data['total_amount']=Helper::totalCartPrice()+$shipping[0];
        //     }
        // }
        // else{
        //     if(session('coupon')){
        //         $order_data['total_amount']=Helper::totalCartPrice()-session('coupon')['value'];
        //     }
        //     else{
        //         $order_data['total_amount']=Helper::totalCartPrice();
        //     }
        // }
        // // return $order_data['total_amount'];
        // $order_data['status']="new";
        // if(request('payment_method')=='paypal'){
        //     $order_data['payment_method']='paypal';
        //     $order_data['payment_status']='paid';
        // }
        // else{
        //     $order_data['payment_method']='cod';
        //     $order_data['payment_status']='Unpaid';
        // }
        // $order->fill($order_data);
        // $status=$order->save();
        // if($order)
        // // dd($order->id);
        // $users=User::where('role','admin')->first();
        // $details=[
        //     'title'=>'New order created',
        //     'actionURL'=>route('admin.order.show',$order->id),
        //     'fas'=>'fa-file-alt'
        // ];
        // Notification::send($users, new StatusNotification($details));
        // if(request('payment_method')=='paypal'){
        //     return redirect()->route('payment')->with(['id'=>$order->id]);
        // }
        // else{
        //     session()->forget('cart');
        //     session()->forget('coupon');
        // }
        // Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => $order->id]);

        // // dd($users);        
        // request()->session()->flash('success','Your product successfully placed in order');
        // return redirect()->route('home');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order=Order::find($id);
        // return $order;
        return view('admin.order.show')->with('order',$order);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function pdf(Request $request){
        $order=Order::getAllOrder($request->id);
        // return $order;
        $file_name=$order->order_number.'-'.$order->first_name.'.pdf';
        // return $file_name;
        $pdf=PDF::loadview('backend.order.pdf',compact('order'));
        return $pdf->download($file_name);
    }

    public function incomeChart(Request $request){
        $year=\Carbon\Carbon::now()->year;
        // dd($year);
        $items=Order::with(['cart'])->whereYear('created_at',$year)->where('status','delivered')->get()
            ->groupBy(function($d){
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });
            // dd($items);
        $result=[];
        foreach($items as $month=>$item_collections){
            foreach($item_collections as $item){
                $amount=$item->cart->sum('amount');
                // dd($amount);
                $m=intval($month);
                // return $m;
                isset($result[$m]) ? $result[$m] += $amount :$result[$m]=$amount;
            }
        }
        $data=[];
        for($i=1; $i <=12; $i++){
            $monthName=date('F', mktime(0,0,0,$i,1));
            $data[$monthName] = (!empty($result[$i]))? number_format((float)($result[$i]), 2, '.', '') : 0.0;
        }
        return $data;
    }


}
