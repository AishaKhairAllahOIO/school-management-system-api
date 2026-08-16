<?php

namespace App\Http\Controllers\Counselor;


use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Counselor\StoreAvailabilityRequest;
use App\Http\Requests\Counselor\UpdateAvailabilityRequest;
use App\Services\Counselor\CounselorAvailabilityService;
use Illuminate\Http\Request;


class CounselorAvailabilityController extends Controller
{
  use ApiResource;

    public function __construct(private CounselorAvailabilityService $service) {}



    public function store(StoreAvailabilityRequest $request)
    {

        $counselorId = $request->user()->id;



       $times = $this->service->saveSchedule(
            $counselorId,
            $request->schedule
        );

        return $this->successResponse($times,'Available times saved successfuly.',201);
       
    }
    public function index(Request $request)
    {

        $data = $this->service->getSchedule(
            $request->user()->id
        );


        return response()->json([

            'status' => true,

            'data' => $data

        ]);

    }
    public function update(UpdateAvailabilityRequest $request, string $day)
    {

        $counselorId = $request->user()->id;


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
    public function destroy(Request $request, string $day)
    {

        $this->service->deleteDay(
            $request->user()->id,
            $day
        );


        return response()->json([
            'status' => true,
            'message' => 'تم حذف اليوم'
        ]);

    }


}