<?php

namespace App\Http\Controllers\admin;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::orderBy("created_at", "DESC")->get();

        return response()->json([
            "status" => true,
            "data" => $services,
            // "message" => "Services fetched successfully",
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required",
            "slug" => "required|unique:services,slug",
            "status" => "required|in:0,1",
        ]);

        if($validator->fails()) {
            return response()->json([
                "status"=> false,
                "errors"=> $validator->errors(),
            ]);
        }

        $model = new Service();
        $model->title = $request->title;
        $model->short_description = $request->short_description;
        $model->slug = Str::slug($request->slug);
        $model->content = $request->content;
        $model->status = (int) $request->status;
        $model->save();

        // if($request->imageId > 0) { 
        //     $tempImage = TempImage::find($request->imageId);

        //     if($tempImage != null) {
        //         $extArray = explode(".",$tempImage->name);
        //         $ext = last($extArray);

        //         $fileName = strtotime("now").$model->id.'.'.$ext;


        //         // // small service picture
        //         // $sourcePath = public_path("uploads/temp/".$tempImage->name);
        //         // $destinationPath = public_path("uploads/services/small/".$fileName);
        //         // $manager = new ImageManager(Driver::class);
        //         // $image = $manager->read($sourcePath);
        //         // $image->coverDown(500, 600);
        //         // $image->save($destinationPath);

        //         // // large service picture
        //         // $destinationPath = public_path("uploads/services/large/".$fileName);
        //         // $manager = new ImageManager(Driver::class);
        //         // $image = $manager->read($sourcePath);
        //         // $image->scaleDown(1200);
        //         // $image->save($destinationPath);

        //         $model->image = $fileName;
        //         $model->save();
        //     }
        // }

        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage != null) {
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);
                
                // create unique filename with model ID
                $fileName = strtotime("now") . $model->id . '.' . $ext;

                // Move from temp to final storage
                File::move(
                    public_path("uploads/temp/" . $tempImage->name),
                    public_path("uploads/services/" . $fileName)
                );

                // Save image path
                $model->image = $fileName;
                $model->save();

                // Clean temp DB
                $tempImage->delete();
            }
        }


        return response()->json([
            "status" => true,
            "message" => "Service created successfully",
            // "data" => $model,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $service = Service::find($id);

        if($service == null) {
            return response()->json([
                "status" => false,
                "message" => "Service not found",
            ]);
        }

        return response()->json([
            "status" => true,
            "data" => $service,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if($service == null) {
            return response()->json([
                "status" => false,
                "message" => "Service not found",
                // "errors"=> $service->errors(),
                ]);
        }

        $validator = Validator::make($request->all(), [
            "title" => "required",
            "slug" => "required|unique:services,slug," . $id,
        ]);

        if($validator->fails()) {
            return response()->json([
                "status"=> false,
                "errors"=> $validator->errors(),
            ]);
        }

        $service->title = $request->title;
        $service->short_description = $request->short_description;
        $service->slug = Str::slug($request->slug);
        $service->content = $request->content;
        $service->status = $request->status;
        $service->save();

        // if($request->imageId > 0) { 

        //     $oldImage = $service->image;
        //     $tempImage = TempImage::find($request->imageId);

        //     if($tempImage != null) {
        //         $extArray = explode(".",$tempImage->name);
        //         $ext = last($extArray);

        //         $fileName = strtotime("now").$service->id.'.'.$ext

        //         // small service picture
        //         // $sourcePath = public_path("uploads/temp/".$tempImage->name);
        //         // $destinationPath = public_path("uploads/services/small/".$fileName);
        //         // $manager = new ImageManager(Driver::class);
        //         // $image = $manager->read($sourcePath);
        //         // $image->coverDown(500, 600);
        //         // $image->save($destinationPath);

        //         // large service picture
        //         // $destinationPath = public_path("uploads/services/large/".$fileName);
        //         // $manager = new ImageManager(Driver::class);
        //         // $image = $manager->read($sourcePath);
        //         // $image->scaleDown(1200);
        //         // $image->save($destinationPath);

        //         $service->image = $fileName;
        //         $service->save();

        //         // if($oldImage != "") {
        //         //     File::delete(public_path("uploads/services/large/".$oldImage));
        //         //     File::delete(public_path("uploads/services/small/".$oldImage));
        //         // }
        //     }
        // }

        if ($request->imageId > 0) {
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage != null) {
                // Delete old image (if exists)
                if (!empty($service->image)) {
                    $oldPath = public_path("uploads/services/" . $service->image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);
                $fileName = strtotime("now") . $service->id . '.' . $ext;

                File::move(
                    public_path("uploads/temp/" . $tempImage->name),
                    public_path("uploads/services/" . $fileName)
                );

                $service->image = $fileName;
                $service->save();

                $tempImage->delete();
            }
        }



        return response()->json([
            "status" => true,
            "message" => "Service updated successfully",
            // "data" => $model,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = Service::find($id);      

        if($service == null) {
            return response()->json([
                "status" => false,
                "message" => "Service not found",
            ]);
        }

        $service->delete();

        return response()->json([
            "status" => true,
            "message"=> "Service deleted successfully",
        ]);
    }
}
