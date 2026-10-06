<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    /**
     * Show payment settings.
     */
    public function edit(): View
    {
        $setting = PaymentSetting::current();

        return view(
            'admin.payment-settings.edit',
            compact('setting')
        );
    }

    /**
     * Update payment settings.
     */
    public function update(
        Request $request
    ): RedirectResponse {

        $validated = $request->validate([
            'bkash_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'bkash_account_type' => [
                'nullable',
                'in:personal,merchant,agent',
            ],

            'nagad_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'nagad_account_type' => [
                'nullable',
                'in:personal,merchant,agent',
            ],

            'payment_instruction' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $bkashEnabled = $request->boolean(
            'bkash_enabled'
        );

        $nagadEnabled = $request->boolean(
            'nagad_enabled'
        );

        if (
            $bkashEnabled &&
            empty($validated['bkash_number'])
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'bkash_number' => 'bKash number is required when bKash is enabled.',
                ]);
        }

        if (
            $nagadEnabled &&
            empty($validated['nagad_number'])
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'nagad_number' => 'Nagad number is required when Nagad is enabled.',
                ]);
        }

        $setting = PaymentSetting::current();

        $setting->update([
            'bkash_number' => $validated[
                'bkash_number'
            ] ?? null,

            'bkash_account_type' => $validated[
                'bkash_account_type'
            ] ?? null,

            'nagad_number' => $validated[
                'nagad_number'
            ] ?? null,

            'nagad_account_type' => $validated[
                'nagad_account_type'
            ] ?? null,

            'bkash_enabled' => $bkashEnabled,

            'nagad_enabled' => $nagadEnabled,

            'payment_instruction' => $validated[
                'payment_instruction'
            ] ?? null,
        ]);

        return back()->with(
            'success',
            'Payment settings updated successfully.'
        );
    }
}