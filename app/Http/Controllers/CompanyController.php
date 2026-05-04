<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyInfo;

class CompanyController extends Controller
{
    public function edit()
    {
        $company = CompanyInfo::first();
        if (!$company) {
            $company = new CompanyInfo();
        }
        return view('company.index', compact('company'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:120',
            'address' => 'nullable|string|max:500',
        ]);

        $company = CompanyInfo::first();
        if (!$company) {
            $company = new CompanyInfo();
        }

        $company->fill($request->only(['name', 'phone', 'email', 'address']));
        $company->save();

        return redirect()->back()->with('success', 'Company settings updated successfully.');
    }
}
