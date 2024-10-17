<?php
  
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\gift\movgift;
use App\gift\giftutenti;

  
class PDFController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generatePDF(Request $request)
    {
        $filename = time();

        $gift = $request->gift;
        $start = $request->start;
        $end = $request->end;

        $reports = movgift::where('gift', $gift)->whereBetween('data_movimento', [$start, $end])->get();
        $user = giftutenti::where('gift', $gift)->first();

        $data = [
            'user' => $user,
            'reports' => $reports
        ];
            
        $pdf = PDF::loadView('GiftReportDate', $data);
       // \Storage::put('public/pdf/'.$filename.'.pdf', $pdf->output());

       \Storage::disk('public')->put('public/pdf/'.$filename.'.pdf', $pdf->output());

     //   return \Storage::download('public/pdf/'.$filename.'.pdf');
     
        $obj = new \StdClass();
      //  $obj->link = 'http://localhost:8000/storage/public/pdf/'.$filename.'.pdf';
      $obj->link = 'https://fidelityrest.bennysrl.it/storage/public/pdf/'.$filename.'.pdf';

        //return $pdf->download('itsolutionstuff.pdf');

        return response()->json($obj, 200);
    }

    public function reportGifAll(Request $request)
    {
        $filename = time();

        $gift = $request->gift;

        $reports = movgift::where('gift', $gift)->get();
        $user = giftutenti::where('gift', $gift)->first();

        $data = [
            'user' => $user,
            'reports' => $reports
        ];
            
        $pdf = PDF::loadView('GiftReportDate', $data)->setPaper('a4', 'landscape');
       // \Storage::put('public/pdf/'.$filename.'.pdf', $pdf->output());

       \Storage::disk('public')->put($filename.'.pdf', $pdf->output());

     //   return \Storage::download('public/pdf/'.$filename.'.pdf');
     
        $obj = new \StdClass();
      //  $obj->link = 'http://localhost:8000/storage/public/pdf/'.$filename.'.pdf';
      $obj->link = 'https://fidelityrest.bennysrl.it/storage/'.$filename.'.pdf';

        //return $pdf->download('itsolutionstuff.pdf');

        return response()->json($obj, 200);
    }
}