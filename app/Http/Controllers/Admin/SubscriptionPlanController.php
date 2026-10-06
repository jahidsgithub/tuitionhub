<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    /**
     * Show all subscription plans.
     */
    public function index(): View
    {
        $plans = SubscriptionPlan::query()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view(
            'admin.subscription-plans.index',
            compact('plans')
        );
    }

    /**
     * Show edit form.
     */
    public function edit(
        SubscriptionPlan $plan
    ): View {

        return view(
            'admin.subscription-plans.edit',
            compact('plan')
        );
    }

    /**
     * Update subscription plan.
     */
    public function update(
        Request $request,
        SubscriptionPlan $plan
    ): RedirectResponse {

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'duration_days' => [
                'required',
                'integer',
                'min:1',
                'max:3650',
            ],

            'application_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],

            'is_featured' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'duration_days' => $validated['duration_days'],
            'application_limit' => $validated['application_limit'] ?? null,
            'sort_order' => $validated['sort_order'],
            'is_featured' => $request->boolean('is_featured'),
            'status' => $request->boolean('status'),
        ]);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with(
                'success',
                'Subscription plan updated successfully.'
            );
    }

    /**
     * Toggle plan status.
     */
    public function toggle(
        SubscriptionPlan $plan
    ): RedirectResponse {

        $plan->update([
            'status' => ! $plan->status,
        ]);

        return back()->with(
            'success',
            'Subscription plan status updated.'
        );
    }
}