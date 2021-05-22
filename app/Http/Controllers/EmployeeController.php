<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Employee::all();

        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->flashExcept(['password', 'password_confirmation', 'photo']);

        $this->validate($request, [
            'name' => 'required',
            'code' => 'required',
            'number' => 'required',
            'email' => 'required|unique:employees,email,except,id',
            'password' => 'required|confirmed',
            'wallet_balance' => 'required',
        ]);

        $employee = Employee::create([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'number' => $request->input('number'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'wallet_balance' => $request->input('wallet_balance'),
        ]);

        if($request->input('wallet_balance') > 0) {
            $payment = Payment::create([
                'date' => $request->input('date'),
                'payment_mode' => $request->input('payment_mode'),
                'amount' => $request->input('wallet_balance'),
            ]);

            if ($request->input('remark') == null) {
                $payment->remark = 'Balance Added';
            } else {
                $payment->remark = 'Balance Added - ' . $request->input('remark');
            }
            $payment->employee()->associate($employee);
            $payment->save();
        }

        if ($request->hasFile('photo')) {
            // Save the photo file
            $fileName = $request->file('photo')->getClientOriginalName();
            $fileExtension = $request->file('photo')->getClientOriginalExtension();
            $fileName = chop($fileName, '.' . $fileExtension);
            $fileNameToStore = $fileName . '_' . $employee->id . '_' . time() . '.' . $fileExtension;
            $path = $request->file('photo')->storeAs('public/employee', $fileNameToStore);
            $employee->photo = $fileNameToStore;
            $employee->save();
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        $user->employee()->save($employee);
        $user->save();

        return redirect(route('employees.index'));
    }

    public function addbalance(Request $request, $id)
    {
        if($request->isMethod('post'))
        {
            $this->validate($request, [
                'wallet_balance' => 'required',
            ]);

            // add balance to employee's account
            $employee = Employee::findorfail($id);
            $wallet_balance = $employee->wallet_balance + $request->input('wallet_balance');
            $employee->wallet_balance = $wallet_balance;
            $employee->save();

            $payment = Payment::create([
                'date' => $request->input('date'),
                'payment_mode' => $request->input('payment_mode'),
                'amount' => $request->input('wallet_balance'),
            ]);

            if ($request->input('remark') == null) {
                $payment->remark = 'Balance Added';
            } else {
                $payment->remark = 'Balance Added - ' . $request->input('remark');
            }
            $payment->employee()->associate($employee);
            $payment->save();

            return redirect(route('employees.index'));
        }

        $employee = Employee::findorfail($id);

        return view('employees.addbalance', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $employee = Employee::findorfail($id);

        return view('employees.edit', compact('employee'));
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
        $this->validate($request, [
            'name' => 'required',
            'code' => 'required',
            'number' => 'required',
            'email' => 'required',
            'wallet_balance' => 'required',
        ]);

        $employee = Employee::findorfail($id);

        $employee->update([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'number' => $request->input('number'),
            'email' => $request->input('email'),
            'wallet_balance' => $request->input('wallet_balance'),
        ]);

        if ($request->hasFile('photo')) {
            // Save the photo file
            $fileName = $request->file('photo')->getClientOriginalName();
            $fileExtension = $request->file('photo')->getClientOriginalExtension();
            $fileName = chop($fileName, $fileExtension);
            $fileNameToStore = $fileName . '_' . $employee->id . '_' . time() . '.' . $fileExtension;
            $path = $request->file('photo')->storeAs('public/employee', $fileNameToStore);
            $employee->photo = $fileNameToStore;
            $employee->save();
        }

        $user = $employee->user;

        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);
        $user->employee()->save($employee);
        $user->save();

        if($request->input('password') != null) {
            $this->validate($request, [
                'password' => 'required|confirmed',
            ]);

            $employee->update([
                'password' => Hash::make($request->input('password')),
            ]);

            $user->update([
                'password' => Hash::make($request->input('password')),
            ]);
        }

        return redirect(route('employees.index'));
    }

    public function wallet()
    {
        $payments = auth()->user()->employee->payments;

        return view('wallet.index', compact('payments'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $employee = Employee::findorfail($id);

        if ($employee->photo !== null) {
            // Delete old photo file
            $file_path = public_path('storage/employee/' . $employee->photo);
            @unlink($file_path);
        }

        $employee->user()->first()->delete();

        $employee->delete();

        return redirect(route('employees.index'));
    }
}
