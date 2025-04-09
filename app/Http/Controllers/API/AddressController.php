<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class AddressController extends Controller
{   
    public function getAllAddress(Request $request){

        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $address = Address::when($user->role === 'customer', function($query) use ($user){
            return $query->where('user_id',$user->user_id);
        })->when($user->role === 'admin',function ($query){
            return $query;
        })
        ->where('status','!=',0)
        ->get();

        return response()->json([
            'message' => 'Address retrieved successfully!',
            'address' => $address
        ]);
    }

    public function createAddress(CreateAddressRequest $request)
    {
        // Get authenticated user
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::beginTransaction();

        try {

            if ($request->is_default) {
                Address::where('user_id', $user->user_id)->update(['is_default' => false]);
            }

            $address = Address::create([
                'user_id' => $user->user_id,
                'user_name' => $request->user_name,
                'phone' => $request->phone,
                'ward' => $request->ward,
                'district' => $request->district,
                'province' => $request->province,
                'type' => $request->type,
                'is_default' => $request->is_default,
                'specific_address' => $request->specific_address,
                'status' => $request->status,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Address created successfully',
                'address' => $address
            ], 201);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('DB Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Database Error!',
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('General Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Server Error!',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateAddress(UpdateAddressRequest $request, $addressId)
    {
        // Get authenticated user
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::beginTransaction();

        try {

            $address = Address::findOrFail($addressId);

            if ($request->user()->user_id !== $address->user_id) {
                return response()->json(['error', 'Unauthorized'], 403);
            }

            $address->update($request->validated());

            DB::commit();

            return response()->json([
                'message' => 'Address updated successfully',
                'address' => $address
            ], 201);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('DB Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Database Error!',
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('General Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Server Error!',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAddress(Request $request, Address $address)
    {
        // Get authenticated user
        $user = $request->user();

        // Verify that user_id belongs to the authenticated user
        if ($address->user_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthenticated'], 403);
        }

        try {
            // Begin SQL transaction
            DB::beginTransaction();

            // Update status to 0 (mark as deleted)
            $address->update([
                'status' => 0
            ]);

            DB::commit(); // Commit changes
            return response()->json(['message' => 'Address deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on failure
            return response()->json([
                'error' => 'Server Error!',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
