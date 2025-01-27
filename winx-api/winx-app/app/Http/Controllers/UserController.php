<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use App\Http\Resources\User as UserResource;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     title="User Resource",
 *     required={"name", "email", "password", "company_id"},
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="BoJack Horseman"),
 *         @OA\Property(property="email", type="string", example="bojack@horse.men"),
 *         @OA\Property(property="password", type="string", example="admin@adm"),
 *         @OA\Property(property="company_id", type="integer", example=1),
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="UserStoreRequest",
 *     type="object",
 *     title="User Store Request",
 *     required={"name", "email", "password", "company_id"},
 *     properties={
 *         @OA\Property(property="name", type="string", example="BoJack Horseman"),
 *         @OA\Property(property="email", type="string", example="bojack@horse.men"),
 *         @OA\Property(property="password", type="string", example="admin@adm"),
 *         @OA\Property(property="company_id", type="integer", example=1),
 *     }
 * ) 
 * 
 */
class UserController extends BaseController
{

    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get list of users",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function index()
    {
        $users = User::get();

        return $this->sendResponse(UserResource::collection($users), 'Users Retrieved Successfully.');
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
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
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
            'name'              => ['required', 'string', 'max:150'],
            'email'             => ['required', 'email', 'max:150'],
            'password'          => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/[A-Z]/',  // Must contain at least one uppercase letter
                'regex:/[0-9]/',  // Must contain at least one number
                'regex:/[@$!%*?&]/' // Must contain at least one special character
            ],
            'company_id'        => ['required', 'exists:companies,id'],
        ]);
    
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input['password'] = bcrypt($input['password']);
    
        $user = User::create($input);
    
        return $this->sendResponse(new UserResource($user), 'User Created Successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
  
        if (is_null($user)) {
            return $this->sendError('User not found.');
        }
   
        return $this->sendResponse(new UserResource($user), 'User Retrieved Successfully.');
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
     *     path="/api/users/{id}",
     *     summary="Update an existing user",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
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
            'name'              => ['required', 'string', 'max:150'],
            'email'             => ['required', 'email', 'max:150'],
            'password'          => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/[A-Z]/',  // Must contain at least one uppercase letter
                'regex:/[0-9]/',  // Must contain at least one number
                'regex:/[@$!%*?&]/' // Must contain at least one special character
            ],
            'company_id'        => ['required', 'exists:companies,id'],
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $user = User::findOrFail($id);
    
        $password = bcrypt($input['password']);
          
        $user->name             = $input['name'];
        $user->email            = $input['email'];
        $user->password         = bcrypt($input['password']);
        $user->company_id       = $input['company_id'];
        $user->save();
   
        return $this->sendResponse(new UserResource($user), 'User Updated Successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->admin) {
            return $this->sendError('Validation Error.', ['User Admin cannot be deleted.']);
        }

        // delete employee info
        $user->employee()->delete();

        // delete user
        $user->delete();
   
        return $this->sendResponse([], 'User and related Employees Deleted Successfully.');
    }
}