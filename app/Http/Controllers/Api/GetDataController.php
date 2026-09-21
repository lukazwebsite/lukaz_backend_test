<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin\Brand;
use App\Models\Api\Country;
use App\Models\Api\District;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GetDataController extends Controller
{

    public function getCountries()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return response()->json($countries);
    }


    public function getDistrictsByCountry($countryId)
    {
        $districts = District::where('country_id', $countryId)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($districts);
    }


    public function getDistrictById($districtId)
    {
        $district = District::findOrFail($districtId);
        return response()->json($district);
    }


    public function getThanaById($districtId)
    {
        $thans = DB::table('police_stations')->where('district_id', $districtId)->get();
        return response()->json($thans);
    }



    /**
     *
     * Get all brand
     */

    public function getBrand(Request $request)
    {
        $countries = Brand::get();
        return response()->json($countries);
    }


}
