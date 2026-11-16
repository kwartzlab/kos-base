<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uploading a cropped photo saves the original as a JPEG plus three thumbnail sizes, and attaches
 * it to the member's profile.
 */
class ImageControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $directory;

    private ?string $savedName = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = public_path('storage/images/users/');
    }

    /**
     * Members upload their own profile photo from the cropping tool. The site then shows the
     * thumbnails in member lists and on the gatekeeper and team pages.
     *
     * Expects: the original saved as .jpeg, plus 512, 256 and 128 px square thumbnails (checked by
     * reading the real image size), and the member's photo field set to the new file name.
     * Upgrade risk: guards the intervention/image package, which has a different API in v3.
     * Note: writes real files to public/storage; tearDown deletes them. Uploading for someone else
     * (manage-users only) is not covered.
     */
    public function test_uploading_a_photo_saves_it_in_every_size_and_attaches_it_to_the_member()
    {
        $member = User::factory()->create(['photo' => '']);

        $response = $this->actingAs($member)->post('/image-crop', [
            'image' => 'data:image/png;base64,'.base64_encode($this->png(600, 400)),
            'photo_type' => 'users',
            'id' => $member->id,
        ]);

        $response->assertOk()->assertJson(['success' => 'done']);
        $this->savedName = $response->json('filename');

        $this->assertFileExists($this->directory.$this->savedName.'.jpeg');
        foreach ([512, 256, 128] as $size) {
            $file = $this->directory.$this->savedName."-{$size}px.jpeg";
            $this->assertFileExists($file);
            $this->assertSame([$size, $size], array_slice(getimagesize($file), 0, 2));
        }
        $this->assertSame($this->savedName, $member->fresh()->photo);
    }

    protected function tearDown(): void
    {
        if ($this->savedName !== null) {
            foreach (glob($this->directory.$this->savedName.'*') as $file) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    private function png(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 120, 40));
        ob_start();
        imagepng($image);

        return ob_get_clean();
    }
}
