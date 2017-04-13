<?php

/*
 * This file is part of the TAS system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Traits;

use Image;
use Storage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait WithPicture
{
    public function setPictureAttribute(UploadedFile $file)
    {
        $picture = Image::make($file);
        $uuid = Uuid::uuid4()->toString();
        $storage = Storage::disk('public');

        $pictureTarget = sprintf('avatars/%s.picture.jpg', $uuid);
        $thumbTarget = sprintf('avatars/%s.thumb.jpg', $uuid);

        $storage->put($pictureTarget, $picture->fit(512)->stream('jpg', 75));
        $storage->put($thumbTarget, $picture->fit(64)->stream('jpg', 50));

        $this->deletePicture();

        $this->attributes['picture_path'] = $pictureTarget;
        $this->attributes['thumbnail_path'] = $thumbTarget;
    }

    public function deletePicture()
    {
        Storage::disk('public')->delete([
            $this->picture_path,
            $this->thumbnail_path
        ]);
    }
}
