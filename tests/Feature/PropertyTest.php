<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Notifications\ContactRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PropertyTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Vérifie la non existence d'un lien
     * @return void
     */
    public function test_send_not_found_on_non_existent_property(): void
    {
        $response = $this->get('/biens/fugit-quo-enim-quidem-ut-magni-omnis-44');

        $response->assertStatus(404);
    }

    /**
     * Vérifie que l'on est bien rediirigé avec un mauvais slug + bon id
     * @return void
     */
    public function test_send_ok_on_bad_slug_property(): void
    {
        /** @var Property $property */
        $property = Property::factory()->create();
        $response = $this->get('/biens/fugit-quo-enim-quidem-ut-magni-omnis-' . $property->id);
        $response->assertRedirectToRoute('property.show', ['property' => $property->id, 'slug' => $property->getSlug()]);
    }

    /**
     * Vérifie qu'on a bien le bon slug+id
     * @return void
     */
    public function test_send_ok_on_property(): void
    {
        /** @var Property $property */
        $property = Property::factory()->create();
        $response = $this->get("/biens/{$property->getSlug()}-{$property->id}");
        $response->assertOk();
        $response->assertSee($property->title);
    }

    public function test_error_on_contact():void
    {
        /** @var Property $property */
        $property = Property::factory()->create();
        $response = $this->post("/biens/{$property->id}/contact", [
            "firstname" => "John",
            "lastname" => "Doe",
            "phone" => "0000000000",
            "email" => "doe",
            "message" => "dezaezaeoe",
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasInput('email', 'doe');
    }

    public function test_ok_on_contact():void
    {
        Notification::fake();

        /** @var Property $property */
        $property = Property::factory()->create();
        $response = $this->post("/biens/{$property->id}/contact", [
            "firstname" => "John",
            "lastname" => "Doe",
            "phone" => "0000000000",
            "email" => "doe@demo.fr",
            "message" => "dezaezaeoe",
        ]);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
        Notification::assertCount(1);
        Notification::assertSentOnDemand(ContactRequestNotification::class);
    }
}
