<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaticAssetTest extends TestCase
{
    public function test_static_asset_without_login()
    {
        $response = $this->getJson(route('static-asset', 'static-asset/unknown-file.jpg'));

        $response->assertUnauthorized();
    }

    public function test_static_asset_not_found()
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('static-asset', 'static-asset/unknown-file.jpg'));

        $response->assertNotFound();
    }

    private function uploadFile($path, $file): void
    {
        Storage::fake();
        Storage::disk()->put($path, $file);
        Storage::assertExists($path);

        Storage::shouldReceive('exists')
            ->once()
            ->with($path)
            ->andReturn(true);

        Storage::shouldReceive('response')
            ->with($path)
            ->andReturn(Response::stream(function () use ($file) {
                return $file;
            }, 200, ['Content-Type' => $file->getMimeType()]));

        Storage::shouldReceive('download')
            ->with($path)
            ->andReturn(Response::stream(function () use ($file) {
                return $file;
            }, 200, [
                'Content-Type' => $file->getMimeType(),
                'Content-disposition' => 'attachment; filename=' . $file->hashName()
            ]));

    }

    public function test_static_asset_return_inline()
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('image.jpg');

        $this->uploadFile($path = 'images/' . $file->hashName(), $file);

        $response = $this->actingAs($user)->get(route('static-asset', $path));

        $response->assertOk()->assertHeader('Content-Type', $file->getMimeType());
    }

    public function test_static_asset_download()
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('image.jpg');

        $this->uploadFile($path = 'tickets/' . $file->hashName(), $file);

        $response = $this->actingAs($user)->get(route('static-asset', ['path' => $path, 'download' => true]));

        $response->assertOk()->assertDownload($file->hashName());
    }
}
