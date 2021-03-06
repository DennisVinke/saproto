<?php

namespace Proto\Models;

use Illuminate\Database\Eloquent\Model;

use Youtube;

class NarrowcastingItem extends Model
{
    protected $table = 'narrowcasting';

    /**
     * @return mixed The image associated with this item..
     */
    public function image()
    {
        return $this->belongsTo('Proto\Models\StorageEntry');
    }

    public function video()
    {
        if ($this->youtube_id !== null) {
            return Youtube::getVideoInfo($this->youtube_id);
        }
        return null;
    }

    protected $guarded = ['id'];
}
