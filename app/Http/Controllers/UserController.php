<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use DB;
use Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::orderBy('id','desc')->paginate(5);

        return view('users.index',compact('users'))
        ->with('i',($request->input('page',1)-1)*5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('display_name','id');
        return view('users.create',compact('roles'));//return the view with the list of roles
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'email'=>'required|email|max:255,unique:users',
            'password'=>'required|confirmed|min:6',
            'roles'=>'required'
        ]);
        
            $input = $request->only('name','email','password');
            $input['password'] = Hash::make($input['password']);//hash the pwd
            $user = User::create($input);//create user table entry

            //attach the selected roles to the user
            foreach($request->input('roles')as $key => $value){
                $user->attachRole($value);
            }
            return redirect()->route('users.index')->with('success','User Created successfully');
        }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('users.show',compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::get();//get all roles
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('users.edit',compact('user','roles','userRoles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this ->validate($request,[
            'name'=>'required|max:255',
            'email'=>'required|email|unique:users,email'.$id,
            'password'=>'confirmed',
            'roles'=>'required'
        ]);
        $input = $request->only('name','email','password');
        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);//update the user password
        }else{
            $input = array_except($input,array('password'));//remove the password from the input array
        }
        
        $user = User::find($id);
        $user->update($input);//update the user info
        //delete all roles currently linked to this user
        DB::table('role_user')->where('user_id',$id)->delete();
        //then attach new roles to the user
        foreach($request->input('roles')as $key => $value){
            $user->attachRole($value);
        };
        return redirect()->route('users.index')->with('success','User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $user = User::find($id);
       $user->delete();
        return redirect()->route('users.index')
        ->with('success','User deleted successfully');
    }
}
