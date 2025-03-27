<?php 
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all(), 200);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return $this->successResponse($user,200);
    }

    public function store(Request $request)
    {
        dd($request->all()); 
    
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        $user->update($request->only(['name', 'email']));
        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }


    public function update(Request $request, $id)
    {
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,  // Allow current user's email
            'password' => 'nullable|string|min:3', // Password field is optional but must be hashed
        ]);
    
        // Find user
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse("User not found.", 404);
        }
    
        // If password is provided, hash it before saving
        if ($request->has('password')) {
            $validated['password'] = bcrypt($request->password);
        }
    
        // Update user with validated data (including hashed password if provided)
        $user->update($validated);
    
        // Return success response
     return $this->successResponse($user,201);
    }
    


    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return $this->successResponse($user);
    }
}
