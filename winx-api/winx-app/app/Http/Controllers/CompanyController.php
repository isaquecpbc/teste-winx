<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use App\Http\Resources\Company as CompanyResource;

/**
 * @OA\Schema(
 *     schema="Company",
 *     type="object",
 *     title="Company",
 *     required={"name"},
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Winx"),
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CompanyStoreRequest",
 *     type="object",
 *     title="Company Store Request",
 *     required={"name"},
 *     properties={
 *         @OA\Property(property="name", type="string", example="Winx"),
 *     }
 * ) 
 * 
 */
class CompanyController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/companies",
     *     summary="Get list of companies",
     *     tags={"Companies"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Company"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function index()
    {
        $company = Company::get();

        return $this->sendResponse(CompanyResource::collection($company), 'Companies Retrieved Successfully.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * @OA\Post(
     *     path="/api/companies",
     *     summary="Create a new companies",
     *     tags={"Companies"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CompanyStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Company")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function store(Request $request)
    {
        $input = $request->all();
    
        // Validação inicial
        $validator = Validator::make($input, [
            'name'    => ['required', 'string', 'max:90'],
        ]);
    
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
    
        $company = Company::create($input);
    
        return $this->sendResponse(new CompanyResource($company), 'Company Created Successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Company::findOrFail($id);
  
        if (is_null($company)) {
            return $this->sendError('Company not found.');
        }
   
        return $this->sendResponse(new CompanyResource($company), 'Company Retrieved Successfully.');
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
     * @OA\Put(
     *     path="/api/companies/{id}",
     *     summary="Update an existing company",
     *     tags={"Companies"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the company to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CompanyStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Company")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
   
        $validator = Validator::make($input, [
            'name'    => ['required', 'string', 'max:90'],
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $company = Company::findOrFail($id);
          
        $company->name   = $input['name'];
        $company->save();
   
        return $this->sendResponse(new CompanyResource($company), 'Company Updated Successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/companies/{id}",
     *     summary="Delete a company",
     *     tags={"Companies"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the company to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Company deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function destroy($id)
    {
        // get company
        $company = Company::findOrFail($id);
        $users = $company->users;
    
        foreach ($users as $user) {
            // delete employee info company
            $user->employee()->delete();
        }
    
        // delete users company
        $company->users()->delete();
    
        // delete company
        $company->delete();
       
        return $this->sendResponse([], 'Company and related Users and Employees deleted successfully.');
    }
}