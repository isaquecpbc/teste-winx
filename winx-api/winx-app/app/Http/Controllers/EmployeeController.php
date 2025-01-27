<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Http\Controllers\BaseController as BaseController;
use App\Http\Resources\Employee as EmployeeResource;
use App\Jobs\ImportEmployeesJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

/**
 * @OA\Schema(
 *     schema="Employee",
 *     type="object",
 *     title="Employee",
 *     required={"responsibility", "admission_at", "phone", "user_id"},
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="responsibility", type="string", example="Actor"),
 *         @OA\Property(property="admission_at", type="string", format="date", example="2024-01-15"),
 *         @OA\Property(property="phone", type="integer", example="47977664433"),
 *         @OA\Property(property="user_id", type="integer", example=1),
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="EmployeeStoreRequest",
 *     type="object",
 *     title="Employee Store Request",
 *     required={"responsibility", "admission_at", "phone", "user_id"},
 *     properties={
 *         @OA\Property(property="responsibility", type="string", example="Actor"),
 *         @OA\Property(property="admission_at", type="string", format="date", example="2024-01-15"),
 *         @OA\Property(property="phone", type="integer", example="47977664433"),
 *         @OA\Property(property="user_id", type="integer", example=1),
 *     }
 * ) 
 * 
 */
class EmployeeController extends BaseController
{

    protected $companyId;

    public function __construct()
    {
        $this->companyId = auth()->user()->company_id;
    }

    /**
     * @OA\Get(
     *     path="/api/employees",
     *     summary="Get list of employees",
     *     tags={"Employees"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="responsibility",
     *         in="query",
     *         required=false,
     *         description="Filter employees by their responsibility",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="admission_at",
     *         in="query",
     *         required=false,
     *         description="Filter employees by admission date (format: yyyy-mm-dd)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         required=false,
     *         description="Filter employees by their phone number",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Employee"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Employee::with('user')
            ->whereIn('user_id', function($query) {
                $query->select('id')
                    ->from('users')
                    ->where('company_id', $this->companyId);
            });

        // set filters
        if ($request->has('responsibility')) {
            $query->where('responsibility', 'like', '%' . $request->input('responsibility') . '%');
        }

        if ($request->has('admission_at')) {
            $query->whereDate('admission_at', $request->input('admission_at'));
        }

        if ($request->has('phone')) {
            $phone = preg_replace('/\D/', '', $request->input('phone')); // Remover caracteres não numéricos
            $query->where('phone', 'like', '%' . $phone . '%');
        }

        // et query
        $employees = $query->get();

        return $this->sendResponse(EmployeeResource::collection($employees), 'Employees Retrieved Successfully.');
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
     *     path="/api/employees",
     *     summary="Create a new employee",
     *     tags={"Employees"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EmployeeStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Employee")
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
            'responsibility'    => ['required', 'string', 'max:90'],
            'admission_at'      => ['required', 'date'],
            'phone'             => ['required', 'string', 'regex:/^\d{11}$/'], // Must be exactly 11 digits
            'user_id'           => ['required', 'exists:users,id'],
        ]);
    
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
    
        $employee = Employee::create($input);
    
        return $this->sendResponse(new EmployeeResource($employee), 'Employee Created Successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/employees/import",
     *     summary="Create new employees by uploading a CSV file",
     *     tags={"Employees"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"csv_file"},
     *                 @OA\Property(
     *                     property="csv_file",
     *                     type="string",
     *                     format="binary",
     *                     description="Choose a CSV file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation. File uploaded and processing started.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Upload feito com sucesso. O processamento está em andamento.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function storeByCSV(Request $request)
    {
        // validate csv file
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        // save temp CSV
        $path = $request->file('csv_file')->store('uploads');

        // get companyId by logged user
        $companyId = auth()->user()->company_id;

        // dispatch job queue
        ImportEmployeesJob::dispatch($path, $companyId);

        return response()->json(['message' => 'Upload feito com sucesso. O processamento está em andamento.'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $employee = Employee::findOrFail($id);
  
        if (is_null($employee)) {
            return $this->sendError('Employee not found.');
        }
   
        return $this->sendResponse(new EmployeeResource($employee), 'Employee Retrieved Successfully.');
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
     *     path="/api/employees/{id}",
     *     summary="Update an existing employee",
     *     tags={"Employees"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the employee to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EmployeeStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Employee")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found"
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
            'responsibility'    => ['required', 'string', 'max:90'],
            'admission_at'      => ['required', 'date'],
            'phone'             => ['required', 'string', 'regex:/^\d{11}$/'], // Must be exactly 11 digits
            'user_id'           => ['required', 'exists:users,id'],
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $employee = Employee::findOrFail($id);
          
        $employee->responsibility   = $input['responsibility'];
        $employee->admission_at     = $input['admission_at'];
        $employee->phone            = $input['phone'];
        $employee->user_id          = $input['user_id'];
        $employee->save();
   
        return $this->sendResponse(new EmployeeResource($employee), 'Employee Updated Successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/employees/{id}",
     *     summary="Delete a employee",
     *     tags={"Employees"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the employee to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Employee deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
   
        return $this->sendResponse([], 'Employee Deleted Successfully.');
    }
}