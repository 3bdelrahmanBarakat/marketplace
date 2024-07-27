<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateClient;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\User;
use App\Traits\ApiResponses;
use App\Traits\ImageTrait;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends Controller
{
    use ApiResponses;
    use ImageTrait;

    /**
     * all clients.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $clients = ClientResource::collection(Client::all());
        return $this->success($clients, 'Done');
    }

    /**
     * Show specific client.
     *
     * @param Client $client
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $client = Client::findOrFail(request('id'));
        return $this->success(new ClientResource($client), 'Done');
    }

    /**
     * Update specific client.
     *
     * @param Request $request
     * @param Client $client
     * @return JsonResponse
     */
    public function update(UpdateClient $request): JsonResponse
    {
        $client = Client::findOrFail(request('id'));
        $client->update([
        'display_name' => $request->display_name,
        'phone'        => $request->phone,
        'address'      => $request->address,

        ]);
        $user=User::find($client->user_id);
        $user->update([
            'name'     => $request->name,

        ]);
        if($request->password){
            $user->update(['password'=>bcrypt($request->password)]);
        }
        if($request->email){
            $user->update(['email'=>$request->email]);
        }

        return $this->success(new ClientResource($client), 'Done', Response::HTTP_ACCEPTED);
    }

    /**
     * Upload profile image for specific client.
     *
     * @param Request $request
     * @param Client $client
     * @return JsonResponse
     */
    public function uploadProfile(Request $request): JsonResponse
    {
        $filename =  time() . '.' . 'png';
        $imagename =  $this->uploadImage($request->profile, $filename, 'profiles');
        $image_link = 'images/profiles/' . $imagename;
        $client=Client::findorfail(request('id'));
        $client->update([
            'profile' => asset($image_link)
        ]);

        return  $this->success(new ClientResource($client), 'Done', Response::HTTP_ACCEPTED);
    }

    public function deleteProfilePhoto(): JsonResponse
    {
        $user_id = auth()->user()->id;
        $client = Client::where('user_id', $user_id)->update([
            'profile' => null
        ]);
        return  $this->success(null, 'Image has been deleted successfully', Response::HTTP_ACCEPTED);
    }


    /**
     * Delete specific client.
     *
     * @param Client $client
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        $client = Client::findOrFail(request('id'));
        $user = User::where('id', $client->user_id);
        $user->delete();
        $client->delete();
        return $this->success(null, 'Deleted', Response::HTTP_NO_CONTENT);
    }


 
}
