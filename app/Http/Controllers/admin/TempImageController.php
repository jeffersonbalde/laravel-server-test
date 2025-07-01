<?php

namespace App\Http\Controllers\admin;

use App\Models\TempImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Faker\Provider\Image;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TempImageController extends Controller
{
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            "image" => "required|mimes:png,jpg,jpeg,gif"
        ]);

        if($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors("image"),
            ]);
        }

        $image = $request->image;

        if(!empty($image)) {
            $extension = $image->getClientOriginalExtension();

            $imageName = strtotime("now").".".$extension;

            $model = new TempImage();
            $model->name = $imageName;
            $model->save();

            $image->move(public_path("uploads/temp"), $imageName);
            
            $sourcePath = public_path("uploads/temp/".$imageName);
            $destinationPath = public_path("uploads/temp/thumb/".$imageName);
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($sourcePath);
            $image->coverDown(300, 300);
            $image->save($destinationPath);

            return response()->json([
                "status" => true,
                "message" => "Image uploaded successfully",
                "data" => $model,
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Image is required",
            ]);
        }
    }
}
