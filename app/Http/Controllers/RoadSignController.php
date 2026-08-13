<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoadSignRequest;
use App\Http\Requests\UpdateRoadSignRequest;
use App\Models\RoadSign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type');
        $perPage = max(1, min(50, (int) $request->query('per_page', 10)));

        $query = RoadSign::query()
            ->where('is_active', true)
            ->when($type && in_array($type, self::TYPES, true), fn ($query) => $query->where('type', $type))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }));

        return Inertia::render('RoadSigns', [
            'roadSigns' => $query->latest()->paginate($perPage)->withQueryString(),
            'types' => self::TYPES,
            'filters' => [
                'search' => $search,
                'type' => $type && in_array($type, self::TYPES, true) ? $type : '',
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoadSignRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['image_path'] = $this->saveImageToStorage($data['image_path'] ?? null);

        RoadSign::query()->create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Road sign created.')]);

        return to_route('road-signs.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoadSignRequest $request, RoadSign $roadSign): RedirectResponse
    {
        $data = $request->validated();

        if (array_key_exists('image_path', $data)) {
            $data['image_path'] = $this->saveImageToStorage($data['image_path']);
        }

        $roadSign->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Road sign updated.')]);

        return to_route('road-signs.index');
    }

    /**
     * Store a base64 image data URL to the public disk and return a web-safe path.
     */
    private function saveImageToStorage(?string $imagePath): ?string
    {
        if (blank($imagePath) || ! str_contains($imagePath, 'data:image/')) {
            return $imagePath;
        }

        if (! preg_match('/^data:image\/(?<extension>jpeg|jpg|png|gif|webp);base64,(?<data>.+)$/i', $imagePath, $matches)) {
            return $imagePath;
        }

        $decodedImage = base64_decode($matches['data'], true);

        if ($decodedImage === false || strlen($decodedImage) === 0) {
            return $imagePath;
        }

        $maxBytes = 5 * 1024 * 1024;
        if (strlen($decodedImage) > $maxBytes) {
            abort(422, 'The image is too large. Please upload an image smaller than 5 MB.');
        }

        $extension = strtolower($matches['extension']);
        $filename = sprintf('%s.%s', Str::uuid(), $extension === 'jpeg' ? 'jpg' : $extension);

        Storage::disk('public')->makeDirectory('road-signs');
        Storage::disk('public')->put('road-signs/'.$filename, $decodedImage);

        return '/storage/road-signs/'.$filename;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoadSign $roadSign): RedirectResponse
    {
        $roadSign->update(['is_active' => false]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Road sign removed.')]);

        return to_route('road-signs.index');
    }
}
