<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Validator;
use Symfony\Component\HttpFoundation\Response;
use function PHPUnit\Framework\exactly;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Product"},
     *     path="/api/products/index",
     *     @OA\Parameter(
     *      name="page",
     *      in="query",
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *        @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *        @OA\Parameter(
     *      name="detail",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Response(response="200", description="List Products.")
     * )
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $input = $request->all();
        $filters = [
            'id' => 'id',
            'name' => 'name',
            'detail' =>'detail',
        ];

        $products=Product::where(function ($query) use ($input, $filters) {
            foreach ($filters as $column => $key) {
                $query->when(\Arr::get($input, $key), function ($query, $value) use ($column) {
                    $query->where($column,"LIKE", "%$value%");
                });
            }
        })->paginate(2);

        return response()->json($products);
    }

    /**
     * @OA\Post(
     ** path="/api/products/store",
     *   tags={"Product"},
     *
     *   @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="detail",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="Image to upload",
     *                     property="file",
     *                     type="file",
     *                ),
     *                 required={"file"}
     *             )
     *         )
     *     ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *    )
     *)
     **/
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $imageName = uniqid().'.'.$request->file->extension();
        $path = public_path('images');
        $request->file->move($path, $imageName);
        $input['image']= '/images/'.$imageName;
        $product = Product::create($input);
        return response()->json([
            "message" => "Product created successfully.",
            "data" => $product
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/products/show/{id}",
     *      tags={"Product"},
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);
        if (is_null($product)) {
            return response()->json(["message"=>'Product not found.'],Response::HTTP_BAD_REQUEST);
        }
        return response()->json([
            "message" => "Product retrieved successfully.",
            "data" => $product
        ]);
    }
    /**
     * @OA\Post(
     *      path="/api/products/update/{id}",
     *      tags={"Product"},
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *       @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="detail",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="Image to upload",
     *                     property="file",
     *                     type="file",
     *                ),
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful updated.",
     *          @OA\MediaType(
     *           mediaType="application/json",
     *          )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,int $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $product = Product::find($id);
        $product->name = $input['name'];
        $product->detail = $input['detail'];
        if($request->file) {
            $imageName = uniqid() . '.' . $request->file->extension();
            $filePath=public_path().$product->image;
            $path = public_path('images');
            $request->file->move($path, $imageName);
            $product->image='/images/'.$imageName;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $product->save();
        return response()->json([
            "message" => "Product updated successfully.",
            "data" => $product
        ],Response::HTTP_OK);
    }
    /**
     * @OA\Delete(
     *      path="/api/products/destroy/{id}",
     *      tags={"Product"},
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        $filePath=public_path().$product->image;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $product->delete();
        return response()->json([
            "message" => "Product deleted successfully."
        ],Response::HTTP_OK);
    }
}
