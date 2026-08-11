<?php

namespace App\Http\Controllers;

use App\Models\RoadSign;
use App\Http\Requests\StoreRoadSignRequest;
use App\Http\Requests\UpdateRoadSignRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoadSignController extends Controller
{
    /**
     * The available road sign types.
     *
     * @var list<string>
     */
    private const TYPES = [
        'warning',
        'regulatory',
        'road_markings',
        'information',
        'road_signs',
        'traffic_signals',
        'hand_signals',
        'other',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('RoadSigns', [
            'roadSigns' => RoadSign::query()
                ->latest()
                ->get(['id', 'name', 'type', 'description', 'image_path', 'is_active'])
                ->toArray(),
            'types' => self::TYPES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoadSignRequest $request): RedirectResponse
    {
        RoadSign::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Road sign created.')]);

        return to_route('road-signs.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoadSignRequest $request, RoadSign $roadSign): RedirectResponse
    {
        $roadSign->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Road sign updated.')]);

        return to_route('road-signs.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoadSign $roadSign): RedirectResponse
    {
        $roadSign->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Road sign deleted.')]);

        return to_route('road-signs.index');
    }
}
