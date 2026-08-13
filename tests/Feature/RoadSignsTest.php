<?php

use App\Models\RoadSign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(\Tests\TestCase::class);
uses(RefreshDatabase::class);

it('stores a base64 road sign image as a saved public file', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/road-signs', [
        'name' => 'Turn Left',
        'type' => 'regulatory',
        'description' => 'Proceed leftward only when safe.',
        'image_path' => 'data:image/png;base64,'.base64_encode(random_bytes(256)),
    ]);

    $response->assertRedirect();

    $roadSign = RoadSign::query()->firstOrFail();

    expect($roadSign->image_path)
        ->toStartWith('/storage/road-signs/');

    expect(Storage::disk('public')->exists(str_replace('/storage/', '', $roadSign->image_path)))
        ->toBeTrue();
});
