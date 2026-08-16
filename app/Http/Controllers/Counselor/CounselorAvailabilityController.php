<?php

namespace App\Http\Controllers\Counselor;


use App\Http\Controllers\Controller;
use App\Http\Requests\Counselor\StoreAvailabilityRequest;
use App\Http\Requests\Counselor\UpdateAvailabilityRequest;
use App\Services\Counselor\CounselorAvailabilityService;
use Illuminate\Http\Request;


class CounselorAvailabilityController extends Controller
{


    public function __construct(
        private CounselorAvailabilityService $service
    ) {
    }



    public function store(
        StoreAvailabilityRequest $request
    ) {

        $counselorId =
            $request->user()->counselor->id;



        $this->service->saveSchedule(
            $counselorId,
            $request->schedule
        );


        return response()->json([

            'status' => true,

            'message' => 'تم حفظ جدول التواجد بنجاح'

        ]);

    }




    public function index(Request $request)
    {

        $data = $this->service->getSchedule(
            $request->user()->counselor->id
        );


        return response()->json([

            'status' => true,

            'data' => $data

        ]);

    }



    public function update(
        UpdateAvailabilityRequest $request,
        string $day
    ) {

        $counselorId =
            $request->user()
                ->counselor
                ->id;


        $availability =
            $this->service->updateDay(
                $counselorId,
                $day,
                $request->validated()
            );


        return response()->json([

            'status' => true,

            'message' => 'تم تعديل وقت التواجد بنجاح',

            'data' => $availability

        ]);

    }


    public function destroy(
    Request $request,
    string $day
)
{

    $this->service->deleteDay(
        $request->user()->counselor->id,
        $day
    );


    return response()->json([
        'status'=>true,
        'message'=>'تم حذف اليوم'
    ]);

}


}