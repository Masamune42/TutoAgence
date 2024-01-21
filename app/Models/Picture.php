<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use League\Glide\Urls\UrlBuilderFactory;

class Picture extends Model
{
    use HasFactory;

    protected $fillable = ['filename'];

    /**
     * Fonction appelée automatiquement lors de l'initialisation du modèle
     * Permet de supprimer physiquement les images quand on supprime un élément avec ce modèle
     *
     * @return void
     */
    protected static function booted(): void
    {
        // On détecte l'évènement de suppression
        static::deleting(function (Picture $picture)
        {
            // On supprime physiquement l'image
            Storage::disk('public')->delete($picture->filename);
        });
    }

    public function getImageUrl(?int $width = null, ?int $height = null): string
    {
        if($width === null)
        {
            return Storage::disk('public')->url($this->filename);
        }
        $urlBuilder = UrlBuilderFactory::create('/images/', config('glide.key'));
        return $urlBuilder->getUrl($this->filename, ['w' => $width, 'h' => $height, 'fit' => 'crop']);
    }
}
