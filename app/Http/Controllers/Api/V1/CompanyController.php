<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Jobs\UserCreated;
use App\Models\Company;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class CompanyController extends Controller
{
    use ApiResponses;

    public function index(): JsonResponse
    {
        $companies = CompanyResource::collection(Company::all());
        return $this->success($companies, 'Done');
    }

    public function show(): JsonResponse
    {
        $company=Company::findOrFail(request('id'));
        return $this->success(new CompanyResource($company), 'Done');
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $request->validated($request->all());

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'type' => 'company'
        ]);

        Company::create([
            'display_name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
            'user_id' => $user->id
        ]);

        UserCreated::dispatch($user->toArray())->onQueue('messageing');

        return $this->success(new UserResource($user), 'Created', Response::HTTP_CREATED);
    }

    public function update(Request $request): JsonResponse
    {
        $company = Company::findOrFail(request('id'));

        $company->update([
        'display_name' => $request->display_name,
        'phone'        => $request->phone,
        'address'      => $request->address,

        ]);
        $user=User::find($company->user_id);
        $user->update([
            'name'     => $request->name,

        ]);
        if($request->password){
            $user->update(['password'=>bcrypt($request->password)]);
        }
        if($request->email){
            $user->update(['email'=>$request->email]);
        }
        return $this->success(new CompanyResource($company), 'Done', Response::HTTP_ACCEPTED);
    }

    public function uploadProfile(Request $request, Company $company): JsonResponse
    {
        $filename =  time() . '.' . 'png';
        $imagename =  $this->uploadImage($request->profile, $filename, 'profiles');
        $image_link = 'images/profiles/' . $imagename;
        $company->update([
            'profile' => asset($image_link)
        ]);

        return $this->success(new CompanyResource($company), 'Done', Response::HTTP_ACCEPTED);
    }

    public function destroy(): JsonResponse
    {
        $company = Company::findOrFail(request('id'));
        $user = User::where('id', $company->user_id);
        $user->delete();
        $company->delete();

        return $this->success(null, 'Deleted', Response::HTTP_NO_CONTENT);
    }
}
